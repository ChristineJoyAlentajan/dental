<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

requireRole('admin');
$page_title = 'Manage Users';
$show_sidebar = true;

$role_filter = $_GET['role'] ?? '';
$message = '';
$error = '';

// Handle user status update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'toggle_status') {
    $user_id = intval($_POST['user_id'] ?? 0);
    $new_status = intval($_POST['is_active'] ?? 0);
    
    if ($user_id > 0) {
        $update_query = "UPDATE users SET is_active = '$new_status' WHERE user_id = '$user_id'";
        
        if ($conn->query($update_query)) {
            $message = 'User status updated!';
            logAudit('Updated user status', 'users', $user_id);
        } else {
            $error = 'Error updating user status';
        }
    }
}

include '../includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <h2 class="mb-4"><i class="fas fa-users"></i> Manage Users</h2>
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

    <!-- Filter Tabs -->
    <div class="row mb-4">
        <div class="col-md-12">
            <ul class="nav nav-tabs">
                <li class="nav-item">
                    <a class="nav-link <?php echo empty($role_filter) ? 'active' : ''; ?>" href="users.php">All Users</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $role_filter == 'patient' ? 'active' : ''; ?>" href="?role=patient">Patients</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $role_filter == 'dentist' ? 'active' : ''; ?>" href="?role=dentist">Dentists</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $role_filter == 'staff' ? 'active' : ''; ?>" href="?role=staff">Staff</a>
                </li>
            </ul>
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
                                <th>Role</th>
                                <th>Registered</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $query = "SELECT * FROM users WHERE role != 'admin'";
                            
                            if (!empty($role_filter)) {
                                $query .= " AND role = '$role_filter'";
                            }
                            
                            $query .= " ORDER BY first_name ASC";
                            
                            $result = $conn->query($query);
                            
                            if ($result && $result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    $status_badge = $row['is_active'] ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>';
                                    $new_status = $row['is_active'] ? 0 : 1;
                                    ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                                        <td><?php echo htmlspecialchars($row['phone'] ?? '-'); ?></td>
                                        <td><span class="badge bg-info"><?php echo ucfirst($row['role']); ?></span></td>
                                        <td><?php echo formatDate($row['registration_date']); ?></td>
                                        <td><?php echo $status_badge; ?></td>
                                        <td>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="action" value="toggle_status">
                                                <input type="hidden" name="user_id" value="<?php echo $row['user_id']; ?>">
                                                <input type="hidden" name="is_active" value="<?php echo $new_status; ?>">
                                                <button type="submit" class="btn btn-sm btn-warning">
                                                    <i class="fas fa-toggle-on"></i> Toggle
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php
                                }
                            } else {
                                echo '<tr><td colspan="7" class="text-center text-muted">No users found</td></tr>';
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
