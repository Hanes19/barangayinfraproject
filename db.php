<?php
// ------------------------------
// Database credentials
// ------------------------------
$servername = "localhost";
$username = "root";       // default XAMPP username
$password = "";           // default XAMPP password is empty
$dbname = "barangay_db"; // replace with your database name

// ------------------------------
// Create connection
// ------------------------------
$conn = new mysqli($servername, $username, $password, $dbname);

// ------------------------------
// Check connection
// ------------------------------
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Optional: set charset to avoid encoding issues
$conn->set_charset("utf8mb4");
