<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Log the logout action
if (isLoggedIn()) {
    logAudit('User logout');
}

// Destroy session
session_destroy();

// Redirect to login
header("Location: " . BASE_URL . "login.php?logout=1");
exit();
?>
