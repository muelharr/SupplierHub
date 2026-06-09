<?php
/**
 * SmartBank SaldoController
 * Endpoint: GET  /smartbank/manajemen_saldo  → cek saldo + riwayat
 *           POST /smartbank/manajemen_saldo  → top-up (admin only)
 *
 * IPO:
 *   Input : user_id (GET) | user_id + amount (POST top-up)
 *   Proses: Validasi user → ambil saldo + ledger history
 *   Output: saldo + riwayat transaksi user
 */

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Ledger.php';
require_once __DIR__ . '/../middleware/Auth.php';
require_once __DIR__ . '/../config/constants.php';

class SaldoController {

    public static function handle(string $method, array $input): array {

        // GET: cek saldo (bisa tanpa auth, cukup user_id — simulasi antar aplikasi)
        if ($method === 'GET') {
            return self::getSaldo($input);
        }

        // POST: top-up saldo (admin) atau info
        return self::topUp($input);
    }

    // -------------------------------------------------------
    // GET /smartbank/manajemen_saldo?user_id=X
    // -------------------------------------------------------
    private static function getSaldo(array $input): array {
        $userId = (int) ($input['user_id'] ?? 0);
        if (!$userId) {
            http_response_code(400);
            return ['status' => 'error', 'message' => 'user_id wajib diisi.', 'meta' => Auth::meta()];
        }

        $user = User::findById($userId);
        if (!$user) {
            http_response_code(404);
            return ['status' => 'error', 'message' => 'User tidak ditemukan.', 'meta' => Auth::meta()];
        }

        $riwayat = Ledger::getByUser($userId, 15);

        return [
            'status'  => 'success',
            'message' => 'Data saldo berhasil diambil.',
            'data'    => [
                'user_id' => $user['id'],
                'name'    => $user['name'],
                'email'   => $user['email'],
                'saldo'   => (int) $user['saldo'],
                'riwayat' => $riwayat,
            ],
            'meta' => Auth::meta()
        ];
    }

    // -------------------------------------------------------
    // POST /smartbank/manajemen_saldo (top-up admin)
    // -------------------------------------------------------
    private static function topUp(array $input): array {
        $userId = (int) ($input['user_id'] ?? 0);
        $amount = (int) ($input['amount']  ?? 0);

        if (!$userId || $amount <= 0) {
            http_response_code(400);
            return ['status' => 'error', 'message' => 'user_id dan amount (>0) wajib diisi.', 'meta' => Auth::meta()];
        }

        $user = User::findById($userId);
        if (!$user) {
            http_response_code(404);
            return ['status' => 'error', 'message' => 'User tidak ditemukan.', 'meta' => Auth::meta()];
        }

        User::credit($userId, $amount);

        $ref = Ledger::generateRef('SB-TOPUP');
        Ledger::record([
            'from_user_id' => 1, // SmartBank Admin
            'to_user_id'   => $userId,
            'type'         => 'credit',
            'amount'       => $amount,
            'fee_bank'     => 0,
            'description'  => 'Top-up saldo via ' . ($input['source_app'] ?? 'SmartBank'),
            'reference_id' => $ref,
            'source_app'   => $input['source_app'] ?? 'SmartBank',
        ]);

        return [
            'status'  => 'success',
            'message' => 'Top-up saldo berhasil.',
            'data'    => [
                'user_id'    => $userId,
                'amount'     => $amount,
                'new_saldo'  => User::getSaldo($userId),
                'payment_id' => $ref,
            ],
            'meta' => Auth::meta()
        ];
    }
}
