<?php
require_once __DIR__ . '/../../../auth/session.php';

// Require 'staff' role to access this page
Session::requireRole('staff');
?>
<?php
$pageTitle = 'Staff Portal - Doc Marly SQMS';
$activeMenu = 'dashboard';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar_user.php';
?>

    <!-- Main Content -->
    <div class="main-content">
        <h1>Staff Portal</h1>
        <h2>Welcome, <?php echo htmlspecialchars(Session::get('username') ?? 'Staff'); ?>!</h2>
        <p>This is the protected staff queue management area.</p>
    </div>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
