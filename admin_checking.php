<?php
session_start();
include 'db.php';

// Fetch only projects that have passed Planning and were transmitted
$projects_query = "SELECT * FROM projects WHERE ceo_status = 'transmitted' ORDER BY created_at DESC";
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
            <div class="menu-label">Navigation</div>
            <a href="admin_dashboard.php"><i class="fas fa-chart-pie"></i><span class="nav-text">Dashboard</span></a>
            <a href="admin_planning.php"><i class="fas fa-clipboard-list"></i><span class="nav-text">Planning & Inspection</span></a>
            <a href="admin_checking.php" class="active"><i class="fas fa-list-check"></i><span class="nav-text">Checking & Review</span></a>
            <a href="admin_monitoring.php"><i class="fas fa-hard-hat"></i><span class="nav-text">Supervision & Monitoring</span></a>
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
                <i class="fas fa-paper-plane me-2"></i> Project successfully resubmitted to CEO Main (File attached if provided).
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
                            <th width="10%">Document</th>
                            <th width="15%">Review Status</th>
                            <th width="10%">Attempts</th>
                            <th width="25%">CEO Suggestions</th>
                            <th width="25%">Admin Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if($projects_result && mysqli_num_rows($projects_result) > 0) {
                            while($project = mysqli_fetch_assoc($projects_result)) {
                                $doc_path = !empty($project['application_file']) ? "uploads/docs/" . htmlspecialchars($project['application_file']) : "#";
                                $checking_status = isset($project['checking_status']) ? strtolower($project['checking_status']) : 'pending';
                                $attempts = isset($project['submission_attempts']) ? intval($project['submission_attempts']) : 1;
                                $ceo_remarks = !empty($project['ceo_main_remarks']) ? htmlspecialchars($project['ceo_main_remarks']) : 'None';
                                
                                // Status Styling
                                if ($checking_status == 'approved') {
                                    $badge = "<span class='badge bg-success'><i class='fas fa-check-circle me-1'></i> Approved</span>";
                                } elseif ($checking_status == 'declined') {
                                    $badge = "<span class='badge bg-danger'><i class='fas fa-times-circle me-1'></i> Declined</span>";
                                } else {
                                    $badge = "<span class='badge bg-warning text-dark'><i class='fas fa-hourglass-half me-1'></i> Pending Review</span>";
                                }

                                echo "<tr>";
                                echo "<td><strong>" . htmlspecialchars($project['title']) . "</strong></td>";
                                echo "<td><a href='$doc_path' target='_blank' class='btn btn-sm btn-outline-primary'><i class='fas fa-file-pdf'></i> View</a></td>";
                                echo "<td>$badge</td>";
                                echo "<td><span class='badge bg-secondary'>$attempts / 3</span></td>";
                                
                                // Suggestions column
                                echo "<td>";
                                if ($checking_status == 'declined') {
                                    echo "<div class='suggestion-box'><i class='fas fa-exclamation-triangle me-1'></i> $ceo_remarks</div>";
                                } else {
                                    echo "<span class='text-muted small'>Waiting on review...</span>";
                                }
                                echo "</td>";
                                
                                // Actions Column
                                echo "<td>";
                                if ($checking_status == 'approved') {
                                    $date = date('M d, Y h:i A', strtotime($project['approved_at']));
                                    echo "<span class='text-success fw-bold small'><i class='fas fa-flag-checkered me-1'></i> Finalized on $date</span>";
                                } elseif ($checking_status == 'declined') {
                                    // Resubmit Form with File Upload (enctype added)
                                    $next_attempt = $attempts + 1;
                                    echo "<form action='update_admin_checking.php' method='POST' enctype='multipart/form-data' class='d-flex flex-column gap-2'>
                                            <input type='hidden' name='id' value='{$project['id']}'>
                                            <input type='hidden' name='current_attempts' value='$attempts'>
                                            
                                            <input type='file' name='new_document' class='form-control form-control-sm' accept='.pdf,.doc,.docx' title='Upload revised document (Optional)'>
                                            
                                            <textarea name='admin_notes' class='form-control form-control-sm' rows='1' placeholder='Type fixes made...' required></textarea>
                                            <button type='submit' class='btn btn-sm btn-primary w-100'><i class='fas fa-redo me-1'></i> Resubmit (Try $next_attempt)</button>
                                          </form>";
                                } else {
                                    echo "<span class='text-muted small'>No action needed.</span>";
                                }
                                echo "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='6' class='text-center py-4 text-muted'>No projects in Checking & Review yet.</td></tr>";
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