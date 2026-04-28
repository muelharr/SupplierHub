<?php
/**
 * Orders API Endpoint
 * POST /api/orders.php?action=checkout      (UMKM)
 * GET  /api/orders.php?action=pending       (Supplier)
 * GET  /api/orders.php?action=detail&id=X   (Supplier)
 * POST /api/orders.php?action=approve       (Supplier)
 * POST /api/orders.php?action=reject        (Supplier)
 * GET  /api/orders.php?action=completed     (Supplier)
 * GET  /api/orders.php?action=history       (UMKM)
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../controllers/OrderController.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../middleware/LoggerMiddleware.php';
require_once __DIR__ . '/../middleware/GatewayMiddleware.php';

GatewayMiddleware::addResponseHeaders();

$action = $_GET['action'] ?? '';
$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$requestData = LoggerMiddleware::getRequestData();
$userId = null;

$response = ['status' => 'error', 'message' => 'Action tidak valid.'];

switch ($action) {
    case 'add_cart':
        $user = AuthMiddleware::requireAuth('umkm');
        $userId = $user['user_id'];
        if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
        $matId = $input['material_id'] ?? 0;
        $qty = $input['qty'] ?? 1;
        $found = false;
        foreach ($_SESSION['cart'] as &$ci) {
            if ($ci['material_id'] == $matId) { $ci['qty'] += $qty; $found = true; break; }
        }
        unset($ci);
        if (!$found) $_SESSION['cart'][] = ['material_id' => $matId, 'qty' => $qty];
        $response = ['status' => 'success', 'message' => 'Ditambahkan ke keranjang.'];
        break;

    case 'checkout':
        $user = AuthMiddleware::requireAuth('umkm');
        $userId = $user['user_id'];
        // If checkout from cart session
        if (!empty($input['from_cart']) && isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
            $input['items'] = $_SESSION['cart'];
        }
        $response = OrderController::checkout($input, $userId);
        if ($response['status'] === 'success') { $_SESSION['cart'] = []; }
        break;

    case 'pending':
        $user = AuthMiddleware::requireAuth('supplier');
        $userId = $user['user_id'];
        $response = OrderController::listPending($userId);
        break;

    case 'detail':
        $user = AuthMiddleware::requireAuth();
        $userId = $user['user_id'];
        $orderId = $_GET['id'] ?? 0;
        $response = OrderController::detail($orderId);
        break;

    case 'approve':
        $user = AuthMiddleware::requireAuth('supplier');
        $userId = $user['user_id'];
        $orderId = $input['order_id'] ?? 0;
        $response = OrderController::approve($orderId, $userId);
        break;

    case 'reject':
        $user = AuthMiddleware::requireAuth('supplier');
        $userId = $user['user_id'];
        $orderId = $input['order_id'] ?? 0;
        $response = OrderController::reject($orderId, $userId);
        break;

    case 'completed':
        $user = AuthMiddleware::requireAuth('supplier');
        $userId = $user['user_id'];
        $response = OrderController::listCompleted($userId);
        break;

    case 'history':
        $user = AuthMiddleware::requireAuth('umkm');
        $userId = $user['user_id'];
        $response = OrderController::history($userId);
        break;

    default:
        $response = ['status' => 'error', 'message' => 'Action tidak dikenali.'];
}

LoggerMiddleware::log('/api/orders.php?action=' . $action, $userId, $requestData, $response);

echo json_encode($response);
