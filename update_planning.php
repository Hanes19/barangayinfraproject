<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $remarks = mysqli_real_escape_string($conn, $_POST['remarks']);
    $action = $_POST['action']; // either 'save' or 'transmit'

    if ($id > 0) {
        if ($action === 'transmit') {
            // Logic for transmitting to main office. 
            // You can append 'Transmitted' to status or handle it based on your database design.
            $update_query = "UPDATE projects SET status='$status', remarks='$remarks' WHERE id=$id";
            mysqli_query($conn, $update_query);
            
            // Redirect with transmitted message
            header("Location: planning.php?msg=transmitted");
            exit();
        } else {
            // Standard save action
            $update_query = "UPDATE projects SET status='$status', remarks='$remarks' WHERE id=$id";
            mysqli_query($conn, $update_query);
            
            // Redirect with updated message
            header("Location: planning.php?msg=updated");
            exit();
        }
    } else {
        echo "Error: Invalid project ID.";
    }
} else {
    header("Location: planning.php");
    exit();
}
?>