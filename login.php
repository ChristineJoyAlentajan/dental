<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$active_tab = 'login';
$error = null;
$success = null;

function validateRegistrationInput(
    $first_name,
    $last_name,
    $email,
    $username,
    $password,
    $confirm_password,
    $role
) {
    $first_name = trim((string) $first_name);
    $last_name = trim((string) $last_name);
    $email = trim((string) $email);
    $username = trim((string) $username);
    $password = (string) $password;
    $confirm_password = (string) $confirm_password;
    $role = trim((string) $role);

    if ($first_name === '' || $last_name === '' || $email === '' || $username === '' || $password === '' || $role === '') {
        return 'Please fill all required fields';
    }

    if (strlen($password) < 6) {
        return 'Password must be at least 6 characters long';
    }

    if ($password !== $confirm_password) {
        return 'Passwords do not match';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return 'Invalid email address';
    }

    if (!in_array($role, ['patient', 'dentist'], true)) {
        return 'Invalid role selected';
    }

    if (strlen($username) < 3 || strlen($username) > 30) {
        return 'Username must be between 3 and 30 characters';
    }

    if (preg_match('/[^a-zA-Z0-9_.-]/', $username)) {
        return 'Username can only contain letters, numbers, dots, underscores, and dashes';
    }

    return null;
}

function getUserByUsername($conn, $username) {
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? AND is_active = 1 LIMIT 1");
    if (!$stmt) {
        return null;
    }

    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result ? $result->fetch_assoc() : null;
    $stmt->close();

    return $user;
}

