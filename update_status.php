<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $new_status = $_POST['status'] ?? '';

    $allowed_statuses = ['pending', 'approved', 'ongoing', 'done', 'rejected'];

    if ($id > 0 && in_array($new_status, $allowed_statuses)) {
        $update_query = "UPDATE projects SET status='$new_status' WHERE id=$id";

        if (mysqli_query($conn, $update_query)) {
            header("Location: planning.php");
            exit();
        } else {
            echo "Database error: " . mysqli_error($conn);
        }
    } else {
        echo "Error: Invalid request or status.";
    }
} else {
    header("Location: planning.php");
    exit();
}
?>