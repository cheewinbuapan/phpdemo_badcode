<?php
// ===========================
// Feature 1: Seed Data
// Auto-seed products and statuses
// ===========================

header('Content-Type: application/json');

require_once __DIR__ . '/../config/db.php';

// Read seed SQL file
$seedFile = __DIR__ . '/../sql/seed.sql';

if (!file_exists($seedFile)) {
    http_response_code(500);
    echo json_encode(array('message' => 'Seed file not found'));
    exit;
}

$sql = file_get_contents($seedFile);

// Execute multi-query (potentially dangerous!)
if (mysqli_multi_query($conn, $sql)) {
    do {
        // Fetch all results
        if ($result = mysqli_store_result($conn)) {
            mysqli_free_result($result);
        }
    } while (mysqli_next_result($conn));
    
    http_response_code(200);
    echo json_encode(array(
        'message' => 'Seeded successfully',
        'status' => 'OK'
    ));
} else {
    http_response_code(500);
    echo json_encode(array(
        'message' => 'Seed failed: ' . mysqli_error($conn)
    ));
}

mysqli_close($conn);
