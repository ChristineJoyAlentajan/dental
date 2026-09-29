<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

requireRole('dentist');
$page_title = 'Dentist Dashboard';
$show_sidebar = true;

$dentist_id = $_SESSION['user_id'];
$current_user = getCurrentUser();

// Get statistics
$total_patients_result = $conn->query("SELECT COUNT(DISTINCT patient_id) as count FROM appointments WHERE dentist_id = '$dentist_id'");
$total_patients = $total_patients_result->fetch_assoc()['count'];

$today_appointments_result = $conn->query("SELECT COUNT(*) as count FROM appointments WHERE dentist_id = '$dentist_id' AND appointment_date = CURDATE() AND status IN ('pending', 'confirmed')");
$today_appointments = $today_appointments_result->fetch_assoc()['count'];

$total_treatments_result = $conn->query("SELECT COUNT(*) as count FROM treatment_history WHERE dentist_id = '$dentist_id'");
$total_treatments = $total_treatments_result->fetch_assoc()['count'];

$pending_confirmations_result = $conn->query("SELECT COUNT(*) as count FROM appointments WHERE dentist_id = '$dentist_id' AND status = 'pending'");
$pending_confirmations = $pending_confirmations_result->fetch_assoc()['count'];

// Get today's schedule
$today_appointments_list = $conn->query("SELECT a.*, u.first_name, u.last_name FROM appointments a
                                         JOIN users u ON a.patient_id = u.user_id
                                         WHERE a.dentist_id = '$dentist_id' AND a.appointment_date = CURDATE()
                                         ORDER BY a.appointment_time ASC");

include '../includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <h1 class="mb-4">
                <i class="fas fa-chart-line"></i> Welcome, Dr. <?php echo htmlspecialchars($current_user['last_name']); ?>!
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
                            <h3 class="mb-0"><?php echo $total_patients; ?></h3>
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
                            <div class="text-muted small">Today's Appointments</div>
                            <h3 class="mb-0"><?php echo $today_appointments; ?></h3>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-check fa-2x left-primary"></i>
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
                            <div class="text-muted small">Total Treatments</div>
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
                            <div class="text-muted small">Pending Confirmations</div>
                            <h3 class="mb-0"><?php echo $pending_confirmations; ?></h3>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-hourglass-half fa-2x left-primary"></i>
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
                <i class="fas fa-calendar"></i> View Appointments
            </a>
            <a href="schedule.php" class="btn btn-primary btn-lg me-2">
                <i class="fas fa-clock"></i> Manage Schedule
            </a>
            <a href="patients.php" class="btn btn-primary btn-lg">
                <i class="fas fa-users"></i> View Patients
            </a>
        </div>
    </div>

    <!-- Today's Appointments -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <i class="fas fa-calendar"></i> Today's Schedule
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Time</th>
                                <th>Patient Name</th>
                                <th>Reason</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($today_appointments_list && $today_appointments_list->num_rows > 0) {
                                while ($apt = $today_appointments_list->fetch_assoc()) {
                                    ?>
                                    <tr>
                                        <td><?php echo formatTime($apt['appointment_time']); ?></td>
                                        <td><?php echo htmlspecialchars($apt['first_name'] . ' ' . $apt['last_name']); ?></td>
                                        <td><?php echo htmlspecialchars($apt['reason_for_visit'] ?? '-'); ?></td>
                                        <td><?php echo getStatusBadge($apt['status']); ?></td>
                                        <td>
                                            <a href="appointment-details.php?id=<?php echo $apt['appointment_id']; ?>" 
                                               class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i> Details
                                            </a>
                                            <?php if ($apt['status'] == 'pending'): ?>
                                                <a href="confirm-appointment.php?id=<?php echo $apt['appointment_id']; ?>" 
                                                   class="btn btn-sm btn-success">
                                                    <i class="fas fa-check"></i> Confirm
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php
                                }
                            } else {
                                echo '<tr><td colspan="5" class="text-center text-muted">No appointments scheduled for today</td></tr>';
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
