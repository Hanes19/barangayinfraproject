<?php
include 'db.php';

// Get project ID from URL
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

// Status stages
$stages = ['pending'=>'Pending', 'approved'=>'Approved', 'ongoing'=>'Ongoing', 'done'=>'Done', 'rejected'=>'Rejected'];
$current_status = $project['status'];

// If you have per-stage timestamps, you could fetch from project_updates table
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>View Project Progress</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
body { font-family: 'Segoe UI', sans-serif; background-color: #f4fdf4; color: #1b5e20; padding: 20px; }
.card { border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
.stage-badge { font-size: 0.9rem; padding: 0.35rem 0.6rem; border-radius: 0.3rem; font-weight: 600; }
.stage-pending { background-color: #ffc107; color: #1b5e20; }
.stage-approved { background-color: #0d6efd; color: #fff; }
.stage-ongoing { background-color: #17a2b8; color: #fff; }
.stage-done { background-color: #28a745; color: #fff; }
.stage-rejected { background-color: #dc3545; color: #fff; }
</style>
</head>
<body>

<div class="container">
    <a href="client_dashboard.php" class="btn btn-sm btn-outline-success mb-3"><i class="fas fa-arrow-left me-1"></i>Back to Dashboard</a>

    <div class="card p-4 mb-4">
        <h4 class="mb-3"><?php echo htmlspecialchars($project['title']); ?></h4>
        <p><strong>Type:</strong> <?php echo htmlspecialchars($project['type_of_request']); ?></p>
        <p><strong>Budget:</strong> ₱<?php echo number_format($project['budget'], 2); ?></p>
        <p><strong>Submitted On:</strong> <?php echo isset($project['created_at']) ? $project['created_at'] : 'N/A'; ?></p>
        <p><strong>Current Status:</strong> 
            <span class="stage-badge stage-<?php echo $current_status; ?>">
                <?php echo ucfirst($current_status); ?>
            </span>
        </p>
        <p><strong>Document:</strong> 
            <?php if(!empty($project['application_file'])): ?>
                <a href="uploads/docs/<?php echo $project['application_file']; ?>" class="btn btn-sm btn-outline-success" download>
                    <i class="fas fa-download me-1"></i>Download
                </a>
            <?php else: ?>
                N/A
            <?php endif; ?>
        </p>
    </div>

    <div class="card p-4">
        <h5 class="mb-3">Progress Timeline</h5>
        <ul class="list-group">
            <?php
            foreach($stages as $key => $label){
                $completed = '';
                if($key == 'rejected' && $current_status == 'rejected'){
                    $completed = 'list-group-item-danger';
                } elseif(array_search($key, array_keys($stages)) <= array_search($current_status, array_keys($stages)) && $current_status != 'rejected'){
                    $completed = 'list-group-item-success';
                }
                echo "<li class='list-group-item d-flex justify-content-between align-items-center $completed'>
                        $label
                        ".($completed ? "<i class='fas fa-check-circle'></i>" : "")."
                      </li>";
            }
            ?>
        </ul>
    </div>
</div>

</body>
</html>