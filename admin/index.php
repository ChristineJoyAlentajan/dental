<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

requireRole('admin');
$page_title = 'Admin Dashboard';
$show_sidebar = true;

include '../includes/header.php';

// Get statistics
$total_users_result = $conn->query("SELECT COUNT(*) as count FROM users");
$total_users = $total_users_result->fetch_assoc()['count'];

$total_appointments_result = $conn->query("SELECT COUNT(*) as count FROM appointments");
$total_appointments = $total_appointments_result->fetch_assoc()['count'];

$total_treatments_result = $conn->query("SELECT COUNT(*) as count FROM treatment_history");
$total_treatments = $total_treatments_result->fetch_assoc()['count'];

$revenue_result = $conn->query("SELECT SUM(amount) as total FROM payments WHERE payment_status = 'completed'");
$revenue_row = $revenue_result->fetch_assoc();
$revenue = $revenue_row['total'] ?? 0;
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <h1 class="mb-4">
                <i class="fas fa-chart-line"></i> Admin Dashboard
            </h1>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-left-primary">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-muted small">Total Users</div>
                            <h3 class="mb-0"><?php echo $total_users; ?></h3>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x left-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-left-primary">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-muted small">Appointments</div>
                            <h3 class="mb-0"><?php echo $total_appointments; ?></h3>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-check fa-2x text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-left-primary">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-muted small">Treatments</div>
                            <h3 class="mb-0"><?php echo $total_treatments; ?></h3>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-file-medical fa-2x left-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-left-primary">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-muted small">Revenue</div>
                            <h3 class="mb-0">Rs. <?php echo number_format($revenue, 2); ?></h3>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-rupee-sign fa-2x left-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Action Buttons -->
    <div class="row mb-4">
        <div class="col-md-12">
            <a href="users.php" class="btn btn-primary btn-lg me-2">
                <i class="fas fa-users"></i> Manage Users
            </a>
            <a href="appointments.php" class="btn btn-primary btn-lg me-2">
                <i class="fas fa-calendar"></i> Appointments
            </a>
            <a href="reports.php" class="btn btn-primary btn-lg me-2">
                <i class="fas fa-chart-bar"></i> Reports
            </a>
            <a href="settings.php" class="btn btn-primary btn-lg">
                <i class="fas fa-cog"></i> Settings
            </a>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
