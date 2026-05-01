<?php
/**
 * Database Configuration - PDO MySQL
 * REST API SupplierHub
 * 
 * Menggunakan database yang SAMA dengan website PHP (supplierhub_db)
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'supplierhub_db');
define('DB_USER', 'root');
define('DB_PASS', '');

// JWT Configuration
define('JWT_SECRET', 'supplierhub_b2b_secret_key_2026');
define('JWT_EXPIRY', 86400); // 24 hours

// Fee & Financial Rules (sesuai Aplikasi.docx)
define('FEE_SUPPLIER', 0.03);   // 3% margin supplier
define('FEE_GATEWAY', 0.005);   // 0.5% fee API Gateway
define('FEE_BANK', 0.01);       // 1% fee SmartBank

// SmartBank API
define('SMARTBANK_API_URL', 'http://localhost/SmartBank/api');

// Application
define('APP_NAME', 'SupplierHub REST API');
define('APP_VERSION', '1.0.0');

/**
 * Get PDO database connection (singleton)
 */
function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                'status'  => 'error',
                'message' => 'Database connection failed: ' . $e->getMessage()
            ]);
            exit;
        }
    }
    return $pdo;
}
