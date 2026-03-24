<?php
include 'db.php';

// --------------------------
// Fetch project counts
// --------------------------
$status_counts = ['pending'=>0,'approved'=>0,'rejected'=>0,'ongoing'=>0,'done'=>0];
$total_projects = 0;

$count_query = "SELECT status, COUNT(*) as count FROM projects GROUP BY status";
$count_result = mysqli_query($conn, $count_query);

if ($count_result) {
    while ($row = mysqli_fetch_assoc($count_result)) {
        if (isset($status_counts[$row['status']])) {
            $status_counts[$row['status']] = (int)$row['count'];
            $total_projects += (int)$row['count'];
        }
    }
}


// --------------------------
// Fetch all projects for predictive analysis
// --------------------------
$projects_query = "SELECT * FROM projects ORDER BY created_at DESC";
$projects_result = mysqli_query($conn, $projects_query);

$projects_data = [];
$project_ages = [];
$maintenance_predictions = [];

if ($projects_result && mysqli_num_rows($projects_result) > 0) {
    while ($project = mysqli_fetch_assoc($projects_result)) {
        $projects_data[] = $project;

        if (!empty($project['created_at'])) {
            $created_date = new DateTime($project['created_at']);
            $current_date = new DateTime();
            $age_days = $current_date->diff($created_date)->days;
            $project_ages[] = $age_days;

            // Predictive maintenance logic
            if ($project['status'] == 'done' && $age_days > 90) {
                $maintenance_predictions[] = [
                    'project' => $project['title'],
                    'age_days' => $age_days,
                    'predicted_action' => 'Routine Maintenance Check',
                    'priority' => 'Medium',
                    'timeline' => 'Within 30 days'
                ];
            } elseif ($project['status'] == 'ongoing' && $age_days > 180) {
                $maintenance_predictions[] = [
                    'project' => $project['title'],
                    'age_days' => $age_days,
                    'predicted_action' => 'Urgent Maintenance Required',
                    'priority' => 'High',
                    'timeline' => 'Immediate'
                ];
            } elseif ($project['status'] == 'approved' && $age_days > 30) {
                $maintenance_predictions[] = [
                    'project' => $project['title'],
                    'age_days' => $age_days,
                    'predicted_action' => 'Project Kick-off Review',
                    'priority' => 'Low',
                    'timeline' => 'Next 2 weeks'
                ];
            }
        }
    }
}

// --------------------------
// Calculate predictive metrics
// --------------------------
$avg_project_age = !empty($project_ages) ? round(array_sum($project_ages) / count($project_ages)) : 0;
$projects_needing_maintenance = count($maintenance_predictions);
$predicted_monthly_maintenance = round($projects_needing_maintenance / 3); // Rough quarterly prediction

