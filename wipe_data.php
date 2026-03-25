<?php
include 'db.php';

echo "<div style='font-family: Arial, sans-serif; padding: 20px; max-width: 800px; margin: auto;'>";
echo "<h2 style='color: #991b1b;'>Database Wiper for Barangay Projects</h2>";
echo "<p>Wiping all project data and resetting IDs...</p>";

// 1. Disable foreign key checks temporarily so we can truncate
mysqli_query($conn, "SET FOREIGN_KEY_CHECKS = 0;");

// 2. Use TRUNCATE to empty the tables and reset the auto-incrementing IDs back to 1
$queries = [
    "TRUNCATE TABLE project_logs",
    "TRUNCATE TABLE projects"
];

$success = true;

foreach ($queries as $sql) {
    if (!mysqli_query($conn, $sql)) {
        echo "<div style='background: #fee2e2; border-left: 4px solid #ef4444; padding: 10px; margin-bottom: 10px;'>";
        echo "<strong>Error wiping table:</strong> " . mysqli_error($conn) . "<br>";
        echo "<code style='font-size: 12px;'>" . htmlspecialchars($sql) . "</code>";
        echo "</div>";
        $success = false;
    }
}

// 3. Re-enable foreign key checks to protect the database structure going forward
mysqli_query($conn, "SET FOREIGN_KEY_CHECKS = 1;");

if ($success) {
    echo "<div style='background: #dcfce7; border: 1px solid #22c55e; padding: 20px; border-radius: 8px; margin-top: 20px;'>";
    echo "<h3 style='margin-top: 0; color: #166534;'><i class='fas fa-check-circle'></i> Wipe Complete!</h3>";
    echo "<p style='font-size: 18px;'>All project and log data has been successfully deleted.</p>";
    echo "</div>";
}

echo "<div style='margin-top: 30px;'>";
echo "<a href='admin_dashboard.php' style='padding: 12px 24px; background: #991b1b; color: white; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 16px;'>Go to Dashboard ➔</a>";
echo "</div>";
echo "</div>";
?>