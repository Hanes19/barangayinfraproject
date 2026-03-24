<?php
include 'db.php';
$message = "";

if (isset($_POST['submit_application'])) {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $budget = floatval($_POST['budget']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    
    // File Upload Handling
    $target_dir = "uploads/docs/";
    // Add a timestamp to the filename so files with the same name don't overwrite each other
    $file_name = time() . "_" . basename($_FILES["attachment"]["name"]);
    $target_file = $target_dir . $file_name;
    
    // Allow certain file formats (Optional but good for security)
    $fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    if($fileType != "pdf" && $fileType != "doc" && $fileType != "docx") {
        $message = "<div class='alert alert-danger'>Sorry, only PDF, DOC & DOCX files are allowed.</div>";
    } else {
        // Attempt to move the uploaded file to your "uploads/docs" folder
        if (move_uploaded_file($_FILES["attachment"]["tmp_name"], $target_file)) {
            
            // Insert data into database
            $sql = "INSERT INTO projects (title, budget, description, application_file, status) 
                    VALUES ('$title', $budget, '$description', '$file_name', 'pending')";
            
            if (mysqli_query($conn, $sql)) {
                $message = "<div class='alert alert-success'>Application submitted successfully!</div>";
            } else {
                $message = "<div class='alert alert-danger'>Database Error: " . mysqli_error($conn) . "</div>";
            }
        } else {
            $message = "<div class='alert alert-danger'>Sorry, there was an error uploading your file. Make sure the 'uploads/docs' folder exists.</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Submit Project Application</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Submit New Project Proposal</h4>
                </div>
                <div class="card-body p-4">
                    <?php echo $message; ?>
                    
                    <form action="submit_project.php" method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label>Project Title</label>
                            <input type="text" name="title" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Proposed Budget (₱)</label>
                            <input type="number" step="0.01" name="budget" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Project Description & Justification</label>
                            <textarea name="description" class="form-control" rows="4" required></textarea>
                        </div>
                        <div class="mb-4">
                            <label>Attach Proposal Document (PDF/Doc)</label>
                            <input type="file" name="attachment" class="form-control" accept=".pdf, .doc, .docx" required>
                        </div>
                        <button type="submit" name="submit_application" class="btn btn-success w-100">Submit Application</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>