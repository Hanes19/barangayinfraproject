<?php
include 'db.php';
$message = "";

if (isset($_POST['submit_application'])) {
    // PHP backend logic remains the same
    $type_of_request = mysqli_real_escape_string($conn, $_POST['type_of_request']);
    $title           = mysqli_real_escape_string($conn, $_POST['title']);
    $loc_barangay    = mysqli_real_escape_string($conn, $_POST['location_barangay']);
    $loc_details     = mysqli_real_escape_string($conn, $_POST['location_details']);
    $source_of_fund  = mysqli_real_escape_string($conn, $_POST['source_of_fund']);
    $punong_barangay = mysqli_real_escape_string($conn, $_POST['punong_barangay']);
    $budget          = floatval($_POST['budget']);
    $description     = mysqli_real_escape_string($conn, $_POST['description']);
    
    $date_today = date("F j, Y");
    
    // Generate Word document content (same)
    $letter_content = "
    <html xmlns:o='urn:schemas-microsoft-com:office:office' xmlns:w='urn:schemas-microsoft-com:office:word' xmlns='http://www.w3.org/TR/REC-html40'>
    <head><title>Project Proposal</title></head>
    <body style='font-family: Arial, sans-serif; font-size: 12pt; line-height: 1.5;'>
        <div style='text-align: center; margin-bottom: 30px;'>
            <strong>Republic of the Philippines</strong><br>
            Province of Bukidnon<br>
            City of Valencia<br>
            <strong>BARANGAY " . strtoupper($loc_barangay) . "</strong><br>
            Office of the Punong Barangay
        </div>

        <p><strong>Date:</strong> $date_today</p>
        
        <p>
            <strong>To the City Engineering Office / City Mayor's Office</strong><br>
            Valencia City, Bukidnon
        </p>

        <p><strong>Subject:</strong> Formal Request for $type_of_request - $title</p>

        <p>Dear Sir/Madam,</p>

        <p>Greetings of peace!</p>

        <p>We are respectfully submitting this formal proposal and request for a <strong>$type_of_request</strong> for our proposed project: <strong>\"$title\"</strong>.</p>

        <p><strong>Project Details:</strong></p>
        <ul>
            <li><strong>Specific Location:</strong> $loc_details, Barangay $loc_barangay, Valencia City</li>
            <li><strong>Proposed Budget:</strong> Php " . number_format($budget, 2) . "</li>
            <li><strong>Intended Source of Fund:</strong> $source_of_fund</li>
        </ul>

        <p><strong>Project Description & Justification:</strong><br>
        $description</p>

        <p>We believe this project will greatly benefit our community. We are hoping for your favorable response and approval regarding this matter.</p>

        <br><br>
        <p>Respectfully yours,</p>
        <br>
        <p>
            <strong>HON. " . strtoupper($punong_barangay) . "</strong><br>
            Punong Barangay<br>
            Barangay $loc_barangay
        </p>
    </body>
    </html>";

    $target_dir = "uploads/docs/";
    $clean_title = preg_replace('/[^A-Za-z0-9\-]/', '_', $title);
    $generated_file_name = time() . "_" . $clean_title . "_Proposal.doc";
    
    if (file_put_contents($target_dir . $generated_file_name, $letter_content)) {
        $final_file_to_save = $generated_file_name; 

        if (!empty($_FILES["attachment"]["name"])) {
            $upload_name = time() . "_SUPPORTING_" . basename($_FILES["attachment"]["name"]);
            if (move_uploaded_file($_FILES["attachment"]["tmp_name"], $target_dir . $upload_name)) {
                $message .= "<div class='alert alert-info mb-1'>Supporting document uploaded.</div>";
            }
        }

        $sql = "INSERT INTO projects (
                    title, type_of_request, location_barangay, location_details, 
                    source_of_fund, punong_barangay, budget, description, application_file, status
                ) VALUES (
                    '$title', '$type_of_request', '$loc_barangay', '$loc_details', 
                    '$source_of_fund', '$punong_barangay', $budget, '$description', '$generated_file_name', 'pending'
                )";
        
        if (mysqli_query($conn, $sql)) {
            $message .= "<div class='alert alert-success'>
                <strong>Success!</strong> Application submitted.<br>
                A formal proposal document has been automatically generated and attached to this project.
                <br><br>
                <a href='uploads/docs/$generated_file_name' class='btn btn-sm btn-outline-success' download>
                    Download Generated Word Document
                </a>
            </div>";
        } else {
            $message .= "<div class='alert alert-danger'>Database Error: " . mysqli_error($conn) . "</div>";
        }
    } else {
        $message = "<div class='alert alert-danger'>Error generating document. Please check folder permissions for 'uploads/docs/'.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Submit Project Application</title>
    <link rel="icon" type="image/png" href="cityengineerlogo.jpg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: url('Plaza.jpg') no-repeat center center fixed;
            background-size: cover;
        }
        .card {
            background-color: rgba(255, 255, 255, 0.95);
            border: 2px solid #28a745; /* green border */
        }
        .card-header {
            background-color: #28a745;
            color: white;
            font-weight: bold;
        }
        .btn-success {
            background-color: #28a745;
            border-color: #28a745;
        }
        .btn-success:hover {
            background-color: #218838;
            border-color: #1e7e34;
        }
        label {
            color: #155724;
        }
        .form-control:focus {
            border-color: #28a745;
            box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
        }
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
        }
        .alert-info {
            background-color: #cce5ff;
            color: #004085;
        }
    </style>
</head>
<body>
<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header text-center">
                    Submit New Project Proposal
                </div>
                <div class="card-body p-4">
                    <?php echo $message; ?>
                    
                    <form action="submit_project.php" method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Type of Request</label>
                            <select name="type_of_request" class="form-select" required>
                                <option value="" disabled selected>Select request type...</option>
                                <option value="Program of works">Program of Works</option>
                                <option value="City inspection">City Inspection</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Project Title</label>
                            <input type="text" name="title" class="form-control" required>
                        </div>

                        <div class="row mb-3 bg-light p-3 border rounded">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Barangay</label>
                                <select name="location_barangay" class="form-select" required>
                                    <option value="" disabled selected>Select Barangay...</option>
                                    <option value="Poblacion">Poblacion</option>
                                    <option value="Lumbo">Lumbo</option>
                                    <option value="Batangan">Batangan</option>
                                    <option value="Bagontaas">Bagontaas</option>
                                    <option value="Mailag">Mailag</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Additional Location Info</label>
                                <input type="text" name="location_details" class="form-control" placeholder="Purok, Street, or Landmark" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Source of Fund</label>
                                <select name="source_of_fund" class="form-select" required>
                                    <option value="" disabled selected>Select funding source...</option>
                                    <option value="Barangay fund">Barangay Fund</option>
                                    <option value="City aid">City Aid</option>
                                    <option value="Others">Others</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Name of Punong Barangay</label>
                                <input type="text" name="punong_barangay" class="form-control" placeholder="Enter Full Name" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Proposed Budget (₱)</label>
                            <input type="number" step="0.01" name="budget" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Project Description & Justification</label>
                            <textarea name="description" class="form-control" rows="3" required></textarea>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Attach Supporting Documents (Optional)</label>
                            <input type="file" name="attachment" class="form-control" accept=".pdf, .doc, .docx">
                            <small class="text-muted">A formal proposal letter will be automatically generated. You only need to attach files here if you have extra blueprints or pictures.</small>
                        </div>
                        
                        <button type="submit" name="submit_application" class="btn btn-success w-100 py-2 fw-bold">Generate Letter & Submit</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>