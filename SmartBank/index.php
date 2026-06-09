<?php
/**
 * SmartBank - Main Router
 * Entry point for all API requests
 */

// ===== CORS & JSON Headers =====
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Source-App, X-Gateway-ID');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// ===== Load Dependencies =====
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/middleware/Auth.php';
require_once __DIR__ . '/middleware/Logger.php';
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/controllers/SaldoController.php';
require_once __DIR__ . '/controllers/TransaksiController.php';
require_once __DIR__ . '/controllers/LedgerController.php';

// ===== Parse Request =====
$method = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// Clean the URI path: strip base directory and query strings
$basePath = '/SmartBank';
$path = parse_url($uri, PHP_URL_PATH);

// If the path starts with the base folder, remove it
if (strpos($path, $basePath) === 0) {
    $path = substr($path, strlen($basePath));
}

// If the path starts with /api, remove it
if (strpos($path, '/api') === 0) {
    $path = substr($path, 4);
}

$path = rtrim($path, '/') ?: '/';

// Parse query params and request body
$queryParams = $_GET;
$inputData = Logger::getInput();
$requestData = array_merge($queryParams, $inputData);

// Log variables
$statusCode = 200;
$responseBody = [];
$userId = (int) ($requestData['user_id'] ?? $requestData['from_user_id'] ?? 0);

// Capture Output for Logging
ob_start();

try {
    // Route matching
    switch ($path) {
        // 1. Registrasi & Login User
        case '/smartbank/registrasi_login_user':
            if ($method !== 'POST') {
                http_response_code(405);
                $responseBody = ['status' => 'error', 'message' => 'Method not allowed. Use POST.', 'meta' => Auth::meta()];
            } else {
                $responseBody = AuthController::handle($requestData);
            }
            break;

        // 2. Manajemen Saldo (Cek saldo & top up)
        case '/smartbank/manajemen_saldo':
            $responseBody = SaldoController::handle($method, $requestData);
            break;

        // 3. Transfer Antar User
        case '/smartbank/transfer_antar_user':
            if ($method !== 'POST') {
                http_response_code(405);
                $responseBody = ['status' => 'error', 'message' => 'Method not allowed. Use POST.', 'meta' => Auth::meta()];
            } else {
                $responseBody = TransaksiController::transferAntarUser($requestData);
            }
            break;

        // 4. Pembayaran Transaksi (Utama SupplierHub)
        case '/smartbank/pembayaran_transaksi':
            if ($method !== 'POST') {
                http_response_code(405);
                $responseBody = ['status' => 'error', 'message' => 'Method not allowed. Use POST.', 'meta' => Auth::meta()];
            } else {
                $responseBody = TransaksiController::pembayaranTransaksi($requestData);
            }
            break;

        // 5. Pinjaman (Loan) - support both endpoints
        case '/smartbank/pinjaman_loan':
        case '/smartbank/pinjaman_(loan)':
            if ($method !== 'POST') {
                http_response_code(405);
                $responseBody = ['status' => 'error', 'message' => 'Method not allowed. Use POST.', 'meta' => Auth::meta()];
            } else {
                $responseBody = TransaksiController::pinjamanLoan($requestData);
            }
            break;

        // 6. Pajak & Biaya - support all variations
        case '/smartbank/pajak_biaya':
        case '/smartbank/pajak_&_biaya':
        case '/smartbank/pajak_&amp;_biaya':
            if ($method !== 'POST') {
                http_response_code(405);
                $responseBody = ['status' => 'error', 'message' => 'Method not allowed. Use POST.', 'meta' => Auth::meta()];
            } else {
                $responseBody = TransaksiController::pajakBiaya($requestData);
            }
            break;

        // 7. Ledger Transaksi
        case '/smartbank/ledger_transaksi':
            $responseBody = LedgerController::handle($method, $requestData);
            break;

        // 8. Biaya Layanan Bank
        case '/smartbank/biaya_layanan_bank':
            if ($method !== 'GET') {
                http_response_code(405);
                $responseBody = ['status' => 'error', 'message' => 'Method not allowed. Use GET.', 'meta' => Auth::meta()];
            } else {
                $responseBody = TransaksiController::biayaLayananBank();
            }
            break;

        // Root / Welcome Info
        case '/':
            $responseBody = [
                'status' => 'success',
                'message' => 'Welcome to SmartBank Mock API Server',
                'endpoints' => [
                    'POST /api/smartbank/registrasi_login_user' => 'Auth & JWT token generation',
                    'GET  /api/smartbank/manajemen_saldo'       => 'Check balance & history',
                    'POST /api/smartbank/manajemen_saldo'       => 'Top up balance (admin)',
                    'POST /api/smartbank/transfer_antar_user'   => 'Transfer balance between users',
                    'POST /api/smartbank/pembayaran_transaksi'  => 'Transaction payments (B2B)',
                    'POST /api/smartbank/pinjaman_loan'         => 'Loan request',
                    'POST /api/smartbank/pajak_biaya'           => 'Tax payments deduction',
                    'GET  /api/smartbank/ledger_transaksi'      => 'View complete transaction log',
                    'GET  /api/smartbank/biaya_layanan_bank'    => 'View fee rules and bank config'
                ],
                'meta' => Auth::meta()
            ];
            break;

        // Route Not Found
        default:
            http_response_code(404);
            $responseBody = [
                'status' => 'error',
                'message' => "Route $method $path not found on SmartBank.",
                'meta' => Auth::meta()
            ];
            break;
    }
} catch (Throwable $e) {
    http_response_code(500);
    $responseBody = [
        'status' => 'error',
        'message' => 'Internal Server Error: ' . $e->getMessage(),
        'meta' => Auth::meta()
    ];
}

$statusCode = http_response_code();
echo json_encode($responseBody, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

// Flush buffer and log request
$rawOutput = ob_get_clean();
echo $rawOutput;

Logger::log($path, $method, $userId ?: null, $requestData, $responseBody, $statusCode);
