<?php
include 'db.php';

// Get project ID from URL, fallback to 1 for testing
$project_id = isset($_GET['id']) && !empty($_GET['id']) ? intval($_GET['id']) : 1;

// Fetch project details
$query = "SELECT * FROM projects WHERE id = $project_id";
$result = mysqli_query($conn, $query);

if (!$result || mysqli_num_rows($result) == 0) {
    echo "<!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <title>Project Not Found</title>
        <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
    </head>
    <body>
        <div class='container mt-5'>
            <div class='alert alert-danger'>
                <h4 class='alert-heading'>Project Not Found</h4>
                <p>We couldn't find the project with ID = $project_id.</p>
                <hr>
                <a href='client_dashboard.php' class='btn btn-success'>Back to Dashboard</a>
            </div>
        </div>
    </body>
    </html>";
    exit;
}

$project = mysqli_fetch_assoc($result);

// Safely extract all status columns
$status = isset($project['status']) ? strtolower($project['status']) : 'pending';
$ceo_status = isset($project['ceo_status']) ? strtolower($project['ceo_status']) : '';
$monitoring_status = isset($project['monitoring_status']) ? strtolower($project['monitoring_status']) : '';
$remarks = isset($project['remarks']) ? htmlspecialchars($project['remarks']) : '';

// --- DYNAMIC TRACKING LOGIC ---

// Step 1: Submission (Always Completed if record exists)
$step1_state = 'completed';
$step1_desc = "Your project proposal has been successfully submitted and logged into the system.";

// Step 2: CPDC & Planning Review
$step2_state = 'pending';
$step2_desc = "Pending review and certification by the City Planning and Development Coordinator (CPDC).";
if ($status == 'approved' || in_array($ceo_status, ['transmitted', 'approved']) || in_array($status, ['ongoing', 'completed', 'done'])) {
    $step2_state = 'completed';
    $step2_desc = "CPDC has reviewed the proposal and cleared the project for the next phase.";
} elseif ($status == 'pending' && (empty($ceo_status) || $ceo_status == 'pending')) {
    $step2_state = 'active';
    $step2_desc = "Currently undergoing initial review and site inspection planning by the CPDC.";
} elseif (in_array($status, ['declined', 'rejected', 'cancelled'])) {
    $step2_state = 'failed';
    $step2_desc = "Project proposal was declined at the planning stage.";
}

// Step 3: CEO Final Review
$step3_state = 'pending';
$step3_desc = "Pending transmission to the City Engineering Office (Main).";
if (in_array($ceo_status, ['approved']) || in_array($status, ['ongoing', 'completed', 'done'])) {
    $step3_state = 'completed';
    $step3_desc = "The City Engineering Office has officially checked and approved the project.";
} elseif ($ceo_status == 'transmitted') {
    $step3_state = 'active';
    $step3_desc = "Transmitted! Currently under thorough document checking and review by the CEO Main Office.";
} elseif ($ceo_status == 'declined') {
    $step3_state = 'failed';
    $step3_desc = "The CEO has requested revisions or declined the proposal. Please coordinate with the Admin.";
}

// Step 4: Implementation & Monitoring
$step4_state = 'pending';
$step4_desc = "Awaiting CEO approval to begin actual implementation.";
if (in_array($status, ['completed', 'done'])) {
    $step4_state = 'completed';
    $step4_desc = "Implementation phase has been finished.";
} elseif ($status == 'ongoing' || $ceo_status == 'approved') {
    $step4_state = 'active';
    if ($monitoring_status == 'inspection_requested') {
        $step4_desc = "You have requested a final inspection. Awaiting admin verification of your submitted photos.";
    } else {
        $step4_desc = "Project is officially Ongoing! The barangay should now be implementing the project. Don't forget to request an inspection once done.";
    }
}

// Step 5: Final Completion
$step5_state = 'pending';
$step5_desc = "Awaiting completion of implementation and final admin verification.";
if (in_array($status, ['completed', 'done'])) {
    $step5_state = 'completed';
    $step5_desc = "Congratulations! The project has passed final inspection and is officially marked as Completed.";
}

