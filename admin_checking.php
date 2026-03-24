<?php
session_start();
include 'db.php';


// Fetch projects that are in checking phase, but HIDE them once checking_status is 'approved'
$projects_query = "SELECT * FROM projects WHERE ceo_status IN ('transmitted', 'approved') AND (checking_status != 'approved' OR checking_status IS NULL) ORDER BY created_at DESC";
$projects_result = mysqli_query($conn, $projects_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Checking & Review</title>
<link rel="icon" type="image/png" href="cityengineerlogo.jpg">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
:root{
    --sidebar-width: 270px;
    --sidebar-collapsed-width: 88px;
    --green-dark: #0f3d22;
    --green-main: #14532d;
    --bg-main: #f4f7fb;
    --card-bg: rgba(255,255,255,0.95);
    --text-dark: #0f172a;
    --text-muted: #64748b;
    --border-soft: rgba(148,163,184,0.16);
    --shadow-soft: 0 12px 30px rgba(15, 23, 42, 0.08);
}
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
.suggestion-box { background-color: #fff1f2; border-left: 3px solid #e11d48; padding: 8px 12px; font-size: 0.85rem; color: #9f1239; border-radius: 4px; }
</style>
</head>
<body>

<div class="app-wrapper">
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header" id="sidebarHeader">
            <div class="brand-text"><h4>Ato Ni! Barangay</h4></div>
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
            <div><h2 class="dashboard-title">Checking & Review</h2></div>
        </div>

        <?php if(isset($_GET['msg']) && $_GET['msg'] == 'resubmitted'): ?>
            <div class="alert alert-primary alert-dismissible fade show" role="alert">
                <i class="fas fa-paper-plane me-2"></i> Project successfully resubmitted to CEO Main (File replaced if provided).
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php elseif(isset($_GET['msg']) && $_GET['msg'] == 'auto_approved'): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-double me-2"></i> 3rd Try Reached! Project automatically finalized and approved. Timestamp recorded.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="panel">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th width="15%">Project Name</th>
                            <th width="15%">Document</th>
                            <th width="15%">Review Status</th>
                            <th width="10%">Attempts</th>
                            <th width="25%">CEO Suggestions</th>
                            <th width="20%">Admin Actions</th>
                        </tr>
                    </thead>
<tbody>
                        <?php
                        if($projects_result && mysqli_num_rows($projects_result) > 0) {
                            while($project = mysqli_fetch_assoc($projects_result)) {
                                $type = !empty($project['implementation_type']) ? htmlspecialchars(ucfirst($project['implementation_type'])) : 'Pending';
                                $img_path = !empty($project['inspection_image']) ? "uploads/docs/" . htmlspecialchars($project['inspection_image']) : "#";
                                $spend = !empty($project['spend_amount']) ? htmlspecialchars($project['spend_amount']) : 'N/A';
                                $timeline = !empty($project['program_timeline']) ? nl2br(htmlspecialchars($project['program_timeline'])) : 'N/A';
                                $monitoring_status = isset($project['monitoring_status']) ? $project['monitoring_status'] : 'pending';

                                echo "<tr>";
                                echo "<form action='update_admin_monitoring.php' method='POST'>";
                                echo "<input type='hidden' name='id' value='{$project['id']}'>";

                                // Col 1: Name
                                echo "<td><strong>" . htmlspecialchars($project['title']) . "</strong></td>";

                                // Col 2: Type
                                echo "<td><span class='badge bg-secondary px-2 py-1'><i class='fas fa-tag me-1'></i> $type</span></td>";

                                // Col 3: Finished Image
                                echo "<td>";
                                if (!empty($project['inspection_image'])) {
                                    echo "<a href='$img_path' target='_blank' class='btn btn-sm btn-outline-info w-100'><i class='fas fa-image me-1'></i> View Photo</a>";
                                } else {
                                    echo "<span class='text-muted small'><i class='fas fa-clock me-1'></i> Waiting for upload</span>";
                                }
                                echo "</td>";

                                // Col 4: Inspection Details
                                echo "<td>";
                                if ($monitoring_status == 'inspection_requested') {
                                    if (strtolower($project['implementation_type']) == 'contract') {
                                        echo "<div class='data-box'>";
                                        echo "<strong class='text-dark d-block mb-1'><i class='fas fa-coins text-warning me-1'></i> Spend: $spend</strong>";
                                        echo "<span class='text-muted'><strong>Timeline:</strong> $timeline</span>";
                                        echo "</div>";
                                    } else {
                                        echo "<span class='text-muted small'><i class='fas fa-info-circle me-1'></i> By Administration. Image verification only.</span>";
                                    }
                                } else {
                                    echo "<span class='text-warning fw-bold small'><i class='fas fa-spinner fa-spin me-1'></i> Pending Barangay Inspection Form</span>";
                                }
                                echo "</td>";

                                // Col 5: Action (Disabled until Barangay submits the form)
                                echo "<td>";
                                if ($monitoring_status == 'inspection_requested') {
                                    echo "<button type='submit' name='action' value='complete' class='btn btn-sm btn-success w-100 mb-1' onclick='return confirm(\"Are you sure you want to finalize this project? It will be moved to Completed.\");'><i class='fas fa-check-double me-1'></i> Verify & Complete</button>";
                                } else {
                                    echo "<button type='button' class='btn btn-sm btn-secondary w-100 mb-1' disabled title='Waiting for Barangay to submit inspection data'><i class='fas fa-ban me-1'></i> Verify & Complete</button>";
                                }
                                echo "</td>";

                                echo "</form>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='5' class='text-center py-5 text-muted'><i class='fas fa-clipboard-list fs-1 mb-3 text-light d-block'></i>No inspection requests pending review.</td></tr>";
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