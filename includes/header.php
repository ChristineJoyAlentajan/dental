<?php
require_once 'config.php';
require_once 'functions.php';

if (!isLoggedIn()) {
    header("Location: " . BASE_URL . "login.php");
    exit();
}

$current_user = getCurrentUser();
$unread_count = getUnreadNotificationsCount($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' . SITE_NAME : SITE_NAME; ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/style.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?php echo BASE_URL; ?>">
                <i class="fas fa-tooth"></i> <?php echo SITE_NAME; ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="notificationDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-bell"></i>
                            <?php if ($unread_count > 0): ?>
                                <span class="badge bg-danger"><?php echo $unread_count; ?></span>
                            <?php endif; ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notificationDropdown" style="max-height: 300px; overflow-y: auto;">
                            <?php
                            $notifications = getNotifications($_SESSION['user_id'], 5);
                            if (count($notifications) > 0) {
                                foreach ($notifications as $notif) {
                                    $read_class = $notif['is_read'] ? '' : 'bg-light';
                                    echo "<li><a class='dropdown-item $read_class' href='#'>";
                                    echo "<strong>" . $notif['title'] . "</strong><br>";
                                    echo "<small>" . substr($notif['message'], 0, 50) . "...</small>";
                                    echo "</a></li>";
                                }
                                echo "<li><hr class='dropdown-divider'></li>";
                                echo "<li><a class='dropdown-item' href='" . BASE_URL . "notifications.php'>View All</a></li>";
                            } else {
                                echo "<li><span class='dropdown-item'>No notifications</span></li>";
                            }
                            ?>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle"></i> <?php echo $current_user['first_name']; ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>profile.php">
                                <i class="fas fa-user"></i> Profile
                            </a></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>settings.php">
                                <i class="fas fa-cog"></i> Settings
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>logout.php">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Sidebar for Dashboard Navigation -->
    <?php if (isset($show_sidebar) && $show_sidebar): ?>
    <div class="container-fluid">
        <div class="row">
            <nav class="col-md-3 bg-light sidebar">
                <div class="sticky-top pt-3">
                    <?php
                    $role = isset($_SESSION['role']) ? $_SESSION['role'] : '';
                         if ($role == 'patient') {
                        ?>
                        <ul class="nav flex-column">
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo BASE_URL; ?>patient/index.php">
                                    <i class="fas fa-home"></i> Dashboard
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo BASE_URL; ?>patient/appointments.php">
                                    <i class="fas fa-calendar"></i> My Appointments
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo BASE_URL; ?>patient/book-appointment.php">
                                    <i class="fas fa-plus"></i> Book Appointment
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo BASE_URL; ?>patient/treatment-history.php">
                                    <i class="fas fa-file-medical"></i> Treatment History
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo BASE_URL; ?>profile.php">
                                    <i class="fas fa-user"></i> Profile
                                </a>
                            </li>
                        </ul>
                        <?php
                    } elseif ($role == 'dentist') {
                        ?>
                        <ul class="nav flex-column">
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo BASE_URL; ?>dentist/index.php">
                                    <i class="fas fa-home"></i> Dashboard
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo BASE_URL; ?>dentist/appointments.php">
                                    <i class="fas fa-calendar"></i> Appointments
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo BASE_URL; ?>dentist/schedule.php">
                                    <i class="fas fa-clock"></i> Schedule
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo BASE_URL; ?>dentist/patients.php">
                                    <i class="fas fa-patients"></i> Patients
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo BASE_URL; ?>dentist/treatment-records.php">
                                    <i class="fas fa-file-medical"></i> Treatment Records
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo BASE_URL; ?>profile.php">
                                    <i class="fas fa-user"></i> Profile
                                </a>
                            </li>
                        </ul>
                        <?php
                    } elseif ($role == 'staff') {
                        ?>
                        <ul class="nav flex-column">
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo BASE_URL; ?>staff/index.php">
                                    <i class="fas fa-home"></i> Dashboard
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo BASE_URL; ?>staff/appointments.php">
                                    <i class="fas fa-calendar"></i> Appointments
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo BASE_URL; ?>staff/patients.php">
                                    <i class="fas fa-patients"></i> Patients
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo BASE_URL; ?>staff/payments.php">
                                    <i class="fas fa-credit-card"></i> Payments
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo BASE_URL; ?>profile.php">
                                    <i class="fas fa-user"></i> Profile
                                </a>
                            </li>
                        </ul>
                        <?php
                    } elseif ($role == 'admin') {
                        ?>
                        <ul class="nav flex-column">
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo BASE_URL; ?>admin/index.php">
                                    <i class="fas fa-home"></i> Dashboard
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo BASE_URL; ?>admin/users.php">
                                    <i class="fas fa-users"></i> Users
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo BASE_URL; ?>admin/appointments.php">
                                    <i class="fas fa-calendar"></i> Appointments
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo BASE_URL; ?>admin/reports.php">
                                    <i class="fas fa-chart-bar"></i> Reports
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo BASE_URL; ?>admin/settings.php">
                                    <i class="fas fa-cog"></i> Settings
                                </a>
                            </li>
                        </ul>
                        <?php
                    }
                    ?>
                </div>
            </nav>
            <main class="col-md-9">
    <?php endif; ?>
</body>
</html>
