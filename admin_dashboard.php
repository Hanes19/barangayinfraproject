<?php
include 'db.php';

$status_counts = ['pending'=>0,'approved'=>0,'rejected'=>0,'ongoing'=>0,'done'=>0];
$total_projects = 0;

$count_query = "SELECT status, COUNT(*) as count FROM projects GROUP BY status";
$count_result = mysqli_query($conn, $count_query);

if ($count_result) {
    while ($row = mysqli_fetch_assoc($count_result)) {
        $status_counts[$row['status']] = $row['count'];
        $total_projects += $row['count'];
    }
}

$projects_query = "SELECT * FROM projects ORDER BY created_at DESC";
$projects_result = mysqli_query($conn, $projects_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Barangay Infrastructure Dashboard</title>

<!-- Bootstrap 5 -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Google Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">

<style>
/* ---------- Global ---------- */
body {
    font-family: 'Inter', sans-serif;
    background-color: #f4f5f7;
    color: #1f2937;
}
h2 { font-weight: 700; margin-bottom: 2rem; text-align: center; }

/* ---------- Cards ---------- */
.card {
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.12);
}
.card h3 { font-weight: 700; }

/* Soft muted gradients for a minimalist feel */
.bg-primary { background: linear-gradient(135deg,#4f46e5,#6366f1)!important; color: #fff; }
.bg-success { background: linear-gradient(135deg,#16a34a,#4ade80)!important; color: #fff; }
.bg-warning { background: linear-gradient(135deg,#facc15,#fbbf24)!important; color: #fff; }
.bg-danger  { background: linear-gradient(135deg,#dc2626,#f87171)!important; color: #fff; }

/* ---------- Table ---------- */
.table-responsive { border-radius: 10px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
.table th, .table td { vertical-align: middle; }
.table-hover tbody tr:hover { background-color: #f1f5f9; transition: 0.2s; }

/* ---------- Badges ---------- */
.badge {
    font-weight: 600;
    text-transform: uppercase;
    padding: 0.45em 0.7em;
    border-radius: 8px;
    font-size: 0.75rem;
}

/* ---------- Dropdown ---------- */
.form-select-sm { min-width: 120px; }

/* ---------- Chart Container ---------- */
.chart-container {
    background: #fff;
    padding: 2rem;
    border-radius: 12px;
    box-shadow: 0 6px 16px rgba(0,0,0,0.06);
}
</style>
</head>
<body>

<div class="container mt-5">
    <h2>Barangay Infrastructure Dashboard</h2>

    <!-- Summary Cards -->
    <div class="row g-4 mb-5">
        <div class="col-md-3">
            <div class="card text-center bg-primary">
                <div class="card-body">
                    <h5>Total Projects</h5>
                    <h3><?php echo $total_projects; ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center bg-warning">
                <div class="card-body">
                    <h5>Ongoing</h5>
                    <h3><?php echo $status_counts['ongoing']; ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center bg-success">
                <div class="card-body">
                    <h5>Done</h5>
                    <h3><?php echo $status_counts['done']; ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center bg-danger">
                <div class="card-body">
                    <h5>Rejected</h5>
                    <h3><?php echo $status_counts['rejected']; ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart -->
    <div class="row mb-5">
        <div class="col-md-8 offset-md-2 chart-container">
            <h5 class="text-center mb-4">Project Status Overview</h5>
            <canvas id="projectChart"></canvas>
        </div>
    </div>

    <!-- Projects Table -->
    <div class="p-4 bg-white rounded mb-5 shadow-sm">
        <h5 class="mb-3">Project List & Management</h5>
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
                        <td><?php echo $project['id']; ?></td>
                        <td><?php echo htmlspecialchars($project['title']); ?></td>
                        <td>₱<?php echo number_format($project['budget'], 2); ?></td>
                        <td>
                            <a href="uploads/docs/<?php echo $project['application_file']; ?>" target="_blank" class="btn btn-sm btn-outline-info">View File</a>
                        </td>
                        <td>
                            <span class="badge bg-<?php echo ($project['status']=='done')?'success':(($project['status']=='rejected')?'danger':'warning'); ?>">
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
</div>

<script>
const ctx = document.getElementById('projectChart').getContext('2d');
new Chart(ctx, {
    type: 'doughnut',
    data: {
        labels: ['Pending','Approved','Ongoing','Done','Rejected'],
        datasets: [{
            data: [
                <?php echo $status_counts['pending']; ?>,
                <?php echo $status_counts['approved']; ?>,
                <?php echo $status_counts['ongoing']; ?>,
                <?php echo $status_counts['done']; ?>,
                <?php echo $status_counts['rejected']; ?>
            ],
            backgroundColor: ['#6c757d','#4f46e5','#facc15','#16a34a','#dc2626'],
            borderColor: '#fff',
            borderWidth: 2
        }]
    },
    options: { responsive:true, plugins:{ legend:{ position:'bottom' } } }
});
</script>

</body>
</html>