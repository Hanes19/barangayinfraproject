<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    $decision = mysqli_real_escape_string($conn, $_POST['decision']);
    $remarks = mysqli_real_escape_string($conn, $_POST['remarks']);
    
    // Grab the CEO's name from session, or default to generic title
    $user_name = isset($_SESSION['full_name']) ? mysqli_real_escape_string($conn, $_SESSION['full_name']) : 'CEO / City Engineer';

    $file_note = "";

    // --- HANDLE OPTIONAL REDLINE/FEEDBACK FILE UPLOAD ---
    if (isset($_FILES['ceo_document']) && $_FILES['ceo_document']['error'] == 0) {
        $allowed_exts = ['pdf', 'doc', 'docx'];
        $filename = $_FILES['ceo_document']['name'];
        $file_ext = pathinfo($filename, PATHINFO_EXTENSION);
        
        if (in_array(strtolower($file_ext), $allowed_exts)) {
            $new_filename = time() . '_CEO_REVIEW_' . basename($filename);
            $target_dir = "uploads/docs/";
            $target_file = $target_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['ceo_document']['tmp_name'], $target_file)) {
                // We append the file name to the remarks so the Admin knows exactly what to look for in the folder
                $file_note = "\n\n[Attached Review Document: " . $new_filename . "]";
            }
        }
    }
    // ----------------------------------------------------

    $final_remarks = $remarks . $file_note;

    if ($decision === 'approved') {
        // Project is fully approved and ready for the next phase (Monitoring)!
        $update_query = "UPDATE projects 
                         SET ceo_status = 'approved', 
                             checking_status = 'approved', 
                             ceo_remarks = '$final_remarks',
                             approved_at = NOW() 
                         WHERE id = $id";
        mysqli_query($conn, $update_query);

        $log_details = "CEO completely APPROVED the project. Remarks: $final_remarks";
        
    } else {
        // Project is declined/needs fixes. Send it back to the Admin Checking dashboard.
        $update_query = "UPDATE projects 
                         SET checking_status = 'declined', 
                             ceo_remarks = '$final_remarks' 
                         WHERE id = $id";
        mysqli_query($conn, $update_query);

        $log_details = "CEO RETURNED the project for fixes. Remarks: $final_remarks";
    }

    // Log the action
    $log_query = "INSERT INTO project_logs (project_id, user_name, action_details) VALUES ($id, '$user_name', '$log_details')";
    mysqli_query($conn, $log_query);

    // Redirect back to CEO dashboard with success message
    header("Location: ceo_main_dashboard.php?msg=processed");
    exit();

} else {
    header("Location: ceo_main_dashboard.php");
    exit();
}
?>