<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

requireRole('patient');
$page_title = 'Appointment Details';
$show_sidebar = true;

$patient_id = $_SESSION['user_id'];
$appointment_id = intval($_GET['id'] ?? 0);

// Handle cancel appointment
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'cancel') {
    $cancel_reason = sanitize($_POST['cancel_reason'] ?? '');
    
    // Check if appointment can be cancelled
    $check_result = $conn->query("SELECT appointment_date, appointment_time, status FROM appointments 
                                 WHERE appointment_id = '$appointment_id' AND patient_id = '$patient_id'");
    
    if ($check_result && $check_result->num_rows > 0) {
        $apt_data = $check_result->fetch_assoc();
        
        // Only allow cancellation of pending and confirmed appointments
        if (in_array($apt_data['status'], ['pending', 'confirmed'])) {
            $update_query = "UPDATE appointments SET status = 'cancelled', notes = '$cancel_reason' 
                            WHERE appointment_id = '$appointment_id'";
            
            if ($conn->query($update_query)) {
                // Get dentist info to notify them
                $dentist_result = $conn->query("SELECT dentist_id FROM appointments WHERE appointment_id = '$appointment_id'");
                if ($dentist_result && $dentist_result->num_rows > 0) {
                    $dentist_info = $dentist_result->fetch_assoc();
                    createNotification($dentist_info['dentist_id'], 'appointment', 'Appointment Cancelled', 
                        'An appointment has been cancelled by the patient', $appointment_id);
                }
                
                logAudit('Patient cancelled appointment', 'appointments', $appointment_id);
                $message = 'Appointment cancelled successfully!';
            } else {
                $error = 'Error cancelling appointment';
            }
        } else {
            $error = 'This appointment cannot be cancelled.';
        }
    } else {
        $error = 'Appointment not found';
    }
}

include '../includes/header.php';

// Get appointment details
$result = $conn->query("SELECT a.*, d.first_name as dentist_first, d.last_name as dentist_last, 
                       d.email as dentist_email, d.phone as dentist_phone, d.specialization
                       FROM appointments a
                       JOIN users d ON a.dentist_id = d.user_id
                       WHERE a.appointment_id = '$appointment_id' AND a.patient_id = '$patient_id'");

if (!$result || $result->num_rows == 0) {
    echo '<div class="container mt-5"><div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> Appointment not found</div></div>';
    include '../includes/footer.php';
    exit;
}

$appointment = $result->fetch_assoc();
$message = $_GET['message'] ?? '';
$error = $_GET['error'] ?? '';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-10 offset-md-1">
            <a href="appointments.php" class="btn btn-secondary mb-3">
                <i class="fas fa-arrow-left"></i> Back to Appointments
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
                <!-- Appointment Details -->
                <div class="col-md-8">
                    <div class="card mb-3">
                        <div class="card-header">
                            <i class="fas fa-calendar"></i> Appointment Details
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <strong>Appointment ID:</strong><br>
                                    #<?php echo $appointment['appointment_id']; ?>
                                </div>
                                <div class="col-md-6">
                                    <strong>Status:</strong><br>
                                    <?php echo getStatusBadge($appointment['status']); ?>
                                </div>
                            </div>

                            <hr>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <strong><i class="fas fa-calendar-alt"></i> Appointment Date:</strong><br>
                                    <?php echo formatDate($appointment['appointment_date']); ?>
                                </div>
                                <div class="col-md-6">
                                    <strong><i class="fas fa-clock"></i> Appointment Time:</strong><br>
                                    <?php echo formatTime($appointment['appointment_time']); ?>
                                    <?php if (!empty($appointment['appointment_end_time'])): ?>
                                        to <?php echo formatTime($appointment['appointment_end_time']); ?>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <strong><i class="fas fa-stethoscope"></i> Reason for Visit:</strong><br>
                                    <p class="text-muted"><?php echo htmlspecialchars($appointment['reason_for_visit'] ?? 'Not specified'); ?></p>
                                </div>
                            </div>

                            <?php if (!empty($appointment['notes'])): ?>
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <strong><i class="fas fa-note-sticky"></i> Notes:</strong><br>
                                    <p class="text-muted"><?php echo htmlspecialchars($appointment['notes']); ?></p>
                                </div>
                            </div>
                            <?php endif; ?>

                            <hr>

                            <div class="row">
                                <div class="col-md-6">
                                    <strong>Booked On:</strong><br>
                                    <small class="text-muted"><?php echo formatDateTime($appointment['created_at']); ?></small>
                                </div>
                                <div class="col-md-6">
                                    <strong>Last Updated:</strong><br>
                                    <small class="text-muted"><?php echo formatDateTime($appointment['updated_at']); ?></small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Dentist Information -->
                    <div class="card">
                        <div class="card-header">
                            <i class="fas fa-user-md"></i> Dentist Information
                        </div>
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-2 text-center">
                                    <i class="fas fa-user-circle fa-3x text-primary"></i>
                                </div>
                                <div class="col-md-10">
                                    <h5>Dr. <?php echo htmlspecialchars($appointment['dentist_first'] . ' ' . $appointment['dentist_last']); ?></h5>
                                    <?php if (!empty($appointment['specialization'])): ?>
                                        <p class="text-muted mb-2">
                                            <strong>Specialization:</strong> <?php echo htmlspecialchars($appointment['specialization']); ?>
                                        </p>
                                    <?php endif; ?>
                                    <p class="mb-1">
                                        <i class="fas fa-envelope"></i> 
                                        <a href="mailto:<?php echo htmlspecialchars($appointment['dentist_email']); ?>">
                                            <?php echo htmlspecialchars($appointment['dentist_email']); ?>
                                        </a>
                                    </p>
                                    <p class="mb-0">
                                        <i class="fas fa-phone"></i> 
                                        <a href="tel:<?php echo htmlspecialchars($appointment['dentist_phone']); ?>">
                                            <?php echo htmlspecialchars($appointment['dentist_phone']); ?>
                                        </a>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar - Status & Actions -->
                <div class="col-md-4">
                    <!-- Status Card -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <i class="fas fa-info-circle"></i> Status Information
                        </div>
                        <div class="card-body">
                            <?php
                            $status = $appointment['status'];
                            $status_text = '';
                            $status_color = '';
                            $status_icon = '';

                            switch ($status) {
                                case 'pending':
                                    $status_text = 'Pending Confirmation';
                                    $status_color = 'warning';
                                    $status_icon = 'hourglass-half';
                                    break;
                                case 'confirmed':
                                    $status_text = 'Confirmed';
                                    $status_color = 'success';
                                    $status_icon = 'check-circle';
                                    break;
                                case 'completed':
                                    $status_text = 'Completed';
                                    $status_color = 'info';
                                    $status_icon = 'check-double';
                                    break;
                                case 'cancelled':
                                    $status_text = 'Cancelled';
                                    $status_color = 'danger';
                                    $status_icon = 'ban';
                                    break;
                                case 'no-show':
                                    $status_text = 'No Show';
                                    $status_color = 'secondary';
                                    $status_icon = 'exclamation-triangle';
                                    break;
                            }
                            ?>
                            <div class="text-center">
                                <i class="fas fa-<?php echo $status_icon; ?> fa-3x text-<?php echo $status_color; ?> mb-2"></i>
                                <h5><?php echo $status_text; ?></h5>
                            </div>

                            <?php if ($status == 'pending'): ?>
                                <div class="alert alert-warning">
                                    <small><i class="fas fa-bell"></i> Waiting for dentist to confirm your appointment</small>
                                </div>
                            <?php elseif ($status == 'confirmed'): ?>
                                <div class="alert alert-success">
                                    <small><i class="fas fa-check"></i> Your appointment has been confirmed!</small>
                                </div>
                            <?php elseif ($status == 'completed'): ?>
                                <div class="alert alert-info">
                                    <small><i class="fas fa-check-double"></i> This appointment has been completed</small>
                                </div>
                            <?php elseif ($status == 'cancelled'): ?>
                                <div class="alert alert-danger">
                                    <small><i class="fas fa-ban"></i> This appointment was cancelled</small>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <?php if (in_array($appointment['status'], ['pending', 'confirmed'])): ?>
                    <div class="card">
                        <div class="card-header">
                            <i class="fas fa-tasks"></i> Actions
                        </div>
                        <div class="card-body">
                            <button class="btn btn-danger btn-sm w-100" data-bs-toggle="modal" data-bs-target="#cancelModal">
                                <i class="fas fa-times"></i> Cancel Appointment
                            </button>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Reminder -->
                    <div class="card mt-3">
                        <div class="card-header">
                            <i class="fas fa-lightbulb"></i> Important Information
                        </div>
                        <div class="card-body">
                            <small class="text-muted">
                                <ul class="list-unstyled">
                                    <li><i class="fas fa-check-circle"></i> Please arrive 10 minutes early</li>
                                    <li><i class="fas fa-check-circle"></i> Bring your insurance card if applicable</li>
                                    <li><i class="fas fa-check-circle"></i> For cancellations, notify us at least 24 hours before</li>
                                </ul>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Cancel Appointment Modal -->
<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="cancel">
                
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="fas fa-times"></i> Cancel Appointment</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                
                <div class="modal-body">
                    <p>Are you sure you want to cancel this appointment?</p>
                    <div class="alert alert-warning">
                        <small><i class="fas fa-info-circle"></i> Please note: Cancelling may affect your ability to book future appointments. Kindly provide a reason for cancellation.</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="cancel_reason" class="form-label">Reason for Cancellation (Optional)</label>
                        <textarea class="form-control" id="cancel_reason" name="cancel_reason" rows="3" 
                                  placeholder="Help us improve by telling us why you're cancelling..."></textarea>
                    </div>

                    <div class="mb-0">
                        <strong>Appointment Details:</strong><br>
                        <small class="text-muted">
                            Dr. <?php echo htmlspecialchars($appointment['dentist_first'] . ' ' . $appointment['dentist_last']); ?><br>
                            <?php echo formatDate($appointment['appointment_date']); ?> at <?php echo formatTime($appointment['appointment_time']); ?>
                        </small>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Keep Appointment</button>
                    <button type="submit" class="btn btn-danger">Yes, Cancel Appointment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
