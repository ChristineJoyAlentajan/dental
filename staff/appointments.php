<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

requireRole(array('staff', 'admin'));
$page_title = 'Manage Appointments';
$show_sidebar = true;

$status_filter = $_GET['status'] ?? '';

include '../includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <h2 class="mb-4"><i class="fas fa-calendar"></i> Manage Appointments</h2>
        </div>
    </div>

    <!-- Filter Tabs -->
    <div class="row mb-4">
        <div class="col-md-12">
            <ul class="nav nav-tabs">
                <li class="nav-item">
                    <a class="nav-link <?php echo empty($status_filter) ? 'active' : ''; ?>" href="appointments.php">All</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $status_filter == 'pending' ? 'active' : ''; ?>" href="?status=pending">Pending</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $status_filter == 'confirmed' ? 'active' : ''; ?>" href="?status=confirmed">Confirmed</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $status_filter == 'completed' ? 'active' : ''; ?>" href="?status=completed">Completed</a>
                </li>
            </ul>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Patient</th>
                                <th>Dentist</th>
                                <th>Reason</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $query = "SELECT a.*, p.first_name as patient_first, p.last_name as patient_last,
                                      d.first_name as dentist_first, d.last_name as dentist_last
                                      FROM appointments a
                                      JOIN users p ON a.patient_id = p.user_id
                                      JOIN users d ON a.dentist_id = d.user_id";
                            
                            if (!empty($status_filter)) {
                                $query .= " WHERE a.status = '$status_filter'";
                            }
                            
                            // Use FIFO (first-booked) ordering by creation time
                            $query .= " ORDER BY a.created_at ASC";
                            
                            $result = $conn->query($query);
                            
                            if ($result && $result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    ?>
                                    <tr>
                                        <td><?php echo formatDate($row['appointment_date']); ?></td>
                                        <td><?php echo formatTime($row['appointment_time']); ?></td>
                                        <td><?php echo htmlspecialchars($row['patient_first'] . ' ' . $row['patient_last']); ?></td>
                                        <td>Dr. <?php echo htmlspecialchars($row['dentist_first'] . ' ' . $row['dentist_last']); ?></td>
                                        <td><?php echo htmlspecialchars($row['reason_for_visit'] ?? '-'); ?></td>
                                        <td><?php echo getStatusBadge($row['status']); ?></td>
                                        <td>
                                            <a href="#" class="btn btn-sm btn-info">View</a>
                                        </td>
                                    </tr>
                                    <?php
                                }
                            } else {
                                echo '<tr><td colspan="7" class="text-center text-muted">No appointments found</td></tr>';
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
