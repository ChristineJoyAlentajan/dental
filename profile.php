<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

requireLogin();
$page_title = 'Profile';
$show_sidebar = true;

$current_user = getCurrentUser();
$message = '';
$error = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'update_profile') {
    $first_name = sanitize($_POST['first_name'] ?? '');
    $last_name = sanitize($_POST['last_name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $address = sanitize($_POST['address'] ?? '');
    $date_of_birth = sanitize($_POST['date_of_birth'] ?? '');
    $gender = sanitize($_POST['gender'] ?? '');
    
    if (empty($first_name) || empty($last_name) || empty($email)) {
        $error = 'Please fill required fields';
    } else {
        $user_id = $_SESSION['user_id'];
        $update_query = "UPDATE users SET first_name = '$first_name', last_name = '$last_name', 
                        email = '$email', phone = '$phone', address = '$address', 
                        date_of_birth = '$date_of_birth', gender = '$gender'
                        WHERE user_id = '$user_id'";
        
        if ($conn->query($update_query)) {
            $message = 'Profile updated successfully!';
            $_SESSION['name'] = $first_name . ' ' . $last_name;
            $current_user = getUserById($user_id);
            logAudit('Updated profile', 'users', $user_id);
        } else {
            $error = 'Error updating profile';
        }
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'change_password') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = 'Please fill all password fields';
    } elseif ($new_password !== $confirm_password) {
        $error = 'New passwords do not match';
    } elseif (strlen($new_password) < 6) {
        $error = 'Password must be at least 6 characters';
    } elseif (!verifyPassword($current_password, $current_user['password'])) {
        $error = 'Current password is incorrect';
    } else {
        $user_id = $_SESSION['user_id'];
        $hashed_password = hashPassword($new_password);
        $update_query = "UPDATE users SET password = '$hashed_password' WHERE user_id = '$user_id'";
        
        if ($conn->query($update_query)) {
            $message = 'Password changed successfully!';
            logAudit('Changed password', 'users', $user_id);
        } else {
            $error = 'Error changing password';
        }
    }
}

include 'includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <h2 class="mb-4"><i class="fas fa-user"></i> My Profile</h2>
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
        <div class="col-md-8">
            <!-- Profile Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-user-edit"></i> Personal Information
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="action" value="update_profile">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="first_name" class="form-label">First Name *</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" 
                                       value="<?php echo htmlspecialchars($current_user['first_name']); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="last_name" class="form-label">Last Name *</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" 
                                       value="<?php echo htmlspecialchars($current_user['last_name']); ?>" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email *</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($current_user['email']); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Phone</label>
                                <input type="tel" class="form-control" id="phone" name="phone" 
                                       value="<?php echo htmlspecialchars($current_user['phone'] ?? ''); ?>">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="address" class="form-label">Address</label>
                            <textarea class="form-control" id="address" name="address" rows="3"><?php echo htmlspecialchars($current_user['address'] ?? ''); ?></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="date_of_birth" class="form-label">Date of Birth</label>
                                <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" 
                                       value="<?php echo $current_user['date_of_birth'] ?? ''; ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="gender" class="form-label">Gender</label>
                                <select class="form-control" id="gender" name="gender">
                                    <option value="">Select Gender</option>
                                    <option value="Male" <?php echo $current_user['gender'] == 'Male' ? 'selected' : ''; ?>>Male</option>
                                    <option value="Female" <?php echo $current_user['gender'] == 'Female' ? 'selected' : ''; ?>>Female</option>
                                    <option value="Other" <?php echo $current_user['gender'] == 'Other' ? 'selected' : ''; ?>>Other</option>
                                </select>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Change Password -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-lock"></i> Change Password
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="action" value="change_password">
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Current Password *</label>
                            <input type="password" class="form-control" id="current_password" name="current_password" required>
                        </div>

                        <div class="mb-3">
                            <label for="new_password" class="form-label">New Password *</label>
                            <input type="password" class="form-control" id="new_password" name="new_password" required>
                        </div>

                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm New Password *</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-key"></i> Change Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- User Information Card -->
            <div class="card">
                <div class="card-header text-center">
                    <i class="fas fa-info-circle"></i> Account Information
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <i class="fas fa-user-circle fa-5x text-primary"></i>
                    </div>
                    <dl class="row">
                        <dt class="col-sm-5">Username:</dt>
                        <dd class="col-sm-7"><?php echo htmlspecialchars($current_user['username']); ?></dd>

                        <dt class="col-sm-5">Role:</dt>
                        <dd class="col-sm-7"><span class="badge bg-info"><?php echo ucfirst($current_user['role']); ?></span></dd>

                        <dt class="col-sm-5">Registered:</dt>
                        <dd class="col-sm-7"><?php echo formatDate($current_user['registration_date']); ?></dd>

                        <dt class="col-sm-5">Last Login:</dt>
                        <dd class="col-sm-7"><?php echo $current_user['last_login'] ? formatDateTime($current_user['last_login']) : 'Never'; ?></dd>

                        <dt class="col-sm-5">Status:</dt>
                        <dd class="col-sm-7">
                            <?php echo $current_user['is_active'] ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>'; ?>
                        </dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
