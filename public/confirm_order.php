<?php
// ===========================
// Feature 7: User Confirm Order
// Updates shipping address and changes status
// VULNERABLE to SQL injection
// ===========================

header('Content-Type: application/json');

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

// Get JSON input
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Check required fields
if (!isset($data['order_number']) || !isset($data['shipping_address'])) {
    http_response_code(400);
    echo json_encode(array('message' => 'Invalid input'));
    exit;
}

$orderNumber = $data['order_number'];
$shippingAddress = $data['shipping_address'];

// Validate shipping address is not empty
if (empty($shippingAddress)) {
    http_response_code(400);
    echo json_encode(array('message' => 'Invalid input'));
    exit;
}

// Confirm order with address (VULNERABLE!)
$result = confirmOrderWithAddress($orderNumber, $shippingAddress);

if (isset($result['error'])) {
    http_response_code($result['code']);
    echo json_encode(array('message' => $result['error']));
} else {
    http_response_code($result['code']);
    echo json_encode(array('message' => $result['message']));
}

mysqli_close($conn);
