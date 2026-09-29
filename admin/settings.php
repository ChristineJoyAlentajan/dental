<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

requireRole('admin');
$page_title = 'System Settings';
$show_sidebar = true;

$message = '';
$error = '';

// Handle settings update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'update_settings') {
    // These would normally be saved to a settings table
    // For now, we'll just show a success message
    $site_name = sanitize($_POST['site_name'] ?? '');
    $site_email = sanitize($_POST['site_email'] ?? '');
    $clinic_phone = sanitize($_POST['clinic_phone'] ?? '');
    $clinic_address = sanitize($_POST['clinic_address'] ?? '');
    $appointment_slot_duration = intval($_POST['appointment_slot_duration'] ?? 30);
    $max_advance_booking_days = intval($_POST['max_advance_booking_days'] ?? 30);
    
    if (empty($site_name) || empty($site_email)) {
        $error = 'Please fill all required fields';
    } else {
        // In a real application, save these to database
        $message = 'Settings updated successfully!';
        logAudit('Updated system settings');
    }
}

include '../includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <h2 class="mb-4"><i class="fas fa-cog"></i> System Settings</h2>
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

    <div class="row">
        <div class="col-md-3">
            <div class="nav flex-column nav-pills">
                <a href="#general" class="nav-link active" data-bs-toggle="pill">
                    <i class="fas fa-cog"></i> General Settings
                </a>
                <a href="#clinic" class="nav-link" data-bs-toggle="pill">
                    <i class="fas fa-hospital"></i> Clinic Information
                </a>
                <a href="#appointments" class="nav-link" data-bs-toggle="pill">
                    <i class="fas fa-calendar"></i> Appointment Settings
                </a>
                <a href="#notifications" class="nav-link" data-bs-toggle="pill">
                    <i class="fas fa-bell"></i> Notification Settings
                </a>
                <a href="#security" class="nav-link" data-bs-toggle="pill">
                    <i class="fas fa-lock"></i> Security Settings
                </a>
            </div>
        </div>

        <div class="col-md-9">
            <div class="tab-content">
                <!-- General Settings -->
                <div class="tab-pane fade show active" id="general">
                    <div class="card">
                        <div class="card-header">
                            <i class="fas fa-cog"></i> General Settings
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <input type="hidden" name="action" value="update_settings">
                                
                                <div class="mb-3">
                                    <label for="site_name" class="form-label">Site Name *</label>
                                    <input type="text" class="form-control" id="site_name" name="site_name" 
                                           value="<?php echo SITE_NAME; ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label for="site_email" class="form-label">Site Email *</label>
                                    <input type="email" class="form-control" id="site_email" name="site_email" 
                                           value="admin@dental.com" required>
                                </div>

                                <div class="mb-3">
                                    <label for="site_timezone" class="form-label">Timezone</label>
                                    <select class="form-control" id="site_timezone" name="site_timezone">
                                        <option value="Asia/Kolkata" selected>Asia/Kolkata (India)</option>
                                        <option value="Asia/Dubai">Asia/Dubai (UAE)</option>
                                        <option value="America/New_York">America/New_York (EST)</option>
                                        <option value="Europe/London">Europe/London (GMT)</option>
                                        <option value="Australia/Sydney">Australia/Sydney</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="site_language" class="form-label">Default Language</label>
                                    <select class="form-control" id="site_language" name="site_language">
                                        <option value="en" selected>English</option>
                                        <option value="hi">Hindi</option>
                                        <option value="es">Spanish</option>
                                        <option value="fr">French</option>
                                    </select>
                                </div>

                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Save Changes
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Clinic Information -->
                <div class="tab-pane fade" id="clinic">
                    <div class="card">
                        <div class="card-header">
                            <i class="fas fa-hospital"></i> Clinic Information
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <input type="hidden" name="action" value="update_settings">
                                
                                <div class="mb-3">
                                    <label for="clinic_name" class="form-label">Clinic Name *</label>
                                    <input type="text" class="form-control" id="clinic_name" name="clinic_name" 
                                           value="Big Smile Dental Clinic" required>
                                </div>

                                <div class="mb-3">
                                    <label for="clinic_phone" class="form-label">Phone Number *</label>
                                    <input type="tel" class="form-control" id="clinic_phone" name="clinic_phone" 
                                           value="+1-800-000-0000" required>
                                </div>

                                <div class="mb-3">
                                    <label for="clinic_email" class="form-label">Email Address *</label>
                                    <input type="email" class="form-control" id="clinic_email" name="clinic_email" 
                                           value="clinic@dental.com" required>
                                </div>

                                <div class="mb-3">
                                    <label for="clinic_address" class="form-label">Address *</label>
                                    <textarea class="form-control" id="clinic_address" name="clinic_address" 
                                              rows="3" required>123 Dental Street, City, State 12345</textarea>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="clinic_city" class="form-label">City</label>
                                        <input type="text" class="form-control" id="clinic_city" name="clinic_city" 
                                               value="New York">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="clinic_state" class="form-label">State</label>
                                        <input type="text" class="form-control" id="clinic_state" name="clinic_state" 
                                               value="NY">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="clinic_website" class="form-label">Website</label>
                                    <input type="url" class="form-control" id="clinic_website" name="clinic_website" 
                                           value="https://www.dentalclinic.com">
                                </div>

                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Save Changes
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Appointment Settings -->
                <div class="tab-pane fade" id="appointments">
                    <div class="card">
                        <div class="card-header">
                            <i class="fas fa-calendar"></i> Appointment Settings
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <input type="hidden" name="action" value="update_settings">
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="appointment_slot_duration" class="form-label">Appointment Slot Duration (minutes) *</label>
                                        <input type="number" class="form-control" id="appointment_slot_duration" 
                                               name="appointment_slot_duration" value="30" min="15" step="15" required>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="max_advance_booking_days" class="form-label">Max Advance Booking (days) *</label>
                                        <input type="number" class="form-control" id="max_advance_booking_days" 
                                               name="max_advance_booking_days" value="30" min="1" required>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="buffer_time" class="form-label">Buffer Time Between Appointments (minutes)</label>
                                        <input type="number" class="form-control" id="buffer_time" name="buffer_time" 
                                               value="10" min="0" step="5">
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="cancellation_hours" class="form-label">Cancellation Notice (hours)</label>
                                        <input type="number" class="form-control" id="cancellation_hours" name="cancellation_hours" 
                                               value="24" min="1">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="require_confirmation" 
                                               name="require_confirmation" checked>
                                        <label class="form-check-label" for="require_confirmation">
                                            Require Dentist Confirmation for All Appointments
                                        </label>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="enable_reminders" 
                                               name="enable_reminders" checked>
                                        <label class="form-check-label" for="enable_reminders">
                                            Enable Appointment Reminders
                                        </label>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="reminder_hours" class="form-label">Send Reminders (hours before appointment)</label>
                                    <input type="number" class="form-control" id="reminder_hours" name="reminder_hours" 
                                           value="24" min="1">
                                </div>

                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Save Changes
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Notification Settings -->
                <div class="tab-pane fade" id="notifications">
                    <div class="card">
                        <div class="card-header">
                            <i class="fas fa-bell"></i> Notification Settings
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <input type="hidden" name="action" value="update_settings">
                                
                                <div class="mb-3">
                                    <h6>Email Notifications</h6>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="email_new_appointment" checked>
                                        <label class="form-check-label" for="email_new_appointment">
                                            New Appointment Booking
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="email_appointment_confirmed" checked>
                                        <label class="form-check-label" for="email_appointment_confirmed">
                                            Appointment Confirmed
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="email_appointment_reminder" checked>
                                        <label class="form-check-label" for="email_appointment_reminder">
                                            Appointment Reminder
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="email_appointment_cancelled" checked>
                                        <label class="form-check-label" for="email_appointment_cancelled">
                                            Appointment Cancelled
                                        </label>
                                    </div>
                                </div>

                                <hr>

                                <div class="mb-3">
                                    <h6>SMS Notifications</h6>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="sms_appointment_confirmed">
                                        <label class="form-check-label" for="sms_appointment_confirmed">
                                            Send SMS for Appointment Confirmation
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="sms_appointment_reminder">
                                        <label class="form-check-label" for="sms_appointment_reminder">
                                            Send SMS Reminders
                                        </label>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Save Changes
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Security Settings -->
                <div class="tab-pane fade" id="security">
                    <div class="card">
                        <div class="card-header">
                            <i class="fas fa-lock"></i> Security Settings
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <input type="hidden" name="action" value="update_settings">
                                
                                <div class="mb-3">
                                    <label for="session_timeout" class="form-label">Session Timeout (minutes)</label>
                                    <input type="number" class="form-control" id="session_timeout" name="session_timeout" 
                                           value="30" min="5" required>
                                </div>

                                <div class="mb-3">
                                    <label for="password_min_length" class="form-label">Minimum Password Length</label>
                                    <input type="number" class="form-control" id="password_min_length" name="password_min_length" 
                                           value="8" min="6" required>
                                </div>

                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="enforce_strong_password" checked>
                                        <label class="form-check-label" for="enforce_strong_password">
                                            Enforce Strong Passwords (uppercase, lowercase, numbers, special characters)
                                        </label>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="enable_two_factor" checked>
                                        <label class="form-check-label" for="enable_two_factor">
                                            Enable Two-Factor Authentication (Optional)
                                        </label>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="password_expiry" class="form-label">Password Expiry (days, 0 to disable)</label>
                                    <input type="number" class="form-control" id="password_expiry" name="password_expiry" 
                                           value="90" min="0">
                                </div>

                                <div class="mb-3">
                                    <label for="max_login_attempts" class="form-label">Max Failed Login Attempts</label>
                                    <input type="number" class="form-control" id="max_login_attempts" name="max_login_attempts" 
                                           value="5" min="1">
                                </div>

                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Save Changes
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
