<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

requireLogin();
$page_title = 'Notifications';
$show_sidebar = true;

$user_id = $_SESSION['user_id'];

// Mark as read
if (isset($_GET['mark_read']) && !empty($_GET['mark_read'])) {
    $notification_id = intval($_GET['mark_read']);
    markNotificationAsRead($notification_id);
    header("Location: notifications.php");
    exit();
}

// Mark all as read
if (isset($_GET['mark_all_read'])) {
    $conn->query("UPDATE notifications SET is_read = 1 WHERE user_id = '$user_id'");
    header("Location: notifications.php");
    exit();
}

include 'includes/header.php';

// Get all notifications
$notifications = $conn->query("SELECT * FROM notifications WHERE user_id = '$user_id' ORDER BY created_at DESC");
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-bell"></i> Notifications</h2>
                <a href="?mark_all_read=1" class="btn btn-sm btn-secondary">
                    <i class="fas fa-check-double"></i> Mark All as Read
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <?php
                if ($notifications && $notifications->num_rows > 0) {
                    while ($notif = $notifications->fetch_assoc()) {
                        $read_class = $notif['is_read'] ? 'bg-white' : 'bg-light';
                        ?>
                        <div class="card mb-2 <?php echo $read_class; ?>" style="border-left: 4px solid <?php echo $notif['is_read'] ? '#ccc' : '#00695C'; ?>">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-10">
                                        <h6 class="card-title mb-1"><?php echo htmlspecialchars($notif['title']); ?></h6>
                                        <p class="card-text text-muted mb-1"><?php echo htmlspecialchars($notif['message']); ?></p>
                                        <small class="text-muted">
                                            <i class="fas fa-clock"></i> <?php echo formatDateTime($notif['created_at']); ?>
                                        </small>
                                    </div>
                                    <div class="col-2 text-end">
                                        <?php if (!$notif['is_read']): ?>
                                            <a href="?mark_read=<?php echo $notif['notification_id']; ?>" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-check"></i>
                                            </a>
                                        <?php else: ?>
                                            <span class="badge bg-success">Read</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php
                    }
                } else {
                    echo '<div class="alert alert-info m-3"><i class="fas fa-info-circle"></i> No notifications found</div>';
                }
                ?>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
