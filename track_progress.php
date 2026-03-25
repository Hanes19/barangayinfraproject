<?php
include 'db.php';

// Get project ID from URL, fallback to 1 for testing
$project_id = isset($_GET['id']) && !empty($_GET['id']) ? intval($_GET['id']) : 1;

// Fetch project details
$query = "SELECT * FROM projects WHERE id = $project_id";
$result = mysqli_query($conn, $query);

if (!$result || mysqli_num_rows($result) == 0) {
    echo "<!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <title>Project Not Found</title>
        <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
    </head>
    <body>
        <div class='container mt-5'>
            <div class='alert alert-danger'>
                <h4 class='alert-heading'>Project Not Found</h4>
                <p>We couldn't find the project with ID = $project_id.</p>
                <hr>
                <a href='client_dashboard.php' class='btn btn-success'>Back to Dashboard</a>
            </div>
        </div>
    </body>
    </html>";
    exit;
}

$project = mysqli_fetch_assoc($result);

// Define progress stages
$stages = [
    'pending'  => 'Pending',
    'approved' => 'Approved',
    'ongoing'  => 'Ongoing',
    'done'     => 'Done',
    'rejected' => 'Rejected'
];

// Current project status
$current_status = $project['status'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Track Project Progress</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
body {
    font-family: 'Segoe UI', sans-serif;
    background-color: #f4fdf4;
    color: #1b5e20;
    padding: 20px;
}
.card {
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}
.timeline {
    list-style: none;
    padding-left: 0;
}
.timeline li {
    position: relative;
    padding-left: 35px;
    margin-bottom: 25px;
}
.timeline li::before {
    content: "";
    position: absolute;
    left: 0;
    top: 0;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background-color: #ccc;
    border: 2px solid #1b5e20;
}
.timeline li.completed::before {
    background-color: #28a745;
    border-color: #28a745;
}
.timeline li.completed .stage-name {
    font-weight: bold;
    color: #28a745;
}
.timeline li:not(:last-child)::after {
    content: "";
    position: absolute;
    left: 9px;
    top: 20px;
    width: 2px;
    height: calc(100% - 20px);
    background-color: #ccc;
}
.timeline li.completed:not(:last-child)::after {
    background-color: #28a745;
}
.stage-name {
    font-size: 1rem;
}
</style>
</head>
<body>

<div class="container">
    <a href="client_dashboard.php" class="btn btn-sm btn-outline-success mb-3"><i class="fas fa-arrow-left me-1"></i>Back to Dashboard</a>

    <div class="card p-4">
        <h4 class="mb-3"><?php echo htmlspecialchars($project['title']); ?></h4>
        <p><strong>Type:</strong> <?php echo htmlspecialchars($project['type_of_request']); ?></p>
        <p><strong>Budget:</strong> ₱<?php echo number_format($project['budget'], 2); ?></p>

        <h5 class="mt-4 mb-3">Project Progress</h5>
<ul class="timeline">
    <?php
    $status_order = ['pending','approved','waiting','preparing','review','released'];

    foreach($status_order as $stage){
        $completed = '';

        // Handle cancelled
        if($current_status == 'cancelled'){
            if($stage == 'pending'){ 
                $completed = 'completed'; 
            }
        } 
        // Normal flow
        elseif(array_search($stage, $status_order) <= array_search($current_status, $status_order)){
            $completed = 'completed';
        }

        echo '<li class="'.$completed.'"><span class="stage-name">';

        switch($stage){
            case 'pending': echo 'Barangay Request'; break;
            case 'approved': echo 'CPDC Approved'; break;
            case 'waiting': echo 'LnB Approved'; break; // 🔥 NEW
            case 'preparing': echo 'Preparation of DED and POW'; break;
            case 'review': echo 'Checking and Review by CEO Main'; break;
            case 'released': echo 'Approved Document for Release to Barangay'; break;
        }

        echo '</span></li>';
    }
    ?>
</ul>

     <div class="mt-4">
    <strong>Current Status:</strong> 
    <span class="badge <?php 
        switch($current_status){
            case 'pending': echo 'bg-warning text-dark'; break;
            case 'approved': echo 'bg-primary'; break;
            case 'waiting': echo 'bg-dark text-light'; break;
            case 'preparing': echo 'bg-info text-dark'; break;
            case 'review': echo 'bg-secondary'; break;
            case 'released': echo 'bg-success'; break;
            case 'cancelled': echo 'bg-danger'; break;
            default: echo 'bg-dark';
        }
    ?>">
        <?php 
        switch($current_status){
            case 'pending': echo 'Pending (Barangay Request)'; break;
            case 'approved': echo 'Approved (CPDC Approved)'; break;
            case 'waiting': echo 'LNB'; break;
            case 'preparing': echo 'Preparation of DED and POW'; break;
            case 'review': echo 'Checking by CEO Main'; break;
            case 'released': echo 'Approved Document for Release to Barangay'; break;
            case 'cancelled': echo 'Cancelled Request'; break;
            default: echo ucfirst($current_status);
        }
        ?>
    </span>
</div>

</body>
</html>









<?php
include 'db.php';

// STATUS COUNTS
$status_counts = ['pending'=>0,'approved'=>0,'ongoing'=>0,'done'=>0,'rejected'=>0];
$total_projects = 0;

$result = mysqli_query($conn, "SELECT status, COUNT(*) as count FROM projects GROUP BY status");
while ($row = mysqli_fetch_assoc($result)) {
    $status_counts[$row['status']] = $row['count'];
    $total_projects += $row['count'];
}

// PROJECTS
$projects_result = mysqli_query($conn, "SELECT * FROM projects ORDER BY id DESC");

// BARANGAY
$barangay_labels=[]; $barangay_data=[];
$res = mysqli_query($conn,"SELECT location_barangay, COUNT(*) total FROM projects GROUP BY location_barangay");
while($r=mysqli_fetch_assoc($res)){
    $barangay_labels[]=$r['location_barangay'];
    $barangay_data[]=$r['total'];
}

// LINE
$date_labels=[]; $date_data=[];
$res = mysqli_query($conn,"SELECT DATE(created_at) d, COUNT(*) t FROM projects GROUP BY d ORDER BY d");
while($r=mysqli_fetch_assoc($res)){
    $date_labels[]=$r['d'];
    $date_data[]=$r['t'];
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
body{
    background:url('musuan.jpg') no-repeat center/cover fixed;
    font-family:'Segoe UI';
}

.card{border-radius:12px;}
.card-header{background:#4caf50;color:#fff;}

.status-badge{
    padding:5px 8px;
    border-radius:5px;
    color:white;
}
.status-pending{background:#ffc107;color:black;}
.status-approved{background:#0d6efd;}
.status-ongoing{background:#17a2b8;}
.status-done{background:#28a745;}
.status-rejected{background:#dc3545;}

canvas{max-height:300px;}

/* 🔥 MOBILE TABLE TRANSFORM */
@media(max-width:768px){

table thead{display:none;}

table, tbody, tr, td{
    display:block;
    width:100%;
}

tr{
    margin-bottom:15px;
    background:white;
    border-radius:10px;
    padding:10px;
    box-shadow:0 2px 10px rgba(0,0,0,0.1);
}

td{
    text-align:right;
    padding-left:50%;
    position:relative;
}

td::before{
    position:absolute;
    left:10px;
    top:10px;
    font-weight:bold;
    text-align:left;
}

/* Labels */
td:nth-child(1)::before{content:"#";}
td:nth-child(2)::before{content:"Title";}
td:nth-child(3)::before{content:"Budget";}
td:nth-child(4)::before{content:"Status";}
}

/* spacing fix */
.container{padding-bottom:50px;}
</style>
</head>

<body>

<div class="container mt-3">

<!-- TABLE -->
<div class="card mb-4">
<div class="card-header">Projects</div>
<div class="card-body">

<table class="table table-hover">
<thead>
<tr>
<th>#</th>
<th>Title</th>
<th>Budget</th>
<th>Status</th>
</tr>
</thead>

<tbody>
<?php $i=1; while($p=mysqli_fetch_assoc($projects_result)): ?>
<tr>
<td><?= $i++ ?></td>
<td><?= $p['title'] ?></td>
<td>₱<?= number_format($p['budget'],2) ?></td>
<td><span class="status-badge status-<?= $p['status'] ?>">
<?= ucfirst($p['status']) ?>
</span></td>
</tr>
<?php endwhile; ?>
</tbody>
</table>

</div>
</div>

<!-- BAR -->
<div class="card mb-4">
<div class="card-header">Overview</div>
<div class="card-body">
<canvas id="barChart"></canvas>
</div>
</div>

<!-- PIE + LINE -->
<div class="row">
<div class="col-md-6">
<div class="card mb-4">
<div class="card-header">Status</div>
<div class="card-body">
<canvas id="pieChart"></canvas>
</div>
</div>
</div>

<div class="col-md-6">
<div class="card mb-4">
<div class="card-header">Timeline</div>
<div class="card-body">
<canvas id="lineChart"></canvas>
</div>
</div>
</div>
</div>

<!-- BARANGAY -->
<div class="card mb-4">
<div class="card-header">Barangay</div>
<div class="card-body">
<canvas id="barangayChart"></canvas>
</div>
</div>

</div>

<script>
// BAR
new Chart(barChart,{
type:'bar',
data:{labels:['Pending','Approved','Ongoing','Done','Rejected'],
datasets:[{data:[
<?= $status_counts['pending'] ?>,
<?= $status_counts['approved'] ?>,
<?= $status_counts['ongoing'] ?>,
<?= $status_counts['done'] ?>,
<?= $status_counts['rejected'] ?>
]}]}
});

// PIE
new Chart(pieChart,{
type:'pie',
data:{labels:['Pending','Approved','Ongoing','Done','Rejected'],
datasets:[{data:[
<?= $status_counts['pending'] ?>,
<?= $status_counts['approved'] ?>,
<?= $status_counts['ongoing'] ?>,
<?= $status_counts['done'] ?>,
<?= $status_counts['rejected'] ?>
]}]}
});

// LINE
new Chart(lineChart,{
type:'line',
data:{
labels:<?= json_encode($date_labels) ?>,
datasets:[{data:<?= json_encode($date_data) ?>}]
}
});

// BARANGAY
new Chart(barangayChart,{
type:'bar',
data:{
labels:<?= json_encode($barangay_labels) ?>,
datasets:[{data:<?= json_encode($barangay_data) ?>}]
},
options:{indexAxis:'y'}
});
</script>

</body>
</html>