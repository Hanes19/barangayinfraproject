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

<div class="container-fluid px-5">
    <h3 class="fw-bold mb-4 text-dark"><i class="fas fa-search me-2"></i>Pending Reviews</h3>
    
    <?php if(isset($_GET['msg']) && $_GET['msg'] == 'processed'): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-2"></i> Decision and files saved successfully.
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
                        <th width="10%">Attempt</th>
                        <th width="15%">Decision</th>
                        <th width="30%">Suggestions / Remarks</th>
                        <th width="15%">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if($projects_result && mysqli_num_rows($projects_result) > 0) {
                        while($project = mysqli_fetch_assoc($projects_result)) {
                            $doc_path = !empty($project['application_file']) ? "uploads/docs/" . htmlspecialchars($project['application_file']) : "#";
                            $attempts = intval($project['submission_attempts']);
                            
                            // Unique form ID to link inputs across columns
                            $form_id = "ceo_form_" . $project['id'];
                            
                            echo "<tr>";
                            
                            // Col 1: Project Name
                            echo "<td><strong>" . htmlspecialchars($project['title']) . "</strong></td>";
                            
                            // Col 2: Document (View + Attach Feedback File)
                            echo "<td>";
                            echo "<a href='$doc_path' target='_blank' class='btn btn-sm btn-outline-primary mb-1 w-100'><i class='fas fa-file-pdf'></i> View Doc</a>";
                            
                            echo "<div class='mt-2 border-top pt-2'>";
                            echo "<small class='text-secondary fw-bold d-block mb-1' style='font-size: 0.7rem;'><i class='fas fa-paperclip'></i> Attach Redlines (Optional):</small>";
                            // Input linked to the form
                            echo "<input type='file' name='ceo_document' form='$form_id' class='form-control form-control-sm' accept='.pdf,.doc,.docx' title='Attach marked-up document'>";
                            echo "</div>";
                            echo "</td>";
                            
                            // Col 3: Attempts
                            echo "<td><span class='badge bg-info text-dark'>Attempt $attempts of 3</span></td>";
                            
                            // Col 4: Decision Dropdown (Linked to form)
                            echo "<td>
                                    <select name='decision' form='$form_id' class='form-select form-select-sm' required>
                                        <option value='approved'>Approve Project</option>
                                        <option value='declined'>Decline / Needs Fix</option>
                                    </select>
                                  </td>";
                                  
                            // Col 5: Remarks box (Linked to form)
                            echo "<td><textarea name='remarks' form='$form_id' class='form-control form-control-sm' rows='3' placeholder='Leave feedback, instructions, or approval notes here...' required></textarea></td>";
                            
                            // Col 6: Action / Submit Button (Where the form actually lives)
                            echo "<td>
                                    <form id='$form_id' action='update_ceo_checking.php' method='POST' enctype='multipart/form-data'>
                                        <input type='hidden' name='id' value='{$project['id']}'>
                                        <button type='submit' class='btn btn-success btn-sm w-100 py-2'><i class='fas fa-save me-1'></i> Submit Decision</button>
                                    </form>
                                  </td>";
                            
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6' class='text-center py-5 text-muted'><i class='fas fa-clipboard-check fs-2 mb-3 d-block text-light'></i>All clear! No projects pending your review.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>