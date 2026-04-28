<?php
/**
 * Order Controller
 * Handles order lifecycle: create → review → approve/reject → payment
 * 
 * Sesuai Aplikasi.docx:
 * - Semua transaksi → payment request ke SmartBank
 * - SupplierHub tidak mengelola saldo langsung
 * - Fee supplier 3%
 */

require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../models/Material.php';
require_once __DIR__ . '/../services/SmartBankService.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../middleware/GatewayMiddleware.php';
require_once __DIR__ . '/../middleware/LoggerMiddleware.php';

class OrderController {

    /**
     * UMKM: Create new order (checkout)
     * IPO: items[] → validate stock → create order (pending)
     */
    public static function checkout($data, $umkm_id) {
        // Validate
        if (empty($data['items']) || !is_array($data['items'])) {
            return ['status' => 'error', 'message' => 'Items pesanan wajib diisi.'];
        }

        if (empty($data['supplier_id'])) {
            return ['status' => 'error', 'message' => 'Supplier ID wajib diisi.'];
        }

        // Validate each item
        foreach ($data['items'] as $item) {
            if (empty($item['material_id']) || empty($item['qty']) || $item['qty'] <= 0) {
                return ['status' => 'error', 'message' => 'Data item tidak valid.'];
            }
        }

        try {
            $result = Order::create($umkm_id, $data['supplier_id'], $data['items']);
            return [
                'status'  => 'success',
                'message' => 'Pesanan berhasil dibuat. Menunggu konfirmasi supplier.',
                'data'    => $result
            ];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => 'Gagal membuat pesanan: ' . $e->getMessage()];
        }
    }

    /**
     * Supplier: Get pending orders
     */
    public static function listPending($supplier_id) {
        $orders = Order::getPending($supplier_id);
        return [
            'status'  => 'success',
            'message' => 'Pesanan masuk berhasil diambil.',
            'data'    => $orders
        ];
    }

    /**
     * Supplier: Get order detail with items
     */
    public static function detail($order_id) {
        $order = Order::getWithItems($order_id);
        if (!$order) {
            return ['status' => 'error', 'message' => 'Pesanan tidak ditemukan.'];
        }

        // Check stock for each item
        $allSufficient = true;
        foreach ($order['items'] as &$item) {
            $item['sufficient'] = $item['current_stock'] >= $item['qty'];
            if (!$item['sufficient']) $allSufficient = false;
        }
        $order['stock_sufficient'] = $allSufficient;

        return [
            'status' => 'success',
            'data'   => $order
        ];
    }

    /**
     * Supplier: Approve order and trigger payment
     * IPO: order_id → validate stock → reduce stock → payment request → completed
     */
    public static function approve($order_id, $supplier_id) {
        // Get order
        $order = Order::getWithItems($order_id);
        if (!$order) {
            return ['status' => 'error', 'message' => 'Pesanan tidak ditemukan.'];
        }

        if ($order['supplier_id'] != $supplier_id) {
            return ['status' => 'error', 'message' => 'Akses ditolak.'];
        }

        if ($order['status'] !== 'pending') {
            return ['status' => 'error', 'message' => 'Pesanan sudah diproses sebelumnya.'];
        }

        // Check stock
        foreach ($order['items'] as $item) {
            if ($item['current_stock'] < $item['qty']) {
                return [
                    'status'  => 'error',
                    'message' => "Stok {$item['material_name']} tidak mencukupi. Tersedia: {$item['current_stock']}, Dibutuhkan: {$item['qty']}"
                ];
            }
        }

        // Send payment request to SmartBank (Aturan #3: Semua transaksi → SmartBank)
        $paymentResponse = SmartBankService::pay(
            $order['umkm_id'],
            $order['subtotal'],
            $order['fee_supplier'],
            "Payment for order {$order['order_code']} from {$order['umkm_name']}"
        );

        if ($paymentResponse['status'] !== 'success') {
            return [
                'status'  => 'error',
                'message' => 'Pembayaran ditolak oleh SmartBank: ' . ($paymentResponse['message'] ?? 'Unknown error')
            ];
        }

        // Reduce stock
        foreach ($order['items'] as $item) {
            Material::reduceStock($item['material_id'], $item['qty']);
        }

        // Update order status
        $smartbankRef = $paymentResponse['data']['payment_id'] ?? null;
        Order::approve($order_id, $smartbankRef);

        return [
            'status'  => 'success',
            'message' => "Pesanan {$order['order_code']} berhasil diapprove. Payment request telah dikirim ke SmartBank.",
            'data'    => [
                'order_code'    => $order['order_code'],
                'subtotal'      => $order['subtotal'],
                'fee_supplier'  => $order['fee_supplier'],
                'total'         => $order['total'],
                'smartbank_ref' => $smartbankRef,
                'payment_status' => $paymentResponse['status']
            ]
        ];
    }

    /**
     * Supplier: Reject order
     */
    public static function reject($order_id, $supplier_id) {
        $order = Order::findById($order_id);
        if (!$order || $order['supplier_id'] != $supplier_id) {
            return ['status' => 'error', 'message' => 'Pesanan tidak ditemukan.'];
        }

        Order::reject($order_id);
        return [
            'status'  => 'success',
            'message' => 'Pesanan berhasil ditolak.'
        ];
    }

    /**
     * Supplier: Get completed orders (laporan tagihan)
     */
    public static function listCompleted($supplier_id) {
        $orders = Order::getCompleted($supplier_id);
        return [
            'status'  => 'success',
            'message' => 'Laporan tagihan berhasil diambil.',
            'data'    => $orders
        ];
    }

    /**
     * UMKM: Get order history
     */
    public static function history($umkm_id) {
        $orders = Order::getByUmkm($umkm_id);
        return [
            'status'  => 'success',
            'message' => 'Riwayat pesanan berhasil diambil.',
            'data'    => $orders
        ];
    }
}
