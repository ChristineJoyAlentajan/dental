<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

requireRole(array('staff', 'admin'));
$page_title = 'Manage Patients';
$show_sidebar = true;

include '../includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <h2 class="mb-4"><i class="fas fa-users"></i> Patients</h2>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>DOB</th>
                                <th>Registered</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $result = $conn->query("SELECT * FROM users WHERE role = 'patient' ORDER BY first_name ASC");
                            
                            if ($result && $result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    $status_badge = $row['is_active'] ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>';
                                    ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                                        <td><?php echo htmlspecialchars($row['phone'] ?? '-'); ?></td>
                                        <td><?php echo formatDate($row['date_of_birth'] ?? ''); ?></td>
                                        <td><?php echo formatDate($row['registration_date']); ?></td>
                                        <td><?php echo $status_badge; ?></td>
                                        <td>
                                            <a href="#" class="btn btn-sm btn-info">View</a>
                                        </td>
                                    </tr>
                                    <?php
                                }
                            } else {
                                echo '<tr><td colspan="7" class="text-center text-muted">No patients found</td></tr>';
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
