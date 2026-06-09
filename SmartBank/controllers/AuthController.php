<?php
/**
 * SmartBank AuthController
 * Endpoint: POST /smartbank/registrasi_login_user
 *
 * IPO:
 *   Input : email, password, [name] (register), action=login|register
 *   Proses: Validasi → create/verify user → generate JWT
 *   Output: token + user data
 */

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../middleware/Auth.php';

class AuthController {

    public static function handle(array $input): array {
        $action = $input['action'] ?? 'login';

        if ($action === 'register') {
            return self::register($input);
        }
        return self::login($input);
    }

    private static function login(array $input): array {
        if (empty($input['email']) || empty($input['password'])) {
            http_response_code(400);
            return ['status' => 'error', 'message' => 'Email dan password wajib diisi.', 'meta' => Auth::meta()];
        }

        $user = User::findByEmail($input['email']);
        if (!$user || !User::verifyPassword($input['password'], $user['password'])) {
            http_response_code(401);
            return ['status' => 'error', 'message' => 'Email atau password salah.', 'meta' => Auth::meta()];
        }

        if (!$user['is_active']) {
            http_response_code(403);
            return ['status' => 'error', 'message' => 'Akun tidak aktif.', 'meta' => Auth::meta()];
        }

        $token = Auth::generateToken($user);

        return [
            'status'  => 'success',
            'message' => 'Login berhasil.',
            'data'    => [
                'token'   => $token,
                'user_id' => $user['id'],
                'name'    => $user['name'],
                'email'   => $user['email'],
                'role'    => $user['role'],
                'saldo'   => (int) $user['saldo'],
            ],
            'meta' => Auth::meta()
        ];
    }

    private static function register(array $input): array {
        if (empty($input['name']) || empty($input['email']) || empty($input['password'])) {
            http_response_code(400);
            return ['status' => 'error', 'message' => 'name, email, dan password wajib diisi.', 'meta' => Auth::meta()];
        }

        if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            return ['status' => 'error', 'message' => 'Format email tidak valid.', 'meta' => Auth::meta()];
        }

        if (strlen($input['password']) < 6) {
            http_response_code(400);
            return ['status' => 'error', 'message' => 'Password minimal 6 karakter.', 'meta' => Auth::meta()];
        }

        if (User::findByEmail($input['email'])) {
            http_response_code(409);
            return ['status' => 'error', 'message' => 'Email sudah terdaftar.', 'meta' => Auth::meta()];
        }

        $userId = User::create($input);
        $user   = User::findById($userId);
        $token  = Auth::generateToken($user);

        http_response_code(201);
        return [
            'status'  => 'success',
            'message' => 'Registrasi berhasil.',
            'data'    => [
                'token'   => $token,
                'user_id' => $userId,
                'name'    => $user['name'],
                'email'   => $user['email'],
                'saldo'   => 0,
            ],
            'meta' => Auth::meta()
        ];
    }
}