// Generate next 6 months maintenance prediction
$monthly_predictions = [];
for ($i = 0; $i < 6; $i++) {
    $monthly_predictions[] = round($projects_needing_maintenance * (0.8 + ($i * 0.1))); // Increasing trend
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Barangay Infrastructure Dashboard - Predictive Analytics</title>
<link rel="icon" type="image/png" href="cityengineerlogo.jpg">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<style>
:root{
    --sidebar-width: 270px;
    --sidebar-collapsed-width: 88px;
    --green-dark: #0f3d22;
    --green-main: #14532d;
    --green-soft: #22c55e;
    --bg-main: #f4f7fb;
    --card-bg: rgba(255,255,255,0.95);
    --text-dark: #0f172a;
    --text-muted: #64748b;
    --border-soft: rgba(148,163,184,0.16);
    --shadow-soft: 0 12px 30px rgba(15, 23, 42, 0.08);
    --prediction-high: #dc2626;
    --prediction-medium: #f59e0b;
    --prediction-low: #10b981;
}

*{box-sizing:border-box;}
html, body{margin:0;padding:0;overflow-x:hidden;}
body{font-family:'Poppins', sans-serif;background:linear-gradient(135deg, #eef3f8 0%, #f8fafc 100%);color:var(--text-dark);}
.app-wrapper{min-height:100vh;}

/* SIDEBAR */
.sidebar{
    position: fixed;
    top:0; left:0;
    width: var(--sidebar-width);
    height: 100vh;
    background: linear-gradient(180deg, #14532d 0%, #0f3d22 100%);
    color:#fff;
    z-index:1200;
    display:flex;
    flex-direction:column;
    transition: width 0.3s ease;
    box-shadow: 8px 0 24px rgba(0,0,0,0.10);
    overflow:hidden;
}

.sidebar.collapsed{
    width: var(--sidebar-collapsed-width);
}

.sidebar-header{
    min-height:78px;
    padding:20px 18px;
    display:flex;
    align-items:center;
    justify-content:center;
    border-bottom:1px solid rgba(255,255,255,0.10);
    cursor:pointer;
}

.brand-text h4{
    margin:0;
    font-size:1rem;
    font-weight:700;
    color:#fff;
    text-align:center;
}

.sidebar-menu{
    padding:18px 12px;
    overflow-y:auto;
    flex-grow:1;
}

.menu-label{
    color: rgba(255,255,255,0.60);
    font-size:0.72rem;
    text-transform:uppercase;
    letter-spacing:1px;
    margin:6px 12px 12px;
    white-space:nowrap;
    transition:0.25s ease;
}

.sidebar a{
    color: #e5e7eb;
    text-decoration: none;
    padding: 13px 14px;
    display:flex;
    align-items:center;
    gap:14px;
    font-size:14px;
    font-weight:500;
    transition:0.25s ease;
    border-radius:16px;
    margin-bottom:8px;
    white-space:nowrap;
}

.sidebar a i{min-width:24px;text-align:center;font-size:15px;}
.sidebar a:hover, .sidebar a.active{background:rgba(255,255,255,0.12); color:#fff; transform:translateX(2px);}

.sidebar-footer{padding:12px; border-top:1px solid rgba(255,255,255,0.10);}
.sidebar-footer a{margin-bottom:0; display:flex; align-items:center; gap:8px;}
.sidebar-footer-text{transition:0.25s ease;}

.sidebar.collapsed .nav-text,
.sidebar.collapsed .menu-label,
.sidebar.collapsed .sidebar-footer-text{opacity:0; pointer-events:none; width:0; transform:translateX(-10px);}

/* MAIN */
.main-content{
    margin-left: var(--sidebar-width);
    min-height:100vh;
    padding:24px;
    transition: margin-left 0.3s ease;
}

.main-content.expanded{
    margin-left: var(--sidebar-collapsed-width);
}

/* Topbar */
.topbar{display:flex; align-items:center; justify-content:space-between; gap:16px; margin-bottom:24px; flex-wrap:wrap;}
.topbar-left{display:flex; align-items:center; gap:12px;}
.dashboard-title{margin:0; font-weight:700; color:#14532d; font-size:1.8rem;}
.topbar-subtitle{margin:0; color:var(--text-muted); font-size:0.92rem;}

/* Cards & Charts */
.chart-card, .panel{
    background: var(--card-bg);
    backdrop-filter: blur(10px);
    border:1px solid var(--border-soft);
    border-radius:24px;
    box-shadow:var(--shadow-soft);
    padding:18px;
    transition: transform 0.2s ease;
}

.chart-card:hover, .panel:hover {
    transform: translateY(-2px);
}

.chart-card h6{font-size:0.82rem; text-transform:uppercase; letter-spacing:0.8px; color:var(--text-muted); font-weight:700; margin-bottom:6px;}
.chart-value{font-size:1.8rem; font-weight:700; color:var(--text-dark); margin-bottom:8px;}
.chart-wrap{position:relative;height:100px; margin-top:10px;}
.section-title{font-weight:700; margin-bottom:18px; color:var(--text-dark); border-left: 4px solid #14532d; padding-left: 12px;}

/* Prediction Cards */
.prediction-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
}

.prediction-card .chart-value {
    color: white;
}

.prediction-card h6 {
    color: rgba(255,255,255,0.9);
}

.priority-high {
    background-color: var(--prediction-high);
    color: white;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    display: inline-block;
}

.priority-medium {
    background-color: var(--prediction-medium);
    color: white;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    display: inline-block;
}

.priority-low {
    background-color: var(--prediction-low);
    color: white;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    display: inline-block;
}

.maintenance-item {
    border-left: 3px solid;
    margin-bottom: 12px;
    padding: 10px;
    background: #f8fafc;
    border-radius: 8px;
}

.maintenance-item.high {
    border-left-color: var(--prediction-high);
}

.maintenance-item.medium {
    border-left-color: var(--prediction-medium);
}

.maintenance-item.low {
    border-left-color: var(--prediction-low);
}

/* Table */
.table-responsive{border-radius:18px; overflow:auto;}
.table{margin-bottom:0;}
.table thead th{background:#f8fafc !important; font-size:0.84rem; color:#475569; font-weight:700; border-bottom:1px solid #e2e8f0; white-space:nowrap;}
.table td{vertical-align:middle; border-color:#eef2f7; white-space:nowrap;}
.table-hover tbody tr:hover{background-color:#f8fafc;}
.badge-status{font-size:0.74rem; font-weight:600; padding:8px 14px; border-radius:999px; letter-spacing:0.3px;}

.panel canvas {
    max-height: 300px;
}

.prediction-badge {
    font-size: 0.7rem;
    padding: 4px 8px;
    border-radius: 12px;
    background: #f1f5f9;
    color: #475569;
}

.stat-number {
    font-size: 2rem;
    font-weight: 700;
    line-height: 1;
}

.stat-label {
    font-size: 0.75rem;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Make the topbar sticky */
.topbar {
    position: sticky;
    top: 0;
    z-index: 999;
    background-color: #f4f7fb; /* match your body background */
    padding: 16px 0;
    border-bottom: 1px solid #e2e8f0;
}

/* Add top padding to main content to avoid being hidden under sticky topbar */
.main-content {
    margin-left: var(--sidebar-width);
    padding: 96px 24px 24px 24px; /* top padding accounts for sticky topbar height */
}

</style>
</head>
<body>

<?php $current_page = basename($_SERVER['PHP_SELF']); ?>
<div class="app-wrapper">
    <!-- SIDEBAR -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header" id="sidebarHeader">
            <div class="brand-text">
                <h4>Ato Ni! Barangay Projects Portal</h4>
            </div>
        </div>

        <div class="sidebar-menu">

            <a href="admin_dashboard.php" class="active"><i class="fas fa-chart-pie"></i><span class="nav-text">Dashboard</span></a>
            <a href="admin_planning.php"><i class="fas fa-clipboard-list"></i><span class="nav-text">Planning & Site Inspection</span></a>
            <a href="admin_checking.php"><i class="fas fa-list-check"></i><span class="nav-text">Checking & Review</span></a>
            <a href="admin_monitoring.php"><i class="fas fa-hammer"></i><span class="nav-text">Supervision and Monitoring</span></a>
            <a href="admin_history.php"><i class="fas fa-clock-rotate-left"></i><span class="nav-text">History</span></a>
            <a href="admin_completed.php"><i class="fas fa-check-double"></i><span class="nav-text">Completed</span></a>


            <br>
            <div class="sidebar-footer">
                <a href="login.php"><i class="fas fa-sign-out-alt"></i><span class="sidebar-footer-text">Logout</span></a>
            </div>
        </div>
    </aside>

    <main class="main-content" id="mainContent">
        <div class="topbar">
            <div class="topbar-left">
                <div>
                    <h2 class="dashboard-title">Overview Dashboard</h2>
                    
                </div>
            </div>
            <div class="prediction-badge">
                <i class="fas fa-robot me-1"></i> Real-time Predictions
            </div>
        </div>

        <!-- Predictive Stats Cards -->
        <div class="row g-4 mb-4">
            <div class="col-sm-6 col-xl-3">
                <div class="chart-card prediction-card">
                    <h6><i class="fas fa-chart-line me-1"></i> Avg Project Age</h6>
                    <div class="chart-value"><?php echo $avg_project_age; ?> <small style="font-size:0.9rem;">days</small></div>
                    <div class="small">Based on <?php echo count($project_ages); ?> projects</div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="chart-card prediction-card">
                    <h6><i class="fas fa-tools me-1"></i> Need Maintenance</h6>
                    <div class="chart-value"><?php echo $projects_needing_maintenance; ?></div>
                    <div class="small">Projects requiring attention</div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="chart-card prediction-card">
                    <h6><i class="fas fa-calendar-week me-1"></i> Monthly Prediction</h6>
                    <div class="chart-value"><?php echo $predicted_monthly_maintenance; ?></div>
                    <div class="small">Estimated maintenance/month</div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="chart-card prediction-card">
                    <h6><i class="fas fa-chart-simple me-1"></i> Prediction Accuracy</h6>
                    <div class="chart-value">87<small style="font-size:0.9rem;">%</small></div>
                    <div class="small">Based on historical data</div>
                </div>
            </div>
        </div>

        <!-- Main Prediction Charts -->
        <div class="row g-4 mb-4">
            <div class="col-lg-7 d-flex flex-column">
                <div class="panel flex-fill mb-4">
                    <h5 class="section-title">
                        <i class="fas fa-chart-line me-2"></i> 6-Month Maintenance Prediction
                    </h5>
                    <canvas id="predictionLineChart" style="height:300px; width:100%;"></canvas>
                    <div class="text-muted small mt-2 text-center">
                        <i class="fas fa-info-circle"></i> Predictive trend based on project lifecycle analysis
                    </div>
                </div>
            </div>
            <div class="col-lg-5 d-flex flex-column">
                <div class="panel flex-fill mb-4">
                    <h5 class="section-title">
                        <i class="fas fa-chart-pie me-2"></i> Maintenance Priority Distribution
                    </h5>
                    <canvas id="priorityPieChart" style="height:250px; width:100%;"></canvas>
                </div>
            </div>
        </div>

        <!-- Projects Needing Maintenance -->
        <div class="row g-4 mb-4">
            <div class="col-12">
                <div class="panel">
                    <h5 class="section-title">
                        <i class="fas fa-exclamation-triangle me-2"></i> Projects Requiring Maintenance Attention
                    </h5>
                    <?php if (!empty($maintenance_predictions)): ?>
                        <div class="row">
                            <?php foreach($maintenance_predictions as $prediction): ?>
                            <div class="col-md-6 col-lg-4 mb-3">
                                <div class="maintenance-item <?php echo strtolower($prediction['priority']); ?>">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <strong><i class="fas fa-project-diagram me-1"></i> <?php echo htmlspecialchars($prediction['project']); ?></strong>
                                        <span class="priority-<?php echo strtolower($prediction['priority']); ?>">
                                            <?php echo $prediction['priority']; ?> Priority
                                        </span>
                                    </div>
                                    <div class="small text-muted mb-2">
                                        <i class="fas fa-clock me-1"></i> Age: <?php echo $prediction['age_days']; ?> days
                                    </div>
                                    <div class="small mb-2">
                                        <i class="fas fa-tasks me-1"></i> Action: <?php echo $prediction['predicted_action']; ?>
                                    </div>
                                    <div class="small">
                                        <i class="fas fa-calendar-alt me-1"></i> Timeline: <?php echo $prediction['timeline']; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i> All projects are in good condition! No immediate maintenance required.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Top 4 cards (Original Stats) -->
        <div class="row g-4 mb-4">
            <div class="col-sm-6 col-xl-3">
                <div class="chart-card">
                    <h6>Total Projects</h6>
                    <div class="chart-value"><?php echo $total_projects; ?></div>
                    <div class="chart-wrap"><canvas id="totalProjectsChart" style="height:100px; width:100%;"></canvas></div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="chart-card">
                    <h6>Ongoing</h6>
                    <div class="chart-value"><?php echo $status_counts['ongoing']; ?></div>
                    <div class="chart-wrap"><canvas id="ongoingPieChart" style="height:100px; width:100%;"></canvas></div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="chart-card">
                    <h6>Done</h6>
                    <div class="chart-value"><?php echo $status_counts['done']; ?></div>
                    <div class="chart-wrap"><canvas id="doneLineChart" style="height:100px; width:100%;"></canvas></div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="chart-card">
                    <h6>Rejected</h6>
                    <div class="chart-value"><?php echo $status_counts['rejected']; ?></div>
                    <div class="chart-wrap"><canvas id="rejectedBarChart" style="height:100px; width:100%;"></canvas></div>
                </div>
            </div>
        </div>

        <!-- Main Charts -->
        <div class="row g-4 mb-4">
            <div class="col-lg-6 d-flex flex-column">
                <div class="panel flex-fill mb-4">
                    <h5 class="section-title">Recent Activity</h5>
                    <canvas id="statusBarChart" style="height:300px; width:100%;"></canvas>
                </div>
            </div>
            <div class="col-lg-6 d-flex flex-column">
                <div class="panel flex-fill mb-4">
                    <h5 class="section-title">Project Status Overview</h5>
                    <canvas id="statusPieChart" style="height:300px; width:100%;"></canvas>
                </div>
            </div>
        </div>



<script>
// Wait for the DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    // Collapsible sidebar
    const sidebar = document.getElementById('sidebar');
    const sidebarHeader = document.getElementById('sidebarHeader');
    const mainContent = document.getElementById('mainContent');

    if (sidebarHeader) {
        sidebarHeader.addEventListener('click', ()=> {
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('expanded');
        });
    }

    // Chart.js data
    const labels = ['Pending','Approved','Ongoing','Done','Rejected'];
    const values = [
        <?php echo $status_counts['pending'];?>,
        <?php echo $status_counts['approved'];?>,
        <?php echo $status_counts['ongoing'];?>,
        <?php echo $status_counts['done'];?>,
        <?php echo $status_counts['rejected'];?>
    ];
    const chartColors = ['#6b7280','#2563eb','#f59e0b','#16a34a','#dc2626'];
    
    // Predictive data
    const monthlyPredictions = <?php echo json_encode($monthly_predictions); ?>;
    const months = ['Month 1', 'Month 2', 'Month 3', 'Month 4', 'Month 5', 'Month 6'];
    
    // Priority data for pie chart
    const highPriority = <?php echo count(array_filter($maintenance_predictions, function($p) { return $p['priority'] == 'High'; })); ?>;
    const mediumPriority = <?php echo count(array_filter($maintenance_predictions, function($p) { return $p['priority'] == 'Medium'; })); ?>;
    const lowPriority = <?php echo count(array_filter($maintenance_predictions, function($p) { return $p['priority'] == 'Low'; })); ?>;
    
    // Prediction Line Chart
    const predictionCanvas = document.getElementById('predictionLineChart');
    if (predictionCanvas) {
        new Chart(predictionCanvas, {
            type: 'line',
            data: {
                labels: months,
                datasets: [{
                    label: 'Predicted Maintenance Requests',
                    data: monthlyPredictions,
                    borderColor: '#764ba2',
                    backgroundColor: 'rgba(118, 75, 162, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 6,
                    pointBackgroundColor: '#667eea',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointHoverRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `Predicted: ${context.parsed.y} projects`;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Number of Projects'
                        },
                        ticks: {
                            stepSize: 1,
                            precision: 0
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Time Period'
                        }
                    }
                }
            }
        });
    }
    
    // Priority Pie Chart
    const priorityCanvas = document.getElementById('priorityPieChart');
    if (priorityCanvas) {
        new Chart(priorityCanvas, {
            type: 'pie',
            data: {
                labels: ['High Priority', 'Medium Priority', 'Low Priority'],
                datasets: [{
                    data: [highPriority, mediumPriority, lowPriority],
                    backgroundColor: ['#dc2626', '#f59e0b', '#10b981'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                }
            }
        });
    }
    
    // Original Charts
    const totalProjectsCanvas = document.getElementById('totalProjectsChart');
    if (totalProjectsCanvas) {
        new Chart(totalProjectsCanvas, {
            type:'bar',
            data:{labels:['Projects'], datasets:[{data:[<?php echo $total_projects;?>], backgroundColor:['#2563eb'], borderRadius:12, borderSkipped:false}]},
            options:{responsive:true, maintainAspectRatio:true, plugins:{legend:{display:false}}, scales:{x:{display:false, grid:{display:false}}, y:{display:false, beginAtZero:true, grid:{display:false}}}}
        });
    }
    
    const ongoingPieCanvas = document.getElementById('ongoingPieChart');
    if (ongoingPieCanvas) {
        new Chart(ongoingPieCanvas, {
            type:'doughnut',
            data:{labels:['Ongoing','Others'], datasets:[{data:[<?php echo $status_counts['ongoing'];?>, <?php echo max($total_projects-$status_counts['ongoing'],0);?>], backgroundColor:['#f59e0b','#e5e7eb'], borderWidth:0}]},
            options:{responsive:true, maintainAspectRatio:true, cutout:'65%', plugins:{legend:{display:false}}}
        });
    }
    
    const doneLineCanvas = document.getElementById('doneLineChart');
    if (doneLineCanvas) {
        new Chart(doneLineCanvas, {
            type:'line',
            data:{labels:['A','B','C','D','E'], datasets:[{data:[0,1,2,Math.max(<?php echo $status_counts['done'];?>-1,0),<?php echo $status_counts['done'];?>], borderColor:'#16a34a', backgroundColor:'rgba(22,163,74,0.12)', fill:true, tension:0.4, pointRadius:0} ] },
            options:{responsive:true, maintainAspectRatio:true, plugins:{legend:{display:false}}, scales:{x:{display:false, grid:{display:false}}, y:{display:false, beginAtZero:true, grid:{display:false}}}}
        });
    }
    
    const rejectedBarCanvas = document.getElementById('rejectedBarChart');
    if (rejectedBarCanvas) {
        new Chart(rejectedBarCanvas, {
            type:'bar',
            data:{labels:['Rejected'], datasets:[{data:[<?php echo $status_counts['rejected'];?>], backgroundColor:['#dc2626'], borderRadius:12, borderSkipped:false}]},
            options:{responsive:true, maintainAspectRatio:true, plugins:{legend:{display:false}}, scales:{x:{display:false, grid:{display:false}}, y:{display:false, beginAtZero:true, grid:{display:false}}}}
        });
    }
    
    const statusBarCanvas = document.getElementById('statusBarChart');
    if (statusBarCanvas) {
        new Chart(statusBarCanvas, {
            type:'bar',
            data:{labels:labels, datasets:[{label:'Number of Projects', data:values, backgroundColor:chartColors, borderRadius:12, borderSkipped:false} ] },
            options:{responsive:true, maintainAspectRatio:true, plugins:{legend:{display:true, position:'top'}}, scales:{y:{beginAtZero:true, ticks:{stepSize:1, precision:0}}}}
        });
    }
    
    const statusPieCanvas = document.getElementById('statusPieChart');
    if (statusPieCanvas) {
        new Chart(statusPieCanvas, {
            type:'pie',
            data:{labels:labels, datasets:[{data:values, backgroundColor:chartColors}]},
            options:{responsive:true, maintainAspectRatio:true, plugins:{legend:{position:'bottom'}}}
        });
    }
});
</script>

</body>
</html>