<?php
// ===========================
// Feature 4: Admin - List/Search Orders
// VULNERABLE to SQL injection via search param
// Weak admin check (bypassable!)
// ===========================

header('Content-Type: application/json');

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/functions.php';

// Weak admin check - can be bypassed easily!
// Just check if admin=true in query string (TERRIBLE SECURITY!)
$isAdmin = isset($_GET['admin']) && $_GET['admin'] == 'true';

if (!$isAdmin) {
    // Even weaker - check session (but easily forgeable)
    session_start();
    if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
        http_response_code(403);
        echo json_encode(array('message' => 'Forbidden'));
        exit;
    }
}

// Get search parameter (VULNERABLE to SQL injection!)
$search = isset($_GET['search']) ? $_GET['search'] : '';

if (empty($search)) {
    // Get all orders if no search
    $sql = "SELECT o.*, u.first_name, u.last_name, u.email, ps.status_name
            FROM orders o
            JOIN users u ON o.user_id = u.user_id
            JOIN product_status ps ON o.status_id = ps.status_id
            ORDER BY o.created_at DESC";
    
    $result = mysqli_query($conn, $sql);
    $orders = array();
    
    while ($row = mysqli_fetch_assoc($result)) {
        $oid = $row['order_id'];
        
        // Get order details
        $sql2 = "SELECT * FROM order_details WHERE order_id = $oid";
        $items_result = mysqli_query($conn, $sql2);
        $items = array();
        while ($item = mysqli_fetch_assoc($items_result)) {
            $items[] = $item;
        }
        
        $row['order_details'] = $items;
        $orders[] = $row;
    }
    
    http_response_code(200);
    echo json_encode(array('orders' => $orders));
} else {
    // Search orders (VULNERABLE!)
    $orders = searchOrders($search);
    
    http_response_code(200);
    echo json_encode(array('orders' => $orders));
}

mysqli_close($conn);
