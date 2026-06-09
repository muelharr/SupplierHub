<?php
/**
 * SmartBank TransaksiController
 * Handles 5 transaction endpoints:
 *
 * POST /smartbank/pembayaran_transaksi  → debit user, kredit merchant (UTAMA SupplierHub)
 * POST /smartbank/transfer_antar_user   → transfer saldo antar user
 * POST /smartbank/pinjaman_loan         → ajukan pinjaman
 * POST /smartbank/pajak_biaya           → potongan pajak
 * GET  /smartbank/biaya_layanan_bank    → info fee bank
 *
 * Aturan Dokumen:
 *   #3 Semua output transaksi = payment request
 *   #4 SmartBank sebagai pusat kontrol
 *   #8 Tidak ada uang dibuat bebas
 *   #9 Semua layanan berbayar (fee bank 1%)
 */

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Ledger.php';
require_once __DIR__ . '/../middleware/Auth.php';
require_once __DIR__ . '/../config/constants.php';

class TransaksiController {

    // ===========================================================
    // POST /smartbank/pembayaran_transaksi
    // Dipanggil oleh SupplierHub saat UMKM checkout / approve order
    //
    // Input: user_id, amount, fee_supplier, description, source_app, [to_user_id]
    // Proses: Debit UMKM → Kredit Supplier → Catat fee bank → Ledger
    // Output: payment_id, amount_debited, saldo_setelah
    // ===========================================================
    public static function pembayaranTransaksi(array $input): array {
        $userId      = (int) ($input['user_id']      ?? 0);
        $amount      = (int) ($input['amount']        ?? 0);
        $feeSupplier = (int) ($input['fee_supplier']  ?? 0);
        $description = $input['description'] ?? 'Pembayaran transaksi';
        $sourceApp   = $input['source_app']  ?? 'unknown';
        $toUserId    = (int) ($input['to_user_id']    ?? 3); // default: SupplierHub (id=3)

        // Validasi input
        if (!$userId || $amount <= 0) {
            http_response_code(400);
            return ['status' => 'error', 'message' => 'user_id dan amount (>0) wajib diisi.', 'meta' => Auth::meta()];
        }

        $user = User::findById($userId);
        if (!$user) {
            http_response_code(404);
            return ['status' => 'error', 'message' => 'User tidak ditemukan.', 'meta' => Auth::meta()];
        }

        // Hitung fee bank (1%) — Aturan #9
        $feeBank   = (int) round($amount * SB_FEE_BANK);
        $totalDebit = $amount + $feeSupplier + $feeBank;

        // Cek saldo cukup (Aturan #8: tidak ada uang bebas)
        if ($user['saldo'] < $totalDebit) {
            http_response_code(402);
            return [
                'status'  => 'error',
                'message' => 'Saldo tidak mencukupi. Saldo: Rp ' . number_format($user['saldo'], 0, ',', '.') . ', Dibutuhkan: Rp ' . number_format($totalDebit, 0, ',', '.'),
                'data'    => ['saldo' => (int) $user['saldo'], 'required' => $totalDebit],
                'meta'    => Auth::meta()
            ];
        }

        // --- TRANSAKSI ATOMIC ---
        $db = getDB();
        $db->beginTransaction();

        try {
            // 1. Debit UMKM
            $ok = User::debit($userId, $totalDebit);
            if (!$ok) throw new Exception('Debit gagal — saldo tidak mencukupi.');

            // 2. Kredit Supplier (amount + fee_supplier, tanpa fee bank)
            User::credit($toUserId, $amount + $feeSupplier);

            // 3. Fee bank masuk ke SmartBank Admin (id=1)
            User::credit(1, $feeBank);

            $ref = Ledger::generateRef('SB-PAY');

            // 4. Catat di ledger — debit UMKM
            Ledger::record([
                'from_user_id' => $userId,
                'to_user_id'   => $toUserId,
                'type'         => 'debit',
                'amount'       => $totalDebit,
                'fee_bank'     => $feeBank,
                'description'  => $description,
                'reference_id' => $ref,
                'source_app'   => $sourceApp,
            ]);

            $db->commit();

            $saldoSetelah = User::getSaldo($userId);

            return [
                'status'  => 'success',
                'message' => 'Pembayaran berhasil diproses oleh SmartBank.',
                'data'    => [
                    'payment_id'     => $ref,
                    'user_id'        => $userId,
                    'amount'         => $amount,
                    'fee_supplier'   => $feeSupplier,
                    'fee_bank'       => $feeBank,
                    'amount_debited' => $totalDebit,
                    'saldo_sebelum'  => (int) $user['saldo'],
                    'saldo_setelah'  => $saldoSetelah,
                    'source_app'     => $sourceApp,
                    'timestamp'      => date('Y-m-d H:i:s'),
                    'simulated'      => false,
                ],
                'meta' => Auth::meta()
            ];

        } catch (Exception $e) {
            $db->rollBack();
            http_response_code(500);
            return ['status' => 'error', 'message' => 'Transaksi gagal: ' . $e->getMessage(), 'meta' => Auth::meta()];
        }
    }

