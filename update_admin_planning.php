<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    
    // Capture the new CEO status and the remarks
    $ceo_status = mysqli_real_escape_string($conn, $_POST['ceo_status']);
    $remarks = mysqli_real_escape_string($conn, $_POST['remarks']);
    $action = $_POST['action'];

    if ($id > 0) {
        if ($action === 'transmit') {
            // Update the ceo_status and remarks in the database
            $update_query = "UPDATE projects SET ceo_status='$ceo_status', remarks='$remarks' WHERE id=$id";
            mysqli_query($conn, $update_query);
            
            // Redirect back with "transmitted" success message
            header("Location: admin_planning.php?msg=transmitted");
            exit();
        } else {
            // Standard "Save" action
            $update_query = "UPDATE projects SET ceo_status='$ceo_status', remarks='$remarks' WHERE id=$id";
            mysqli_query($conn, $update_query);
            
            // Redirect back with "updated" success message
            header("Location: admin_planning.php?msg=updated");
            exit();
        }
    } else {
        echo "Error: Invalid project ID.";
    }
} else {
    header("Location: admin_planning.php");
    exit();
}
?>