<?php
/**
 * Material Service
 * Database queries untuk tabel materials
 * 
 * IPO Pattern (Aplikasi.docx):
 *   Input: item_bahan, qty, harga, kategori, satuan, supplier_id
 *   Proses: Validasi → CRUD → simpan → log
 *   Output: data bahan baku
 */

require_once __DIR__ . '/../config/database.php';

class MaterialService {

    /**
     * List semua materials dengan pagination (untuk katalog UMKM)
     */
    public static function list($page = 1, $limit = 10) {
        $db = getDB();
        $offset = ($page - 1) * $limit;

        // Data
        $stmt = $db->prepare("
            SELECT m.*, u.name AS supplier_name 
            FROM materials m 
            JOIN users u ON m.supplier_id = u.id 
            ORDER BY m.name ASC 
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue('limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue('offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll();

        // Total count
        $countStmt = $db->query("SELECT COUNT(*) AS total FROM materials");
        $total = (int) $countStmt->fetch()['total'];

        return ['data' => $data, 'total' => $total];
    }

    /**
     * List materials milik supplier tertentu
     */
    public static function getBySupplier($supplierId, $page = 1, $limit = 10) {
        $db = getDB();
        $offset = ($page - 1) * $limit;

        $stmt = $db->prepare("
            SELECT * FROM materials 
            WHERE supplier_id = :sid 
            ORDER BY created_at DESC
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue('sid', (int)$supplierId, PDO::PARAM_INT);
        $stmt->bindValue('limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue('offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll();

        $countStmt = $db->prepare("SELECT COUNT(*) AS total FROM materials WHERE supplier_id = :sid");
        $countStmt->execute(['sid' => $supplierId]);
        $total = (int) $countStmt->fetch()['total'];

        return ['data' => $data, 'total' => $total];
    }

    /**
     * Cari material berdasarkan ID
     */
    public static function findById($id) {
        $db = getDB();
        $stmt = $db->prepare("SELECT m.*, u.name AS supplier_name FROM materials m JOIN users u ON m.supplier_id = u.id WHERE m.id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Buat material baru
     */
    public static function create($data) {
        $db = getDB();
        $code = self::generateCode();

        $stmt = $db->prepare("
            INSERT INTO materials (material_code, name, category, price, stock, unit, icon, supplier_id)
            VALUES (:code, :name, :category, :price, :stock, :unit, :icon, :supplier_id)
        ");
        $stmt->execute([
            'code'        => $code,
            'name'        => $data['name'],
            'category'    => $data['category'],
            'price'       => $data['price'],
            'stock'       => $data['stock'],
            'unit'        => $data['unit'],
            'icon'        => $data['icon'] ?? self::getIcon($data['category']),
            'supplier_id' => $data['supplier_id']
        ]);

        $id = (int) $db->lastInsertId();
        return self::findById($id);
    }

    /**
     * Update material
     */
    public static function update($id, $data) {
        $db = getDB();
        $stmt = $db->prepare("
            UPDATE materials 
            SET name = :name, category = :category, price = :price, 
                stock = :stock, unit = :unit, icon = :icon
            WHERE id = :id
        ");
        $stmt->execute([
            'name'     => $data['name'],
            'category' => $data['category'],
            'price'    => $data['price'],
            'stock'    => $data['stock'],
            'unit'     => $data['unit'],
            'icon'     => $data['icon'] ?? self::getIcon($data['category']),
            'id'       => $id
        ]);

        return self::findById($id);
    }

    /**
     * Hapus material
     */
    public static function delete($id) {
        $db = getDB();
        $stmt = $db->prepare("DELETE FROM materials WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Kurangi stok setelah order di-approve
     */
    public static function reduceStock($id, $qty) {
        $db = getDB();
        $stmt = $db->prepare("UPDATE materials SET stock = stock - :qty WHERE id = :id AND stock >= :qty2");
        $stmt->execute(['qty' => $qty, 'id' => $id, 'qty2' => $qty]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Generate kode material unik
     */
    private static function generateCode() {
        $db = getDB();
        $stmt = $db->query("SELECT MAX(id) AS max_id FROM materials");
        $row = $stmt->fetch();
        $nextId = ($row['max_id'] ?? 0) + 1;
        return 'MAT-' . str_pad($nextId, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Icon berdasarkan kategori
     */
    private static function getIcon($category) {
        $icons = [
            'Bahan Pokok'    => 'ph-package',
            'Cair'           => 'ph-drop',
            'Bumbu & Rempah' => 'ph-plant',
            'Kemasan'        => 'ph-box-arrow-up',
            'Lainnya'        => 'ph-cube'
        ];
        return $icons[$category] ?? 'ph-package';
    }
}