    // ===========================================================
    // POST /smartbank/transfer_antar_user
    // Input: from_user_id, to_user_id, amount, description
    // ===========================================================
    public static function transferAntarUser(array $input): array {
        $fromId  = (int) ($input['from_user_id'] ?? 0);
        $toId    = (int) ($input['to_user_id']   ?? 0);
        $amount  = (int) ($input['amount']        ?? 0);
        $desc    = $input['description'] ?? 'Transfer antar user';

        if (!$fromId || !$toId || $amount <= 0) {
            http_response_code(400);
            return ['status' => 'error', 'message' => 'from_user_id, to_user_id, dan amount wajib diisi.', 'meta' => Auth::meta()];
        }

        if ($fromId === $toId) {
            http_response_code(400);
            return ['status' => 'error', 'message' => 'Tidak dapat transfer ke akun sendiri.', 'meta' => Auth::meta()];
        }

        $from = User::findById($fromId);
        $to   = User::findById($toId);
        if (!$from || !$to) {
            http_response_code(404);
            return ['status' => 'error', 'message' => 'User tidak ditemukan.', 'meta' => Auth::meta()];
        }

        $feeBank   = (int) round($amount * SB_FEE_BANK);
        $totalDebit = $amount + $feeBank;

        if ($from['saldo'] < $totalDebit) {
            http_response_code(402);
            return ['status' => 'error', 'message' => 'Saldo tidak mencukupi untuk transfer.', 'meta' => Auth::meta()];
        }

        $db = getDB();
        $db->beginTransaction();
        try {
            User::debit($fromId, $totalDebit);
            User::credit($toId, $amount);
            User::credit(1, $feeBank); // fee ke SmartBank admin

            $ref = Ledger::generateRef('SB-TRF');
            Ledger::record([
                'from_user_id' => $fromId,
                'to_user_id'   => $toId,
                'type'         => 'debit',
                'amount'       => $amount,
                'fee_bank'     => $feeBank,
                'description'  => $desc,
                'reference_id' => $ref,
                'source_app'   => $input['source_app'] ?? 'SmartBank',
            ]);
            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            http_response_code(500);
            return ['status' => 'error', 'message' => 'Transfer gagal: ' . $e->getMessage(), 'meta' => Auth::meta()];
        }

        return [
            'status'  => 'success',
            'message' => 'Transfer berhasil.',
            'data'    => [
                'payment_id'    => $ref,
                'from_user'     => $from['name'],
                'to_user'       => $to['name'],
                'amount'        => $amount,
                'fee_bank'      => $feeBank,
                'total_debited' => $totalDebit,
                'timestamp'     => date('Y-m-d H:i:s'),
            ],
            'meta' => Auth::meta()
        ];
    }

