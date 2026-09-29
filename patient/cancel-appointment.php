<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

requireRole('patient');

$patient_id = $_SESSION['user_id'];
$appointment_id = intval($_GET['id'] ?? 0);

if ($appointment_id <= 0) {
    header("Location: appointments.php?error=Invalid appointment");
    exit();
}

// Get appointment details
$result = $conn->query("SELECT a.*, d.user_id as dentist_id FROM appointments a
                       JOIN users d ON a.dentist_id = d.user_id
                       WHERE a.appointment_id = '$appointment_id' AND a.patient_id = '$patient_id'");

if (!$result || $result->num_rows == 0) {
    header("Location: appointments.php?error=Appointment not found");
    exit();
}

$appointment = $result->fetch_assoc();

// Check if appointment can be cancelled
if (!in_array($appointment['status'], ['pending', 'confirmed'])) {
    header("Location: view-appointment.php?id=$appointment_id&error=This appointment cannot be cancelled");
    exit();
}

// Cancel the appointment
$update_query = "UPDATE appointments SET status = 'cancelled' WHERE appointment_id = '$appointment_id'";

if ($conn->query($update_query)) {
    // Notify dentist
    createNotification($appointment['dentist_id'], 'appointment', 'Appointment Cancelled', 
        'An appointment has been cancelled by the patient', $appointment_id);
    
    // Log audit
    logAudit('Patient cancelled appointment', 'appointments', $appointment_id);
    
    header("Location: view-appointment.php?id=$appointment_id&message=" . urlencode('Appointment cancelled successfully'));
} else {
    header("Location: view-appointment.php?id=$appointment_id&error=Error cancelling appointment");
}
exit();
?>
