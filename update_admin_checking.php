<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    $current_attempts = intval($_POST['current_attempts']);
    $admin_notes = mysqli_real_escape_string($conn, $_POST['admin_notes']);
    $engineer_name = isset($_SESSION['full_name']) ? mysqli_real_escape_string($conn, $_SESSION['full_name']) : 'Admin Engineer';

    $new_attempts = $current_attempts + 1;
    $file_update_query = ""; // Default empty string

    // --- HANDLE FILE UPLOAD LOGIC ---
    // Check if a new file was uploaded without errors
    if (isset($_FILES['new_document']) && $_FILES['new_document']['error'] == 0) {
        $allowed_exts = ['pdf', 'doc', 'docx'];
        $filename = $_FILES['new_document']['name'];
        $file_ext = pathinfo($filename, PATHINFO_EXTENSION);
        
        // Verify extension
        if (in_array(strtolower($file_ext), $allowed_exts)) {
            // Generate a unique file name to avoid overwriting existing files
            $new_filename = time() . '_' . basename($filename);
            $target_dir = "uploads/docs/";
            $target_file = $target_dir . $new_filename;
            
            // Move file to the server folder
            if (move_uploaded_file($_FILES['new_document']['tmp_name'], $target_file)) {
                // If successful, prepare the SQL snippet to update the application_file column
                $file_update_query = ", application_file = '$new_filename'";
            }
        }
    }
    // --------------------------------

    // RULE: If they reach 3 tries, it is AUTOMATICALLY APPROVED and TIMESTAMPED.
    if ($new_attempts >= 3) {
        $update_query = "UPDATE projects SET checking_status = 'approved', submission_attempts = 3, approved_at = NOW() $file_update_query WHERE id = $id";
        mysqli_query($conn, $update_query);

        // Log the auto-approval
        $log_details = "Admin resubmitted fixes. 3rd attempt reached. System AUTOMATICALLY FINALIZED AND APPROVED the project.";
        mysqli_query($conn, "INSERT INTO project_logs (project_id, user_name, action_details) VALUES ($id, 'System', '$log_details')");

        header("Location: admin_checking.php?msg=auto_approved");
        exit();
    } else {
        // Just a standard resubmission
        $update_query = "UPDATE projects SET checking_status = 'pending', submission_attempts = $new_attempts $file_update_query WHERE id = $id";
        mysqli_query($conn, $update_query);

        // Log the resubmission
        $log_details = "Admin resubmitted project to CEO Main (Attempt $new_attempts/3). Admin notes: $admin_notes";
        if ($file_update_query !== "") {
            $log_details .= " [New Document Uploaded]";
        }
        mysqli_query($conn, "INSERT INTO project_logs (project_id, user_name, action_details) VALUES ($id, '$engineer_name', '$log_details')");

        header("Location: admin_checking.php?msg=resubmitted");
        exit();
    }
} else {
    header("Location: admin_checking.php");
}
?>