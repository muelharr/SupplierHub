<?php
/**
 * SmartBank User Model
 * Manages sb_users table
 */

require_once __DIR__ . '/../config/database.php';

class User {

    public static function findByEmail(string $email): ?array {
        $db   = getDB();
        $stmt = $db->prepare("SELECT * FROM sb_users WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        return $stmt->fetch() ?: null;
    }

    public static function findById(int $id): ?array {
        $db   = getDB();
        $stmt = $db->prepare("SELECT id, name, email, saldo, role, created_at FROM sb_users WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public static function create(array $data): int {
        $db   = getDB();
        $stmt = $db->prepare("
            INSERT INTO sb_users (name, email, password, saldo, role)
            VALUES (:name, :email, :password, 0, 'user')
        ");
        $stmt->execute([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => password_hash($data['password'], PASSWORD_BCRYPT),
        ]);
        return (int) $db->lastInsertId();
    }

    public static function verifyPassword(string $plain, string $hash): bool {
        return password_verify($plain, $hash);
    }

    /**
     * Deduct saldo (debit) — atomic update
     * Returns false if insufficient balance
     */
    public static function debit(int $userId, int $amount): bool {
        $db   = getDB();
        $stmt = $db->prepare("
            UPDATE sb_users
            SET saldo = saldo - :amount1
            WHERE id = :id AND saldo >= :amount2
        ");
        $stmt->execute(['amount1' => $amount, 'amount2' => $amount, 'id' => $userId]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Add saldo (credit)
     */
    public static function credit(int $userId, int $amount): bool {
        $db   = getDB();
        $stmt = $db->prepare("UPDATE sb_users SET saldo = saldo + :amount WHERE id = :id");
        $stmt->execute(['amount' => $amount, 'id' => $userId]);
        return $stmt->rowCount() > 0;
    }

    public static function getSaldo(int $userId): int {
        $db   = getDB();
        $stmt = $db->prepare("SELECT saldo FROM sb_users WHERE id = :id");
        $stmt->execute(['id' => $userId]);
        $row = $stmt->fetch();
        return $row ? (int) $row['saldo'] : 0;
    }
}
