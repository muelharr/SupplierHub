<?php
/**
 * SmartBank LedgerController
 * Endpoint: GET /smartbank/ledger_transaksi
 *
 * IPO:
 *   Input : limit (GET, optional)
 *   Proses: Ambil list semua ledger dari model Ledger
 *   Output: list ledger transaksi lengkap
 */

require_once __DIR__ . '/../models/Ledger.php';
require_once __DIR__ . '/../middleware/Auth.php';

class LedgerController {

    public static function handle(string $method, array $input): array {
        if ($method !== 'GET') {
            http_response_code(405);
            return [
                'status'  => 'error',
                'message' => 'Method not allowed. Use GET.',
                'meta'    => Auth::meta()
            ];
        }

        $limit = isset($input['limit']) ? (int) $input['limit'] : 50;
        if ($limit <= 0) {
            $limit = 50;
        }

        $ledger = Ledger::getAll($limit);

        // Format data sesuai kebutuhan standard
        return [
            'status'  => 'success',
            'message' => 'Data ledger transaksi berhasil diambil.',
            'data'    => [
                'count'  => count($ledger),
                'ledger' => $ledger
            ],
            'meta' => Auth::meta()
        ];
    }
}