    // ===========================================================
    // POST /smartbank/pinjaman_loan
    // Input: user_id, amount
    // ===========================================================
    public static function pinjamanLoan(array $input): array {
        $userId = (int) ($input['user_id'] ?? 0);
        $amount = (int) ($input['amount']  ?? 0);

        if (!$userId || $amount <= 0) {
            http_response_code(400);
            return ['status' => 'error', 'message' => 'user_id dan amount wajib diisi.', 'meta' => Auth::meta()];
        }

        $user = User::findById($userId);
        if (!$user) {
            http_response_code(404);
            return ['status' => 'error', 'message' => 'User tidak ditemukan.', 'meta' => Auth::meta()];
        }

        // Batas maksimal pinjaman Rp 5.000.000
        if ($amount > 5000000) {
            http_response_code(400);
            return ['status' => 'error', 'message' => 'Maksimal pinjaman Rp 5.000.000.', 'meta' => Auth::meta()];
        }

        $db = getDB();
        $interest   = SB_LOAN_RATE * 100; // 2%
        $totalBayar = $amount + (int) round($amount * SB_LOAN_RATE);
        $dueDate    = date('Y-m-d', strtotime('+30 days'));

        $stmt = $db->prepare("INSERT INTO sb_loans (user_id, amount, interest, status, due_date) VALUES (:uid, :amt, :int, 'approved', :due)");
        $stmt->execute(['uid' => $userId, 'amt' => $amount, 'int' => $interest, 'due' => $dueDate]);
        $loanId = $db->lastInsertId();

        // Kredit saldo user
        User::credit($userId, $amount);

        $ref = Ledger::generateRef('SB-LOAN');
        Ledger::record([
            'from_user_id' => 1,
            'to_user_id'   => $userId,
            'type'         => 'loan',
            'amount'       => $amount,
            'fee_bank'     => 0,
            'description'  => 'Pinjaman disetujui',
            'reference_id' => $ref,
            'source_app'   => $input['source_app'] ?? 'SmartBank',
        ]);

        return [
            'status'  => 'success',
            'message' => 'Pinjaman disetujui dan saldo telah dikreditkan.',
            'data'    => [
                'loan_id'     => $loanId,
                'payment_id'  => $ref,
                'user_id'     => $userId,
                'amount'      => $amount,
                'interest'    => $interest . '%',
                'total_bayar' => $totalBayar,
                'due_date'    => $dueDate,
                'new_saldo'   => User::getSaldo($userId),
            ],
            'meta' => Auth::meta()
        ];
    }

    // ===========================================================
    // POST /smartbank/pajak_biaya
    // Input: user_id, amount, tax_rate (default 0.5%)
    // ===========================================================
    public static function pajakBiaya(array $input): array {
        $userId  = (int) ($input['user_id']   ?? 0);
        $amount  = (int) ($input['amount']    ?? 0);
        $taxRate = (float) ($input['tax_rate'] ?? SB_FEE_TAX);

        if (!$userId || $amount <= 0) {
            http_response_code(400);
            return ['status' => 'error', 'message' => 'user_id dan amount wajib diisi.', 'meta' => Auth::meta()];
        }

        $user = User::findById($userId);
        if (!$user) {
            http_response_code(404);
            return ['status' => 'error', 'message' => 'User tidak ditemukan.', 'meta' => Auth::meta()];
        }

        $taxAmount = (int) round($amount * $taxRate);
        if ($user['saldo'] < $taxAmount) {
            http_response_code(402);
            return ['status' => 'error', 'message' => 'Saldo tidak cukup untuk membayar pajak.', 'meta' => Auth::meta()];
        }

        User::debit($userId, $taxAmount);
        User::credit(1, $taxAmount); // pajak ke SmartBank admin

        $ref = Ledger::generateRef('SB-TAX');
        Ledger::record([
            'from_user_id' => $userId,
            'to_user_id'   => 1,
            'type'         => 'tax',
            'amount'       => $taxAmount,
            'fee_bank'     => 0,
            'description'  => 'Pajak transaksi (' . ($taxRate * 100) . '%)',
            'reference_id' => $ref,
            'source_app'   => $input['source_app'] ?? 'SmartBank',
        ]);

        return [
            'status'  => 'success',
            'message' => 'Pajak berhasil dipotong.',
            'data'    => [
                'payment_id' => $ref,
                'user_id'    => $userId,
                'tax_rate'   => ($taxRate * 100) . '%',
                'tax_amount' => $taxAmount,
                'new_saldo'  => User::getSaldo($userId),
            ],
            'meta' => Auth::meta()
        ];
    }

    // ===========================================================
    // GET /smartbank/biaya_layanan_bank
    // Info fee bank (tidak mengubah data)
    // ===========================================================
    public static function biayaLayananBank(): array {
        return [
            'status'  => 'success',
            'message' => 'Informasi biaya layanan SmartBank.',
            'data'    => [
                'fee_bank_persen'     => (SB_FEE_BANK * 100) . '%',
                'fee_bank_desc'       => 'Biaya layanan bank per transaksi pembayaran',
                'fee_pajak_persen'    => (SB_FEE_TAX * 100) . '%',
                'fee_pajak_desc'      => 'Pajak transaksi default',
                'bunga_pinjaman'      => (SB_LOAN_RATE * 100) . '% per bulan',
                'maks_pinjaman'       => 5000000,
                'min_transfer'        => 10000,
                'currency'            => 'IDR',
            ],
            'meta' => Auth::meta()
        ];
    }
}