// Handle login
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'login') {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (trim($username) === '' || trim((string) $password) === '') {
        $error = 'Please enter username and password';
    } else {
        $user = getUserByUsername($conn, $username);

        if ($user && verifyPassword($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['name'] = $user['first_name'] . ' ' . $user['last_name'];

            $update_stmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE user_id = ?");
            if ($update_stmt) {
                $update_stmt->bind_param('i', $user['user_id']);
                $update_stmt->execute();
                $update_stmt->close();
            }

            logAudit('User login', 'users', $user['user_id']);

            if ($user['role'] == 'patient') {
                header("Location: patient/index.php");
            } elseif ($user['role'] == 'dentist') {
                header("Location: dentist/index.php");
            } elseif ($user['role'] == 'staff') {
                header("Location: staff/index.php");
            } elseif ($user['role'] == 'admin') {
                header("Location: admin/index.php");
            }
            exit();
        } else {
            $error = 'Username or password is incorrect';
        }
    }
}

// Handle registration
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'register') {
    $active_tab = 'register';

    $first_name = sanitize($_POST['first_name'] ?? '');
    $last_name = sanitize($_POST['last_name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $role = sanitize($_POST['role'] ?? '');
    $dob = sanitize($_POST['dob'] ?? '');

    $validation_error = validateRegistrationInput(
        $first_name,
        $last_name,
        $email,
        $username,
        $password,
        $confirm_password,
        $role
    );

    if ($validation_error) {
        $error = $validation_error;
    } else {
        $username_check = $conn->prepare("SELECT user_id FROM users WHERE username = ? LIMIT 1");
        $email_check = $conn->prepare("SELECT user_id FROM users WHERE email = ? LIMIT 1");

        if (!$username_check || !$email_check) {
            $error = 'Error creating account. Please try again.';
        } else {
            $username_check->bind_param('s', $username);
            $username_check->execute();
            $username_result = $username_check->get_result();

            if ($username_result && $username_result->num_rows > 0) {
                $error = 'Username already exists';
            } else {
                $email_check->bind_param('s', $email);
                $email_check->execute();
                $email_result = $email_check->get_result();

                if ($email_result && $email_result->num_rows > 0) {
                    $error = 'Email already registered';
                } else {
                    $gender = isset($_POST['gender']) ? sanitize($_POST['gender']) : 'Not Specified';
                    $address = sanitize($_POST['address'] ?? '');
                    $hashed_password = hashPassword($password);

                    $insert_query = $conn->prepare(
                        "INSERT INTO users 
                        (first_name, last_name, email, phone, username, password, role, gender, date_of_birth, address, is_active, created_at) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, NOW())"
                    );

                    if (!$insert_query) {
                        $error = 'Error creating account. Please try again.';
                    } else {
                        $insert_query->bind_param(
                            'ssssssssss',
                            $first_name,
                            $last_name,
                            $email,
                            $phone,
                            $username,
                            $hashed_password,
                            $role,
                            $gender,
                            $dob,
                            $address
                        );

                        if ($insert_query->execute()) {
                            $user_id = $conn->insert_id;
                            logAudit('New user registered', 'users', $user_id);
                            $success = 'Account created successfully! You can now login.';
                        } else {
                            $error = 'Error creating account. Please try again.';
                        }

                        $insert_query->close();
                    }
                }
            }

            $username_check->close();
            $email_check->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #00695C 0%, #00897B 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .login-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.2);
            max-width: 500px;
            width: 100%;
            padding: 40px;
        }
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .login-header i {
            font-size: 40px;
            color: #00695C;
            margin-bottom: 10px;
        }
        .login-header h2 {
            color: #333;
            margin-bottom: 5px;
        }
        .login-header p {
            color: #666;
            font-size: 14px;
        }
        .form-control {
            border-radius: 5px;
            border: 1px solid #ddd;
            padding: 10px 15px;
            margin-bottom: 15px;
        }
        .form-control:focus {
            border-color: #00695C;
            box-shadow: 0 0 0 0.2rem rgba(0, 105, 92, 0.25);
        }
        .btn-login {
            background: linear-gradient(135deg, #00695C 0%, #00897B 100%);
            border: none;
            color: white;
            padding: 10px;
            border-radius: 5px;
            width: 100%;
            font-weight: 600;
            margin-top: 10px;
        }
        .btn-login:hover {
            color: white;
            transform: translateY(-2px);
        }
        .demo-credentials {
            background: #f8f9fa;
            border-left: 4px solid #00695C;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
            font-size: 12px;
        }
        .alert {
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .nav-tabs {
            border-bottom: 2px solid #ddd;
        }
        .nav-tabs .nav-link {
            color: #666;
            border: none;
            border-bottom: 3px solid transparent;
            font-weight: 500;
            cursor: pointer;
        }
        .nav-tabs .nav-link:hover {
            border-bottom-color: #00695C;
            color: #00695C;
        }
        .nav-tabs .nav-link.active {
            color: #00695C;
            border-bottom-color: #00695C;
            background: none;
        }
        .tab-content {
            padding-top: 20px;
        }
        .form-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
            font-size: 14px;
        }
        .row .col-md-6 {
            padding-right: 7px;
        }
        .row .col-md-6:last-child {
            padding-left: 7px;
            padding-right: 0;
        }
        .mb-3 {
            margin-bottom: 15px;
        }
        .password-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }
        .password-wrapper .form-control {
            padding-right: 45px;
        }
        .btn-toggle-password {
            position: absolute;
            right: 12px;
            background: none;
            border: none;
            color: #00695C;
            cursor: pointer;
            padding: 5px 8px;
            font-size: 16px;
            transition: color 0.3s ease;
        }
        .btn-toggle-password:hover {
            color: #00897B;
        }
        .btn-toggle-password:focus {
            outline: none;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <i class="fas fa-tooth"></i>
            <h2><?php echo SITE_NAME; ?></h2>
            <p>Login to your account</p>
        </div>

        <!-- Tabs Navigation -->
        <ul class="nav nav-tabs mb-3" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link <?php echo $active_tab == 'login' ? 'active' : ''; ?>"
                        id="login-tab" data-bs-toggle="tab" data-bs-target="#login" type="button" role="tab">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?php echo $active_tab == 'register' ? 'active' : ''; ?>"
                        id="register-tab" data-bs-toggle="tab" data-bs-target="#register" type="button" role="tab">
                    <i class="fas fa-user-plus"></i> Create Account
                </button>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content">
            <!-- Login Tab -->
            <div class="tab-pane fade <?php echo $active_tab == 'login' ? 'show active' : ''; ?>" id="login" role="tabpanel">
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo htmlspecialchars($error); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <input type="hidden" name="action" value="login">

                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username"
                               placeholder="Enter username" required autofocus>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <div class="password-wrapper">
                            <input type="password" class="form-control" id="password" name="password"
                                   placeholder="Enter password" required>
                            <button type="button" class="btn-toggle-password" onclick="togglePassword('password')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-login">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </button>
                </form>

                <div class="demo-credentials">
                    <strong><i class="fas fa-info-circle"></i> Demo Credentials:</strong>
                    <ul style="margin-top: 10px; margin-bottom: 0; padding-left: 20px;">
                        <li><strong>Patient:</strong> patient1 / password</li>
                        <li><strong>Dentist 1:</strong> dentist1 / password <em>(Dr. John Smith)</em></li>
                        <li><strong>Dentist 2:</strong> dentist2 / password <em>(Dr. Sarah Johnson)</em></li>
                        <li><strong>Staff:</strong> staff1 / password</li>
                        <li><strong>Admin:</strong> admin / password</li>
                    </ul>
                </div>
            </div>

            <!-- Register Tab -->
            <div class="tab-pane fade <?php echo $active_tab == 'register' ? 'show active' : ''; ?>" id="register" role="tabpanel">
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo htmlspecialchars($error); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (!empty($success)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle"></i>
                        <?php echo htmlspecialchars($success); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <input type="hidden" name="action" value="register">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="first_name" class="form-label">First Name *</label>
                            <input type="text" class="form-control" id="first_name" name="first_name"
                                   placeholder="First name">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="last_name" class="form-label">Last Name *</label>
                            <input type="text" class="form-control" id="last_name" name="last_name"
                                   placeholder="Last name">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address *</label>
                        <input type="email" class="form-control" id="email" name="email"
                               placeholder="your@email.com">
                    </div>

                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone Number</label>
                        <input type="tel" class="form-control" id="phone" name="phone"
                               placeholder="+1-800-000-0000">
                    </div>

                    <div class="mb-3">
                        <label for="dob" class="form-label">Date of Birth</label>
                        <input type="date" class="form-control" id="dob" name="dob">
                    </div>

                    <div class="mb-3">
                        <label for="gender" class="form-label">Gender</label>
                        <select class="form-control" id="gender" name="gender">
                            <option value="">Select Gender</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="register_role" class="form-label">I want to register as: *</label>
                        <select class="form-control" id="register_role" name="role">
                            <option value="">-- Select Role --</option>
                            <option value="patient">Patient - Book appointments and track treatments</option>
                            <option value="dentist">Dentist - Manage appointments and patient records</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="register_username" class="form-label">Username *</label>
                        <input type="text" class="form-control" id="register_username" name="username"
                               placeholder="Choose a username">
                    </div>

                    <div class="mb-3">
                        <label for="register_password" class="form-label">Password *</label>
                        <div class="password-wrapper">
                            <input type="password" class="form-control" id="register_password" name="password"
                                   placeholder="Min 6 characters">
                            <button type="button" class="btn-toggle-password" onclick="togglePassword('register_password')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm Password *</label>
                        <div class="password-wrapper">
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password"
                                   placeholder="Re-enter password">
                            <button type="button" class="btn-toggle-password" onclick="togglePassword('confirm_password')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-login">
                        <i class="fas fa-user-plus"></i> Create Account
                    </button>
                </form>

                <div class="demo-credentials" style="background: #e7f3ff;">
                    <strong><i class="fas fa-lightbulb"></i> Note:</strong>
                    <p style="margin-top: 10px; margin-bottom: 0;">
                        Register as a <strong>Patient</strong> to book appointments and view your treatment history.<br>
                        Register as a <strong>Dentist</strong> to manage your schedule and patient records.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function togglePassword(fieldId) {
            const passwordField = document.getElementById(fieldId);
            const button = event.target.closest('.btn-toggle-password');
            const icon = button.querySelector('i');

            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordField.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>
