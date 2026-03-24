<?php
include 'db.php';
$message = "";

// Check if an ID was passed in the URL (e.g., update_progress.php?id=5)
if (!isset($_GET['id']) && !isset($_POST['upload_photo'])) {
    die("Project ID is missing. Go back to the dashboard.");
}

$project_id = isset($_GET['id']) ? intval($_GET['id']) : intval($_POST['project_id']);

if (isset($_POST['upload_photo'])) {
    $target_dir = "uploads/progress_images/";
    $img_name = time() . "_" . basename($_FILES["progress_img"]["name"]);
    $target_file = $target_dir . $img_name;
    
    // Check if image file is a actual image or fake image
    $check = getimagesize($_FILES["progress_img"]["tmp_name"]);
    if($check !== false) {
        if (move_uploaded_file($_FILES["progress_img"]["tmp_name"], $target_file)) {
            // Update the database with the image filename
            $sql = "UPDATE projects SET progress_image = '$img_name' WHERE id = $project_id";
            
            if (mysqli_query($conn, $sql)) {
                $message = "<div class='alert alert-success'>Progress photo uploaded successfully! <a href='admin_dashboard.php'>Return to Dashboard</a></div>";
            }
        } else {
            $message = "<div class='alert alert-danger'>Error uploading photo. Make sure 'uploads/progress_images' exists.</div>";
        }
    } else {
        $message = "<div class='alert alert-danger'>File is not an image.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Upload Progress Photo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-secondary text-white">
                    <h4 class="mb-0">Upload Project Progress Photo</h4>
                </div>
                <div class="card-body p-4">
                    <?php echo $message; ?>
                    
                    <form action="update_progress.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="project_id" value="<?php echo $project_id; ?>">
                        
                        <div class="mb-4">
                            <label>Select Image (JPG, PNG)</label>
                            <input type="file" name="progress_img" class="form-control" accept="image/png, image/jpeg, image/jpg" required>
                        </div>
                        <button type="submit" name="upload_photo" class="btn btn-primary w-100">Upload Photo</button>
                        <a href="admin_dashboard.php" class="btn btn-outline-secondary w-100 mt-2">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>