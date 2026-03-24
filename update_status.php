<?php
// 1. Include the database connection
include 'db.php';

// 2. Check if the form was actually submitted
if (isset($_POST['update_status'])) {
    
    // 3. Get the data from the form and sanitize it to prevent SQL injection
    $id = intval($_POST['id']); 
    $new_status = mysqli_real_escape_string($conn, $_POST['status']);
    
    // 4. (Optional but recommended) Validate that the status is one of our allowed options
    $allowed_statuses = ['pending', 'approved', 'ongoing', 'done', 'rejected'];
    
    if (in_array($new_status, $allowed_statuses)) {
        
        // 5. Create and run the SQL Update query
        $update_query = "UPDATE projects SET status = '$new_status' WHERE id = $id";
        
        if (mysqli_query($conn, $update_query)) {
            // 6. If successful, redirect back to the dashboard
            header("Location: admin_dashboard.php?msg=status_updated");
            exit(); // Always use exit() after a header redirect
        } else {
            // If the query fails, show the error
            echo "Error updating record: " . mysqli_error($conn);
        }
    } else {
        echo "Error: Invalid status selected.";
    }
    
} else {
    // If someone tries to visit this page directly without submitting the form,
    // redirect them back to the dashboard.
    header("Location: admin_dashboard.php");
    exit();
}
?>