<?php
include 'db.php';
session_start();

$project_id = isset($_GET['id']) && !empty($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch projects for dropdown
$all_projects_query = "SELECT id, title FROM projects ORDER BY created_at DESC";
$all_projects_result = mysqli_query($conn, $all_projects_query);
$projects_list = mysqli_fetch_all($all_projects_result, MYSQLI_ASSOC);

if ($project_id == 0 && count($projects_list) > 0) {
    $project_id = $projects_list[0]['id'];
}

// Fetch current project details
$query = "SELECT * FROM projects WHERE id = $project_id";
$result = mysqli_query($conn, $query);
$project = mysqli_fetch_assoc($result);

if (!$project) { die("Project not found."); }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>HTML Preview - <?php echo htmlspecialchars($project['title']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #525659; font-family: 'Times New Roman', Times, serif; padding: 40px 0; }
        
        /* A4 Paper Styling */
        .a4-page {
            width: 210mm;
            min-height: 297mm;
            padding: 20mm;
            margin: 0 auto;
            background: white;
            box-shadow: 0 0 10px rgba(0,0,0,0.5);
            color: black;
            line-height: 1.5;
            position: relative;
        }

        .header-text { text-align: center; margin-bottom: 40px; }
        .header-text h5 { margin: 0; font-weight: bold; text-transform: uppercase; font-size: 16px; }
        .header-text p { margin: 0; font-size: 14px; }

        .content-body { font-size: 14px; text-align: justify; }
        .details-list { list-style: none; padding-left: 20px; margin: 20px 0; }
        .details-list li { margin-bottom: 10px; }
        .details-list strong { display: inline-block; width: 150px; }

        .signature-section { margin-top: 60px; }

        /* Navigation Bar - fix for visibility */
        .nav-container {
            width: 210mm;
            margin: 0 auto 20px auto;
            display: flex;
            justify-content: space-between;
        }
        
        @media print {
            body { background: none; padding: 0; }
            .nav-container { display: none; }
            .a4-page { box-shadow: none; margin: 0; width: 100%; }
        }
    </style>
</head>
<body>

<div class="nav-container no-print">

       
    <div>
        <button onclick="window.print()" class="btn btn-sm btn-light"><i class="fas fa-print"></i> Print to PDF</button>
        <a href="client_dashboard.php" class="btn btn-sm btn-danger">Exit</a>
    </div>
</div>

<div class="a4-page">
    <div class="header-text">
        <h5>Republic of the Philippines</h5>
        <p>Province of Bukidnon</p>
        <p>City of Valencia</p>
        <h5 style="margin-top:5px;">BARANGAY POBLACION</h5>
        <p>Office of the Punong Barangay</p>
    </div>

    <div class="content-body">
        <p><strong>Date:</strong> <?php echo date('F d, Y', strtotime($project['created_at'])); ?></p>
        
        <p style="margin-top: 25px;">
            <strong>To the City Engineering Office / City Mayor's Office</strong><br>
            Valencia City, Bukidnon
        </p>

        <p style="margin-top: 20px;"><strong>Subject:</strong> Formal Request for City inspection - <?= htmlspecialchars($project['title']) ?></p>

        <p style="margin-top: 20px;">Dear Sir/Madam,</p>
        <p>Greetings of peace!</p>
        <p>We are respectfully submitting this formal proposal and request for a <strong>City inspection</strong> for our proposed project: <strong>"<?= htmlspecialchars($project['title']) ?>"</strong>.</p>

        <p><strong>Project Details:</strong></p>
        <ul class="details-list">
            <li><strong>Specific Location:</strong> 15, Barangay Poblacion, Valencia City</li>
            <li><strong>Proposed Budget:</strong> Php <?= number_format($project['budget'], 2) ?></li>
            <li><strong>Intended Source of Fund:</strong> City aid</li>
        </ul>

        <p><strong>Project Description & Justification:</strong></p>
        <p><?= nl2br(htmlspecialchars($project['type_of_request'])) ?>. For the good of Poblacion, Valencia City.</p>

        <p style="margin-top: 20px;">We believe this project will greatly benefit our community. We are hoping for your favorable response and approval regarding this matter.</p>

        <div class="signature-section">
            <p>Respectfully yours,</p>
            <br><br>
            <p><strong>HON. POBLACION, VALENCIA CITY</strong><br>
            Punong Barangay<br>
            Barangay Poblacion</p>
        </div>
    </div>
</div>

</body>
</html>