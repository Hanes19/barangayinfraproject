<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    
    $ceo_status = mysqli_real_escape_string($conn, $_POST['ceo_status']);
    $remarks = mysqli_real_escape_string($conn, $_POST['remarks']);
    $action = $_POST['action'];

    // Grab the logged-in user's name from the session (fallback if not set)
    $engineer_name = isset($_SESSION['full_name']) ? mysqli_real_escape_string($conn, $_SESSION['full_name']) : 'Admin Engineer';

    if ($id > 0) {
        
        // --- NEW LOGIC: CPDC Certification Check ---
        // Fetch the current CPDC status (Assuming it's stored in the 'status' column)
        $check_query = "SELECT status FROM projects WHERE id = $id";
        $check_result = mysqli_query($conn, $check_query);
        $row = mysqli_fetch_assoc($check_result);
        $cpdc_status = strtolower($row['status']);

        // Block the Admin if they try to approve, but CPDC is not approved
        if ($ceo_status === 'approved' && $cpdc_status !== 'approved') {
            // Redirect back to the planning page with an error flag
            header("Location: admin_planning.php?msg=cpdc_error");
            exit();
        }
        // -------------------------------------------

        if ($action === 'transmit') {
            // 1. Mark as transmitted
            $update_query = "UPDATE projects SET ceo_status='transmitted', remarks='$remarks' WHERE id=$id";
            mysqli_query($conn, $update_query);
            
            // 2. Log the action
            $log_details = "Transmitted project to Main Office. Remarks: $remarks";
            $log_query = "INSERT INTO project_logs (project_id, user_name, action_details) VALUES ($id, '$engineer_name', '$log_details')";
            mysqli_query($conn, $log_query);
            
            header("Location: admin_planning.php?msg=transmitted");
            exit();
        } else {
            // 1. Standard Save
            $update_query = "UPDATE projects SET ceo_status='$ceo_status', remarks='$remarks' WHERE id=$id";
            mysqli_query($conn, $update_query);
            
            // 2. Log the action (Fixed the single quotes issue from earlier)
            $log_details = "Updated CEO Approval to " . ucfirst($ceo_status) . ". Remarks: $remarks";
            $log_query = "INSERT INTO project_logs (project_id, user_name, action_details) VALUES ($id, '$engineer_name', '$log_details')";
            mysqli_query($conn, $log_query);
            
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