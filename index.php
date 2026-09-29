<?php
if (file_exists('includes/functions.php')) {
    require_once 'includes/functions.php';
} else {
    die("Error: functions.php file not found at " . __DIR__ . '/includes/functions.php');
}
if (isLoggedIn()) {
    // Use ?? to provide a default value (null) if 'role' isn't set
    $role = $_SESSION['role'] ?? null;

    if ($role == 'patient') {
        header("Location: patient/index.php");
    } elseif ($role == 'dentist') {
        header("Location: dentist/index.php");
    } elseif ($role == 'staff') {
        header("Location: staff/index.php");
    } elseif ($role == 'admin') {
        header("Location: admin/index.php");
    } else {
        // Handle unexpected or missing roles
        header("Location: login.php");
    }
    exit();
}

// Redirect to login
header("Location: login.php");
exit();
?>
