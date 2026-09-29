<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

requireRole('admin');
$page_title = 'Manage Appointments';
$show_sidebar = true;

$status_filter = $_GET['status'] ?? '';
$date_filter = $_GET['date'] ?? '';
$dentist_filter = $_GET['dentist'] ?? '';
$message = '';
$error = '';

// Handle appointment status update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'update_status') {
    $appointment_id = intval($_POST['appointment_id'] ?? 0);
    $new_status = sanitize($_POST['status'] ?? '');
    
    if (!empty($appointment_id) && !empty($new_status)) {
        $update_query = "UPDATE appointments SET status = '$new_status' WHERE appointment_id = '$appointment_id'";
        
        if ($conn->query($update_query)) {
            // Notify both patient and dentist
            $apt_result = $conn->query("SELECT patient_id, dentist_id FROM appointments WHERE appointment_id = '$appointment_id'");
            if ($apt_result && $apt_result->num_rows > 0) {
                $apt = $apt_result->fetch_assoc();
                createNotification($apt['patient_id'], 'appointment', 'Appointment Status Updated', 
                    'Your appointment status has been updated to: ' . ucfirst($new_status), $appointment_id);
                createNotification($apt['dentist_id'], 'appointment', 'Appointment Status Updated', 
                    'Appointment status has been changed to: ' . ucfirst($new_status), $appointment_id);
            }
            
            logAudit('Updated appointment status', 'appointments', $appointment_id);
            $message = 'Appointment status updated successfully!';
        } else {
            $error = 'Error updating appointment status';
        }
    }
}

