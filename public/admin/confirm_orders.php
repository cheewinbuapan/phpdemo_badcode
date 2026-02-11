<?php
// ===========================
// Feature 8: Admin Bulk Confirm Orders
// Updates multiple orders to CONFIRMED status
// No transaction, weak validation
// ===========================

header('Content-Type: application/json');

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/functions.php';

// Weak admin check
$isAdmin = isset($_GET['admin']) && $_GET['admin'] == 'true';

if (!$isAdmin) {
    session_start();
    if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
        http_response_code(403);
        echo json_encode(array('message' => 'Forbidden'));
        exit;
    }
}

// Get JSON input
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Check if order_ids array exists
if (!isset($data['order_ids']) || !is_array($data['order_ids'])) {
    http_response_code(400);
    echo json_encode(array('message' => 'Invalid input'));
    exit;
}

$orderIds = $data['order_ids'];

if (empty($orderIds)) {
    http_response_code(400);
    echo json_encode(array('message' => 'No orders provided'));
    exit;
}

// Bulk confirm (NO TRANSACTION!)
$count = bulkConfirmOrders($orderIds);

http_response_code(200);
echo json_encode(array(
    'message' => 'Orders confirmed',
    'count' => $count
));

mysqli_close($conn);
