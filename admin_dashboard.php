<?php
include 'db.php';

// Fetch project counts
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

// Fetch all projects
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
}

.chart-card h6{font-size:0.82rem; text-transform:uppercase; letter-spacing:0.8px; color:var(--text-muted); font-weight:700; margin-bottom:6px;}
.chart-value{font-size:1.8rem; font-weight:700; color:var(--text-dark); margin-bottom:8px;}
.chart-wrap{position:relative;height:100px; margin-top:10px;}
.main-chart-box{min-height:300px; height:100%;}
.section-title{font-weight:700; margin-bottom:18px; color:var(--text-dark);}

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
</style>
</head>
<body>

<div class="app-wrapper">
    <!-- SIDEBAR -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header" id="sidebarHeader">
            <div class="brand-text">
                <h4>Ato Ni! Barangay Projects Portal</h4>
            </div>
        </div>

        <div class="sidebar-menu">
            <div class="menu-label">Navigation</div>
            <a href="admin_dashboard.php" class="active"><i class="fas fa-chart-pie"></i><span class="nav-text">Dashboard</span></a>
            <a href="#"><i class="fas fa-clipboard-list"></i><span class="nav-text">Planning</span></a>
            <a href="#"><i class="fas fa-map-location-dot"></i><span class="nav-text">Site Inspection</span></a>
            <a href="#"><i class="fas fa-list-check"></i><span class="nav-text">Checking & Review</span></a>
            <a href="#"><i class="fas fa-hammer"></i><span class="nav-text">Implementation</span></a>
            <a href="#"><i class="fas fa-desktop"></i><span class="nav-text">Monitoring</span></a>
            <a href="#"><i class="fas fa-clock-rotate-left"></i><span class="nav-text">History</span></a>
            <a href="#"><i class="fas fa-check-double"></i><span class="nav-text">Completed</span></a>

            <br>
            <div class="sidebar-footer">
                <a href="login.php"><i class="fas fa-sign-out-alt"></i><span class="sidebar-footer-text">Logout</span></a>
            </div>
        </div>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="main-content" id="mainContent">
        <div class="topbar">
            <div class="topbar-left">
                <div>
                    <h2 class="dashboard-title">Overview Dashboard</h2>
                    <p class="topbar-subtitle">Track project status, updates, and management overview.</p>
                </div>
            </div>
        </div>

        <!-- Top 4 cards -->
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

        <div class="panel mb-4">
            <h5 class="section-title">Status Distribution Trend</h5>
            <canvas id="statusLineChart" style="height:300px; width:100%;"></canvas>
        </div>

        <!-- Table -->
        <div class="panel mb-5">
            <h5 class="section-title">Project List & Management</h5>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                         <tr>
                            <th>ID</th><th>Project Name</th><th>Budget</th><th>Application File</th><th>Status</th><th>Action</th>
                         </tr>
                    </thead>
                    <tbody>
                        <?php if($projects_result && mysqli_num_rows($projects_result) > 0): ?>
                            <?php while($project = mysqli_fetch_assoc($projects_result)): ?>
                            <tr>
                                <td class="fw-bold text-muted">#<?php echo $project['id']; ?></td>
                                <td class="fw-semibold"><?php echo htmlspecialchars($project['title']); ?></td>
                                <td>₱<?php echo number_format($project['budget'],2); ?></td>
                                <td><a href="uploads/docs/<?php echo htmlspecialchars($project['application_file']); ?>" target="_blank" class="btn btn-sm btn-outline-secondary rounded-pill px-3"><i class="fas fa-file-pdf me-1"></i>View</a></td>
                                <td>
                                    <?php
                                    $status = $project['status'];
                                    $badgeClass = 'bg-secondary';
                                    if($status=='done') $badgeClass='bg-success';
                                    elseif($status=='rejected') $badgeClass='bg-danger';
                                    elseif($status=='ongoing') $badgeClass='bg-warning text-dark';
                                    elseif($status=='approved') $badgeClass='bg-primary';
                                    ?>
                                    <span class="badge badge-status <?php echo $badgeClass;?>"><?php echo strtoupper($status);?></span>
                                </td>
                                <td>
                                    <form action="update_status.php" method="POST" class="d-flex gap-2">
                                        <input type="hidden" name="id" value="<?php echo $project['id'];?>">
                                        <select name="status" class="form-select form-select-sm rounded-3" style="width:130px;">
                                            <option value="pending" <?php if($status=='pending') echo 'selected';?>>Pending</option>
                                            <option value="approved" <?php if($status=='approved') echo 'selected';?>>Approved</option>
                                            <option value="ongoing" <?php if($status=='ongoing') echo 'selected';?>>Ongoing</option>
                                            <option value="done" <?php if($status=='done') echo 'selected';?>>Done</option>
                                            <option value="rejected" <?php if($status=='rejected') echo 'selected';?>>Rejected</option>
                                        </select>
                                        <button type="submit" name="update_status" class="btn btn-sm btn-success rounded-3"><i class="fas fa-save"></i></button>
                                    </form>
                                </td>
                            </tr>
                            <?php endwhile;?>
                        <?php else: ?>
                            <tr><td colspan="6" class="text-center py-4 text-muted">No projects found.</td></tr>
                        <?php endif;?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
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
    
    // Check if we have canvas elements before creating charts
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
    
    // Main charts
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
    
    const statusLineCanvas = document.getElementById('statusLineChart');
    if (statusLineCanvas) {
        new Chart(statusLineCanvas, {
            type:'line',
            data:{labels:labels, datasets:[{label:'Project Count', data:values, fill:true, backgroundColor:'rgba(37,99,235,0.2)', borderColor:'#2563eb', tension:0.4, pointRadius:6, pointBackgroundColor:chartColors} ] },
            options:{responsive:true, maintainAspectRatio:true, plugins:{legend:{display:true, position:'top'}}, scales:{y:{beginAtZero:true, ticks:{stepSize:1, precision:0}}}}
        });
    }
});
</script>

</body>
</html>