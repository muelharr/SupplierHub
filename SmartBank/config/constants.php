<?php
/**
 * SmartBank Constants
 * Sesuai Aplikasi.docx:
 * - Fee bank 1% per transaksi (Aturan #9)
 * - JWT untuk auth (Aturan #6)
 */

define('SB_APP_NAME',    'SmartBank');
define('SB_VERSION',     '1.0.0');

// Fee (Aplikasi.docx Aturan #9)
define('SB_FEE_BANK',    0.01);   // 1% biaya layanan bank
define('SB_FEE_TAX',     0.005);  // 0.5% pajak transaksi
define('SB_LOAN_RATE',   0.02);   // 2% bunga pinjaman per bulan

// JWT
define('SB_JWT_SECRET',  'smartbank_secret_key_2026_b2blink');
define('SB_JWT_EXPIRY',  86400); // 24 jam

// CORS - izinkan SupplierHub
define('SB_ALLOWED_APPS', ['SupplierHub', 'LogistiKita', 'Marketplace', 'SmartBank']);
