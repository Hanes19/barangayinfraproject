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
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
/* ===== Body & Overlay ===== */
body {
    margin: 0;
    font-family: 'Segoe UI', sans-serif;
    background: url('musuan.jpg') no-repeat center center fixed;
    background-size: cover; /* make it cover the whole screen */
    color: #1b5e20; /* medium green text */
}

.overlay {
    min-height: 100vh;
    padding-bottom: 50px;
}

/* ===== Navbar ===== */
.navbar-custom {
    background-color: #4caf50; /* medium green */
}
.navbar-custom .navbar-brand,
.navbar-custom .nav-link,
.navbar-custom .btn-outline-success {
    color: white !important;
}
.navbar-custom .btn-outline-success:hover {
    color: #4caf50;
    background-color: white;
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
}

/* ===== Cards ===== */
.card {
    border: none;
    border-radius: 12px;
    background-color: white;
    transition: transform 0.2s, box-shadow 0.2s;
}
.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}
.card-header {
    background-color: #4caf50; /* medium green */
    color: white;
    font-weight: 600;
    border-top-left-radius: 12px;
    border-top-right-radius: 12px;
    font-size: 0.95rem;
}

/* ===== Summary Cards Row Centered ===== */
.summary-row {
    justify-content: center; /* center all cards */
}

/* ===== Status Badges ===== */
.status-badge {
    padding: 0.4em 0.7em;
    font-size: 0.85em;
    border-radius: 0.3rem;
    color: white;
    font-weight: 500;
    text-transform: uppercase;
}
.status-pending { background-color: #ffc107; color: #1b5e20; }
.status-approved { background-color: #0d6efd; }
.status-ongoing { background-color: #17a2b8; }
.status-done { background-color: #28a745; }
.status-rejected { background-color: #dc3545; }

/* ===== Progress Bar ===== */
.progress {
    height: 22px;
    border-radius: 12px;
    background-color: #e9ecef;
}
.progress-bar {
    border-radius: 12px;
    font-weight: 600;
}

/* ===== Table ===== */
.table {
    margin-bottom: 0;
    background-color: white;
}
.table thead {
    background-color: #4caf50; /* medium green */
    color: white;
}
.table-hover tbody tr:hover {
    background-color: rgba(76, 175, 80, 0.1);
}
a.btn-outline-success {
    border-radius: 50px;
    padding: 0.25rem 0.7rem;
    font-size: 0.85rem;
    transition: all 0.2s;
}
a.btn-outline-success:hover {
    background-color: #28a745;
    color: white;
    transform: scale(1.05);
}

/* ===== Responsive ===== */
@media (max-width: 767px) {
    .card-header { font-size: 0.9rem; }
    .table thead { display: none; }
    .table, .table tbody, .table tr, .table td { display: block; width: 100%; }
    .table tr { margin-bottom: 1rem; }
    .table td { text-align: right; padding-left: 50%; position: relative; }
    .table td::before { 
        content: attr(data-label);
        position: absolute;
        left: 15px;
        width: 45%;
        text-align: left;
        font-weight: bold;
    }
}
</style>
</head>
<body>
<div class="overlay">

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-custom mb-4 shadow-sm">
        <div class="container">
        <a class="navbar-brand fw-bold" href="#">
    Ato Ni! Barangay Projects Portal
</a>
            <div class="ms-auto">
                <a href="submit_project.php" class="btn btn-outline-success fw-bold"><i class="fas fa-plus-circle me-1"></i>Submit Project</a>
            </div>
        </div>
    </nav>

    <div class="container">

        <!-- Centered Summary Cards -->
        <div class="row mb-4 g-3 summary-row">
            <div class="col-6 col-md-2">
                <div class="card shadow-sm text-center">
                    <div class="card-header">Pending</div>
                    <div class="card-body">
                        <h4 class="fw-bold"><?php echo $status_counts['pending']; ?></h4>
                        <small class="text-muted">Under Review</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-2">
                <div class="card shadow-sm text-center">
                    <div class="card-header">Done</div>
                    <div class="card-body">
                        <h4 class="fw-bold"><?php echo $status_counts['done']; ?></h4>
                        <small class="text-muted">Completed</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-2">
                <div class="card shadow-sm text-center">
                    <div class="card-header">Rejected</div>
                    <div class="card-body">
                        <h4 class="fw-bold"><?php echo $status_counts['rejected']; ?></h4>
                        <small class="text-muted">Declined</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-2">
                <div class="card shadow-sm text-center">
                    <div class="card-header">Total</div>
                    <div class="card-body">
                        <h4 class="fw-bold"><?php echo $total_projects; ?></h4>
                        <small class="text-muted">Projects Submitted</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Projects Table -->
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
                                <th>Budget (₱)</th>
                                <th>Status</th>
                                <th>Progress</th>
                                <th>Document</th>
                                  <th>Actions</th> <!-- NEW -->
                            </tr>
                        </thead>
                        <tbody>
                        <?php if(mysqli_num_rows($projects_result) > 0): 
                            $i = 1;
                            while($project = mysqli_fetch_assoc($projects_result)): 
                                switch($project['status']){
                                    case 'pending': $progress = 10; break;
                                    case 'approved': $progress = 30; break;
                                    case 'ongoing': $progress = 60; break;
                                    case 'done': $progress = 100; break;
                                    case 'rejected': $progress = 0; break;
                                    default: $progress = 0;
                                }
                        ?>
                            <tr>
                                <td data-label="#"> <?php echo $i++; ?> </td>
                                <td data-label="Title"> <?php echo $project['title']; ?> </td>
                                <td data-label="Type"> <?php echo $project['type_of_request']; ?> </td>
                                <td data-label="Budget"> <?php echo number_format($project['budget'], 2); ?> </td>
                                <td data-label="Status">
                                    <span class="status-badge status-<?php echo $project['status']; ?>">
                                        <?php echo ucfirst($project['status']); ?>
                                    </span>
                                </td>
                                <td data-label="Progress">
                                    <div class="progress">
                                        <div class="progress-bar" role="progressbar" style="width: <?php echo $progress; ?>%;" aria-valuenow="<?php echo $progress; ?>" aria-valuemin="0" aria-valuemax="100">
                                            <?php echo $progress; ?>%
                                        </div>
                                    </div>
                                </td>
                                <td data-label="Document">
                                    <a href="uploads/docs/<?php echo $project['application_file']; ?>" class="btn btn-sm btn-outline-success" download><i class="fas fa-download me-1"></i>Download</a>
                                </td>
                                <td data-label="Actions">
    <a href="view_progress.php?id=<?php echo $project['id']; ?>" class="btn btn-sm btn-outline-primary me-1">
        <i class="fas fa-eye me-1"></i>View
    </a>
    <a href="track_progress.php?id=<?php echo $project['id']; ?>" class="btn btn-sm btn-outline-info">
        <i class="fas fa-location-arrow me-1"></i>Track
    </a>
</td>
                            </tr>
                        <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="7" class="text-center py-3">No projects submitted yet.</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>
</body>
</html>