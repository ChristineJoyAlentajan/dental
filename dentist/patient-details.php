<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

requireRole('dentist');
$page_title = 'Patient Details';
$show_sidebar = true;

$dentist_id = $_SESSION['user_id'];
$patient_id = intval($_GET['id'] ?? 0);

include '../includes/header.php';

// Get patient details
$patient_result = $conn->query("SELECT u.* FROM users u
                               WHERE u.user_id = '$patient_id' AND u.role = 'patient'");

if (!$patient_result || $patient_result->num_rows == 0) {
    echo '<div class="container mt-5"><div class="alert alert-danger">Patient not found</div></div>';
    include '../includes/footer.php';
    exit;
}

$patient = $patient_result->fetch_assoc();

// Get patient's appointments with this dentist
$appointments_result = $conn->query("SELECT * FROM appointments 
                                     WHERE patient_id = '$patient_id' AND dentist_id = '$dentist_id'
                                     ORDER BY appointment_date DESC");

// Get patient's treatment history with this dentist
$treatments_result = $conn->query("SELECT * FROM treatment_history 
                                   WHERE patient_id = '$patient_id' AND dentist_id = '$dentist_id'
                                   ORDER BY created_at DESC LIMIT 10");
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <a href="patients.php" class="btn btn-primary mb-3">
                <i class="fas fa-arrow-left"></i> 
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Patient Info -->
        <div class="col-md-9">
            <div class="card mb-3">
                <div class="card-header">
                    <i class="fas fa-user"></i> Patient Information
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <i class="fas fa-user-circle fa-5x text-primary"></i>
                    </div>
                    <dl class="row">
                        <dt class="col-sm-5">Name:</dt>
                        <dd class="col-sm-7"><?php echo htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']); ?></dd>

                        <dt class="col-sm-5">Email:</dt>
                        <dd class="col-sm-7"><?php echo htmlspecialchars($patient['email']); ?></dd>

                        <dt class="col-sm-5">Phone:</dt>
                        <dd class="col-sm-7"><?php echo htmlspecialchars($patient['phone'] ?? '-'); ?></dd>

                        <dt class="col-sm-5">DOB:</dt>
                        <dd class="col-sm-7"><?php echo formatDate($patient['date_of_birth'] ?? ''); ?></dd>

                        <dt class="col-sm-5">Gender:</dt>
                        <dd class="col-sm-7"><?php echo htmlspecialchars($patient['gender'] ?? '-'); ?></dd>

                        <dt class="col-sm-5">Registered:</dt>
                        <dd class="col-sm-7"><?php echo formatDate($patient['registration_date']); ?></dd>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Appointments -->
        <div class="col-md-9">
            <div class="card mb-3">
                <div class="card-header">
                    <i class="fas fa-calendar"></i> Appointment History
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Reason</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($appointments_result && $appointments_result->num_rows > 0) {
                                while ($apt = $appointments_result->fetch_assoc()) {
                                    ?>
                                    <tr>
                                        <td><?php echo formatDate($apt['appointment_date']); ?></td>
                                        <td><?php echo formatTime($apt['appointment_time']); ?></td>
                                        <td><?php echo htmlspecialchars($apt['reason_for_visit'] ?? '-'); ?></td>
                                        <td><?php echo getStatusBadge($apt['status']); ?></td>
                                        <td>
                                            <a href="appointment-details.php?id=<?php echo $apt['appointment_id']; ?>" 
                                               class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php
                                }
                            } else {
                                echo '<tr><td colspan="5" class="text-center text-muted">No appointments found</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Treatment History -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-file-medical"></i> Treatment Records
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Treatment Type</th>
                                <th>Description</th>
                                <th>Cost</th>
                                <th>Payment</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($treatments_result && $treatments_result->num_rows > 0) {
                                while ($treatment = $treatments_result->fetch_assoc()) {
                                    ?>
                                    <tr>
                                        <td><?php echo formatDate($treatment['created_at']); ?></td>
                                        <td><?php echo htmlspecialchars($treatment['treatment_type']); ?></td>
                                        <td><?php echo htmlspecialchars(substr($treatment['treatment_description'] ?? '', 0, 30)); ?></td>
                                        <td>Rs. <?php echo number_format($treatment['procedure_cost'] ?? 0, 2); ?></td>
                                        <td><?php echo getPaymentStatusBadge($treatment['payment_status']); ?></td>
                                    </tr>
                                    <?php
                                }
                            } else {
                                echo '<tr><td colspan="5" class="text-center text-muted">No treatment records found</td></tr>';
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
