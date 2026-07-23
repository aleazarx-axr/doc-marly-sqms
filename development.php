<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/database.php';
Session::requireLogin();

$pageTitle = 'In Development - Admin Portal';
// Check if an active menu was passed
$activeMenu = $_GET['menu'] ?? '';

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar_admin.php';
?>

<div class="main-content" style="display: flex; flex-direction: column; justify-content: center; align-items: center; min-height: 70vh; text-align: center;">
    <i class="bi bi-tools" style="font-size: 5rem; color: #a5b4ff; margin-bottom: 20px;"></i>
    <h2 style="color: #2a296f; font-weight: bold; margin-bottom: 15px;">Under Development</h2>
    <p style="color: #666; font-size: 1.1rem; max-width: 500px; margin-bottom: 30px;">
        This section is currently being built and will be available in a future update. We're working hard to bring you the best experience!
    </p>
    <a href="/index.php" style="background-color: #2a296f; color: white; padding: 10px 25px; border-radius: 8px; text-decoration: none; font-weight: 500; transition: background-color 0.2s;">
        <i class="bi bi-arrow-left me-2"></i> Return to Dashboard
    </a>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
