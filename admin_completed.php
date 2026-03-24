<?php
session_start();
include 'db.php';

// Fetch ONLY projects that hit 100% completion
$projects_query = "SELECT * FROM projects WHERE monitoring_status = 'completed' ORDER BY completed_at DESC";
$projects_result = mysqli_query($conn, $projects_query);

// Get the logged-in admin's name
$admin_name = isset($_SESSION['full_name']) ? htmlspecialchars($_SESSION['full_name']) : 'Admin User';
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Completed Projects Archive</title>
<link rel="icon" type="image/png" href="cityengineerlogo.jpg">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
:root{ --sidebar-width: 270px; --sidebar-collapsed-width: 88px; --green-dark: #0f3d22; --green-main: #14532d; --bg-main: #f4f7fb; --card-bg: rgba(255,255,255,0.95); --text-dark: #0f172a; --border-soft: rgba(148,163,184,0.16); --shadow-soft: 0 12px 30px rgba(15, 23, 42, 0.08); }
*{box-sizing:border-box;}
body{font-family:'Poppins', sans-serif;background:var(--bg-main);color:var(--text-dark); margin:0; padding:0; overflow-x:hidden;}
.app-wrapper{min-height:100vh;}
.sidebar{ position: fixed; top:0; left:0; width: var(--sidebar-width); height: 100vh; background: linear-gradient(180deg, #14532d 0%, #0f3d22 100%); color:#fff; z-index:1200; display:flex; flex-direction:column; transition: width 0.3s ease; box-shadow: 8px 0 24px rgba(0,0,0,0.10); overflow:hidden; }
.sidebar-header{ min-height:78px; padding:20px 18px; display:flex; align-items:center; justify-content:center; border-bottom:1px solid rgba(255,255,255,0.10); cursor:pointer; }
.brand-text h4{ margin:0; font-size:1rem; font-weight:700; color:#fff; text-align:center; }
.sidebar-menu{ padding:18px 12px; overflow-y:auto; flex-grow:1; }
.menu-label{ color: rgba(255,255,255,0.60); font-size:0.72rem; text-transform:uppercase; letter-spacing:1px; margin:6px 12px 12px; white-space:nowrap; transition:0.25s ease; }
.sidebar a{ color: #e5e7eb; text-decoration: none; padding: 13px 14px; display:flex; align-items:center; gap:14px; font-size:14px; font-weight:500; transition:0.25s ease; border-radius:16px; margin-bottom:8px; white-space:nowrap; }
.sidebar a i{min-width:24px;text-align:center;font-size:15px;}
.sidebar a:hover, .sidebar a.active{background:rgba(255,255,255,0.12); color:#fff; transform:translateX(2px);}
.sidebar-footer{padding:12px; border-top:1px solid rgba(255,255,255,0.10);}
.sidebar-footer a{margin-bottom:0; display:flex; align-items:center; gap:8px;}
.main-content{ margin-left: var(--sidebar-width); min-height:100vh; padding:24px; transition: margin-left 0.3s ease; }
.topbar{display:flex; align-items:center; justify-content:space-between; margin-bottom:24px; background-color: var(--bg-main); padding: 16px 0; border-bottom: 1px solid #e2e8f0;}
.dashboard-title{margin:0; font-weight:700; color:#14532d; font-size:1.8rem;}
.panel{ background: var(--card-bg); border:1px solid var(--border-soft); border-radius:18px; box-shadow:var(--shadow-soft); padding:20px; }
.table thead th{background:#f8fafc !important; font-size:0.84rem; color:#475569; font-weight:700; border-bottom:1px solid #e2e8f0;}
</style>
</head>
<body>

<div class="app-wrapper">
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header" id="sidebarHeader">
            <div class="brand-text"><h4>Ato Ni! Barangay</h4></div>
        </div>
        <div class="sidebar-menu">
            <div class="menu-label">Navigation</div>
            <a href="admin_dashboard.php"><i class="fas fa-chart-pie"></i><span class="nav-text">Dashboard</span></a>
            <a href="admin_planning.php"><i class="fas fa-clipboard-list"></i><span class="nav-text">Planning & Inspection</span></a>
            <a href="admin_checking.php"><i class="fas fa-list-check"></i><span class="nav-text">Checking & Review</span></a>
            <a href="admin_monitoring.php"><i class="fas fa-hard-hat"></i><span class="nav-text">Supervision & Monitoring</span></a>
            <a href="admin_history.php"><i class="fas fa-clock-rotate-left"></i><span class="nav-text">History</span></a>
            <a href="admin_completed.php" class="active"><i class="fas fa-check-double"></i><span class="nav-text">Completed</span></a>
            <br>
            <div class="sidebar-footer">
                <a href="login.php"><i class="fas fa-sign-out-alt"></i><span class="sidebar-footer-text">Logout</span></a>
            </div>
        </div>
    </aside>

    <main class="main-content" id="mainContent">
        <div class="topbar">
            <div><h2 class="dashboard-title text-success"><i class="fas fa-award me-2"></i>Completed Projects Archive</h2></div>
        </div>

        <div class="panel border-success border-top border-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th width="15%">Handled By (Admin)</th>
                            <th width="20%">Project Name</th>
                            <th width="15%">Location</th>
                            <th width="30%">Project Timeline (Start - End)</th>
                            <th width="20%">Documentation</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if($projects_result && mysqli_num_rows($projects_result) > 0) {
                            while($project = mysqli_fetch_assoc($projects_result)) {
                                $location = !empty($project['location']) ? htmlspecialchars($project['location']) : 'Not Specified';
                                
                                // Format the exact timestamps
                                $date_started = $project['approved_at'] ? date('M d, Y', strtotime($project['approved_at'])) : 'N/A';
                                $date_ended = $project['completed_at'] ? date('M d, Y', strtotime($project['completed_at'])) : 'N/A';

                                echo "<tr>";
                                echo "<td><small class='text-muted'><i class='fas fa-user-shield me-1'></i> $admin_name</small></td>";
                                echo "<td><strong>" . htmlspecialchars($project['title']) . "</strong></td>";
                                echo "<td><i class='fas fa-map-marker-alt text-danger me-1'></i> $location</td>";
                                
                                echo "<td>
                                        <div class='d-flex align-items-center gap-2'>
                                            <span class='badge bg-light text-dark border'><i class='fas fa-play text-primary me-1'></i> $date_started</span>
                                            <i class='fas fa-arrow-right text-muted'></i>
                                            <span class='badge bg-light text-dark border'><i class='fas fa-stop text-success me-1'></i> $date_ended</span>
                                        </div>
                                      </td>";
                                      
                                echo "<td><a href='admin_project_documentation.php?id={$project['id']}' class='btn btn-sm btn-success w-100'><i class='fas fa-folder-open me-1'></i> View Full Report</a></td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='5' class='text-center py-5 text-muted'><i class='fas fa-folder-open mb-3 fs-1 text-light'></i><br>No completed projects yet.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>
</body>
</html>