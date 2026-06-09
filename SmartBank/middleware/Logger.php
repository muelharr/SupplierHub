<?php
/**
 * SmartBank Logger Middleware
 * Logging semua request/response (Aturan #6: Validasi & Logging wajib)
 */

require_once __DIR__ . '/../config/database.php';

class Logger {

    /**
     * Log a request and its response to sb_request_logs
     */
    public static function log(
        string $endpoint,
        string $method,
        ?int   $userId,
        ?array $requestBody,
        ?array $responseBody,
        int    $statusCode = 200
    ): void {
        try {
            $db   = getDB();
            $stmt = $db->prepare("
                INSERT INTO sb_request_logs
                    (endpoint, method, source_app, user_id, request_body, response_body, status_code, ip_address)
                VALUES
                    (:endpoint, :method, :source_app, :user_id, :req, :res, :code, :ip)
            ");
            $stmt->execute([
                'endpoint'   => $endpoint,
                'method'     => $method,
                'source_app' => $_SERVER['HTTP_X_SOURCE_APP'] ?? 'unknown',
                'user_id'    => $userId,
                'req'        => $requestBody ? json_encode($requestBody, JSON_UNESCAPED_UNICODE) : null,
                'res'        => $responseBody ? json_encode($responseBody, JSON_UNESCAPED_UNICODE) : null,
                'code'       => $statusCode,
                'ip'         => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
            ]);
        } catch (Throwable $e) {
            // Logging should never break the app
            error_log('[SmartBank Logger] ' . $e->getMessage());
        }
    }

    /**
     * Get request body (JSON or POST)
     */
    public static function getInput(): array {
        $raw = file_get_contents('php://input');
        $json = json_decode($raw, true);
        if ($json && is_array($json)) return $json;
        return $_POST ?: [];
    }
}
