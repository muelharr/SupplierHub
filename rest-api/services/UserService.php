<?php
/**
 * User Service
 * Database queries untuk tabel users
 * 
 * Mengikuti pola services/books.ts dari referensi pertemuan 06
 */

require_once __DIR__ . '/../config/database.php';

class UserService {

    /**
     * Cari user berdasarkan email
     * @return array|false
     */
    public static function findByEmail($email) {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        return $stmt->fetch();
    }

    /**
     * Cari user berdasarkan ID (tanpa password)
     * @return array|false
     */
    public static function findById($id) {
        $db = getDB();
        $stmt = $db->prepare("SELECT id, name, email, role, created_at FROM users WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Buat user baru
     * @return int ID user yang baru dibuat
     */
    public static function create($data) {
        $db = getDB();
        $stmt = $db->prepare("
            INSERT INTO users (name, email, password, role) 
            VALUES (:name, :email, :password, :role)
        ");
        $stmt->execute([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => password_hash($data['password'], PASSWORD_BCRYPT),
            'role'     => $data['role']
        ]);
        return (int) $db->lastInsertId();
    }

    /**
     * Verifikasi password
     */
    public static function verifyPassword($plainPassword, $hashedPassword) {
        return password_verify($plainPassword, $hashedPassword);
    }
}
