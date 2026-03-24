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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-light">

<div class="container mt-4">
    <h2 class="mb-4">Barangay Infrastructure Dashboard</h2>

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-white bg-primary mb-3">
                <div class="card-body">
                    <h5 class="card-title">Total Projects</h5>
                    <h3><?php echo $total_projects; ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-warning mb-3">
                <div class="card-body">
                    <h5 class="card-title">Ongoing</h5>
                    <h3><?php echo $status_counts['ongoing']; ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-success mb-3">
                <div class="card-body">
                    <h5 class="card-title">Done</h5>
                    <h3><?php echo $status_counts['done']; ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-danger mb-3">
                <div class="card-body">
                    <h5 class="card-title">Rejected</h5>
                    <h3><?php echo $status_counts['rejected']; ?></h3>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-6 offset-md-3 bg-white p-4 shadow-sm rounded">
            <h5 class="text-center">Project Status Overview</h5>
            <canvas id="projectChart"></canvas>
        </div>
    </div>

    <div class="bg-white p-4 shadow-sm rounded mb-5">
        <h5>Project List & Management</h5>
        <table class="table table-striped mt-3">
            <thead>
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
                    <td><?php echo $project['id']; ?></td>
                    <td><?php echo htmlspecialchars($project['title']); ?></td>
                    <td>₱<?php echo number_format($project['budget'], 2); ?></td>
                    <td>
                        <a href="uploads/docs/<?php echo $project['application_file']; ?>" target="_blank" class="btn btn-sm btn-outline-info">View File</a>
                    </td>
                    <td>
                        <span class="badge bg-<?php 
                            echo ($project['status'] == 'done') ? 'success' : (($project['status'] == 'rejected') ? 'danger' : 'warning'); 
                        ?>">
                            <?php echo strtoupper($project['status']); ?>
                        </span>
                    </td>
                    <td>
                        <form action="update_status.php" method="POST" class="d-flex">
                            <input type="hidden" name="id" value="<?php echo $project['id']; ?>">
                            <select name="status" class="form-select form-select-sm me-2">
                                <option value="pending" <?php if($project['status']=='pending') echo 'selected'; ?>>Pending</option>
                                <option value="approved" <?php if($project['status']=='approved') echo 'selected'; ?>>Approved</option>
                                <option value="ongoing" <?php if($project['status']=='ongoing') echo 'selected'; ?>>Ongoing</option>
                                <option value="done" <?php if($project['status']=='done') echo 'selected'; ?>>Done</option>
                                <option value="rejected" <?php if($project['status']=='rejected') echo 'selected'; ?>>Rejected</option>
                            </select>
                            <button type="submit" name="update_status" class="btn btn-sm btn-primary">Save</button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    const ctx = document.getElementById('projectChart').getContext('2d');
    const projectChart = new Chart(ctx, {
        type: 'doughnut', // 'pie', 'bar', or 'doughnut'
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
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });
</script>

</body>
</html>