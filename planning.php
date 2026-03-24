<?php
session_start();
include 'db.php';

// Check if user is CPDC (you can add your own check here)

// Fetch 2 latest projects
$recent_projects_query = "SELECT * FROM projects ORDER BY created_at DESC LIMIT 10";
$recent_projects_result = mysqli_query($conn, $recent_projects_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Planning Dashboard - CPDC</title>
<link rel="icon" type="image/png" href="cityengineerlogo.jpg">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { 
    background: url('CityHall.jpg') no-repeat center center fixed; /* <-- Replace 'background.jpg' with your image path */
    background-size: cover;
    font-family: Arial, sans-serif; 
}
h3 { 
    margin-bottom: 20px; 
    color: #14532d; 
}
.table thead { 
    background-color: #6ee7a0; 
    color: white; 
}
.table-hover tbody tr:hover { 
    background-color: #d1fae5; 
}
.btn-approve { 
    background-color: #22c55e; 
    color: white; 
}
.btn-approve:hover { 
    background-color: #16a34a; 
}
.btn-reject { 
    background-color: #ef4444; 
    color: white; 
}
.btn-reject:hover { 
    background-color: #b91c1c; 
}
/* Optional: add semi-transparent background to table for readability */
.table-responsive {
    background-color: rgba(255, 255, 255, 0.85); 
    padding: 15px;
    border-radius: 10px;
}

h3 { 
    margin-bottom: 20px; 
    color: #f8f9fa; /* soft white, clearer on bright backgrounds */
}

.heading-container {
    background-color: rgba(0, 0, 0, 0.5); /* semi-transparent black */
    display: inline-block; /* shrink to fit the heading */
    padding: 10px 20px;
    border-radius: 8px;
    margin-bottom: 20px;
}

.heading-container h3 {
    color: #f8f9fa; /* soft white */
    margin: 0;
}

</style>
</head>
<body>
<div class="container mt-5">
  <div class="heading-container">
    <h3>Recent Projects Management (CPDC View)</h3>
</div>
    <div class="table-responsive mt-3">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Project Name</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if($recent_projects_result && mysqli_num_rows($recent_projects_result) > 0) {
                    while($project = mysqli_fetch_assoc($recent_projects_result)) {
                        echo "<tr>";
                        echo "<td>{$project['id']}</td>";
                        echo "<td>{$project['title']}</td>";
                        echo "<td>{$project['status']}</td>";
                        echo "<td>
                            <form action='update_status.php' method='POST' class='d-flex gap-2 flex-wrap'>
                                <input type='hidden' name='id' value='{$project['id']}'>
                                <button type='submit' name='status' value='approved' class='btn btn-sm btn-approve'>
                                    <i class='fas fa-check'></i> Approve
                                </button>
                                <button type='submit' name='status' value='rejected' class='btn btn-sm btn-reject'>
                                    <i class='fas fa-times'></i> Reject
                                </button>
                            </form>
                        </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='4' class='text-center'>No projects found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>