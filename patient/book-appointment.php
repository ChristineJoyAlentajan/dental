<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

requireRole('patient');
$page_title = 'Book Appointment';
$show_sidebar = true;

$patient_id = $_SESSION['user_id'];
$message = '';
$error = '';

// Get all active dentists
$dentists_result = $conn->query("SELECT user_id, first_name, last_name, specialization FROM users WHERE role = 'dentist' AND is_active = 1");

// Handle appointment booking
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $dentist_id = intval($_POST['dentist_id'] ?? 0);
    $appointment_date = sanitize($_POST['appointment_date'] ?? '');
    $appointment_time = sanitize($_POST['appointment_time'] ?? '');
    $reason_for_visit = sanitize($_POST['reason_for_visit'] ?? '');
    $notes = sanitize($_POST['notes'] ?? '');
    
    // Validation
    if (empty($dentist_id) || empty($appointment_date) || empty($appointment_time) || empty($reason_for_visit)) {
        $error = 'Please fill all required fields';
    } elseif (strtotime($appointment_date) < strtotime(date('Y-m-d'))) {
        $error = 'Appointment date cannot be in the past';
    } else {
        // Prevent multiple pending bookings for the same patient
        $pending_check = $conn->query("SELECT COUNT(*) as count FROM appointments WHERE patient_id = '$patient_id' AND status = 'pending'");
        $pending_row = $pending_check->fetch_assoc();

        if ($pending_row['count'] > 0) {
            $error = 'You already have a pending appointment. Please wait for it to be confirmed before booking another.';
        } else {
            // Check if slot is available
            $check = $conn->query("SELECT COUNT(*) as count FROM appointments WHERE dentist_id = '$dentist_id' AND appointment_date = '$appointment_date' AND appointment_time = '$appointment_time' AND status != 'cancelled'");
            $check_row = $check->fetch_assoc();
            
            if ($check_row['count'] > 0) {
                $error = 'This slot is already booked. Please select another time';
            } else {
                // Insert appointment
                $query = "INSERT INTO appointments (patient_id, dentist_id, appointment_date, appointment_time, reason_for_visit, notes, status) 
                          VALUES ('$patient_id', '$dentist_id', '$appointment_date', '$appointment_time', '$reason_for_visit', '$notes', 'pending')";
                
                if ($conn->query($query)) {
                    $appointment_id = $conn->insert_id;
                    $message = 'Appointment booked successfully! Your appointment is pending confirmation.';
                    
                    // Create notification for dentist
                    $dentist = getUserById($dentist_id);
                    createNotification($dentist_id, 'appointment', 'New Appointment Request', 
                        'You have a new appointment request from a patient', $appointment_id);
                    
                    // Log audit
                    logAudit('Appointment booked', 'appointments', $appointment_id);
                } else {
                    $error = 'Error booking appointment. Please try again.';
                }
            }
        }
    }
}

include '../includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <h2 class="mb-4"><i class="fas fa-calendar-plus"></i> Book an Appointment</h2>

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

            <div class="card">
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-3">
                            <label for="dentist_id" class="form-label">Select Dentist *</label>
                            <select class="form-control" id="dentist_id" name="dentist_id" required>
                                <option value="">-- Choose Dentist --</option>
                                <?php
                                while ($dentist = $dentists_result->fetch_assoc()) {
                                    echo '<option value="' . $dentist['user_id'] . '">';
                                    echo 'Dr. ' . htmlspecialchars($dentist['first_name'] . ' ' . $dentist['last_name']);
                                    if (!empty($dentist['specialization'])) {
                                        echo ' (' . htmlspecialchars($dentist['specialization']) . ')';
                                    }
                                    echo '</option>';
                                }
                                ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="appointment_date" class="form-label">Appointment Date *</label>
                            <input type="date" class="form-control" id="appointment_date" name="appointment_date" 
                                   min="<?php echo date('Y-m-d'); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="appointment_time" class="form-label">Appointment Time *</label>
                            <input type="time" class="form-control" id="appointment_time" name="appointment_time" required>
                        </div>

                        <div class="mb-3">
                            <label for="reason_for_visit" class="form-label">Reason for Visit *</label>
                            <input type="text" class="form-control" id="reason_for_visit" name="reason_for_visit" 
                                   placeholder="e.g., Check-up, Cleaning, Root canal" required>
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label">Additional Notes</label>
                            <textarea class="form-control" id="notes" name="notes" rows="4" 
                                      placeholder="Any additional information you'd like to share"></textarea>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-check"></i> Book Appointment
                            </button>
                            <a href="appointments.php" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
