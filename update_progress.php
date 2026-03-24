include 'db.php';

if (isset($_POST['upload_progress'])) {
    $id = $_POST['id'];
    $target_dir = "uploads/progress_images/";
    $img_name = time() . "_" . basename($_FILES["progress_img"]["name"]);
    
    if (move_uploaded_file($_FILES["progress_img"]["tmp_name"], $target_dir . $img_name)) {
        // Update the database with the image path and set status to 'done'
        $sql = "UPDATE projects SET progress_image = '$img_name', status = 'done' WHERE id = $id";
        mysqli_query($conn, $sql);
        echo "Progress updated with image!";
    }
}