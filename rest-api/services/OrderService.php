<?php
/**
 * Order Service
 * Database queries untuk tabel orders + order_items
 * 
 * IPO Pattern (Aplikasi.docx):
 *   Input: supplier_id, items[] (material_id, qty)
 *   Proses: Validasi → hitung subtotal → fee supplier 3% → create order → log
 *   Output: order data (order_code, subtotal, fee, total)
 * 
 * Aturan #3: Semua output transaksi = payment request
 * Aturan #7: Fee Supplier = 3%
 */

require_once __DIR__ . '/../config/database.php';

class OrderService {

    /**
     * List orders dengan filter berdasarkan role
     */
    public static function list($userId, $role, $status = null, $page = 1, $limit = 10) {
        $db = getDB();
        $offset = ($page - 1) * $limit;

        // Base query berdasarkan role
        if ($role === 'supplier') {
            $where = "o.supplier_id = :uid";
            $join = "JOIN users u ON o.umkm_id = u.id";
            $select = "u.name AS umkm_name";
        } else {
            $where = "o.umkm_id = :uid";
            $join = "JOIN users u ON o.supplier_id = u.id";
            $select = "u.name AS supplier_name";
        }

        // Filter status
        if ($status) {
            $where .= " AND o.status = :status";
        }

        // Data
        $sql = "SELECT o.*, $select FROM orders o $join WHERE $where ORDER BY o.created_at DESC LIMIT :limit OFFSET :offset";
        $stmt = $db->prepare($sql);
        $stmt->bindValue('uid', (int)$userId, PDO::PARAM_INT);
        $stmt->bindValue('limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue('offset', (int)$offset, PDO::PARAM_INT);
        if ($status) {
            $stmt->bindValue('status', $status);
        }
        $stmt->execute();
        $data = $stmt->fetchAll();

        // Total count
        $countSql = "SELECT COUNT(*) AS total FROM orders o WHERE $where";
        $countStmt = $db->prepare($countSql);
        $countStmt->bindValue('uid', (int)$userId, PDO::PARAM_INT);
        if ($status) {
            $countStmt->bindValue('status', $status);
        }
        $countStmt->execute();
        $total = (int) $countStmt->fetch()['total'];

        return ['data' => $data, 'total' => $total];
    }

    /**
     * Cari order berdasarkan ID
     */
    public static function findById($id) {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM orders WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Cari order dengan items detail (JOIN)
     */
    public static function findWithItems($id) {
        $db = getDB();

        // Get order
        $stmt = $db->prepare("
            SELECT o.*, u.name AS umkm_name, s.name AS supplier_name
            FROM orders o 
            JOIN users u ON o.umkm_id = u.id 
            JOIN users s ON o.supplier_id = s.id
            WHERE o.id = :id
        ");
        $stmt->execute(['id' => $id]);
        $order = $stmt->fetch();

        if (!$order) return null;

        // Get items
        $stmt = $db->prepare("
            SELECT oi.*, m.name AS material_name, m.unit, m.stock AS current_stock, m.icon, m.material_code
            FROM order_items oi 
            JOIN materials m ON oi.material_id = m.id 
            WHERE oi.order_id = :oid
        ");
        $stmt->execute(['oid' => $id]);
        $order['items'] = $stmt->fetchAll();

        // Cek stok per item
        $allSufficient = true;
        foreach ($order['items'] as &$item) {
            $item['sufficient'] = $item['current_stock'] >= $item['qty'];
            if (!$item['sufficient']) $allSufficient = false;
        }
        $order['stock_sufficient'] = $allSufficient;

        return $order;
    }

    /**
     * Buat order baru (checkout)
     * Fee supplier 3% dihitung otomatis (Aturan Keuangan #7)
     */
    public static function create($umkmId, $supplierId, $items) {
        $db = getDB();
        $db->beginTransaction();

        try {
            // Hitung subtotal
            $subtotal = 0;
            foreach ($items as &$item) {
                $stmt = $db->prepare("SELECT price FROM materials WHERE id = :id");
                $stmt->execute(['id' => $item['material_id']]);
                $mat = $stmt->fetch();
                if (!$mat) {
                    throw new Exception("Material ID {$item['material_id']} tidak ditemukan.");
                }
                $item['price_at_order'] = $mat['price'];
                $subtotal += $mat['price'] * $item['qty'];
            }

            // Hitung fee (Aturan Keuangan #7: Fee Supplier 3%)
            $fee = (int) round($subtotal * FEE_SUPPLIER);
            $total = $subtotal + $fee;
            $orderCode = self::generateCode();

            // Insert order
            $stmt = $db->prepare("
                INSERT INTO orders (order_code, umkm_id, supplier_id, status, subtotal, fee_supplier, total)
                VALUES (:code, :umkm, :supplier, 'pending', :subtotal, :fee, :total)
            ");
            $stmt->execute([
                'code'     => $orderCode,
                'umkm'     => $umkmId,
                'supplier' => $supplierId,
                'subtotal' => $subtotal,
                'fee'      => $fee,
                'total'    => $total
            ]);
            $orderId = (int) $db->lastInsertId();

            // Insert order items
            $stmt = $db->prepare("
                INSERT INTO order_items (order_id, material_id, qty, price_at_order)
                VALUES (:order_id, :material_id, :qty, :price)
            ");
            foreach ($items as $item) {
                $stmt->execute([
                    'order_id'    => $orderId,
                    'material_id' => $item['material_id'],
                    'qty'         => $item['qty'],
                    'price'       => $item['price_at_order']
                ]);
            }

            $db->commit();

            return [
                'id'           => $orderId,
                'order_code'   => $orderCode,
                'subtotal'     => $subtotal,
                'fee_supplier' => $fee,
                'total'        => $total,
                'status'       => 'pending'
            ];
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    /**
     * Approve order dan update status
     */
    public static function approve($id, $smartbankRef = null) {
        $db = getDB();
        $stmt = $db->prepare("
            UPDATE orders 
            SET status = 'completed', smartbank_ref = :ref, completed_at = NOW()
            WHERE id = :id AND status = 'pending'
        ");
        $stmt->execute([
            'ref' => $smartbankRef,
            'id'  => $id
        ]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Reject order
     */
    public static function reject($id) {
        $db = getDB();
        $stmt = $db->prepare("UPDATE orders SET status = 'rejected' WHERE id = :id AND status = 'pending'");
        $stmt->execute(['id' => $id]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Generate kode order unik
     */
    private static function generateCode() {
        $db = getDB();
        $stmt = $db->query("SELECT MAX(id) AS max_id FROM orders");
        $row = $stmt->fetch();
        $nextId = ($row['max_id'] ?? 0) + 1;
        return 'ORD-B2B-' . str_pad($nextId, 3, '0', STR_PAD_LEFT);
    }
}
