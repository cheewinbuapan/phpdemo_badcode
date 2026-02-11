<?php
// ===========================
// Feature 5: Create Order
// Mixed logic, long processing, no transaction
// ===========================

header('Content-Type: application/json');

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

// Get JSON input
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Check if data is valid
if (!isset($data['user_id']) || !isset($data['items'])) {
    http_response_code(400);
    echo json_encode(array('message' => 'Invalid input'));
    exit;
}

// Process order using the long spaghetti function
$result = processOrder($data);

if (isset($result['error'])) {
    http_response_code($result['code']);
    echo json_encode(array('message' => $result['error']));
} else {
    http_response_code($result['code']);
    echo json_encode(array(
        'order_number' => $result['order_number'],
        'message' => 'Order created successfully'
    ));
}

mysqli_close($conn);
