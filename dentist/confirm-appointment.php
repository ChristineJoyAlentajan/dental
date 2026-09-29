<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

requireRole('dentist');

$dentist_id = $_SESSION['user_id'];
$appointment_id = intval($_GET['id'] ?? 0);

if ($appointment_id <= 0) {
    header("Location: appointments.php?error=Invalid appointment");
    exit();
}

// Get appointment details
$result = $conn->query("SELECT a.*, p.first_name, p.last_name, p.user_id as patient_id 
                       FROM appointments a
                       JOIN users p ON a.patient_id = p.user_id
                       WHERE a.appointment_id = '$appointment_id' AND a.dentist_id = '$dentist_id'");

if (!$result || $result->num_rows == 0) {
    header("Location: appointments.php?error=Appointment not found");
    exit();
}

$appointment = $result->fetch_assoc();

// Update appointment status to confirmed
$update_query = "UPDATE appointments SET status = 'confirmed' WHERE appointment_id = '$appointment_id'";

if ($conn->query($update_query)) {
    // Create notification for patient
    $patient_id = $appointment['patient_id'];
    createNotification($patient_id, 'appointment', 'Appointment Confirmed', 
        'Your appointment on ' . formatDate($appointment['appointment_date']) . ' at ' . formatTime($appointment['appointment_time']) . ' has been confirmed', 
        $appointment_id);
    
    // Log audit
    logAudit('Confirmed appointment', 'appointments', $appointment_id);
    
    header("Location: appointment-details.php?id=$appointment_id&confirmed=1");
} else {
    header("Location: appointment-details.php?id=$appointment_id&error=1");
}
exit();
?>
