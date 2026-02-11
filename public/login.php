<?php
// ===========================
// Feature 3: User Login
// VULNERABLE to SQL injection
// Compares plaintext passwords!
// ===========================

session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../config/db.php';

// Get JSON input
$input = file_get_contents('php://input');
$data = json_decode($input, true);

$email = $data['email'];
$password = $data['password'];

// Validate inputs
if (empty($email) || empty($password)) {
    http_response_code(400);
    echo json_encode(array('message' => 'Invalid credentials'));
    exit;
}

// SQL INJECTION VULNERABILITY!
// Direct string concatenation in WHERE clause
$sql = "SELECT * FROM users WHERE email = '$email' AND password = '$password'";
$result = mysqli_query($conn, $sql);

// Expose SQL errors (BAD PRACTICE!)
if (!$result) {
    http_response_code(500);
    echo json_encode(array('message' => 'Login error: ' . mysqli_error($conn)));
    exit;
}

if (mysqli_num_rows($result) > 0) {
    $user = mysqli_fetch_assoc($result);
    
    // Set session (weak session management)
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['is_admin'] = $user['is_admin'];
    
    http_response_code(200);
    echo json_encode(array(
        'message' => 'Login success',
        'user_id' => $user['user_id'],
        'email' => $user['email'],
        'first_name' => $user['first_name'],
        'last_name' => $user['last_name'],
        'is_admin' => $user['is_admin']
    ));
} else {
    http_response_code(401);
    echo json_encode(array('message' => 'Invalid credentials'));
}

mysqli_close($conn);
