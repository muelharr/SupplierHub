<?php
/**
 * Dashboard Controller
 * Provides statistics for Supplier and UMKM dashboards
 */

require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../models/Material.php';

class DashboardController {

    /**
     * Get supplier dashboard statistics
     */
    public static function supplierStats($supplier_id) {
        // Stock valuation
        $materials = Material::getBySupplier($supplier_id);
        $stockValuation = 0;
        foreach ($materials as $m) {
            $stockValuation += $m['price'] * $m['stock'];
        }

        // Order stats
        $orderStats = Order::getSupplierStats($supplier_id);

        return [
            'status' => 'success',
            'data'   => [
                'stock_valuation' => $stockValuation,
                'total_items'     => count($materials),
                'pending_orders'  => $orderStats['pending_orders'],
                'total_revenue'   => $orderStats['total_revenue']
            ]
        ];
    }

    /**
     * Get UMKM dashboard statistics
     */
    public static function umkmStats($umkm_id) {
        $stats = Order::getUmkmStats($umkm_id);

        return [
            'status' => 'success',
            'data'   => [
                'total_orders' => $stats['total_orders'],
                'total_spent'  => $stats['total_spent']
            ]
        ];
    }
}
