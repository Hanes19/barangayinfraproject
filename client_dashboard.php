<?php
include 'db.php';

// Fetch project counts
$status_counts = ['pending' => 0, 'approved' => 0, 'ongoing' => 0, 'done' => 0, 'rejected' => 0];
$total_projects = 0;

$count_query = "SELECT status, COUNT(*) as count FROM projects GROUP BY status";
$result = mysqli_query($conn, $count_query);

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $status_counts[$row['status']] = $row['count'];
        $total_projects += $row['count'];
    }
}

// Fetch all projects
$projects_query = "SELECT * FROM projects ORDER BY id DESC";
$projects_result = mysqli_query($conn, $projects_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Client Dashboard</title>

<link rel="icon" type="image/png" href="cityengineerlogo.jpg">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
body {
    margin: 0;
    font-family: 'Segoe UI', sans-serif;
    background: url('musuan.jpg') no-repeat center center fixed;
    background-size: cover;
    color: #1b5e20;
}

.overlay {
    min-height: 100vh;
    padding-bottom: 50px;
}

.navbar-custom {
    background-color: #4caf50;
}
.navbar-custom .navbar-brand,
.navbar-custom .nav-link,
.navbar-custom .btn-outline-success {
    color: white !important;
}

.card {
    border: none;
    border-radius: 12px;
    background-color: white;
}

.card-header {
    background-color: #4caf50;
    color: white;
    font-weight: 600;
}

/* Chart Fix */
canvas {
    max-height: 300px;
}

/* Status badge */
.status-badge {
    padding: 0.4em 0.7em;
    border-radius: 5px;
    color: white;
}
.status-pending { background-color: #ffc107; color:#000; }
.status-approved { background-color: #0d6efd; }
.status-ongoing { background-color: #17a2b8; }
.status-done { background-color: #28a745; }
.status-rejected { background-color: #dc3545; }

.progress {
    height: 20px;
}
</style>
</head>

<body>
<div class="overlay">

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-custom mb-4">
    <div class="container">
        <a class="navbar-brand fw-bold">Ato Ni! Barangay Projects Portal</a>
        <div class="ms-auto">
            <a href="submit_project.php" class="btn btn-outline-success fw-bold">
                <i class="fas fa-plus-circle"></i> Submit Project
            </a>
        </div>
    </div>
</nav>

<div class="container">

<!-- TABLE -->
<div class="card shadow-sm">
    <div class="card-header">Your Submitted Projects</div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Title</th>
                        <th>Type</th>
                        <th>Budget</th>
                        <th>Status</th>
                        <th>Progress</th>
                        <th>Document</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if(mysqli_num_rows($projects_result) > 0): 
                    $i=1;
                    while($project = mysqli_fetch_assoc($projects_result)): 

                        switch($project['status']){
                            case 'pending': $progress=10; break;
                            case 'approved': $progress=30; break;
                            case 'ongoing': $progress=60; break;
                            case 'done': $progress=100; break;
                            case 'rejected': $progress=0; break;
                            default: $progress=0;
                        }
                ?>
                <tr>
                    <td><?php echo $i++; ?></td>
                    <td><?php echo $project['title']; ?></td>
                    <td><?php echo $project['type_of_request']; ?></td>
                    <td>₱<?php echo number_format($project['budget'],2); ?></td>

                    <td>
                        <span class="status-badge status-<?php echo $project['status']; ?>">
                            <?php echo ucfirst($project['status']); ?>
                        </span>
                    </td>

                    <td>
                        <div class="progress">
                            <div class="progress-bar bg-success" style="width:<?php echo $progress; ?>%">
                                <?php echo $progress; ?>%
                            </div>
                        </div>
                    </td>

                    <td>
                        <a href="uploads/docs/<?php echo $project['application_file']; ?>" 
                           class="btn btn-sm btn-outline-success" download>
                           Download
                        </a>
                    </td>

                    <td>
                        <a href="view_progress.php?id=<?php echo $project['id']; ?>" class="btn btn-sm btn-primary">View</a>
                        <a href="track_progress.php?id=<?php echo $project['id']; ?>" class="btn btn-sm btn-info">Track</a>
                    </td>
                </tr>

                <?php endwhile; ?>
                <?php else: ?>
                <tr>
                    <td colspan="8" class="text-center">No projects yet</td>
                </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</div>
</div>
