<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    $decision = mysqli_real_escape_string($conn, $_POST['decision']);
    $remarks = mysqli_real_escape_string($conn, $_POST['remarks']);
    
    // Fallback name if you don't have a specific CEO session variable
    $ceo_name = isset($_SESSION['full_name']) ? mysqli_real_escape_string($conn, $_SESSION['full_name']) : 'CEO Main User';

    $file_update_query = ""; // Default empty string

    // --- HANDLE CEO FILE UPLOAD LOGIC ---
    if (isset($_FILES['ceo_document']) && $_FILES['ceo_document']['error'] == 0) {
        $allowed_exts = ['pdf', 'doc', 'docx'];
        $filename = $_FILES['ceo_document']['name'];
        $file_ext = pathinfo($filename, PATHINFO_EXTENSION);
        
        // Verify extension
        if (in_array(strtolower($file_ext), $allowed_exts)) {
            // Generate a unique file name starting with "CEO_Feedback_"
            $new_filename = time() . '_CEO_Feedback_' . basename($filename);
            $target_dir = "uploads/docs/";
            $target_file = $target_dir . $new_filename;
            
            // Move file to the server folder
            if (move_uploaded_file($_FILES['ceo_document']['tmp_name'], $target_file)) {
                $file_update_query = ", application_file = '$new_filename'";
            }
        }
    }
    // ------------------------------------

    if ($decision === 'approved') {
        // CRITICAL: Records the exact time of final approval!
        // Added: status = 'approved' to push the main project progress forward
        $update_query = "UPDATE projects SET checking_status = 'approved', status = 'approved', approved_at = NOW(), ceo_main_remarks = '$remarks' $file_update_query WHERE id = $id";
        mysqli_query($conn, $update_query);
        
        $log_details = "CEO Main APPROVED the project. Timestamp recorded. Remarks: $remarks";
        if ($file_update_query !== "") {
            $log_details .= " [CEO Attached Signed/Finalized Document]";
        }
        mysqli_query($conn, "INSERT INTO project_logs (project_id, user_name, action_details) VALUES ($id, '$ceo_name', '$log_details')");
        
    } else {
        // Declined
        $update_query = "UPDATE projects SET checking_status = 'declined', ceo_main_remarks = '$remarks' $file_update_query WHERE id = $id";
        mysqli_query($conn, $update_query);
        
        $log_details = "CEO Main DECLINED the project. Suggestions provided: $remarks";
        if ($file_update_query !== "") {
            $log_details .= " [CEO Attached Redlined/Feedback Document]";
        }
        mysqli_query($conn, "INSERT INTO project_logs (project_id, user_name, action_details) VALUES ($id, '$ceo_name', '$log_details')");
    }

    header("Location: ceo_main_dashboard.php?msg=processed");
    exit();
} else {
    header("Location: ceo_main_dashboard.php");
}
?>