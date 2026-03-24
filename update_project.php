include 'db.php';

if (isset($_POST['update_status'])) {
    $project_id = $_POST['id'];
    $new_status = $_POST['status'];

    $sql = "UPDATE projects SET status = '$new_status' WHERE id = $project_id";
    
    if (mysqli_query($conn, $sql)) {
        header("Location: admin_dashboard.php?msg=updated");
    }
}