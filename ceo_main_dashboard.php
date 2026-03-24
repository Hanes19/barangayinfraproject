<?php
session_start();
include 'db.php';

// CEO Main only sees projects transmitted to them that are NOT yet approved
$projects_query = "SELECT * FROM projects WHERE ceo_status = 'transmitted' AND checking_status IN ('pending', 'declined') ORDER BY created_at DESC";
$projects_result = mysqli_query($conn, $projects_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>CEO Main - Checking & Review</title>
<link rel="icon" type="image/png" href="cityengineerlogo.jpg">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
    body { background-color: #f4f7fb; font-family: 'Poppins', sans-serif; }
    .navbar-ceo { background-color: #0f172a; color: white; }
    .panel { background: white; border-radius: 12px; padding: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
    .table thead { background-color: #e2e8f0; }
</style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-ceo py-3 mb-4 shadow-sm">
    <div class="container">
        <a class="navbar-brand text-white fw-bold" href="#"><i class="fas fa-user-tie me-2"></i>CEO Main Portal</a>
        <a href="login.php" class="btn btn-outline-light btn-sm"><i class="fas fa-sign-out-alt me-1"></i> Logout</a>
    </div>
</nav>

<div class="container">
    <h3 class="fw-bold mb-4 text-dark"><i class="fas fa-search me-2"></i>Pending Reviews</h3>
    
    <?php if(isset($_GET['msg']) && $_GET['msg'] == 'processed'): ?>
        <div class="alert alert-success"><i class="fas fa-check-circle me-2"></i> Decision saved successfully.</div>
    <?php endif; ?>

    <div class="panel">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th width="20%">Project</th>
                    <th width="10%">Doc</th>
                    <th width="15%">Attempt</th>
                    <th width="20%">Decision</th>
                    <th width="25%">Suggestions / Remarks</th>
                    <th width="10%">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if($projects_result && mysqli_num_rows($projects_result) > 0) {
                    while($project = mysqli_fetch_assoc($projects_result)) {
                        $doc_path = !empty($project['application_file']) ? "uploads/docs/" . htmlspecialchars($project['application_file']) : "#";
                        $attempts = intval($project['submission_attempts']);
                        
                        echo "<tr>";
                        echo "<form action='update_ceo_checking.php' method='POST'>";
                        echo "<input type='hidden' name='id' value='{$project['id']}'>";
                        
                        echo "<td><strong>" . htmlspecialchars($project['title']) . "</strong></td>";
                        echo "<td><a href='$doc_path' target='_blank' class='btn btn-sm btn-outline-primary'>View Doc</a></td>";
                        echo "<td><span class='badge bg-info text-dark'>Attempt $attempts of 3</span></td>";
                        
                        // Decision Dropdown
                        echo "<td>
                                <select name='decision' class='form-select form-select-sm' required>
                                    <option value='approved'>Approve Project</option>
                                    <option value='declined'>Decline / Needs Fix</option>
                                </select>
                              </td>";
                              
                        // Remarks box
                        echo "<td><textarea name='remarks' class='form-control form-control-sm' rows='2' placeholder='If declining, what should the Admin fix?' required></textarea></td>";
                        
                        // Submit
                        echo "<td><button type='submit' class='btn btn-success btn-sm w-100'><i class='fas fa-save'></i> Save</button></td>";
                        
                        echo "</form>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='6' class='text-center py-4 text-muted'>All clear! No projects pending your review.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>