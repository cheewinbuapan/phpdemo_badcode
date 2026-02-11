<?php
// ===========================
// Feature 2: User Registration
// VULNERABLE to SQL injection
// Stores plaintext passwords!
// ===========================

header('Content-Type: application/json');

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/validation.php';
require_once __DIR__ . '/../includes/db_helper.php';

// Get JSON input
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Extract fields
$email = $data['email'];
$firstName = $data['first_name'];
$lastName = $data['last_name'];
$phone = $data['phone'];
$password = $data['password'];
$confirmPassword = $data['confirm_password'];

// Minimal validation
if (!validateEmail($email)) {
    http_response_code(400);
    echo json_encode(array('message' => 'Invalid input'));
    exit;
}

if (!validateName($firstName) || !validateName($lastName)) {
    http_response_code(400);
    echo json_encode(array('message' => 'Invalid input'));
    exit;
}

if (!validatePhone($phone)) {
    http_response_code(400);
    echo json_encode(array('message' => 'Invalid input'));
    exit;
}

if (!validatePassword($password)) {
    http_response_code(400);
    echo json_encode(array('message' => 'Invalid input'));
    exit;
}

// Check password confirmation
if ($password !== $confirmPassword) {
    http_response_code(400);
    echo json_encode(array('message' => 'Invalid input'));
    exit;
}

// Check if email already exists (VULNERABLE!)
$existingUser = getUserByEmail($email);
if ($existingUser) {
    http_response_code(409);
    echo json_encode(array('message' => 'Invalid input'));
    exit;
}

// Insert user with PLAINTEXT password (MAJOR VULNERABILITY!)
$result = insertUser($email, $firstName, $lastName, $phone, $password);

if ($result) {
    http_response_code(201);
    echo json_encode(array('message' => 'User created'));
} else {
    http_response_code(500);
    echo json_encode(array('message' => 'Registration failed'));
}

mysqli_close($conn);
