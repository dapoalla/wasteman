<?php
// db.php
// This file contains the database connection logic.
// IMPORTANT: Update these details with your cPanel MySQL database credentials.

// Database credentials
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'cyberros_wasteman'); // Replace with your cPanel database username
define('DB_PASSWORD', 'w[p}yn7)rSB0jSQV'); // The password you provided
define('DB_NAME', 'cyberros_waste');

// Attempt to connect to MySQL database
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if($conn === false){
    die("ERROR: Could not connect. " . $conn->connect_error);
}

// Start session on all pages that include this file
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
