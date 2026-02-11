<?php
// ===========================
// Feature 6: Update Order
// Delete-then-insert approach (inefficient!)
// No ownership check, vulnerable
// ===========================

header('Content-Type: application/json');

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

// Get JSON input
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Check required fields
if (!isset($data['order_number']) || !isset($data['items'])) {
    http_response_code(400);
    echo json_encode(array('message' => 'Invalid input'));
    exit;
}

$orderNumber = $data['order_number'];
$items = $data['items'];

// Update order items (no ownership check!)
$result = updateOrderItems($orderNumber, $items);

if (isset($result['error'])) {
    http_response_code($result['code']);
    echo json_encode(array('message' => $result['error']));
} else {
    http_response_code($result['code']);
    echo json_encode(array('message' => $result['message']));
}

mysqli_close($conn);
