<?php
session_start();
include 'db.php';

// --- 1. RBAC CLUSTER LOGIC ---
// In a real application, this should be set during login (e.g., in login.php).
// For demonstration, we will default it to Cluster 1 if not set. 
// You can change this to 2 to test the other cluster.
if (!isset($_SESSION['admin_cluster'])) {
    $_SESSION['admin_cluster'] = 1; 
}

$admin_cluster = $_SESSION['admin_cluster'];

// Define which Barangays belong to which cluster
$cluster_filter = "";
$cluster_name = "";

if ($admin_cluster == 1) {
    $cluster_filter = "AND location_barangay IN ('Poblacion', 'Lumbo')";
    $cluster_name = "Cluster 1 (Poblacion, Lumbo)";
} elseif ($admin_cluster == 2) {
    $cluster_filter = "AND location_barangay IN ('Batangan', 'Bagontaas', 'Mailag')";
    $cluster_name = "Cluster 2 (Batangan, Bagontaas, Mailag)";
}

// --- 2. QUEUE SYSTEM LOGIC (FIFO) ---
// Fetch active projects, apply cluster filter, and ORDER BY created_at ASC (Oldest First)
$projects_query = "SELECT * FROM projects 
                   WHERE ((ceo_status != 'transmitted' AND ceo_status != 'approved') OR ceo_status IS NULL) 
                   $cluster_filter
                   ORDER BY created_at ASC"; 
                   
