include 'db.php';

if (isset($_POST['submit'])) {
    $title = $_POST['title'];
    $desc = $_POST['description'];
    
    // File Upload Logic
    $target_dir = "uploads/docs/";
    $file_name = time() . "_" . basename($_FILES["attachment"]["name"]);
    $target_file = $target_dir . $file_name;

    if (move_uploaded_file($_FILES["attachment"]["tmp_name"], $target_file)) {
        $sql = "INSERT INTO projects (title, description, status, application_file) 
                VALUES ('$title', '$desc', 'pending', '$file_name')";
        
        if (mysqli_query($conn, $sql)) {
            echo "Application submitted successfully!";
        }
    } else {
        echo "Error uploading file.";
    }
}