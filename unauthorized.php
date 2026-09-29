<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

requireLogin();
$page_title = 'Unauthorized';
$show_sidebar = true;

include 'includes/header.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6 text-center">
            <div class="error-template">
                <h1 style="font-size: 72px; color: #dc3545;">
                    <i class="fas fa-lock"></i>
                </h1>
                <h2>Access Denied</h2>
                <div class="error-details mb-4">
                    You do not have permission to access this page. Your user role does not have the required privileges.
                </div>
                <div class="error-actions">
                    <a href="<?php echo BASE_URL; ?>" class="btn btn-primary">
                        <i class="fas fa-home"></i> Go to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