$projects_result = mysqli_query($conn, $projects_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Planning Dashboard - Queue</title>
<link rel="icon" type="image/png" href="cityengineerlogo.jpg">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
/* Sidebar styles remain exactly the same */
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
.sidebar-header{
    min-height:78px; padding:20px 18px; display:flex; align-items:center; justify-content:center;
    border-bottom:1px solid rgba(255,255,255,0.10); cursor:pointer;
}
.brand-text h4{ margin:0; font-size:1rem; font-weight:700; color:#fff; text-align:center; }
.sidebar-menu{ padding:18px 12px; overflow-y:auto; flex-grow:1; }
.menu-label{ color: rgba(255,255,255,0.60); font-size:0.72rem; text-transform:uppercase; letter-spacing:1px; margin:6px 12px 12px; white-space:nowrap; transition:0.25s ease; }
.sidebar a{
    color: #e5e7eb; text-decoration: none; padding: 13px 14px; display:flex; align-items:center; gap:14px;
    font-size:14px; font-weight:500; transition:0.25s ease; border-radius:16px; margin-bottom:8px; white-space:nowrap;
}
.sidebar a i{min-width:24px;text-align:center;font-size:15px;}
.sidebar a:hover, .sidebar a.active{background:rgba(255,255,255,0.12); color:#fff; transform:translateX(2px);}
.sidebar-footer{padding:12px; border-top:1px solid rgba(255,255,255,0.10);}
.sidebar-footer a{margin-bottom:0; display:flex; align-items:center; gap:8px;}
.sidebar.collapsed .nav-text, .sidebar.collapsed .menu-label, .sidebar.collapsed .sidebar-footer-text{opacity:0; pointer-events:none; width:0; transform:translateX(-10px);}

.main-content{
    margin-left: var(--sidebar-width); min-height:100vh; padding:24px; transition: margin-left 0.3s ease;
}
.main-content.expanded{ margin-left: var(--sidebar-collapsed-width); }
.topbar{display:flex; align-items:center; justify-content:space-between; margin-bottom:24px; background-color: var(--bg-main); padding: 16px 0; border-bottom: 1px solid #e2e8f0;}
.dashboard-title{margin:0; font-weight:700; color:#14532d; font-size:1.8rem;}

.panel{
    background: var(--card-bg);
    border:1px solid var(--border-soft); border-radius:18px;
    box-shadow:var(--shadow-soft); padding:20px;
}
.table thead th{background:#f8fafc !important; font-size:0.84rem; color:#475569; font-weight:700; border-bottom:1px solid #e2e8f0;}

/* Queue Specific Styles */
.queue-number {
    font-size: 1.2rem;
    font-weight: bold;
    color: #0f3d22;
    display: inline-block;
    width: 35px;
    height: 35px;
    line-height: 35px;
    text-align: center;
    border-radius: 50%;
    background-color: #e2e8f0;
}
.queue-priority {
    background-color: #dc3545; /* Red for Top 1 */
    color: white;
}
.queue-next {
    background-color: #ffc107; /* Yellow for 2nd and 3rd */
    color: #000;
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
            <a href="admin_dashboard.php"><i class="fas fa-chart-pie"></i><span class="nav-text">Dashboard</span></a>
            <a href="admin_planning.php" class="active"><i class="fas fa-clipboard-list"></i><span class="nav-text">Planning & Site Inspection</span></a>
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
            <div>
                <h2 class="dashboard-title">Planning Queue</h2>
                <p class="text-muted mb-0"><i class="fas fa-map-marker-alt me-1"></i> Showing projects for: <strong><?php echo $cluster_name; ?></strong></p>
            </div>
        </div>

        <?php if(isset($_GET['msg']) && $_GET['msg'] == 'updated'): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i> Project planning details saved successfully.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php elseif(isset($_GET['msg']) && $_GET['msg'] == 'transmitted'): ?>
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <i class="fas fa-paper-plane me-2"></i> Project successfully transmitted to the Main Office.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php elseif(isset($_GET['msg']) && $_GET['msg'] == 'cpdc_error'): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i> <strong>Action Blocked:</strong> You cannot approve this project because the CPDC Certification is not yet approved.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="panel">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0 text-success"><i class="fas fa-layer-group me-2"></i> Priority Queue</h5>
                <span class="badge bg-secondary">Total Pending: <?php echo mysqli_num_rows($projects_result); ?></span>
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th width="5%" class="text-center">Q#</th>
                            <th width="15%">Project Name</th>
                            <th width="10%">Barangay</th>
                            <th width="10%">Document</th>
                            <th width="10%">CPDC Cert.</th>
                            <th width="12%">CEO Approval</th>
                            <th width="20%">Inspection Remarks</th>
                            <th width="18%">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if($projects_result && mysqli_num_rows($projects_result) > 0) {
                            $queue_counter = 1; // Initialize the queue counter
                            
                            while($project = mysqli_fetch_assoc($projects_result)) {
                                $doc_path = !empty($project['application_file']) ? "uploads/docs/" . htmlspecialchars($project['application_file']) : "#";
                                $cpdc_status = strtolower($project['status']);
                                $ceo_status = isset($project['ceo_status']) ? strtolower($project['ceo_status']) : 'pending';
                                $remarks = isset($project['remarks']) ? htmlspecialchars($project['remarks']) : '';
                                $barangay = isset($project['location_barangay']) ? htmlspecialchars($project['location_barangay']) : 'N/A';
                                
                                $badgeClass = 'bg-warning text-dark';
                                $statusText = 'Pending';
                                if ($cpdc_status == 'approved') {
                                    $badgeClass = 'bg-success';
                                    $statusText = 'Approved';
                                } elseif ($cpdc_status == 'rejected' || $cpdc_status == 'declined' || $cpdc_status == 'cancelled') {
                                    $badgeClass = 'bg-danger';
                                    $statusText = 'Declined';
                                }
                                
                                // Determine styling based on Queue position
                                $queueStyle = '';
                                if ($queue_counter == 1) {
                                    $queueStyle = 'queue-priority shadow-sm'; // Highlight the oldest project
                                } elseif ($queue_counter == 2 || $queue_counter == 3) {
                                    $queueStyle = 'queue-next'; // Highlight the next two
                                }
                                
                                echo "<tr " . ($queue_counter == 1 ? "style='background-color: #fef2f2;'" : "") . ">";
                                echo "<form action='update_admin_planning.php' method='POST'>";
                                echo "<input type='hidden' name='id' value='{$project['id']}'>";
                                
                                // Queue Number Output
                                echo "<td class='text-center'><span class='queue-number {$queueStyle}'>{$queue_counter}</span></td>";
                                
                                // Project Name & Submitted Date
                                $date_submitted = date('M d, Y', strtotime($project['created_at']));
                                echo "<td>
                                        <strong>" . htmlspecialchars($project['title']) . "</strong><br>
                                        <small class='text-muted'>Added: {$date_submitted}</small>
                                      </td>";
                                
                                // Barangay Location
                                echo "<td><span class='badge bg-light text-dark border'>{$barangay}</span></td>";
                                
                                // View Document
                                echo "<td>";
                                if (!empty($project['application_file'])) {
                                    echo "<a href='$doc_path' target='_blank' class='btn btn-sm btn-outline-primary'><i class='fas fa-file-pdf me-1'></i> View</a>";
                                } else {
                                    echo "<span class='text-muted small'>No File</span>";
                                }
                                echo "</td>";
                                
                                // Read-Only CPDC Certification Badge
                                echo "<td>
                                        <span class='badge {$badgeClass} px-2 py-1' style='font-size: 0.75rem;'>
                                            <i class='fas fa-certificate me-1'></i> {$statusText}
                                        </span>
                                      </td>";

                                // CEO Approval Dropdown
                                echo "<td>
                                        <select name='ceo_status' class='form-select form-select-sm'>
                                            <option value='pending' " . ($ceo_status == 'pending' ? 'selected' : '') . ">Pending</option>
                                            <option value='approved' " . ($ceo_status == 'approved' ? 'selected' : '') . ">Approved</option>
                                            <option value='declined' " . ($ceo_status == 'declined' ? 'selected' : '') . ">Declined</option>
                                        </select>
                                      </td>";
                                      
                                // Inspection Remarks
                                echo "<td>
                                        <textarea name='remarks' class='form-control form-control-sm' rows='1' placeholder='Remarks...'>$remarks</textarea>
                                      </td>";
                                      
                                // Actions
                                echo "<td>
                                        <div class='d-flex gap-1 flex-wrap'>
                                            <button type='submit' name='action' value='save' class='btn btn-sm btn-success'>
                                                <i class='fas fa-save'></i> Save
                                            </button>
                                            <button type='submit' name='action' value='transmit' class='btn btn-sm btn-warning text-dark' onclick='return confirm(\"Are you sure you want to transmit this project to the main office?\");'>
                                                <i class='fas fa-share-square'></i> Transmit
                                            </button>
                                        </div>
                                      </td>";
                                      
                                echo "</form>";
                                echo "</tr>";
                                
                                $queue_counter++; // Increment for the next row
                            }
                        } else {
                            echo "<tr><td colspan='8' class='text-center py-5 text-muted'><i class='fas fa-inbox fs-1 mb-3 d-block'></i>Queue is currently empty for this cluster.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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

    // --- NEW: Trigger Popup if URL has msg=cpdc_error ---
    <?php if(isset($_GET['msg']) && $_GET['msg'] == 'cpdc_error'): ?>
    document.addEventListener("DOMContentLoaded", function() {
        Swal.fire({
            icon: 'error',
            title: 'Action Denied',
            text: 'You cannot mark this as Approved. The CPDC Certification must be approved first!',
            confirmButtonColor: '#14532d', // Matches your theme green
            confirmButtonText: 'Understood'
        }).then((result) => {
            // Optional: Clean up the URL so the popup doesn't show again on refresh
            window.history.replaceState(null, null, window.location.pathname);
        });
    });
    <?php endif; ?>
</script>
</body>
</html>