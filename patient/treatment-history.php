<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

requireRole('patient');
$page_title = 'Treatment History';
$show_sidebar = true;

$patient_id = $_SESSION['user_id'];

include '../includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <h2 class="mb-4"><i class="fas fa-file-medical"></i> Treatment History</h2>
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
                                <th>Treatment Type</th>
                                <th>Dentist</th>
                                <th>Description</th>
                                <th>Cost</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $query = "SELECT t.*, u.first_name, u.last_name FROM treatment_history t
                                      JOIN users u ON t.dentist_id = u.user_id
                                      WHERE t.patient_id = '$patient_id'
                                      ORDER BY t.created_at DESC";
                            
                            $result = $conn->query($query);
                            
                            if ($result && $result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    ?>
                                    <tr>
                                        <td><?php echo formatDate($row['created_at']); ?></td>
                                        <td><?php echo htmlspecialchars($row['treatment_type']); ?></td>
                                        <td>Dr. <?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                                        <td><?php echo htmlspecialchars(substr($row['treatment_description'] ?? '', 0, 50)); ?></td>
                                        <td>Rs. <?php echo number_format($row['procedure_cost'] ?? 0, 2); ?></td>
                                        <td><?php echo getPaymentStatusBadge($row['payment_status']); ?></td>
                                        <td>
                                            <a href="view-treatment.php?id=<?php echo $row['treatment_id']; ?>" 
                                               class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                    <?php
                                }
                            } else {
                                echo '<tr><td colspan="7" class="text-center text-muted">No treatment records found</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
