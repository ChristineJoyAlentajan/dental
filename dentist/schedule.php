<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

requireRole('dentist');
$page_title = 'Manage Schedule';
$show_sidebar = true;

$dentist_id = $_SESSION['user_id'];
$message = '';
$error = '';

// Get current schedule
$schedule_result = $conn->query("SELECT * FROM dentist_schedule WHERE dentist_id = '$dentist_id' ORDER BY FIELD(day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')");

// Handle schedule update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action == 'update') {
        $schedule_id = intval($_POST['schedule_id'] ?? 0);
        $start_time = sanitize($_POST['start_time'] ?? '');
        $end_time = sanitize($_POST['end_time'] ?? '');
        $max_patients = intval($_POST['max_patients'] ?? 10);
        $is_available = intval($_POST['is_available'] ?? 0);
        
        if (empty($start_time) || empty($end_time)) {
            $error = 'Please fill all required fields';
        } else {
            $update_query = "UPDATE dentist_schedule SET start_time = '$start_time', end_time = '$end_time', 
                            max_patients = '$max_patients', is_available = '$is_available'
                            WHERE schedule_id = '$schedule_id' AND dentist_id = '$dentist_id'";
            
            if ($conn->query($update_query)) {
                $message = 'Schedule updated successfully!';
                logAudit('Updated dentist schedule', 'dentist_schedule', $schedule_id);
            } else {
                $error = 'Error updating schedule';
            }
        }
    }
}

include '../includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <h2 class="mb-4"><i class="fas fa-clock"></i> Manage Your Schedule</h2>
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
        <div class="col-md-12">
            <div class="card">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Day of Week</th>
                                <th>Start Time</th>
                                <th>End Time</th>
                                <th>Max Patients</th>
                                <th>Available</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($schedule_result && $schedule_result->num_rows > 0) {
                                while ($schedule = $schedule_result->fetch_assoc()) {
                                    ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($schedule['day_of_week']); ?></td>
                                        <td><?php echo formatTime($schedule['start_time']); ?></td>
                                        <td><?php echo formatTime($schedule['end_time']); ?></td>
                                        <td><?php echo $schedule['max_patients']; ?></td>
                                        <td>
                                            <?php
                                            if ($schedule['is_available']) {
                                                echo '<span class="badge bg-success">Available</span>';
                                            } else {
                                                echo '<span class="badge bg-danger">Not Available</span>';
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-warning" data-bs-toggle="modal" 
                                                    data-bs-target="#editScheduleModal" 
                                                    onclick="editSchedule(<?php echo htmlspecialchars(json_encode($schedule)); ?>)">
                                                <i class="fas fa-edit"></i> Edit
                                            </button>
                                        </td>
                                    </tr>
                                    <?php
                                }
                            } else {
                                echo '<tr><td colspan="6" class="text-center text-muted">No schedule set</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Schedule Modal -->
<div class="modal fade" id="editScheduleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="schedule_id" id="schedule_id">
                
                <div class="modal-header">
                    <h5 class="modal-title">Edit Schedule</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="day_of_week" class="form-label">Day of Week</label>
                        <input type="text" class="form-control" id="day_of_week" disabled>
                    </div>
                    
                    <div class="mb-3">
                        <label for="start_time" class="form-label">Start Time *</label>
                        <input type="time" class="form-control" id="start_time" name="start_time" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="end_time" class="form-label">End Time *</label>
                        <input type="time" class="form-control" id="end_time" name="end_time" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="max_patients" class="form-label">Max Patients</label>
                        <input type="number" class="form-control" id="max_patients" name="max_patients" min="1">
                    </div>
                    
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="is_available" name="is_available" value="1">
                        <label class="form-check-label" for="is_available">
                            Available on this day
                        </label>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editSchedule(schedule) {
    document.getElementById('schedule_id').value = schedule.schedule_id;
    document.getElementById('day_of_week').value = schedule.day_of_week;
    document.getElementById('start_time').value = schedule.start_time;
    document.getElementById('end_time').value = schedule.end_time;
    document.getElementById('max_patients').value = schedule.max_patients;
    document.getElementById('is_available').checked = schedule.is_available == 1;
}
</script>

<?php include '../includes/footer.php'; ?>
