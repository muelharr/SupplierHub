<?php
/**
 * SmartBank Ledger Model
 * Records all financial transactions (Aturan #7: Analytics hanya membaca)
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';

class Ledger {

    /**
     * Record a transaction entry
     */
    public static function record(array $data): int {
        $db   = getDB();
        $stmt = $db->prepare("
            INSERT INTO sb_ledger
                (from_user_id, to_user_id, type, amount, fee_bank, description, reference_id, source_app, status)
            VALUES
                (:from, :to, :type, :amount, :fee_bank, :desc, :ref, :source, :status)
        ");
        $stmt->execute([
            'from'     => $data['from_user_id'] ?? null,
            'to'       => $data['to_user_id']   ?? null,
            'type'     => $data['type'],
            'amount'   => $data['amount'],
            'fee_bank' => $data['fee_bank'] ?? 0,
            'desc'     => $data['description'] ?? null,
            'ref'      => $data['reference_id'],
            'source'   => $data['source_app'] ?? 'SmartBank',
            'status'   => $data['status'] ?? 'success',
        ]);
        return (int) $db->lastInsertId();
    }

    /**
     * Get ledger entries for a specific user
     */
    public static function getByUser(int $userId, int $limit = 20): array {
        $db   = getDB();
        $stmt = $db->prepare("
            SELECT l.*,
                   fu.name AS from_name,
                   tu.name AS to_name
            FROM   sb_ledger l
            LEFT   JOIN sb_users fu ON l.from_user_id = fu.id
            LEFT   JOIN sb_users tu ON l.to_user_id   = tu.id
            WHERE  l.from_user_id = :uid OR l.to_user_id = :uid2
            ORDER  BY l.created_at DESC
            LIMIT  :lim
        ");
        $stmt->bindValue(':uid',  $userId, PDO::PARAM_INT);
        $stmt->bindValue(':uid2', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':lim',  $limit,  PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Get ALL ledger entries (admin)
     */
    public static function getAll(int $limit = 50): array {
        $db   = getDB();
        $stmt = $db->prepare("
            SELECT l.*,
                   fu.name AS from_name,
                   tu.name AS to_name
            FROM   sb_ledger l
            LEFT   JOIN sb_users fu ON l.from_user_id = fu.id
            LEFT   JOIN sb_users tu ON l.to_user_id   = tu.id
            ORDER  BY l.created_at DESC
            LIMIT  :lim
        ");
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Generate unique reference ID
     */
    public static function generateRef(string $prefix = 'SB'): string {
        return $prefix . '-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
    }
}
