<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

requireRole('dentist');
$page_title = 'Treatment Records';
$show_sidebar = true;

$dentist_id = $_SESSION['user_id'];
$message = '';
$error = '';

// Handle treatment record submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add_treatment') {
    $appointment_id = intval($_POST['appointment_id'] ?? 0);
    $patient_id = intval($_POST['patient_id'] ?? 0);
    $treatment_type = sanitize($_POST['treatment_type'] ?? '');
    $treatment_description = sanitize($_POST['treatment_description'] ?? '');
    $procedure_cost = floatval($_POST['procedure_cost'] ?? 0);
    $medication_prescribed = sanitize($_POST['medication_prescribed'] ?? '');
    $follow_up_date = sanitize($_POST['follow_up_date'] ?? '') ?: null;
    $tooth_number = sanitize($_POST['tooth_number'] ?? '');
    $diagnosis = sanitize($_POST['diagnosis'] ?? '');
    
    if (empty($treatment_type) || empty($patient_id)) {
        $error = 'Please fill all required fields';
    } else {
        $follow_up_required = !empty($follow_up_date) ? 1 : 0;
        $follow_up_date_sql = !empty($follow_up_date) ? "'$follow_up_date'" : "NULL";
        
        $insert_query = "INSERT INTO treatment_history 
                        (appointment_id, patient_id, dentist_id, treatment_type, treatment_description, 
                         procedure_cost, medication_prescribed, follow_up_required, follow_up_date, 
                         tooth_number, diagnosis, payment_status)
                        VALUES ('$appointment_id', '$patient_id', '$dentist_id', '$treatment_type', 
                               '$treatment_description', '$procedure_cost', '$medication_prescribed', 
                               '$follow_up_required', $follow_up_date_sql, '$tooth_number', '$diagnosis', 'unpaid')";
        
        if ($conn->query($insert_query)) {
            $message = 'Treatment record added successfully!';
            
            // Create notification
            createNotification($patient_id, 'treatment', 'New Treatment Record', 
                'A new treatment record has been added to your profile', $conn->insert_id);
            
            logAudit('Added treatment record', 'treatment_history', $conn->insert_id);
        } else {
            $error = 'Error adding treatment record';
        }
    }
}

include '../includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <h2 class="mb-4"><i class="fas fa-file-medical"></i> Treatment Records</h2>
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

    <div class="row mb-4">
        <div class="col-md-12">
            <button class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#addTreatmentModal">
                <i class="fas fa-plus"></i> Add Treatment Record
            </button>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Patient Name</th>
                                <th>Treatment Type</th>
                                <th>Tooth #</th>
                                <th>Cost</th>
                                <th>Payment Status</th>
                                <th>Follow-up</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Show treatment records in FIFO (oldest first) order
                            $query = "SELECT t.*, u.first_name, u.last_name FROM treatment_history t
                                      JOIN users u ON t.patient_id = u.user_id
                                      WHERE t.dentist_id = '$dentist_id'
                                      ORDER BY t.created_at ASC";
                            
                            $result = $conn->query($query);
                            
                            if ($result && $result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    ?>
                                    <tr>
                                        <td><?php echo formatDate($row['created_at']); ?></td>
                                        <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['treatment_type']); ?></td>
                                        <td><?php echo htmlspecialchars($row['tooth_number'] ?? '-'); ?></td>
                                        <td>Rs. <?php echo number_format($row['procedure_cost'] ?? 0, 2); ?></td>
                                        <td><?php echo getPaymentStatusBadge($row['payment_status']); ?></td>
                                        <td>
                                            <?php
                                            if ($row['follow_up_required']) {
                                                echo '<span class="badge bg-warning">' . formatDate($row['follow_up_date']) . '</span>';
                                            } else {
                                                echo '<span class="badge bg-secondary">-</span>';
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <a href="view-treatment.php?id=<?php echo $row['treatment_id']; ?>" 
                                               class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php
                                }
                            } else {
                                echo '<tr><td colspan="8" class="text-center text-muted">No treatment records found</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Treatment Modal -->
<div class="modal fade" id="addTreatmentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="add_treatment">
                
                <div class="modal-header">
                    <h5 class="modal-title">Add Treatment Record</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="patient_id" class="form-label">Select Patient *</label>
                            <select class="form-control" id="patient_id" name="patient_id" required>
                                <option value="">-- Select Patient --</option>
                                <?php
                                $patients = $conn->query("SELECT DISTINCT u.user_id, u.first_name, u.last_name
                                                         FROM users u
                                                         JOIN appointments a ON u.user_id = a.patient_id
                                                         WHERE a.dentist_id = '$dentist_id' AND a.status IN ('completed', 'confirmed')
                                                         ORDER BY u.first_name ASC");
                                
                                if ($patients && $patients->num_rows > 0) {
                                    while ($patient = $patients->fetch_assoc()) {
                                        echo '<option value="' . $patient['user_id'] . '">' . 
                                             htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']) . 
                                             '</option>';
                                    }
                                }
                                ?>
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="appointment_id" class="form-label">Appointment</label>
                            <input type="number" class="form-control" id="appointment_id" name="appointment_id">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="treatment_type" class="form-label">Treatment Type *</label>
                            <input type="text" class="form-control" id="treatment_type" name="treatment_type" 
                                   placeholder="e.g., Root Canal, Cleaning" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="tooth_number" class="form-label">Tooth Number</label>
                            <input type="text" class="form-control" id="tooth_number" name="tooth_number" 
                                   placeholder="e.g., 16, 17">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="treatment_description" class="form-label">Treatment Description</label>
                        <textarea class="form-control" id="treatment_description" name="treatment_description" 
                                  rows="3"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="diagnosis" class="form-label">Diagnosis</label>
                        <textarea class="form-control" id="diagnosis" name="diagnosis" rows="2"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="procedure_cost" class="form-label">Cost</label>
                            <input type="number" class="form-control" id="procedure_cost" name="procedure_cost" 
                                   step="0.01" min="0">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="follow_up_date" class="form-label">Follow-up Date</label>
                            <input type="date" class="form-control" id="follow_up_date" name="follow_up_date">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="medication_prescribed" class="form-label">Medication Prescribed</label>
                        <textarea class="form-control" id="medication_prescribed" name="medication_prescribed" 
                                  rows="2"></textarea>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Treatment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