include '../includes/header.php';
?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-12">
            <h2 class="mb-4"><i class="fas fa-calendar"></i> Manage All Appointments</h2>
        </div>
    </div>

    <?php if (!empty($message)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label for="status_filter" class="form-label">Status</label>
                            <select class="form-control" id="status_filter" name="status">
                                <option value="">All Status</option>
                                <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="confirmed" <?php echo $status_filter == 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                <option value="completed" <?php echo $status_filter == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                <option value="cancelled" <?php echo $status_filter == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                <option value="no-show" <?php echo $status_filter == 'no-show' ? 'selected' : ''; ?>>No Show</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label for="date_filter" class="form-label">Date</label>
                            <input type="date" class="form-control" id="date_filter" name="date" 
                                   value="<?php echo htmlspecialchars($date_filter); ?>">
                        </div>

                        <div class="col-md-3">
                            <label for="dentist_filter" class="form-label">Dentist</label>
                            <select class="form-control" id="dentist_filter" name="dentist">
                                <option value="">All Dentists</option>
                                <?php
                                $dentists = $conn->query("SELECT user_id, first_name, last_name FROM users WHERE role = 'dentist' AND is_active = 1");
                                if ($dentists && $dentists->num_rows > 0) {
                                    while ($dentist = $dentists->fetch_assoc()) {
                                        $selected = $dentist_filter == $dentist['user_id'] ? 'selected' : '';
                                        echo '<option value="' . $dentist['user_id'] . '" ' . $selected . '>';
                                        echo 'Dr. ' . htmlspecialchars($dentist['first_name'] . ' ' . $dentist['last_name']);
                                        echo '</option>';
                                    }
                                }
                                ?>
                            </select>
                        </div>

                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search"></i> Filter
                            </button>
                            <a href="appointments.php" class="btn btn-secondary w-100 ms-2">
                                <i class="fas fa-redo"></i> Reset
                            </a>
                        </div>
                    </form>
                </div>
            </div>
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
                                <th>#</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Patient</th>
                                <th>Dentist</th>
                                <th>Reason</th>
                                <th>Status</th>
                                <th>Booked</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $query = "SELECT a.*, 
                                      p.first_name as patient_first, p.last_name as patient_last,
                                      d.first_name as dentist_first, d.last_name as dentist_last
                                      FROM appointments a
                                      JOIN users p ON a.patient_id = p.user_id
                                      JOIN users d ON a.dentist_id = d.user_id
                                      WHERE 1=1";
                            
                            if (!empty($status_filter)) {
                                $query .= " AND a.status = '$status_filter'";
                            }
                            if (!empty($date_filter)) {
                                $query .= " AND a.appointment_date = '$date_filter'";
                            }
                            if (!empty($dentist_filter)) {
                                $dentist_filter = intval($dentist_filter);
                                $query .= " AND a.dentist_id = '$dentist_filter'";
                            }
                            
                            // Use FIFO (first-booked) ordering by creation time
                            $query .= " ORDER BY a.created_at ASC";
                            
                            $result = $conn->query($query);
                            $serial = 1;
                            
                            if ($result && $result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    ?>
                                    <tr>
                                        <td><?php echo $serial++; ?></td>
                                        <td><?php echo formatDate($row['appointment_date']); ?></td>
                                        <td><?php echo formatTime($row['appointment_time']); ?></td>
                                        <td><?php echo htmlspecialchars($row['patient_first'] . ' ' . $row['patient_last']); ?></td>
                                        <td>Dr. <?php echo htmlspecialchars($row['dentist_first'] . ' ' . $row['dentist_last']); ?></td>
                                        <td><?php echo htmlspecialchars($row['reason_for_visit'] ?? '-'); ?></td>
                                        <td><?php echo getStatusBadge($row['status']); ?></td>
                                        <td><?php echo formatDate($row['created_at']); ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-info" data-bs-toggle="modal" 
                                                    data-bs-target="#viewModal"
                                                    onclick="viewAppointment(<?php echo htmlspecialchars(json_encode($row)); ?>)">
                                                <i class="fas fa-eye"></i> View
                                            </button>
                                            <?php if (in_array($row['status'], ['pending', 'confirmed'])): ?>
                                            <button class="btn btn-sm btn-warning" data-bs-toggle="modal" 
                                                    data-bs-target="#statusModal"
                                                    onclick="updateStatus(<?php echo $row['appointment_id']; ?>, '<?php echo $row['status']; ?>')">
                                                <i class="fas fa-edit"></i> Update
                                            </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php
                                }
                            } else {
                                echo '<tr><td colspan="9" class="text-center text-muted">No appointments found</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- View Appointment Modal -->
<div class="modal fade" id="viewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-calendar"></i> Appointment Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Appointment ID:</strong><br>
                        <span id="apt_id">#000</span>
                    </div>
                    <div class="col-md-6">
                        <strong>Status:</strong><br>
                        <span id="apt_status"></span>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Date:</strong><br>
                        <span id="apt_date"></span>
                    </div>
                    <div class="col-md-6">
                        <strong>Time:</strong><br>
                        <span id="apt_time"></span>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Patient:</strong><br>
                        <span id="apt_patient"></span>
                    </div>
                    <div class="col-md-6">
                        <strong>Dentist:</strong><br>
                        <span id="apt_dentist"></span>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-12">
                        <strong>Reason:</strong><br>
                        <span id="apt_reason"></span>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <strong>Notes:</strong><br>
                        <span id="apt_notes"></span>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Update Status Modal -->
<div class="modal fade" id="statusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="update_status">
                <input type="hidden" name="appointment_id" id="status_apt_id">
                
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-edit"></i> Update Appointment Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="status" class="form-label">New Status *</label>
                        <select class="form-control" id="status" name="status" required>
                            <option value="">-- Select Status --</option>
                            <option value="pending">Pending</option>
                            <option value="confirmed">Confirmed</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                            <option value="no-show">No Show</option>
                        </select>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Status</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function viewAppointment(appointment) {
    document.getElementById('apt_id').textContent = '#' + appointment.appointment_id;
    document.getElementById('apt_status').innerHTML = appointment.status;
    document.getElementById('apt_date').textContent = appointment.appointment_date;
    document.getElementById('apt_time').textContent = appointment.appointment_time;
    document.getElementById('apt_patient').textContent = appointment.patient_first + ' ' + appointment.patient_last;
    document.getElementById('apt_dentist').textContent = 'Dr. ' + appointment.dentist_first + ' ' + appointment.dentist_last;
    document.getElementById('apt_reason').textContent = appointment.reason_for_visit || '-';
    document.getElementById('apt_notes').textContent = appointment.notes || '-';
}

function updateStatus(appointmentId, currentStatus) {
    document.getElementById('status_apt_id').value = appointmentId;
    document.getElementById('status').value = '';
}
</script>

<?php include '../includes/footer.php'; ?>
