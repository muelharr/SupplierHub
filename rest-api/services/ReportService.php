<?php
/**
 * Report Service
 * Dashboard statistics queries
 * (Aturan #7: Analytics hanya membaca data)
 */

require_once __DIR__ . '/../config/database.php';

class ReportService {

    /**
     * Statistik dashboard supplier
     */
    public static function supplierStats($supplierId) {
        $db = getDB();

        // Stock valuation & total items
        $stmt = $db->prepare("SELECT COUNT(*) AS total_items, COALESCE(SUM(price * stock), 0) AS stock_valuation FROM materials WHERE supplier_id = :sid");
        $stmt->execute(['sid' => $supplierId]);
        $stock = $stmt->fetch();

        // Pending orders
        $stmt = $db->prepare("SELECT COUNT(*) AS cnt FROM orders WHERE supplier_id = :sid AND status = 'pending'");
        $stmt->execute(['sid' => $supplierId]);
        $pending = (int) $stmt->fetch()['cnt'];

        // Total revenue (completed)
        $stmt = $db->prepare("SELECT COALESCE(SUM(total), 0) AS total FROM orders WHERE supplier_id = :sid AND status = 'completed'");
        $stmt->execute(['sid' => $supplierId]);
        $revenue = (int) $stmt->fetch()['total'];

        return [
            'stock_valuation' => (int) $stock['stock_valuation'],
            'total_items'     => (int) $stock['total_items'],
            'pending_orders'  => $pending,
            'total_revenue'   => $revenue
        ];
    }

    /**
     * Statistik dashboard UMKM
     */
    public static function umkmStats($umkmId) {
        $db = getDB();

        $stmt = $db->prepare("SELECT COUNT(*) AS cnt, COALESCE(SUM(total), 0) AS spent FROM orders WHERE umkm_id = :uid AND status = 'completed'");
        $stmt->execute(['uid' => $umkmId]);
        $row = $stmt->fetch();

        return [
            'total_orders' => (int) $row['cnt'],
            'total_spent'  => (int) $row['spent']
        ];
    }
}
