<?php
// Placeholder files for view pages
require_once '../includes/config.php';
require_once '../includes/functions.php';

requireRole('patient');
$page_title = 'View Treatment';
$show_sidebar = true;

include '../includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <a href="treatment-history.php" class="btn btn-secondary mb-3">
                <i class="fas fa-arrow-left"></i> Back
            </a>
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-file-medical"></i> Treatment Details
                </div>
                <div class="card-body">
                    <?php
                    $treatment_id = intval($_GET['id'] ?? 0);
                    $result = $conn->query("SELECT t.*, u.first_name, u.last_name FROM treatment_history t
                                           JOIN users u ON t.dentist_id = u.user_id
                                           WHERE t.treatment_id = '$treatment_id' AND t.patient_id = '{$_SESSION['user_id']}'");
                    
                    if ($result && $result->num_rows > 0) {
                        $treatment = $result->fetch_assoc();
                        ?>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>Treatment Type:</strong> <?php echo htmlspecialchars($treatment['treatment_type']); ?>
                            </div>
                            <div class="col-md-6">
                                <strong>Date:</strong> <?php echo formatDate($treatment['created_at']); ?>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>Dentist:</strong> Dr. <?php echo htmlspecialchars($treatment['first_name'] . ' ' . $treatment['last_name']); ?>
                            </div>
                            <div class="col-md-6">
                                <strong>Cost:</strong> Rs. <?php echo number_format($treatment['procedure_cost'] ?? 0, 2); ?>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>Payment Status:</strong> <?php echo getPaymentStatusBadge($treatment['payment_status']); ?>
                            </div>
                            <div class="col-md-6">
                                <strong>Tooth Number:</strong> <?php echo htmlspecialchars($treatment['tooth_number'] ?? '-'); ?>
                            </div>
                        </div>
                        <?php if (!empty($treatment['diagnosis'])): ?>
                        <div class="mb-3">
                            <strong>Diagnosis:</strong>
                            <p><?php echo htmlspecialchars($treatment['diagnosis']); ?></p>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($treatment['treatment_description'])): ?>
                        <div class="mb-3">
                            <strong>Treatment Description:</strong>
                            <p><?php echo htmlspecialchars($treatment['treatment_description']); ?></p>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($treatment['medication_prescribed'])): ?>
                        <div class="mb-3">
                            <strong>Medication Prescribed:</strong>
                            <p><?php echo htmlspecialchars($treatment['medication_prescribed']); ?></p>
                        </div>
                        <?php endif; ?>
                        <?php if ($treatment['follow_up_required']): ?>
                        <div class="alert alert-info">
                            <strong>Follow-up Required:</strong> <?php echo formatDate($treatment['follow_up_date']); ?>
                        </div>
                        <?php endif; ?>
                        <?php
                    } else {
                        echo '<div class="alert alert-warning">Treatment record not found</div>';
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
