<?php
/**
 * SmartBank Auth Middleware
 * JWT generation & validation (Aturan #6)
 */

require_once __DIR__ . '/../config/constants.php';

class Auth {

    /**
     * Generate JWT token
     */
    public static function generateToken(array $user): string {
        $header  = base64_encode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
        $payload = base64_encode(json_encode([
            'sub'   => $user['id'],
            'name'  => $user['name'],
            'email' => $user['email'],
            'role'  => $user['role'],
            'iat'   => time(),
            'exp'   => time() + SB_JWT_EXPIRY,
        ]));
        $sig = base64_encode(hash_hmac('sha256', "$header.$payload", SB_JWT_SECRET, true));
        return "$header.$payload.$sig";
    }

    /**
     * Validate JWT from Authorization header
     * Returns decoded payload or null
     */
    public static function validateToken(): ?array {
        $auth = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (!$auth) {
            // Also check Bearer in query for convenience
            $auth = 'Bearer ' . ($_GET['token'] ?? '');
        }

        if (!str_starts_with($auth, 'Bearer ')) return null;
        $token = substr($auth, 7);

        $parts = explode('.', $token);
        if (count($parts) !== 3) return null;

        [$header, $payload, $sig] = $parts;
        $expected = base64_encode(hash_hmac('sha256', "$header.$payload", SB_JWT_SECRET, true));

        if (!hash_equals($expected, $sig)) return null;

        $data = json_decode(base64_decode($payload), true);
        if (!$data || $data['exp'] < time()) return null;

        return $data;
    }

    /**
     * Require valid JWT or return 401
     */
    public static function require(): array {
        $user = self::validateToken();
        if (!$user) {
            http_response_code(401);
            echo json_encode([
                'status'  => 'error',
                'message' => 'Token tidak valid atau sudah expired.',
                'meta'    => self::meta()
            ]);
            exit;
        }
        return $user;
    }

    /**
     * Standard SmartBank response meta
     */
    public static function meta(): array {
        return [
            'smartbank_version' => SB_VERSION,
            'processed_at'      => date('Y-m-d H:i:s'),
            'request_id'        => 'SB-REQ-' . strtoupper(substr(uniqid(), -8)),
        ];
    }
}
