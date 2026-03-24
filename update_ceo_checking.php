<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    $decision = mysqli_real_escape_string($conn, $_POST['decision']);
    $remarks = mysqli_real_escape_string($conn, $_POST['remarks']);
    
    // Fallback name if you don't have a specific CEO session variable
    $ceo_name = isset($_SESSION['full_name']) ? mysqli_real_escape_string($conn, $_SESSION['full_name']) : 'CEO Main User';

    if ($decision === 'approved') {
        // CRITICAL: Records the exact time of final approval!
        $update_query = "UPDATE projects SET checking_status = 'approved', approved_at = NOW(), ceo_main_remarks = '$remarks' WHERE id = $id";
        mysqli_query($conn, $update_query);
        
        $log_details = "CEO Main APPROVED the project. Timestamp recorded. Remarks: $remarks";
        mysqli_query($conn, "INSERT INTO project_logs (project_id, user_name, action_details) VALUES ($id, '$ceo_name', '$log_details')");
        
    } else {
        // Declined
        $update_query = "UPDATE projects SET checking_status = 'declined', ceo_main_remarks = '$remarks' WHERE id = $id";
        mysqli_query($conn, $update_query);
        
        $log_details = "CEO Main DECLINED the project. Suggestions provided: $remarks";
        mysqli_query($conn, "INSERT INTO project_logs (project_id, user_name, action_details) VALUES ($id, '$ceo_name', '$log_details')");
    }

    header("Location: ceo_main_dashboard.php?msg=processed");
    exit();
} else {
    header("Location: ceo_main_dashboard.php");
}
?>