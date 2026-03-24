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

// Define progress stages
$stages = [
    'pending'  => 'Pending',
    'approved' => 'Approved',
    'ongoing'  => 'Ongoing',
    'done'     => 'Done',
    'rejected' => 'Rejected'
];

// Current project status
$current_status = $project['status'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Track Project Progress</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
body {
    font-family: 'Segoe UI', sans-serif;
    background-color: #f4fdf4;
    color: #1b5e20;
    padding: 20px;
}
.card {
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}
.timeline {
    list-style: none;
    padding-left: 0;
}
.timeline li {
    position: relative;
    padding-left: 35px;
    margin-bottom: 25px;
}
.timeline li::before {
    content: "";
    position: absolute;
    left: 0;
    top: 0;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background-color: #ccc;
    border: 2px solid #1b5e20;
}
.timeline li.completed::before {
    background-color: #28a745;
    border-color: #28a745;
}
.timeline li.completed .stage-name {
    font-weight: bold;
    color: #28a745;
}
.timeline li:not(:last-child)::after {
    content: "";
    position: absolute;
    left: 9px;
    top: 20px;
    width: 2px;
    height: calc(100% - 20px);
    background-color: #ccc;
}
.timeline li.completed:not(:last-child)::after {
    background-color: #28a745;
}
.stage-name {
    font-size: 1rem;
}
</style>
</head>
<body>

<div class="container">
    <a href="client_dashboard.php" class="btn btn-sm btn-outline-success mb-3"><i class="fas fa-arrow-left me-1"></i>Back to Dashboard</a>

    <div class="card p-4">
        <h4 class="mb-3"><?php echo htmlspecialchars($project['title']); ?></h4>
        <p><strong>Type:</strong> <?php echo htmlspecialchars($project['type_of_request']); ?></p>
        <p><strong>Budget:</strong> ₱<?php echo number_format($project['budget'], 2); ?></p>

        <h5 class="mt-4 mb-3">Project Progress</h5>
        <ul class="timeline">
            <?php
            $status_order = ['pending','approved','ongoing','done','rejected'];
            foreach($status_order as $stage){
                $completed = '';
                if($stage == 'rejected' && $current_status == 'rejected'){
                    $completed = 'completed';
                } elseif(array_search($stage, $status_order) <= array_search($current_status, $status_order) && $current_status != 'rejected'){
                    $completed = 'completed';
                }
                echo '<li class="'.$completed.'"><span class="stage-name">'.ucfirst($stages[$stage]).'</span></li>';
            }
            ?>
        </ul>

        <div class="mt-4">
            <strong>Current Status:</strong> 
            <span class="badge <?php 
                switch($current_status){
                    case 'pending': echo 'bg-warning text-dark'; break;
                    case 'approved': echo 'bg-primary'; break;
                    case 'ongoing': echo 'bg-info text-dark'; break;
                    case 'done': echo 'bg-success'; break;
                    case 'rejected': echo 'bg-danger'; break;
                }
            ?>">
                <?php echo ucfirst($current_status); ?>
            </span>
        </div>
    </div>
</div>

</body>
</html>