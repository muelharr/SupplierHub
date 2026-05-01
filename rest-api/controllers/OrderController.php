<?php
/**
 * Order Controller
 * Fitur 3: Order Bahan Baku + Fitur 4: Konfirmasi Order
 * 
 * IPO Pattern (Aplikasi.docx):
 *   Input: supplier_id, items[] (checkout) / order_id (approve/reject)
 *   Proses: Validasi → hitung biaya → fee 3% → kirim payment ke SmartBank
 *   Output: order data + payment status
 * 
 * Aturan #3: Semua output transaksi = payment request
 * Aturan #4: SmartBank sebagai pusat kontrol
 * Aturan #7: Fee Supplier = 3%
 */

require_once __DIR__ . '/../helpers/Response.php';
require_once __DIR__ . '/../helpers/Validator.php';
require_once __DIR__ . '/../services/OrderService.php';
require_once __DIR__ . '/../services/MaterialService.php';
require_once __DIR__ . '/../services/SmartBankService.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class OrderController {

    /**
     * GET /api/v1/orders
     * List orders (filtered by role)
     * Query: ?status=pending|completed|rejected&page=1&limit=10
     */
    public static function index($user) {
        $page = (int) ($_GET['page'] ?? 1);
        $limit = (int) ($_GET['limit'] ?? 10);
        $status = $_GET['status'] ?? null;

        $result = OrderService::list($user['user_id'], $user['role'], $status, $page, $limit);

        Response::paginated($result['data'], $page, $limit, $result['total'], 'Data pesanan berhasil diambil.');
    }

    /**
     * GET /api/v1/orders/:id
     * Detail order + items + stock check
     */
    public static function show($user, $id) {
        $order = OrderService::findWithItems($id);

        if (!$order) {
            Response::error('Pesanan tidak ditemukan.', 404);
        }

        // Cek akses: hanya UMKM pemesan atau supplier tujuan
        if ($order['umkm_id'] != $user['user_id'] && $order['supplier_id'] != $user['user_id']) {
            Response::error('Akses ditolak.', 403);
        }

        Response::success($order, 'Detail pesanan berhasil diambil.');
    }

    /**
     * POST /api/v1/orders
     * Buat pesanan baru / checkout (UMKM only)
     * 
     * IPO:
     *   Input: supplier_id, items: [{material_id, qty}]
     *   Proses: Validasi → hitung subtotal → fee 3% → create order (pending)
     *   Output: order_code, subtotal, fee_supplier, total
     */
    public static function store($user) {
        AuthMiddleware::requireRole($user, 'umkm');

        $input = Validator::getJsonBody();

        // Validasi
        $errors = Validator::validate($input, [
            'supplier_id' => 'required|integer',
            'items'       => 'required|array',
        ]);
        if (!empty($errors)) {
            Response::error('Validasi gagal.', 400, $errors);
        }

        // Validasi setiap item
        if (empty($input['items'])) {
            Response::error('Items pesanan tidak boleh kosong.', 400);
        }

        foreach ($input['items'] as $i => $item) {
            if (empty($item['material_id']) || empty($item['qty']) || $item['qty'] <= 0) {
                Response::error("Item ke-" . ($i + 1) . " tidak valid. material_id dan qty (> 0) wajib diisi.", 400);
            }
        }

        try {
            $result = OrderService::create($user['user_id'], $input['supplier_id'], $input['items']);
            Response::success($result, 'Pesanan berhasil dibuat. Menunggu konfirmasi supplier.', 201);
        } catch (Exception $e) {
            Response::error('Gagal membuat pesanan: ' . $e->getMessage(), 500);
        }
    }

    /**
     * PATCH /api/v1/orders/:id/approve
     * Approve order (Supplier only)
     * 
     * IPO:
     *   Input: order_id (dari URL)
     *   Proses: Cek stok → kirim payment ke SmartBank (Aturan #3) → reduce stock → update status
     *   Output: smartbank_ref, payment_status
     */
    public static function approve($user, $id) {
        AuthMiddleware::requireRole($user, 'supplier');

        // Get order detail
        $order = OrderService::findWithItems($id);
        if (!$order) {
            Response::error('Pesanan tidak ditemukan.', 404);
        }

        // Cek ownership
        if ($order['supplier_id'] != $user['user_id']) {
            Response::error('Akses ditolak. Pesanan bukan untuk supplier Anda.', 403);
        }

        // Cek status
        if ($order['status'] !== 'pending') {
            Response::error('Pesanan sudah diproses sebelumnya.', 409);
        }

        // Cek stok setiap item
        foreach ($order['items'] as $item) {
            if ($item['current_stock'] < $item['qty']) {
                Response::error(
                    "Stok {$item['material_name']} tidak mencukupi. Tersedia: {$item['current_stock']}, Dibutuhkan: {$item['qty']}",
                    400
                );
            }
        }

        // Kirim payment request ke SmartBank (Aturan #3 & #4)
        $paymentResponse = SmartBankService::pay(
            $order['umkm_id'],
            $order['subtotal'],
            $order['fee_supplier'],
            "Payment for order {$order['order_code']} from {$order['umkm_name']}"
        );

        if ($paymentResponse['status'] !== 'success') {
            Response::error('Pembayaran ditolak oleh SmartBank: ' . ($paymentResponse['message'] ?? 'Unknown error'), 402);
        }

        // Reduce stock
        foreach ($order['items'] as $item) {
            MaterialService::reduceStock($item['material_id'], $item['qty']);
        }

        // Update order status
        $smartbankRef = $paymentResponse['data']['payment_id'] ?? null;
        OrderService::approve($id, $smartbankRef);

        Response::success([
            'order_code'     => $order['order_code'],
            'subtotal'       => $order['subtotal'],
            'fee_supplier'   => $order['fee_supplier'],
            'total'          => $order['total'],
            'smartbank_ref'  => $smartbankRef,
            'payment_status' => $paymentResponse['status']
        ], "Pesanan {$order['order_code']} berhasil di-approve. Payment request telah dikirim ke SmartBank.");
    }

    /**
     * PATCH /api/v1/orders/:id/reject
     * Reject order (Supplier only)
     */
    public static function reject($user, $id) {
        AuthMiddleware::requireRole($user, 'supplier');

        $order = OrderService::findById($id);
        if (!$order || $order['supplier_id'] != $user['user_id']) {
            Response::error('Pesanan tidak ditemukan.', 404);
        }

        if ($order['status'] !== 'pending') {
            Response::error('Pesanan sudah diproses sebelumnya.', 409);
        }

        OrderService::reject($id);

        Response::success(null, 'Pesanan berhasil ditolak.');
    }
}
