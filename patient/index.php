<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

requireRole('patient');
$page_title = 'Patient Dashboard';
$show_sidebar = true;

$current_user = getCurrentUser();
$user_id = $_SESSION['user_id'];

// Get statistics
$appointments_result = $conn->query("SELECT COUNT(*) as count FROM appointments WHERE patient_id = '$user_id'");
$appointments_count = $appointments_result->fetch_assoc()['count'];

$upcoming_result = $conn->query("SELECT COUNT(*) as count FROM appointments WHERE patient_id = '$user_id' AND appointment_date >= CURDATE() AND status IN ('pending', 'confirmed')");
$upcoming_count = $upcoming_result->fetch_assoc()['count'];

$completed_result = $conn->query("SELECT COUNT(*) as count FROM appointments WHERE patient_id = '$user_id' AND status = 'completed'");
$completed_count = $completed_result->fetch_assoc()['count'];

$pending_payments_result = $conn->query("SELECT SUM(amount) as total FROM payments WHERE patient_id = '$user_id' AND payment_status = 'pending'");
$pending_row = $pending_payments_result->fetch_assoc();
$pending_payment_total = $pending_row['total'] ?? 0;

$upcoming_appointments = getUpcomingAppointments($user_id, 5);
$recent_treatments = getRecentTreatments($user_id, 5);

include '../includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <h1 class="mb-4">
                <i class="fas fa-chart-line"></i> Welcome, <?php echo htmlspecialchars($current_user['first_name']); ?>!
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
                            <div class="text-muted small">Total Appointments</div>
                            <h3 class="mb-0"><?php echo $appointments_count; ?></h3>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar fa-2x text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-left-success">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-muted small">Upcoming</div>
                            <h3 class="mb-0"><?php echo $upcoming_count; ?></h3>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-hourglass-end fa-2x text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-left-info">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-muted small">Completed</div>
                            <h3 class="mb-0"><?php echo $completed_count; ?></h3>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-left-warning">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-muted small">Pending Payment</div>
                            <h3 class="mb-0">Rs. <?php echo number_format($pending_payment_total, 2); ?></h3>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-rupee-sign fa-2x text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Action Buttons -->
    <div class="row mb-4">
        <div class="col-md-12">
            <a href="book-appointment.php" class="btn btn-primary btn-lg me-2">
                <i class="fas fa-plus"></i> Book Appointment
            </a>
            <a href="appointments.php" class="btn btn-info btn-lg me-2">
                <i class="fas fa-calendar"></i> View Appointments
            </a>
            <a href="treatment-history.php" class="btn btn-secondary btn-lg">
                <i class="fas fa-file-medical"></i> Treatment History
            </a>
        </div>
    </div>

    <!-- Upcoming Appointments -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <i class="fas fa-calendar"></i> Upcoming Appointments
                </div>
                <div class="card-body">
                    <?php if (count($upcoming_appointments) > 0): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($upcoming_appointments as $apt): ?>
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between">
                                        <strong><?php echo formatDate($apt['appointment_date']); ?></strong>
                                        <?php echo getStatusBadge($apt['status']); ?>
                                    </div>
                                    <small class="text-muted">
                                        <i class="fas fa-clock"></i> <?php echo formatTime($apt['appointment_time']); ?> - 
                                        With Dr. <?php echo htmlspecialchars($apt['first_name'] . ' ' . $apt['last_name']); ?>
                                    </small>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No upcoming appointments</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Recent Treatments -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <i class="fas fa-file-medical"></i> Recent Treatment Records
                </div>
                <div class="card-body">
                    <?php if (count($recent_treatments) > 0): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($recent_treatments as $treatment): ?>
                                <div class="list-group-item">
                                    <strong><?php echo htmlspecialchars($treatment['treatment_type']); ?></strong>
                                    <br>
                                    <small class="text-muted">
                                        Dr. <?php echo htmlspecialchars($treatment['first_name'] . ' ' . $treatment['last_name']); ?>
                                        <br>
                                        <?php echo formatDate($treatment['created_at']); ?>
                                    </small>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No treatment records found</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
