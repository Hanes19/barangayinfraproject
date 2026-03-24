<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    $engineer_name = isset($_SESSION['full_name']) ? mysqli_real_escape_string($conn, $_SESSION['full_name']) : 'Admin Engineer';

    // Verify and Complete
    if (isset($_POST['action']) && $_POST['action'] === 'complete') {
        
        // Mark as 100% completed, record timestamp
        $update_query = "UPDATE projects SET progress_percentage = 100, monitoring_status = 'completed', completed_at = NOW() WHERE id = $id";
        mysqli_query($conn, $update_query);

        $log_details = "Admin reviewed Inspection Request and verified the project. Project officially finalized and moved to COMPLETED.";
        mysqli_query($conn, "INSERT INTO project_logs (project_id, user_name, action_details) VALUES ($id, '$engineer_name', '$log_details')");

        header("Location: admin_monitoring.php?msg=completed");
        exit();
    }
} else {
    header("Location: admin_monitoring.php");
}
?>