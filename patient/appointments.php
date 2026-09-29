<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

requireRole('patient');
$page_title = 'My Appointments';
$show_sidebar = true;

$patient_id = $_SESSION['user_id'];
$status_filter = $_GET['status'] ?? '';

include '../includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-calendar"></i> My Appointments</h2>
                <a href="book-appointment.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Book New Appointment
                </a>
            </div>
        </div>
    </div>

    <!-- Filter Tabs -->
    <div class="row mb-4">
        <div class="col-md-12">
            <ul class="nav nav-tabs">
                <li class="nav-item">
                    <a class="nav-link <?php echo empty($status_filter) ? 'active' : ''; ?>" 
                       href="appointments.php">All</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $status_filter == 'pending' ? 'active' : ''; ?>" 
                       href="?status=pending">Pending</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $status_filter == 'confirmed' ? 'active' : ''; ?>" 
                       href="?status=confirmed">Confirmed</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $status_filter == 'completed' ? 'active' : ''; ?>" 
                       href="?status=completed">Completed</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $status_filter == 'cancelled' ? 'active' : ''; ?>" 
                       href="?status=cancelled">Cancelled</a>
                </li>
            </ul>
        </div>
    </div>

    <!-- Appointments Table -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Dentist</th>
                                <th>Reason</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $query = "SELECT a.*, u.first_name, u.last_name FROM appointments a
                                      JOIN users u ON a.dentist_id = u.user_id
                                      WHERE a.patient_id = '$patient_id'";
                            
                            if (!empty($status_filter)) {
                                $query .= " AND a.status = '$status_filter'";
                            }
                            
                            // Show newest booked appointments first
                            $query .= " ORDER BY a.created_at DESC";
                            
                            $result = $conn->query($query);
                            
                            if ($result && $result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    ?>
                                    <tr>
                                        <td><?php echo formatDate($row['appointment_date']); ?></td>
                                        <td><?php echo formatTime($row['appointment_time']); ?></td>
                                        <td>Dr. <?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['reason_for_visit'] ?? '-'); ?></td>
                                        <td><?php echo getStatusBadge($row['status']); ?></td>
                                        <td>
                                            <a href="view-appointment.php?id=<?php echo $row['appointment_id']; ?>" 
                                               class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                    <?php
                                }
                            } else {
                                echo '<tr><td colspan="6" class="text-center text-muted">No appointments found</td></tr>';
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
