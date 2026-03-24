<?php
session_start();
include 'db.php';

// Role protection
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'lnbpres') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Use single query with conditional aggregation for better performance
$query = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN status = 'Approved' THEN 1 ELSE 0 END) as approved,
    SUM(CASE WHEN status = 'Rejected' THEN 1 ELSE 0 END) as rejected
    FROM projects";

$result = mysqli_query($conn, $query);
$counts = mysqli_fetch_assoc($result);

header('Content-Type: application/json');
echo json_encode($counts);
?>