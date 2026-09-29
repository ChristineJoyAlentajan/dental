<?php

// Sanitize user input
function sanitize($data) {
    global $conn;
    return $conn->real_escape_string(trim($data));
}

// Validate email
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Hash password
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT);
}

// Verify password
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Get current user information
function getCurrentUser() {
    global $conn;
    if (!isLoggedIn()) {
        return null;
    }
    
    $user_id = $_SESSION['user_id'];
    $result = $conn->query("SELECT * FROM users WHERE user_id = '$user_id'");
    
    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    return null;
}

// Check user role
function checkRole($required_role) {
    if (!isLoggedIn()) {
        return false;
    }
    return $_SESSION['role'] == $required_role;
}

// Check multiple roles
function checkRoles($roles_array) {
    if (!isLoggedIn()) {
        return false;
    }
    return in_array($_SESSION['role'], $roles_array);
}

// Redirect to login if not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: " . BASE_URL . "login.php");
        exit();
    }
}

// Redirect if not specific role
function requireRole($role) {
    requireLogin();
    if ($_SESSION['role'] != $role) {
        header("Location: " . BASE_URL . "unauthorized.php");
        exit();
    }
}

// Get user by ID
function getUserById($user_id) {
    global $conn;
    $result = $conn->query("SELECT * FROM users WHERE user_id = '$user_id'");
    return $result ? $result->fetch_assoc() : null;
}

// Create notification
function createNotification($user_id, $type, $title, $message, $related_id = null) {
    global $conn;
    $user_id = intval($user_id);
    $type = sanitize($type);
    $title = sanitize($title);
    $message = sanitize($message);
    $related_id = intval($related_id);
    
    $query = "INSERT INTO notifications (user_id, type, title, message, related_id) 
              VALUES ('$user_id', '$type', '$title', '$message', '$related_id')";
    
    return $conn->query($query);
}

