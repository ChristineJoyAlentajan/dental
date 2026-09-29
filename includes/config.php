<?php
// Database Connection Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'dental');

// Create connection using mysqli
$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to utf8
$conn->set_charset("utf8");

// Session configuration
session_start();

// Base URL for the application
define('BASE_URL', 'http://localhost/dental/');

// Application settings
define('SITE_NAME', 'Big Smile Dental Clinic');
define('SITE_VERSION', '1.0.0');

// Timezone
date_default_timezone_set('Asia/Kolkata');
?>
