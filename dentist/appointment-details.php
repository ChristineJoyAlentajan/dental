<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

requireRole('dentist');
$page_title = 'Appointment Details';
$show_sidebar = true;

$dentist_id = $_SESSION['user_id'];
$appointment_id = intval($_GET['id'] ?? 0);

// Handle status update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $new_status = sanitize($_POST['status'] ?? '');
    $notes = sanitize($_POST['notes'] ?? '');
    
    if (!empty($new_status)) {
        $update_query = "UPDATE appointments SET status = '$new_status', notes = '$notes' 
                        WHERE appointment_id = '$appointment_id' AND dentist_id = '$dentist_id'";
        
        if ($conn->query($update_query)) {
            // Create notification for patient
            $apt_result = $conn->query("SELECT patient_id FROM appointments WHERE appointment_id = '$appointment_id'");
            if ($apt_result && $apt_result->num_rows > 0) {
                $apt = $apt_result->fetch_assoc();
                $status_text = ucfirst($new_status);
                createNotification($apt['patient_id'], 'appointment', 'Appointment Status Update', 
                    'Your appointment status has been updated to: ' . $status_text, $appointment_id);
            }
            
            logAudit('Updated appointment status', 'appointments', $appointment_id);
            $message = 'Appointment status updated successfully!';
        } else {
            $error = 'Error updating appointment status';
        }
    }
}

include '../includes/header.php';

// Get appointment details
$result = $conn->query("SELECT a.*, p.first_name as patient_first, p.last_name as patient_last, 
                       p.email as patient_email, p.phone as patient_phone
                       FROM appointments a
                       JOIN users p ON a.patient_id = p.user_id
                       WHERE a.appointment_id = '$appointment_id' AND a.dentist_id = '$dentist_id'");

if (!$result || $result->num_rows == 0) {
    echo '<div class="container mt-5"><div class="alert alert-danger">Appointment not found</div></div>';
    include '../includes/footer.php';
    exit;
}

$appointment = $result->fetch_assoc();
$message = $_SESSION['message'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['message'], $_SESSION['error']);
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-10 offset-md-1">
            <a href="appointments.php" class="btn btn-primary mb-3">
                <i class="fas fa-arrow-left"></i>
            </a>

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

            <div class="row">
                <!-- Appointment Info -->
                <div class="col-md-8">
                    <div class="card mb-3">
                        <div class="card-header">
                            <i class="fas fa-calendar"></i> Appointment Information
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <strong>Appointment ID:</strong> #<?php echo $appointment['appointment_id']; ?>
                                </div>
                                <div class="col-md-6">
                                    <strong>Status:</strong> <?php echo getStatusBadge($appointment['status']); ?>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <strong>Date:</strong> <?php echo formatDate($appointment['appointment_date']); ?>
                                </div>
                                <div class="col-md-6">
                                    <strong>Time:</strong> <?php echo formatTime($appointment['appointment_time']); ?>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <strong>Reason for Visit:</strong><br>
                                    <?php echo htmlspecialchars($appointment['reason_for_visit'] ?? '-'); ?>
                                </div>
                            </div>

                            <?php if (!empty($appointment['notes'])): ?>
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <strong>Notes:</strong><br>
                                    <?php echo htmlspecialchars($appointment['notes']); ?>
                                </div>
                            </div>
                            <?php endif; ?>

                            <div class="row">
                                <div class="col-md-12">
                                    <strong>Created:</strong> <?php echo formatDateTime($appointment['created_at']); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Patient Info -->
                <div class="col-md-4">
                    <div class="card mb-3">
                        <div class="card-header">
                            <i class="fas fa-user"></i> Patient Information
                        </div>
                        <div class="card-body">
                            <p class="mb-2">
                                <strong><?php echo htmlspecialchars($appointment['patient_first'] . ' ' . $appointment['patient_last']); ?></strong>
                            </p>
                            <p class="mb-2">
                                <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($appointment['patient_email']); ?>
                            </p>
                            <p class="mb-2">
                                <i class="fas fa-phone"></i> <?php echo htmlspecialchars($appointment['patient_phone'] ?? '-'); ?>
                            </p>
                            <hr>
                            <a href="patient-details.php?id=<?php echo $appointment['patient_id']; ?>" 
                               class="btn btn-sm btn-info w-100">
                                <i class="fas fa-user-circle"></i> View Full Profile
                            </a>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <?php if ($appointment['status'] == 'pending'): ?>
                    <div class="card">
                        <div class="card-header">
                            <i class="fas fa-tasks"></i> Quick Actions
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <input type="hidden" name="appointment_id" value="<?php echo $appointment['appointment_id']; ?>">
                                <div class="d-grid gap-2">
                                    <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#confirmModal">
                                        <i class="fas fa-check"></i> Confirm
                                    </button>
                                    <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#cancelModal">
                                        <i class="fas fa-times"></i> Cancel
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <?php elseif ($appointment['status'] == 'confirmed'): ?>
                    <div class="card">
                        <div class="card-header">
                            <i class="fas fa-tasks"></i> Quick Actions
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <input type="hidden" name="appointment_id" value="<?php echo $appointment['appointment_id']; ?>">
                                <div class="d-grid gap-2">
                                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#completeModal">
                                        <i class="fas fa-check-double"></i> Mark Complete
                                    </button>
                                    <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#cancelModal">
                                        <i class="fas fa-times"></i> Cancel
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Confirm Modal -->
<div class="modal fade" id="confirmModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="update_status">
                <input type="hidden" name="status" value="confirmed">
                <input type="hidden" name="appointment_id" value="<?php echo $appointment['appointment_id']; ?>">
                
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Appointment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                
                <div class="modal-body">
                    <p>Are you sure you want to confirm this appointment?</p>
                    <div class="mb-3">
                        <label for="notes" class="form-label">Add Notes (Optional)</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Confirm Appointment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Complete Modal -->
<div class="modal fade" id="completeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="update_status">
                <input type="hidden" name="status" value="completed">
                <input type="hidden" name="appointment_id" value="<?php echo $appointment['appointment_id']; ?>">
                
                <div class="modal-header">
                    <h5 class="modal-title">Mark Appointment as Complete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                
                <div class="modal-body">
                    <p>Mark this appointment as completed?</p>
                    <div class="mb-3">
                        <label for="notes" class="form-label">Add Notes (Optional)</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Mark Complete</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Cancel Modal -->
<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="update_status">
                <input type="hidden" name="status" value="cancelled">
                <input type="hidden" name="appointment_id" value="<?php echo $appointment['appointment_id']; ?>">
                
                <div class="modal-header">
                    <h5 class="modal-title">Cancel Appointment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                
                <div class="modal-body">
                    <p>Are you sure you want to cancel this appointment?</p>
                    <div class="mb-3">
                        <label for="notes" class="form-label">Cancellation Reason</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Please provide a reason for cancellation"></textarea>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Don't Cancel</button>
                    <button type="submit" class="btn btn-danger">Cancel Appointment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