// Get unread notifications count
function getUnreadNotificationsCount($user_id) {
    global $conn;
    $user_id = intval($user_id);
    $result = $conn->query("SELECT COUNT(*) as count FROM notifications 
                            WHERE user_id = '$user_id' AND is_read = 0");
    $row = $result->fetch_assoc();
    return $row['count'];
}

// Get notifications for user
function getNotifications($user_id, $limit = 10) {
    global $conn;
    $user_id = intval($user_id);
    $limit = intval($limit);
    $result = $conn->query("SELECT * FROM notifications WHERE user_id = '$user_id' 
                            ORDER BY created_at DESC LIMIT $limit");
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

// Mark notification as read
function markNotificationAsRead($notification_id) {
    global $conn;
    $notification_id = intval($notification_id);
    return $conn->query("UPDATE notifications SET is_read = 1 WHERE notification_id = '$notification_id'");
}

// Format date
function formatDate($date) {
    if (empty($date)) {
        return '';
    }
    return date('d-m-Y', strtotime($date));
}

// Format datetime
function formatDateTime($datetime) {
    if (empty($datetime)) {
        return '';
    }
    return date('d-m-Y H:i:s', strtotime($datetime));
}

// Format time
function formatTime($time) {
    if (empty($time)) {
        return '';
    }
    return date('h:i A', strtotime($time));
}

// Get appointment status badge
function getStatusBadge($status) {
    $colors = [
        'pending' => 'warning',
        'confirmed' => 'info',
        'completed' => 'success',
        'cancelled' => 'danger',
        'no-show' => 'secondary'
    ];
    
    $color = $colors[$status] ?? 'secondary';
    $label = ucfirst($status);
    
    return "<span class='badge bg-$color'>$label</span>";
}

// Get payment status badge
function getPaymentStatusBadge($status) {
    $colors = [
        'paid' => 'success',
        'unpaid' => 'danger',
        'partial' => 'warning'
    ];
    
    $color = $colors[$status] ?? 'secondary';
    $label = ucfirst($status);
    
    return "<span class='badge bg-$color'>$label</span>";
}

// Log audit trail
function logAudit($action, $table_name = null, $record_id = null, $old_value = null, $new_value = null) {
    global $conn;
    
    if (!isLoggedIn()) {
        return false;
    }
    
    $user_id = $_SESSION['user_id'];
    $user_id = intval($user_id);
    $action = sanitize($action);
    $table_name = $table_name ? sanitize($table_name) : null;
    $record_id = $record_id ? intval($record_id) : null;
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    
    $query = "INSERT INTO audit_logs (user_id, action, table_name, record_id, old_value, new_value, ip_address, user_agent) 
              VALUES ('$user_id', '$action', " . ($table_name ? "'$table_name'" : "NULL") . ", " . 
              ($record_id ? "$record_id" : "NULL") . ", " . 
              ($old_value ? "'$old_value'" : "NULL") . ", " . 
              ($new_value ? "'$new_value'" : "NULL") . ", '$ip_address', '$user_agent')";
    
    return $conn->query($query);
}

// Get available appointment slots
function getAvailableSlots($dentist_id, $date) {
    global $conn;
    
    $dentist_id = intval($dentist_id);
    $date = sanitize($date);
    $day_of_week = date('l', strtotime($date));
    
    // Get dentist schedule for the day
    $schedule_result = $conn->query("SELECT * FROM dentist_schedule 
                                     WHERE dentist_id = '$dentist_id' 
                                     AND day_of_week = '$day_of_week' 
                                     AND is_available = 1");
    
    if (!$schedule_result || $schedule_result->num_rows == 0) {
        return [];
    }
    
    $schedule = $schedule_result->fetch_assoc();
    $start_time = strtotime($schedule['start_time']);
    $end_time = strtotime($schedule['end_time']);
    $slot_duration = 30; // 30 minutes per appointment
    
    $slots = [];
    
    // Generate time slots
    for ($time = $start_time; $time < $end_time; $time += ($slot_duration * 60)) {
        $slot_time = date('H:i', $time);
        $slot_end_time = date('H:i', $time + (30 * 60));
        
        // Check if slot is already booked
        $booked = $conn->query("SELECT COUNT(*) as count FROM appointments 
                               WHERE dentist_id = '$dentist_id' 
                               AND appointment_date = '$date' 
                               AND appointment_time = '$slot_time'
                               AND status != 'cancelled'");
        
        $booked_row = $booked->fetch_assoc();
        
        if ($booked_row['count'] == 0) {
            $slots[] = [
                'time' => $slot_time,
                'end_time' => $slot_end_time,
                'available' => true
            ];
        }
    }
    
    return $slots;
}

// Send email notification (placeholder)
function sendEmailNotification($email, $subject, $message) {
    // Note: Configure valid SMTP settings for production
    // For now, returns true
    return true;
}

// Get appointment count for dentist on a date
function getAppointmentCount($dentist_id, $date) {
    global $conn;
    $dentist_id = intval($dentist_id);
    $date = sanitize($date);
    
    $result = $conn->query("SELECT COUNT(*) as count FROM appointments 
                            WHERE dentist_id = '$dentist_id' 
                            AND appointment_date = '$date'
                            AND status IN ('pending', 'confirmed', 'completed')");
    
    $row = $result->fetch_assoc();
    return $row['count'];
}

// Get upcoming appointments for user
function getUpcomingAppointments($user_id, $limit = 5) {
    global $conn;
    $user_id = intval($user_id);
    $limit = intval($limit);
    
    $query = "SELECT a.*, u.first_name, u.last_name 
              FROM appointments a
              JOIN users u ON (a.dentist_id = u.user_id OR a.patient_id = u.user_id)
              WHERE (a.patient_id = '$user_id' OR a.dentist_id = '$user_id')
              AND a.appointment_date >= CURDATE()
              AND a.status IN ('pending', 'confirmed')
              ORDER BY a.appointment_date ASC, a.appointment_time ASC
              LIMIT $limit";
    
    $result = $conn->query($query);
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

// Get recent treatments
function getRecentTreatments($patient_id, $limit = 5) {
    global $conn;
    $patient_id = intval($patient_id);
    $limit = intval($limit);
    
    $query = "SELECT t.*, u.first_name, u.last_name 
              FROM treatment_history t
              JOIN users u ON t.dentist_id = u.user_id
              WHERE t.patient_id = '$patient_id'
              ORDER BY t.created_at DESC
              LIMIT $limit";
    
    $result = $conn->query($query);
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

?>
