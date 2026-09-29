<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

requireRole(array('staff', 'admin'));
$page_title = 'Manage Payments';
$show_sidebar = true;

$message = '';
$error = '';

// Handle payment status update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'update_payment') {
    $payment_id = intval($_POST['payment_id'] ?? 0);
    $payment_status = sanitize($_POST['payment_status'] ?? '');
    
    if (!empty($payment_id) && !empty($payment_status)) {
        $update_query = "UPDATE payments SET payment_status = '$payment_status', payment_date = NOW() WHERE payment_id = '$payment_id'";
        
        if ($conn->query($update_query)) {
            $message = 'Payment status updated successfully!';
            logAudit('Updated payment status', 'payments', $payment_id);
        } else {
            $error = 'Error updating payment status';
        }
    }
}

include '../includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <h2 class="mb-4"><i class="fas fa-credit-card"></i> Payment Management</h2>
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
                                <th>Date</th>
                                <th>Patient</th>
                                <th>Amount</th>
                                <th>Method</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $result = $conn->query("SELECT p.*, u.first_name, u.last_name FROM payments p
                                                   JOIN users u ON p.patient_id = u.user_id
                                                   ORDER BY p.created_at DESC");
                            
                            if ($result && $result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    ?>
                                    <tr>
                                        <td><?php echo formatDate($row['created_at']); ?></td>
                                        <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                                        <td>Rs. <?php echo number_format($row['amount'], 2); ?></td>
                                        <td><?php echo ucfirst($row['payment_method']); ?></td>
                                        <td><?php echo getPaymentStatusBadge($row['payment_status']); ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-warning" data-bs-toggle="modal" 
                                                    data-bs-target="#updatePaymentModal"
                                                    onclick="setPaymentId(<?php echo $row['payment_id']; ?>, '<?php echo $row['payment_status']; ?>')">
                                                <i class="fas fa-edit"></i> Update
                                            </button>
                                        </td>
                                    </tr>
                                    <?php
                                }
                            } else {
                                echo '<tr><td colspan="6" class="text-center text-muted">No payments found</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Update Payment Modal -->
<div class="modal fade" id="updatePaymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="update_payment">
                <input type="hidden" name="payment_id" id="payment_id">
                
                <div class="modal-header">
                    <h5 class="modal-title">Update Payment Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="payment_status" class="form-label">Payment Status *</label>
                        <select class="form-control" id="payment_status" name="payment_status" required>
                            <option value="pending">Pending</option>
                            <option value="completed">Completed</option>
                            <option value="failed">Failed</option>
                        </select>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function setPaymentId(paymentId, currentStatus) {
    document.getElementById('payment_id').value = paymentId;
    document.getElementById('payment_status').value = currentStatus;
}
</script>

<?php include '../includes/footer.php'; ?>
