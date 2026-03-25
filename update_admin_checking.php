<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    $current_attempts = intval($_POST['current_attempts']);
    $is_first_submission = isset($_POST['is_first_submission']) ? intval($_POST['is_first_submission']) : 0;
    $admin_notes = mysqli_real_escape_string($conn, $_POST['admin_notes']);
    $engineer_name = isset($_SESSION['full_name']) ? mysqli_real_escape_string($conn, $_SESSION['full_name']) : 'Admin Engineer';

    $new_attempts = $current_attempts + 1;
    $file_update_query = ""; // Default empty string

    // --- HANDLE FILE UPLOAD LOGIC ---
    if (isset($_FILES['new_document']) && $_FILES['new_document']['error'] == 0) {
        $allowed_exts = ['pdf', 'doc', 'docx'];
        $filename = $_FILES['new_document']['name'];
        $file_ext = pathinfo($filename, PATHINFO_EXTENSION);
        
        if (in_array(strtolower($file_ext), $allowed_exts)) {
            $new_filename = time() . '_' . basename($filename);
            $target_dir = "uploads/docs/";
            $target_file = $target_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['new_document']['tmp_name'], $target_file)) {
                $file_update_query = ", application_file = '$new_filename'";
            }
        }
    }
    // --------------------------------

    if ($new_attempts >= 3 && $is_first_submission == 0) {
        // Auto Approve on 3rd attempt
        $update_query = "UPDATE projects SET checking_status = 'approved', submission_attempts = 3, approved_at = NOW() $file_update_query WHERE id = $id";
        mysqli_query($conn, $update_query);

        $log_details = "Admin resubmitted fixes. 3rd attempt reached. System AUTOMATICALLY FINALIZED AND APPROVED the project.";
        mysqli_query($conn, "INSERT INTO project_logs (project_id, user_name, action_details) VALUES ($id, 'System', '$log_details')");

        header("Location: admin_checking.php?msg=auto_approved");
        exit();
    } else {
        if ($is_first_submission == 1) {
            // FIRST SUBMISSION: Change ceo_status to transmitted so the CEO can now see it
            $update_query = "UPDATE projects SET ceo_status = 'transmitted', checking_status = 'pending', submission_attempts = 1 $file_update_query WHERE id = $id";
            mysqli_query($conn, $update_query);

            $log_details = "Admin submitted project to CEO Main for the first time. Admin notes: $admin_notes";
            if ($file_update_query !== "") {
                $log_details .= " [Document Attached]";
            }
        } else {
            // STANDARD RESUBMISSION
            // FIX: Added `ceo_status = 'transmitted'` here so it reappears in the CEO queue!
            $update_query = "UPDATE projects SET ceo_status = 'transmitted', checking_status = 'pending', submission_attempts = $new_attempts $file_update_query WHERE id = $id";
            mysqli_query($conn, $update_query);

            $log_details = "Admin resubmitted project to CEO Main (Attempt $new_attempts/3). Admin notes: $admin_notes";
            if ($file_update_query !== "") {
                $log_details .= " [New Document Uploaded]";
            }
        }
        
        mysqli_query($conn, "INSERT INTO project_logs (project_id, user_name, action_details) VALUES ($id, '$engineer_name', '$log_details')");

        header("Location: admin_checking.php?msg=resubmitted");
        exit();
    }
} else {
    header("Location: admin_checking.php");
}
?>