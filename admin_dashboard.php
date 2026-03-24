<?php
// 1. Include your database connection
include 'db.php';

// 2. Fetch data for the summary cards and charts
$status_counts = ['pending' => 0, 'approved' => 0, 'rejected' => 0, 'ongoing' => 0, 'done' => 0];
$total_projects = 0;

$count_query = "SELECT status, COUNT(*) as count FROM projects GROUP BY status";
$count_result = mysqli_query($conn, $count_query);

if ($count_result) {
    while ($row = mysqli_fetch_assoc($count_result)) {
        $status_counts[$row['status']] = $row['count'];
        $total_projects += $row['count'];
    }
}

// 3. Fetch all projects for the data table
$projects_query = "SELECT * FROM projects ORDER BY created_at DESC";
$projects_result = mysqli_query($conn, $projects_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barangay Infrastructure Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
            display: flex;
            min-height: 100vh;
            margin: 0;
            overflow-x: hidden;
        }

        /* Sidebar Styling */
        .sidebar {
            width: 260px;
            background: #14532d; /* Dark green matching your login page */
            color: white;
            flex-shrink: 0;
            display: flex;
            flex-direction: column;
            box-shadow: 4px 0 10px rgba(0,0,0,0.1);
            z-index: 1000;
        }

        .sidebar-header {
            padding: 24px 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 10px;
        }

        .sidebar-header h4 {
            margin: 0;
            font-weight: 700;
            font-size: 1.2rem;
            letter-spacing: 0.5px;
        }

        .sidebar a {
            color: #e5e7eb;
            text-decoration: none;
            padding: 14px 24px;
            display: flex;
            align-items: center;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .sidebar a i {
            width: 24px;
            font-size: 16px;
            margin-right: 10px;
            text-align: center;
        }

        .sidebar a:hover, .sidebar a.active {
            background: rgba(255,255,255,0.1);
            color: #fff;
            border-left: 4px solid #4ade80; /* Bright green highlight */
        }

        /* Main Content Area */
        .main-content {
            flex-grow: 1;
            padding: 30px 40px;
            height: 100vh;
            overflow-y: auto;
        }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="sidebar-header">
        <h4><i class="fas fa-building me-2"></i>Ato Ni! Admin</h4>
    </div>
    
    <a href="admin_dashboard.php" class="active"><i class="fas fa-chart-pie"></i> Dashboard</a>
    <a href="#"><i class="fas fa-clipboard-list"></i> Planning</a>
    <a href="#"><i class="fas fa-map-location-dot"></i> Site Inspection</a>
    <a href="#"><i class="fas fa-list-check"></i> Checking & Review</a>
    <a href="#"><i class="fas fa-hammer"></i> Implementation</a>
    <a href="#"><i class="fas fa-desktop"></i> Monitoring</a>
    <a href="#"><i class="fas fa-clock-rotate-left"></i> History</a>
    <a href="#"><i class="fas fa-check-double"></i> Completed</a>
    
    <div class="mt-auto mb-3">
        <a href="login.php" class="text-danger hover-danger"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
</div>

<div class="main-content">
    <h2 class="mb-4 fw-bold" style="color: #14532d;">Overview Dashboard</h2>

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-white bg-primary mb-3 shadow-sm border-0 rounded-4">
                <div class="card-body">
                    <h6 class="card-title text-uppercase fw-semibold opacity-75">Total Projects</h6>
                    <h2 class="fw-bold mb-0"><?php echo $total_projects; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-warning mb-3 shadow-sm border-0 rounded-4">
                <div class="card-body">
                    <h6 class="card-title text-uppercase fw-semibold opacity-75">Ongoing</h6>
                    <h2 class="fw-bold mb-0"><?php echo $status_counts['ongoing']; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-success mb-3 shadow-sm border-0 rounded-4">
                <div class="card-body">
                    <h6 class="card-title text-uppercase fw-semibold opacity-75">Done</h6>
                    <h2 class="fw-bold mb-0"><?php echo $status_counts['done']; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-danger mb-3 shadow-sm border-0 rounded-4">
                <div class="card-body">
                    <h6 class="card-title text-uppercase fw-semibold opacity-75">Rejected</h6>
                    <h2 class="fw-bold mb-0"><?php echo $status_counts['rejected']; ?></h2>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-5 bg-white p-4 shadow-sm rounded-4 ms-3 me-4">
            <h5 class="text-center fw-semibold mb-3">Project Status Overview</h5>
            <canvas id="projectChart"></canvas>
        </div>
        
        <div class="col-md-6 bg-white p-4 shadow-sm rounded-4 flex-grow-1">
            <h5 class="fw-semibold mb-3">Recent Activity</h5>
            <p class="text-muted small">Quick stats and updates will appear here.</p>
            </div>
    </div>

    <div class="bg-white p-4 shadow-sm rounded-4 mb-5">
        <h5 class="fw-semibold mb-4">Project List & Management</h5>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Project Name</th>
                        <th>Budget</th>
                        <th>Application File</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($project = mysqli_fetch_assoc($projects_result)): ?>
                    <tr>
                        <td class="fw-bold text-muted">#<?php echo $project['id']; ?></td>
                        <td class="fw-semibold"><?php echo htmlspecialchars($project['title']); ?></td>
                        <td>₱<?php echo number_format($project['budget'], 2); ?></td>
                        <td>
                            <a href="uploads/docs/<?php echo $project['application_file']; ?>" target="_blank" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                                <i class="fas fa-file-pdf"></i> View
                            </a>
                        </td>
                        <td>
                            <span class="badge rounded-pill bg-<?php 
                                echo ($project['status'] == 'done') ? 'success' : (($project['status'] == 'rejected') ? 'danger' : (($project['status'] == 'ongoing') ? 'warning text-dark' : 'secondary')); 
                            ?> px-3 py-2">
                                <?php echo strtoupper($project['status']); ?>
                            </span>
                        </td>
                        <td>
                            <form action="update_status.php" method="POST" class="d-flex align-items-center">
                                <input type="hidden" name="id" value="<?php echo $project['id']; ?>">
                                <select name="status" class="form-select form-select-sm me-2 rounded-3" style="width: 120px;">
                                    <option value="pending" <?php if($project['status']=='pending') echo 'selected'; ?>>Pending</option>
                                    <option value="approved" <?php if($project['status']=='approved') echo 'selected'; ?>>Approved</option>
                                    <option value="ongoing" <?php if($project['status']=='ongoing') echo 'selected'; ?>>Ongoing</option>
                                    <option value="done" <?php if($project['status']=='done') echo 'selected'; ?>>Done</option>
                                    <option value="rejected" <?php if($project['status']=='rejected') echo 'selected'; ?>>Rejected</option>
                                </select>
                                <button type="submit" name="update_status" class="btn btn-sm btn-success rounded-3"><i class="fas fa-save"></i></button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    const ctx = document.getElementById('projectChart').getContext('2d');
    const projectChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Pending', 'Approved', 'Ongoing', 'Done', 'Rejected'],
            datasets: [{
                data: [
                    <?php echo $status_counts['pending']; ?>, 
                    <?php echo $status_counts['approved']; ?>, 
                    <?php echo $status_counts['ongoing']; ?>, 
                    <?php echo $status_counts['done']; ?>, 
                    <?php echo $status_counts['rejected']; ?>
                ],
                backgroundColor: [
                    '#6c757d', // Grey for pending
                    '#0d6efd', // Blue for approved
                    '#ffc107', // Yellow for ongoing
                    '#198754', // Green for done
                    '#dc3545'  // Red for rejected
                ],
                borderWidth: 0,
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            cutout: '70%',
            plugins: {
                legend: { position: 'bottom', labels: { padding: 20, font: { family: 'Poppins' } } }
            }
        }
    });
</script>

</body>
</html>