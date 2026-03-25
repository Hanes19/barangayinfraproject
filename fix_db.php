<?php
include 'db.php';

echo "<div style='font-family: Arial; padding: 20px;'>";
echo "<h2 style='color: #14532d;'>Database Structure Updater (Safe Mode)</h2>";

$queries = [
    "ALTER TABLE projects ADD COLUMN checking_status VARCHAR(50) DEFAULT 'pending'",
    "ALTER TABLE projects ADD COLUMN submission_attempts INT DEFAULT 0",
    "ALTER TABLE projects ADD COLUMN ceo_remarks TEXT DEFAULT NULL",
    "ALTER TABLE projects ADD COLUMN approved_at DATETIME DEFAULT NULL"
];

foreach ($queries as $sql) {
    try {
        // Try to run the query
        mysqli_query($conn, $sql);
        echo "<p style='color: #16a34a;'><strong>Success:</strong> Column added to table.</p>";
    } catch (mysqli_sql_exception $e) {
        // Catch the error without crashing the script
        $error = $e->getMessage();
        if (strpos($error, 'Duplicate column name') !== false) {
            echo "<p style='color: #ca8a04;'><strong>Notice:</strong> Column already exists, skipping to next...</p>";
        } else {
            echo "<p style='color: #dc2626;'><strong>Error:</strong> $error</p>";
        }
    }
}

echo "<h4 style='margin-top: 20px;'>Update complete! You can now go back and click 'Send to CEO' again.</h4>";
echo "<a href='admin_checking.php' style='padding: 10px 15px; background: #14532d; color: white; text-decoration: none; border-radius: 5px;'>Back to Checking</a>";
echo "</div>";
?>