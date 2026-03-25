<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $new_status = $_POST['status'] ?? '';

    $allowed_statuses = ['pending', 'approved', 'ongoing', 'done', 'rejected'];

    if ($id > 0 && in_array($new_status, $allowed_statuses)) {
        
        $update_file_query = ""; // Default empty string

        // Check if a file was uploaded without errors
        if (isset($_FILES['signed_document']) && $_FILES['signed_document']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = 'uploads/docs/';
            
            // Create directory if it doesn't exist
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            // Prefix with timestamp to prevent naming conflicts
            $file_name = time() . '_' . basename($_FILES['signed_document']['name']);
            $target_file = $upload_dir . $file_name;
            
            // Move uploaded file
            if (move_uploaded_file($_FILES['signed_document']['tmp_name'], $target_file)) {
                $escaped_file_name = mysqli_real_escape_string($conn, $file_name);
                // Prepare the SQL snippet to overwrite the application_file
                $update_file_query = ", application_file='$escaped_file_name'";
            } else {
                echo "Error uploading the file.";
                exit();
            }
        }

        // Execute the update query (will update the file if uploaded, otherwise just updates status)
        $update_query = "UPDATE projects SET status='$new_status' $update_file_query WHERE id=$id";

        if (mysqli_query($conn, $update_query)) {
            header("Location: planning.php");
            exit();
        } else {
            echo "Database error: " . mysqli_error($conn);
        }
    } else {
        echo "Error: Invalid request or status.";
    }
} else {
    header("Location: planning.php");
    exit();
}
?>