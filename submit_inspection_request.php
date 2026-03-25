<?php
session_start();
include 'db.php';

// 1. HANDLE THE FORM SUBMISSION (POST REQUEST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    $type = mysqli_real_escape_string($conn, $_POST['implementation_type']);
    
    // Safely handle optional fields
    $spend_val = !empty($_POST['spend_amount']) ? "'" . mysqli_real_escape_string($conn, $_POST['spend_amount']) . "'" : "NULL";
    $timeline_val = !empty($_POST['program_timeline']) ? "'" . mysqli_real_escape_string($conn, $_POST['program_timeline']) . "'" : "NULL";
    
    $image_query_part = "";

    // Handle Image Upload
    if (isset($_FILES['inspection_image']) && $_FILES['inspection_image']['error'] == 0) {
        $allowed_exts = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['inspection_image']['name'];
        $file_ext = pathinfo($filename, PATHINFO_EXTENSION);
        
        if (in_array(strtolower($file_ext), $allowed_exts)) {
            $new_filename = time() . '_Inspection_' . basename($filename);
            $target_dir = "uploads/docs/";
            
            if (!file_exists($target_dir)) { mkdir($target_dir, 0777, true); }
            
            if (move_uploaded_file($_FILES['inspection_image']['tmp_name'], $target_dir . $new_filename)) {
                $image_query_part = ", inspection_image = '$new_filename'";
            }
        }
    }

    // UPDATE QUERY: Sets monitoring to 'inspection_requested' AND main status to 'ongoing'
    $update_query = "UPDATE projects SET 
                     implementation_type = '$type', 
                     spend_amount = $spend_val, 
                     program_timeline = $timeline_val,
                     monitoring_status = 'inspection_requested',
                     status = 'ongoing'
                     $image_query_part 
                     WHERE id = $id";
                     
    mysqli_query($conn, $update_query);

    // Log it
    $log_details = "Barangay requested inspection. Type: " . ucfirst($type);
    mysqli_query($conn, "INSERT INTO project_logs (project_id, user_name, action_details) VALUES ($id, 'Barangay User', '$log_details')");

    // Redirect back to the client dashboard with a success message
    header("Location: client_dashboard.php?msg=inspection_requested");
    exit();
}

// 2. HANDLE THE PAGE LOAD (GET REQUEST)
$project_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($project_id == 0) { die("Invalid Project ID."); }

$query = "SELECT title FROM projects WHERE id = $project_id";
$result = mysqli_query($conn, $query);
$project = mysqli_fetch_assoc($result);

if (!$project) { die("Project not found."); }
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Request Inspection - <?php echo htmlspecialchars($project['title']); ?></title>
<link rel="icon" type="image/png" href="cityengineerlogo.jpg">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
    body { background: url('musuan.jpg') no-repeat center center fixed; background-size: cover; font-family: 'Segoe UI', sans-serif; }
    .overlay { min-height: 100vh; background-color: rgba(255, 255, 255, 0.85); padding-bottom: 50px; }
    .navbar-custom { background-color: #4caf50; }
    .navbar-custom .navbar-brand { color: white !important; font-weight: bold; }
    .form-container { background: white; border-radius: 12px; box-shadow: 0 8px 25px rgba(0,0,0,0.15); padding: 30px; margin-top: 40px; }
    .form-label { font-weight: 600; color: #1b5e20; }
</style>
</head>
<body>
<div class="overlay">
    <nav class="navbar navbar-expand-lg navbar-custom mb-4 shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="client_dashboard.php"><i class="fas fa-arrow-left me-2"></i> Back to Dashboard</a>
        </div>
    </nav>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="form-container">
                    <h3 class="mb-1 text-success"><i class="fas fa-hard-hat me-2"></i>Request Final Inspection</h3>
                    <p class="text-muted mb-4">Project: <strong><?php echo htmlspecialchars($project['title']); ?></strong></p>

                    <form action="submit_inspection_request.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="id" value="<?php echo $project_id; ?>">

                        <div class="mb-3">
                            <label for="implementation_type" class="form-label">Implementation Type</label>
                            <select name="implementation_type" id="implementation_type" class="form-select" required onchange="toggleFields()">
                                <option value="" disabled selected>Select how the project was implemented...</option>
                                <option value="contract">By Contract</option>
                                <option value="administration">By Administration</option>
                            </select>
                        </div>

                        <div id="contract_fields" style="display: none; background-color: #f8fafc; padding: 15px; border-radius: 8px; border: 1px solid #e2e8f0; margin-bottom: 15px;">
                            <div class="mb-3">
                                <label for="spend_amount" class="form-label">Total Spend Amount (₱)</label>
                                <input type="number" step="0.01" name="spend_amount" id="spend_amount" class="form-control" placeholder="e.g. 50000.00">
                            </div>
                            <div class="mb-3">
                                <label for="program_timeline" class="form-label">Program Timeline</label>
                                <textarea name="program_timeline" id="program_timeline" class="form-control" rows="2" placeholder="e.g. Started Jan 1 - Completed March 1"></textarea>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="inspection_image" class="form-label">Upload Finished Project Image</label>
                            <input type="file" name="inspection_image" id="inspection_image" class="form-control" accept="image/*" required>
                            <small class="text-muted d-block mt-1">Please provide a clear photo of the completed project for verification.</small>
                            <small class="text-primary fw-bold d-block mt-2">
                                <i class="fas fa-info-circle me-1"></i> Tip: Ensure Location Services (GPS) are enabled on your camera when taking the photo so the project can be geo-tracked.
                            </small>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success btn-lg fw-bold shadow-sm"><i class="fas fa-paper-plane me-2"></i>Submit Inspection Request</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleFields() {
        var type = document.getElementById('implementation_type').value;
        var contractFields = document.getElementById('contract_fields');
        var spendInput = document.getElementById('spend_amount');
        var timelineInput = document.getElementById('program_timeline');

        if (type === 'contract') {
            contractFields.style.display = 'block';
            spendInput.required = true;
            timelineInput.required = true;
        } else {
            contractFields.style.display = 'none';
            spendInput.required = false;
            timelineInput.required = false;
            spendInput.value = '';
            timelineInput.value = '';
        }
    }
</script>
</body>
</html>