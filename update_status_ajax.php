<?php
session_start();
include 'db.php';

// Role protection
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'lnbpres') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$action = isset($_GET['action']) ? $_GET['action'] : '';

$response = ['success' => false];

if ($id > 0 && in_array($action, ['approve', 'reject'])) {
    $status = ($action === 'approve') ? 'Approved' : 'Rejected';
    
    // Use prepared statement for security
    $stmt = mysqli_prepare($conn, "UPDATE projects SET status = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "si", $status, $id);
    
    if (mysqli_stmt_execute($stmt)) {
        $response['success'] = true;
    }
    
    mysqli_stmt_close($stmt);
}

header('Content-Type: application/json');
echo json_encode($response);
?>