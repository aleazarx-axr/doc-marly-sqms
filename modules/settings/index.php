<?php
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/models/Setting.php';

Session::requireRole('admin');

$db = new Database();
$conn = $db->getConnection();
$settingModel = new Setting($conn);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $updates = [
        'smtp_host' => trim($_POST['smtp_host'] ?? ''),
        'smtp_port' => trim($_POST['smtp_port'] ?? ''),
        'smtp_user' => trim($_POST['smtp_user'] ?? ''),
        'smtp_from_email' => trim($_POST['smtp_from_email'] ?? ''),
        'smtp_from_name' => trim($_POST['smtp_from_name'] ?? '')
    ];
    
    // Only update password if a new one is provided
    if (!empty($_POST['smtp_pass'])) {
        $updates['smtp_pass'] = $_POST['smtp_pass'];
    }

    if ($settingModel->updateMultiple($updates)) {
        header('Location: index.php?status=success');
        exit();
    } else {
        header('Location: index.php?status=error');
        exit();
    }
}

$settings = $settingModel->getAll();

$pageTitle = 'System Settings - Admin Portal';
$activeMenu = 'settings';

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar_admin.php';
?>

<div class="main-content">
    <h2>System Settings</h2>
    
    <?php if(isset($_GET['status'])): ?>
        <?php
            $status = $_GET['status'];
            if ($status == 'success') { $msg = "Settings updated successfully."; $color = "green"; }
            if ($status == 'error') { $msg = "An error occurred while updating settings."; $color = "red"; }
        ?>
        <p style="color: <?php echo $color; ?>; background: #f0f9f0; padding: 10px; border: 1px solid <?php echo $color; ?>; margin-bottom: 15px;">
            <?php echo $msg; ?>
        </p>
    <?php endif; ?>

    <div style="background: #fff; padding: 20px; border-radius: 5px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); max-width: 600px;">
        <h3 style="margin-top: 0; border-bottom: 1px solid #eee; padding-bottom: 10px;">Email / SMTP Configuration</h3>
        <p style="font-size: 14px; color: #666; margin-bottom: 20px;">Configure the Gmail/SMTP account used to send One-Time Passwords (OTPs) and Welcome links.</p>
        
        <form action="index.php" method="POST">
            <div class="form-group" style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px; font-weight: bold;">SMTP Host:</label>
                <input type="text" name="smtp_host" value="<?php echo htmlspecialchars($settings['smtp_host'] ?? ''); ?>" required style="width: 100%; padding: 8px;">
            </div>
            
            <div class="form-group" style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px; font-weight: bold;">SMTP Port:</label>
                <input type="number" name="smtp_port" value="<?php echo htmlspecialchars($settings['smtp_port'] ?? ''); ?>" required style="width: 100%; padding: 8px;">
            </div>

            <div class="form-group" style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px; font-weight: bold;">SMTP Username (Gmail Address):</label>
                <input type="email" name="smtp_user" value="<?php echo htmlspecialchars($settings['smtp_user'] ?? ''); ?>" style="width: 100%; padding: 8px;">
            </div>

            <div class="form-group" style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px; font-weight: bold;">SMTP App Password:</label>
                <input type="password" name="smtp_pass" placeholder="Leave blank to keep current password" style="width: 100%; padding: 8px;">
                <small style="color: #888; font-size: 12px; display:block; margin-top:4px;">Use a 16-character Google App Password if using Gmail.</small>
            </div>

            <div class="form-group" style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px; font-weight: bold;">Sender Email (From):</label>
                <input type="email" name="smtp_from_email" value="<?php echo htmlspecialchars($settings['smtp_from_email'] ?? ''); ?>" required style="width: 100%; padding: 8px;">
            </div>

            <div class="form-group" style="margin-bottom: 25px;">
                <label style="display: block; margin-bottom: 5px; font-weight: bold;">Sender Name (From):</label>
                <input type="text" name="smtp_from_name" value="<?php echo htmlspecialchars($settings['smtp_from_name'] ?? ''); ?>" required style="width: 100%; padding: 8px;">
            </div>

            <button type="submit" style="padding: 10px 20px; background-color: blue; color: white; border: none; cursor: pointer;">Save Settings</button>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
