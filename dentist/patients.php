<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

requireRole('dentist');
$page_title = 'My Patients';
$show_sidebar = true;

$dentist_id = $_SESSION['user_id'];

include '../includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <h2 class="mb-4"><i class="fas fa-users"></i> My Patients</h2>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Patient Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Appointments</th>
                                <th>Last Visit</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $query = "SELECT DISTINCT u.user_id, u.first_name, u.last_name, u.email, u.phone
                                      FROM users u
                                      JOIN appointments a ON u.user_id = a.patient_id
                                      WHERE a.dentist_id = '$dentist_id'
                                      ORDER BY u.first_name ASC";
                            
                            $result = $conn->query($query);
                            
                            if ($result && $result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    $patient_id = $row['user_id'];
                                    
                                    // Get appointment count
                                    $apt_count_result = $conn->query("SELECT COUNT(*) as count FROM appointments 
                                                                      WHERE patient_id = '$patient_id' AND dentist_id = '$dentist_id'");
                                    $apt_count = $apt_count_result->fetch_assoc()['count'];
                                    
                                    // Get last visit
                                    $last_visit_result = $conn->query("SELECT appointment_date FROM appointments 
                                                                       WHERE patient_id = '$patient_id' AND dentist_id = '$dentist_id' 
                                                                       AND status IN ('completed', 'confirmed')
                                                                       ORDER BY appointment_date DESC LIMIT 1");
                                    $last_visit = $last_visit_result && $last_visit_result->num_rows > 0 
                                                  ? formatDate($last_visit_result->fetch_assoc()['appointment_date'])
                                                  : '-';
                                    ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                                        <td><?php echo htmlspecialchars($row['phone'] ?? '-'); ?></td>
                                        <td><?php echo $apt_count; ?></td>
                                        <td><?php echo $last_visit; ?></td>
                                        <td>
                                            <a href="patient-details.php?id=<?php echo $row['user_id']; ?>" 
                                               class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php
                                }
                            } else {
                                echo '<tr><td colspan="6" class="text-center text-muted">No patients found</td></tr>';
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
