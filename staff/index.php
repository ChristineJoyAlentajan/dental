<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

requireRole('staff');
$page_title = 'Staff Dashboard';
$show_sidebar = true;

include '../includes/header.php';

// Get statistics
$total_appointments_result = $conn->query("SELECT COUNT(*) as count FROM appointments WHERE appointment_date >= CURDATE()");
$total_appointments = $total_appointments_result->fetch_assoc()['count'];

$patients_count_result = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'patient'");
$patients_count = $patients_count_result->fetch_assoc()['count'];

$dentists_count_result = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'dentist' AND is_active = 1");
$dentists_count = $dentists_count_result->fetch_assoc()['count'];

$today_appointments_result = $conn->query("SELECT COUNT(*) as count FROM appointments WHERE appointment_date = CURDATE()");
$today_appointments = $today_appointments_result->fetch_assoc()['count'];
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <h1 class="mb-4">
                <i class="fas fa-chart-line"></i> Staff Dashboard
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
                            <div class="text-muted small">Total Patients</div>
                            <h3 class="mb-0"><?php echo $patients_count; ?></h3>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-primary"></i>
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
                            <div class="text-muted small">Active Dentists</div>
                            <h3 class="mb-0"><?php echo $dentists_count; ?></h3>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-md fa-2x text-success"></i>
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
                            <div class="text-muted small">Upcoming Appointments</div>
                            <h3 class="mb-0"><?php echo $total_appointments; ?></h3>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-check fa-2x text-info"></i>
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
                            <div class="text-muted small">Today's Appointments</div>
                            <h3 class="mb-0"><?php echo $today_appointments; ?></h3>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-hourglass-end fa-2x text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Action Buttons -->
    <div class="row mb-4">
        <div class="col-md-12">
            <a href="appointments.php" class="btn btn-primary btn-lg me-2">
                <i class="fas fa-calendar"></i> Manage Appointments
            </a>
            <a href="patients.php" class="btn btn-info btn-lg me-2">
                <i class="fas fa-users"></i> Manage Patients
            </a>
            <a href="payments.php" class="btn btn-secondary btn-lg">
                <i class="fas fa-credit-card"></i> Process Payments
            </a>
        </div>
    </div>

    <!-- Recent Appointments -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <i class="fas fa-calendar"></i> Recent Appointments
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Patient</th>
                                <th>Dentist</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $appointments = $conn->query("SELECT a.*, p.first_name as patient_first, p.last_name as patient_last, 
                                                          d.first_name as dentist_first, d.last_name as dentist_last
                                                         FROM appointments a
                                                         JOIN users p ON a.patient_id = p.user_id
                                                         JOIN users d ON a.dentist_id = d.user_id
                                                         ORDER BY a.created_at DESC LIMIT 5");
                            
                            if ($appointments && $appointments->num_rows > 0) {
                                while ($apt = $appointments->fetch_assoc()) {
                                    ?>
                                    <tr>
                                        <td><?php echo formatDate($apt['appointment_date']); ?></td>
                                        <td><?php echo formatTime($apt['appointment_time']); ?></td>
                                        <td><?php echo htmlspecialchars($apt['patient_first'] . ' ' . $apt['patient_last']); ?></td>
                                        <td>Dr. <?php echo htmlspecialchars($apt['dentist_first'] . ' ' . $apt['dentist_last']); ?></td>
                                        <td><?php echo getStatusBadge($apt['status']); ?></td>
                                    </tr>
                                    <?php
                                }
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
