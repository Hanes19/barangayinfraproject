<?php
session_start();
include 'db.php';

// Fetch logs and join with the projects table to get the project title
$logs_query = "SELECT pl.*, p.title 
               FROM project_logs pl 
               JOIN projects p ON pl.project_id = p.id 
               ORDER BY pl.created_at DESC";
$logs_result = mysqli_query($conn, $logs_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin History Logs</title>
<link rel="icon" type="image/png" href="cityengineerlogo.jpg">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
/* Sidebar and main styling */
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

.sidebar{
    position: fixed; top:0; left:0; width: var(--sidebar-width); height: 100vh;
    background: linear-gradient(180deg, #14532d 0%, #0f3d22 100%);
    color:#fff; z-index:1200; display:flex; flex-direction:column;
    transition: width 0.3s ease; box-shadow: 8px 0 24px rgba(0,0,0,0.10); overflow:hidden;
}
.sidebar.collapsed{ width: var(--sidebar-collapsed-width); }
.sidebar-header{ min-height:78px; padding:20px 18px; display:flex; align-items:center; justify-content:center; border-bottom:1px solid rgba(255,255,255,0.10); cursor:pointer; }
.brand-text h4{ margin:0; font-size:1rem; font-weight:700; color:#fff; text-align:center; }
.sidebar-menu{ padding:18px 12px; overflow-y:auto; flex-grow:1; }
.menu-label{ color: rgba(255,255,255,0.60); font-size:0.72rem; text-transform:uppercase; letter-spacing:1px; margin:6px 12px 12px; white-space:nowrap; transition:0.25s ease; }
.sidebar a{ color: #e5e7eb; text-decoration: none; padding: 13px 14px; display:flex; align-items:center; gap:14px; font-size:14px; font-weight:500; transition:0.25s ease; border-radius:16px; margin-bottom:8px; white-space:nowrap; }
.sidebar a i{min-width:24px;text-align:center;font-size:15px;}
.sidebar a:hover, .sidebar a.active{background:rgba(255,255,255,0.12); color:#fff; transform:translateX(2px);}
.sidebar-footer{padding:12px; border-top:1px solid rgba(255,255,255,0.10);}
.sidebar-footer a{margin-bottom:0; display:flex; align-items:center; gap:8px;}
.sidebar.collapsed .nav-text, .sidebar.collapsed .menu-label, .sidebar.collapsed .sidebar-footer-text{opacity:0; pointer-events:none; width:0; transform:translateX(-10px);}

.main-content{ margin-left: var(--sidebar-width); min-height:100vh; padding:24px; transition: margin-left 0.3s ease; }
.main-content.expanded{ margin-left: var(--sidebar-collapsed-width); }
.topbar{display:flex; align-items:center; justify-content:space-between; margin-bottom:24px; background-color: var(--bg-main); padding: 16px 0; border-bottom: 1px solid #e2e8f0;}
.dashboard-title{margin:0; font-weight:700; color:#14532d; font-size:1.8rem;}

.panel{ background: var(--card-bg); border:1px solid var(--border-soft); border-radius:18px; box-shadow:var(--shadow-soft); padding:20px; }
.table thead th{background:#f8fafc !important; font-size:0.84rem; color:#475569; font-weight:700; border-bottom:1px solid #e2e8f0;}

/* Custom Modal Styles for separation */
.action-box {
    background-color: #e0f2fe; /* Light blue */
    border-left: 4px solid #0284c7;
    padding: 12px 16px;
    border-radius: 6px;
    margin-bottom: 16px;
    color: #0c4a6e;
    font-weight: 500;
}
.remarks-box {
    background-color: #f8fafc; /* Light gray */
    border: 1px solid #e2e8f0;
    padding: 12px 16px;
    border-radius: 6px;
    color: #334155;
    font-size: 0.95rem;
    line-height: 1.5;
}
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
            <div><h2 class="dashboard-title">System Action Logs</h2></div>
        </div>

        <div class="panel">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th width="20%">Date & Time</th>
                            <th width="25%">Engineer Name</th>
                            <th width="35%">Project Title</th>
                            <th width="20%">Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $modals_html = ""; // We will store all the modal HTML here

                        if($logs_result && mysqli_num_rows($logs_result) > 0) {
                            while($log = mysqli_fetch_assoc($logs_result)) {
                                $date = date('M d, Y h:i A', strtotime($log['created_at']));
                                
                                // Clean up the raw details
                                $raw_details = htmlspecialchars($log['action_details']);
                                
                                // SPLIT THE ACTION FROM THE REMARKS
                                // Since we saved it as "Some Action. Remarks: Some remarks", we can split it at "Remarks: "
                                $parts = explode("Remarks: ", $raw_details);
                                
                                // Part 1 is the Action Taken
                                $action_taken = trim($parts[0]); 
                                
                                // Part 2 is the Remarks (if it exists, otherwise show a placeholder)
                                $engineer_remarks = (isset($parts[1]) && trim($parts[1]) !== '') 
                                                    ? nl2br(trim($parts[1])) 
                                                    : '<em class="text-muted">No additional remarks provided.</em>';
                                
                                
                                // Main Table Row
                                echo "<tr>";
                                echo "<td><small class='text-muted'><i class='far fa-clock me-1'></i>{$date}</small></td>";
                                echo "<td><strong><i class='fas fa-user-hard-hat me-2 text-secondary'></i>" . htmlspecialchars($log['user_name']) . "</strong></td>";
                                echo "<td>" . htmlspecialchars($log['title']) . "</td>";
                                
                                // View Details Button
                                echo "<td>
                                        <button class='btn btn-sm btn-outline-primary' data-bs-toggle='modal' data-bs-target='#logModal{$log['id']}'>
                                            <i class='fas fa-folder-open me-1'></i> View Details
                                        </button>
                                      </td>";
                                echo "</tr>";

                                // Generate the Separated Modal
                                $modals_html .= "
                                <div class='modal fade' id='logModal{$log['id']}' tabindex='-1' aria-hidden='true'>
                                  <div class='modal-dialog modal-dialog-centered'>
                                    <div class='modal-content'>
                                      <div class='modal-header'>
                                        <h5 class='modal-title text-success fw-bold'><i class='fas fa-clipboard-check me-2'></i>Audit Log Details</h5>
                                        <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
                                      </div>
                                      
                                      <div class='modal-body'>
                                        <div class='mb-3'>
                                            <small class='text-muted text-uppercase fw-bold d-block' style='font-size: 0.7rem; letter-spacing: 0.5px;'>Project</small>
                                            <strong class='fs-6'>" . htmlspecialchars($log['title']) . "</strong>
                                        </div>
                                        <div class='row mb-4'>
                                            <div class='col-6'>
                                                <small class='text-muted text-uppercase fw-bold d-block' style='font-size: 0.7rem; letter-spacing: 0.5px;'>Updated By</small>
                                                <span>" . htmlspecialchars($log['user_name']) . "</span>
                                            </div>
                                            <div class='col-6'>
                                                <small class='text-muted text-uppercase fw-bold d-block' style='font-size: 0.7rem; letter-spacing: 0.5px;'>Date</small>
                                                <span>{$date}</span>
                                            </div>
                                        </div>
                                        
                                        <small class='text-muted text-uppercase fw-bold d-block mb-1' style='font-size: 0.7rem; letter-spacing: 0.5px;'><i class='fas fa-bolt text-warning me-1'></i>System Action Taken</small>
                                        <div class='action-box'>
                                            {$action_taken}
                                        </div>

                                        <small class='text-muted text-uppercase fw-bold d-block mb-1' style='font-size: 0.7rem; letter-spacing: 0.5px;'><i class='fas fa-comment-dots text-secondary me-1'></i>Engineer Remarks</small>
                                        <div class='remarks-box shadow-sm'>
                                            {$engineer_remarks}
                                        </div>
                                        
                                      </div>
                                      
                                      <div class='modal-footer border-top-0'>
                                        <button type='button' class='btn btn-light shadow-sm text-secondary' data-bs-dismiss='modal'>Close Window</button>
                                      </div>
                                    </div>
                                  </div>
                                </div>
                                ";
                            }
                        } else {
                            echo "<tr><td colspan='4' class='text-center py-4 text-muted'>No history logs found.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<?php echo $modals_html; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const sidebar = document.getElementById('sidebar');
    const sidebarHeader = document.getElementById('sidebarHeader');
    const mainContent = document.getElementById('mainContent');

    if (sidebarHeader) {
        sidebarHeader.addEventListener('click', ()=> {
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('expanded');
        });
    }
</script>
</body>
</html>