<?php
// ===========================
// Index / Landing Page
// Shows available endpoints
// ===========================
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Management System - Legacy PHP Demo</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background: #f5f5f5;
        }
        h1 {
            color: #333;
        }
        .warning {
            background: #ffebee;
            border: 1px solid #f44336;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .endpoint {
            background: white;
            padding: 15px;
            margin: 10px 0;
            border-radius: 4px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .method {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 3px;
            font-weight: bold;
            margin-right: 10px;
        }
        .post { background: #4CAF50; color: white; }
        .get { background: #2196F3; color: white; }
        code {
            background: #f5f5f5;
            padding: 2px 6px;
            border-radius: 3px;
        }
    </style>
</head>
<body>
    <h1>🛒 Order Management System</h1>
    <p>Legacy PHP 7 Demo Application - Intentionally Vulnerable Code</p>
    
    <div class="warning">
        <strong>⚠️ WARNING:</strong> This application contains intentional security vulnerabilities for educational/demo purposes only. 
        DO NOT use in production!
        <ul>
            <li>Plaintext password storage</li>
            <li>SQL injection vulnerabilities</li>
            <li>Weak authentication</li>
            <li>No input sanitization</li>
        </ul>
    </div>

    <h2>📋 Available Endpoints</h2>

    <div class="endpoint">
        <span class="method post">POST</span>
        <strong>/seed.php</strong>
        <p>Seed database with initial product data and statuses</p>
        <code>curl -X POST http://localhost:8080/seed.php</code>
    </div>

    <div class="endpoint">
        <span class="method post">POST</span>
        <strong>/register.php</strong>
        <p>Register a new user account</p>
        <code>Body: { email, first_name, last_name, phone, password, confirm_password }</code>
    </div>

    <div class="endpoint">
        <span class="method post">POST</span>
        <strong>/login.php</strong>
        <p>User login</p>
        <code>Body: { email, password }</code>
    </div>

    <div class="endpoint">
        <span class="method post">POST</span>
        <strong>/order.php</strong>
        <p>Create new order</p>
        <code>Body: { user_id, items: [{ product_number, quantity }] }</code>
    </div>

    <div class="endpoint">
        <span class="method post">POST</span>
        <strong>/update_order.php</strong>
        <p>Update existing order</p>
        <code>Body: { order_number, items: [{ product_number, quantity }] }</code>
    </div>

    <div class="endpoint">
        <span class="method post">POST</span>
        <strong>/confirm_order.php</strong>
        <p>Confirm order with shipping address</p>
        <code>Body: { order_number, shipping_address }</code>
    </div>

    <div class="endpoint">
        <span class="method get">GET</span>
        <strong>/admin/orders.php</strong>
        <p>List/search orders (Admin only*)</p>
        <code>Query: ?search=keyword&admin=true</code>
    </div>

    <div class="endpoint">
        <span class="method post">POST</span>
        <strong>/admin/confirm_orders.php</strong>
        <p>Bulk confirm multiple orders (Admin only*)</p>
        <code>Body: { order_ids: [1, 2, 3] }</code>
    </div>

    <h2>🧪 Test Credentials</h2>
    <p>After running seed.php:</p>
    <ul>
        <li><strong>Admin:</strong> admin@test.com / admin123</li>
        <li><strong>User 1:</strong> user1@test.com / user123</li>
        <li><strong>User 2:</strong> user2@test.com / user123</li>
    </ul>

    <h2>💡 Sample SQL Injection</h2>
    <p>Login bypass example (VULNERABLE!):</p>
    <code>{ "email": "admin@test.com' OR '1'='1", "password": "anything" }</code>

    <hr>
    <p style="color: #999; font-size: 12px;">
        * Admin check is intentionally weak and can be bypassed via ?admin=true query parameter
    </p>
</body>
</html>