// Global Rejection Override
if (in_array($status, ['declined', 'rejected', 'cancelled'])) {
    if ($step3_state == 'pending') $step3_state = 'failed';
    if ($step4_state == 'pending') $step4_state = 'failed';
    if ($step5_state == 'pending') $step5_state = 'failed';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Track Project Progress - <?php echo htmlspecialchars($project['title']); ?></title>
<link rel="icon" type="image/png" href="cityengineerlogo.jpg">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
    body {
        background-color: #f4f7fb;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        padding-top: 30px;
        padding-bottom: 50px;
    }
    .tracker-container {
        max-width: 800px;
        margin: auto;
    }
    .project-header {
        background: white;
        padding: 25px;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        margin-bottom: 30px;
        border-left: 5px solid #14532d;
    }
    .project-header h3 {
        color: #0f3d22;
        font-weight: 700;
        margin-bottom: 10px;
    }
    
    /* Timeline Styles */
    .timeline {
        position: relative;
        padding: 0;
        list-style: none;
    }
    .timeline::before {
        content: '';
        position: absolute;
        top: 0;
        bottom: 0;
        left: 24px;
        width: 3px;
        background: #e2e8f0;
        border-radius: 3px;
    }
    .timeline-item {
        position: relative;
        margin-bottom: 30px;
        padding-left: 70px;
    }
    .timeline-item:last-child {
        margin-bottom: 0;
    }
    
    /* Icons */
    .timeline-icon {
        position: absolute;
        left: 8px;
        top: 0;
        width: 35px;
        height: 35px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        font-weight: bold;
        z-index: 2;
        box-shadow: 0 0 0 4px #f4f7fb;
    }
    
    /* States */
    .state-completed .timeline-icon {
        background-color: #22c55e;
        color: white;
    }
    .state-completed .timeline-content h5 {
        color: #16a34a;
    }
    
    .state-active .timeline-icon {
        background-color: #3b82f6;
        color: white;
        box-shadow: 0 0 0 4px #dbeafe;
        animation: pulse 2s infinite;
    }
    .state-active .timeline-content h5 {
        color: #2563eb;
        font-weight: 700;
    }
    .state-active .timeline-content {
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        padding: 15px;
        border-radius: 8px;
    }

    .state-pending .timeline-icon {
        background-color: #cbd5e1;
        color: #64748b;
    }
    .state-pending .timeline-content h5, 
    .state-pending .timeline-content p {
        color: #94a3b8;
    }

    .state-failed .timeline-icon {
        background-color: #ef4444;
        color: white;
    }
    .state-failed .timeline-content h5 {
        color: #dc2626;
    }

    /* Content */
    .timeline-content h5 {
        font-size: 1.1rem;
        margin-bottom: 5px;
        font-weight: 600;
    }
    .timeline-content p {
        margin: 0;
        font-size: 0.95rem;
        color: #475569;
        line-height: 1.5;
    }

    @keyframes pulse {
        0% { box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.4); }
        70% { box-shadow: 0 0 0 10px rgba(59, 130, 246, 0); }
        100% { box-shadow: 0 0 0 0 rgba(59, 130, 246, 0); }
    }

    .remarks-box {
        background-color: #fef2f2;
        border-left: 4px solid #ef4444;
        padding: 15px;
        margin-top: 25px;
        border-radius: 0 8px 8px 0;
    }
</style>
</head>
<body>

<div class="container tracker-container">
    <a href="client_dashboard.php" class="btn btn-outline-secondary mb-4"><i class="fas fa-arrow-left me-2"></i>Back to Dashboard</a>

    <div class="project-header">
        <div class="d-flex justify-content-between align-items-start flex-wrap">
            <div>
                <span class="badge bg-success mb-2"><?php echo htmlspecialchars($project['type_of_request']); ?></span>
                <h3><?php echo htmlspecialchars($project['title']); ?></h3>
                <p class="text-muted mb-0"><i class="fas fa-map-marker-alt me-1"></i> Barangay <?php echo htmlspecialchars($project['location_barangay']); ?></p>
            </div>
            <div class="text-end mt-2 mt-sm-0">
                <p class="mb-1 text-muted small">Proposed Budget</p>
                <h4 class="text-dark fw-bold">₱<?php echo number_format($project['budget'], 2); ?></h4>
            </div>
        </div>
    </div>

    <div class="bg-white p-4 p-md-5 border-radius-12 shadow-sm rounded-4">
        <h4 class="mb-4 text-dark fw-bold"><i class="fas fa-route me-2 text-primary"></i>Live Status Tracker</h4>
        
        <ul class="timeline">
            <li class="timeline-item state-<?php echo $step1_state; ?>">
                <div class="timeline-icon"><i class="fas fa-file-signature"></i></div>
                <div class="timeline-content">
                    <h5>1. Proposal Submitted</h5>
                    <p><?php echo $step1_desc; ?></p>
                </div>
            </li>

            <li class="timeline-item state-<?php echo $step2_state; ?>">
                <div class="timeline-icon"><i class="fas fa-search-location"></i></div>
                <div class="timeline-content">
                    <h5>2. CPDC Planning & Site Inspection</h5>
                    <p><?php echo $step2_desc; ?></p>
                </div>
            </li>

            <li class="timeline-item state-<?php echo $step3_state; ?>">
                <div class="timeline-icon"><i class="fas fa-building"></i></div>
                <div class="timeline-content">
                    <h5>3. CEO Final Review</h5>
                    <p><?php echo $step3_desc; ?></p>
                </div>
            </li>

            <li class="timeline-item state-<?php echo $step4_state; ?>">
                <div class="timeline-icon"><i class="fas fa-hard-hat"></i></div>
                <div class="timeline-content">
                    <h5>4. Implementation & Monitoring</h5>
                    <p><?php echo $step4_desc; ?></p>
                </div>
            </li>

            <li class="timeline-item state-<?php echo $step5_state; ?>">
                <div class="timeline-icon"><i class="fas fa-check-double"></i></div>
                <div class="timeline-content">
                    <h5>5. Project Completed</h5>
                    <p><?php echo $step5_desc; ?></p>
                </div>
            </li>
        </ul>

        <?php if (!empty($remarks) && in_array($status, ['pending', 'declined', 'rejected', 'cancelled'])): ?>
            <div class="remarks-box">
                <h6 class="text-danger fw-bold mb-1"><i class="fas fa-exclamation-circle me-1"></i> Important Remarks from Office:</h6>
                <p class="mb-0 text-dark"><?php echo nl2br($remarks); ?></p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>