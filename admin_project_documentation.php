<?php
session_start();
include 'db.php';

// Ensure an ID was passed in the URL
if (!isset($_GET['id'])) {
    header("Location: admin_completed.php");
    exit();
}

$project_id = intval($_GET['id']);
$query = "SELECT * FROM projects WHERE id = $project_id";
$result = mysqli_query($conn, $query);

if (!$result || mysqli_num_rows($result) == 0) {
    echo "<h3>Project not found.</h3><a href='admin_completed.php'>Go Back</a>";
    exit();
}

$project = mysqli_fetch_assoc($result);

// Variables setup
$title = htmlspecialchars($project['title']);
$location = !empty($project['location']) ? htmlspecialchars($project['location']) : 'Not Specified';
$type = !empty($project['implementation_type']) ? htmlspecialchars(ucfirst($project['implementation_type'])) : 'N/A';
$spend = !empty($project['spend_amount']) ? htmlspecialchars($project['spend_amount']) : 'N/A';
$timeline_notes = !empty($project['program_timeline']) ? nl2br(htmlspecialchars($project['program_timeline'])) : 'No timeline notes provided.';

$date_started = $project['approved_at'] ? date('F d, Y - h:i A', strtotime($project['approved_at'])) : 'Unknown';
$date_ended = $project['completed_at'] ? date('F d, Y - h:i A', strtotime($project['completed_at'])) : 'Unknown';

$doc_path = !empty($project['application_file']) ? "uploads/docs/" . htmlspecialchars($project['application_file']) : null;
$img_path = !empty($project['inspection_image']) ? "uploads/docs/" . htmlspecialchars($project['inspection_image']) : null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Documentation: <?php echo $title; ?></title>
<link rel="icon" type="image/png" href="cityengineerlogo.jpg">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
    body { background-color: #f4f7fb; font-family: 'Poppins', sans-serif; padding-top: 30px; padding-bottom: 50px; }
    .report-container { max-width: 900px; margin: 0 auto; background: white; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); overflow: hidden; }
    .report-header { background: #14532d; color: white; padding: 40px 30px; text-align: center; }
    .report-header h2 { font-weight: 700; margin-bottom: 10px; }
    .report-header p { margin: 0; color: #a7f3d0; }
    .report-body { padding: 40px; }
    .section-title { font-size: 1.2rem; font-weight: 700; color: #0f3d22; border-bottom: 2px solid #e2e8f0; padding-bottom: 10px; margin-bottom: 20px; margin-top: 30px; }
    .info-box { background: #f8fafc; border: 1px solid #e2e8f0; padding: 15px; border-radius: 8px; margin-bottom: 15px; }
    .info-label { display: block; font-size: 0.8rem; text-transform: uppercase; color: #64748b; font-weight: 700; margin-bottom: 5px; }
    .info-value { font-size: 1.05rem; color: #0f172a; font-weight: 500; }
    .project-image { width: 100%; max-height: 400px; object-fit: cover; border-radius: 8px; border: 3px solid #e2e8f0; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
    
    @media print {
        body { background-color: white; padding: 0; }
        .report-container { box-shadow: none; max-width: 100%; border: none; }
        .no-print { display: none !important; }
    }
</style>
</head>
<body>

<div class="container">
    <div class="mb-4 no-print d-flex justify-content-between align-items-center" style="max-width: 900px; margin: 0 auto;">
        <a href="admin_completed.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i>Back to Archive</a>
        <button onclick="window.print()" class="btn btn-primary"><i class="fas fa-print me-2"></i>Print Report</button>
    </div>

    <div class="report-container">
        <div class="report-header">
            <i class="fas fa-building fs-1 mb-3 text-success"></i>
            <h2><?php echo $title; ?></h2>
            <p><i class="fas fa-map-marker-alt me-1"></i> <?php echo $location; ?></p>
        </div>

        <div class="report-body">
            
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="info-box border-start border-primary border-4">
                        <span class="info-label">Date Started (Approved)</span>
                        <span class="info-value"><i class="fas fa-calendar-alt text-primary me-2"></i><?php echo $date_started; ?></span>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-box border-start border-success border-4">
                        <span class="info-label">Date Completed (Finished)</span>
                        <span class="info-value"><i class="fas fa-flag-checkered text-success me-2"></i><?php echo $date_ended; ?></span>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-box">
                        <span class="info-label">Implementation Type</span>
                        <span class="info-value"><i class="fas fa-cog text-secondary me-2"></i><?php echo $type; ?></span>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-box">
                        <span class="info-label">Total Spend</span>
                        <span class="info-value"><i class="fas fa-money-bill-wave text-success me-2"></i><?php echo $spend; ?></span>
                    </div>
                </div>
            </div>

            <?php if ($type === 'Contract'): ?>
            <h4 class="section-title"><i class="fas fa-stream me-2"></i>Program Timeline & Notes</h4>
            <div class="info-box">
                <p class="mb-0 text-dark" style="line-height: 1.6;"><?php echo $timeline_notes; ?></p>
            </div>
            <?php endif; ?>

            <h4 class="section-title"><i class="fas fa-folder-open me-2"></i>Initial Documentation</h4>
            <?php if ($doc_path): ?>
                <div class="info-box d-flex align-items-center justify-content-between">
                    <div>
                        <strong class="d-block text-dark mb-1">Project Application & Proposal</strong>
                        <small class="text-muted">The initial paperwork submitted and approved by the CEO.</small>
                    </div>
                    <a href="<?php echo $doc_path; ?>" target="_blank" class="btn btn-outline-primary no-print"><i class="fas fa-download me-1"></i> Download / View</a>
                </div>
            <?php else: ?>
                <p class="text-muted fst-italic">No initial document was attached to this project.</p>
            <?php endif; ?>

            <h4 class="section-title"><i class="fas fa-camera-retro me-2"></i>Final Inspection Image</h4>
            <?php if ($img_path): ?>
                <div class="text-center mt-3">
                    <img src="<?php echo $img_path; ?>" alt="Completed Project Image" class="project-image">
                    <p class="text-muted small mt-2">Verified final output of the project.</p>
                </div>
            <?php else: ?>
                <div class="info-box text-center py-4">
                    <i class="fas fa-image fs-1 text-muted mb-2"></i>
                    <p class="text-muted mb-0 fst-italic">No inspection image was uploaded.</p>
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>

</body>
</html>