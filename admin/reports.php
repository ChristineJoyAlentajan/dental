<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

requireRole('admin');
$page_title = 'Reports & Analytics';
$show_sidebar = true;

include '../includes/header.php';

// Get statistics for reports
$total_appointments_result = $conn->query("SELECT COUNT(*) as count FROM appointments");
$total_appointments = $total_appointments_result->fetch_assoc()['count'];

$completed_appointments_result = $conn->query("SELECT COUNT(*) as count FROM appointments WHERE status = 'completed'");
$completed_appointments = $completed_appointments_result->fetch_assoc()['count'];

$total_patients_result = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'patient'");
$total_patients = $total_patients_result->fetch_assoc()['count'];

$active_patients_result = $conn->query("SELECT COUNT(*) as count FROM (SELECT DISTINCT patient_id FROM appointments WHERE appointment_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)) as active");
$active_patients = $active_patients_result->fetch_assoc()['count'];

$total_revenue_result = $conn->query("SELECT SUM(amount) as total FROM payments WHERE payment_status = 'completed'");
$total_revenue = $total_revenue_result->fetch_assoc()['total'] ?? 0;

$pending_revenue_result = $conn->query("SELECT SUM(amount) as total FROM payments WHERE payment_status = 'pending'");
$pending_revenue = $pending_revenue_result->fetch_assoc()['total'] ?? 0;

$treatment_count_result = $conn->query("SELECT treatment_type, COUNT(*) as count FROM treatment_history GROUP BY treatment_type ORDER BY count DESC LIMIT 5");

?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <h2 class="mb-4"><i class="fas fa-chart-bar"></i> Reports & Analytics</h2>
        </div>
    </div>

    <!-- Statistics Row 1 -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-left-primary">
                <div class="card-body">
                    <div class="text-muted small">Total Appointments</div>
                    <h3 class="mb-0"><?php echo $total_appointments; ?></h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-left-primary">
                <div class="card-body">
                    <div class="text-muted small">Completed Appointments</div>
                    <h3 class="mb-0"><?php echo $completed_appointments; ?></h3>
                    <small class="text-muted">
                        <?php echo $total_appointments > 0 ? round(($completed_appointments / $total_appointments) * 100, 1) : 0; ?>% completion rate
                    </small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-left-primary">
                <div class="card-body">
                    <div class="text-muted small">Total Patients</div>
                    <h3 class="mb-0"><?php echo $total_patients; ?></h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
                <div class="card border-left-primary">
                    <div class="card-body">
                        <div class="text-muted small">Active Patients (30 days)</div>
                    <h3 class="mb-0"><?php echo $active_patients; ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Row 2 -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-left-primary">
                <div class="card-body">
                    <div class="text-muted small">Total Revenue</div>
                    <h3 class="mb-0">Rs. <?php echo number_format($total_revenue, 2); ?></h3>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-left-primary">
                <div class="card-body">
                    <div class="text-muted small">Pending Revenue</div>
                    <h3 class="mb-0">Rs. <?php echo number_format($pending_revenue, 2); ?></h3>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-left-primary">
                <div class="card-body">
                    <div class="text-muted small">Average per Appointment</div>
                    <h3 class="mb-0">Rs. <?php echo $total_appointments > 0 ? number_format($total_revenue / $total_appointments, 2) : 0; ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Treatments -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-file-medical"></i> Top Treatments
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Treatment Type</th>
                                <th>Count</th>
                                <th>%</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($treatment_count_result && $treatment_count_result->num_rows > 0) {
                                $total_treatments = 0;
                                $treatments_data = [];
                                
                                while ($row = $treatment_count_result->fetch_assoc()) {
                                    $total_treatments += $row['count'];
                                    $treatments_data[] = $row;
                                }
                                
                                foreach ($treatments_data as $treatment) {
                                    $percentage = $total_treatments > 0 ? round(($treatment['count'] / (($total_appointments > 0) ? $total_appointments : 1)) * 100, 1) : 0;
                                    echo '<tr>';
                                    echo '<td>' . htmlspecialchars($treatment['treatment_type']) . '</td>';
                                    echo '<td>' . $treatment['count'] . '</td>';
                                    echo '<td>' . $percentage . '%</td>';
                                    echo '</tr>';
                                }
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Appointments by Status -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-tasks"></i> Appointments by Status
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Status</th>
                                <th>Count</th>
                                <th>%</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $statuses = ['pending', 'confirmed', 'completed', 'cancelled', 'no-show'];
                            foreach ($statuses as $status) {
                                $status_result = $conn->query("SELECT COUNT(*) as count FROM appointments WHERE status = '$status'");
                                $status_count = $status_result->fetch_assoc()['count'];
                                $percentage = $total_appointments > 0 ? round(($status_count / $total_appointments) * 100, 1) : 0;
                                
                                echo '<tr>';
                                echo '<td>' . ucfirst($status) . '</td>';
                                echo '<td>' . $status_count . '</td>';
                                echo '<td>' . $percentage . '%</td>';
                                echo '</tr>';
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
