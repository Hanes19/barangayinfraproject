<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    $type = mysqli_real_escape_string($conn, $_POST['implementation_type']);
    
    // Default to NULL if they aren't provided (e.g., By Administration)
    $spend = isset($_POST['spend_amount']) ? mysqli_real_escape_string($conn, $_POST['spend_amount']) : NULL;
    $timeline = isset($_POST['program_timeline']) ? mysqli_real_escape_string($conn, $_POST['program_timeline']) : NULL;
    
    $image_query_part = "";

    // Handle Image Upload
    if (isset($_FILES['inspection_image']) && $_FILES['inspection_image']['error'] == 0) {
        $filename = $_FILES['inspection_image']['name'];
        $new_filename = time() . '_Inspection_' . basename($filename);
        $target_dir = "uploads/docs/"; // Using your existing uploads folder
        
        // Create directory if it doesn't exist
        if (!file_exists($target_dir)) { mkdir($target_dir, 0777, true); }
        
        if (move_uploaded_file($_FILES['inspection_image']['tmp_name'], $target_dir . $new_filename)) {
            $image_query_part = ", inspection_image = '$new_filename'";
        }
    }

    // Update the database. Change monitoring_status so the Admin knows it was requested.
    $update_query = "UPDATE projects SET 
                     implementation_type = '$type', 
                     spend_amount = '$spend', 
                     program_timeline = '$timeline',
                     monitoring_status = 'inspection_requested'
                     $image_query_part 
                     WHERE id = $id";
                     
    mysqli_query($conn, $update_query);

    // Log it
    $log_details = "Barangay requested inspection. Type: " . ucfirst($type);
    mysqli_query($conn, "INSERT INTO project_logs (project_id, user_name, action_details) VALUES ($id, 'Barangay User', '$log_details')");

    header("Location: barangay_request_inspection.php?msg=submitted");
    exit();
}
?>