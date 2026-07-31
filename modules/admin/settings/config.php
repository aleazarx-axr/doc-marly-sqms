<?php
require_once __DIR__ . '/../../../includes/functions.php';
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../includes/models/Setting.php';

Session::requireRole('admin');

$db = new Database();
$conn = $db->getConnection();
$settingModel = new Setting($conn);



if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_smtp') {
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
            header('Location: /admin/settings/config?status=success');
            exit();
        } else {
            header('Location: /admin/settings/config?status=error');
            exit();
        }
    } elseif ($action === 'force_reset') {
        $settingModel->updateMultiple(['last_reset_time' => date('Y-m-d H:i:s')]);
        
        require_once __DIR__ . '/../../../includes/models/Ticket.php';
        $ticketModel = new Ticket($conn);
        $session_start = getQueueSessionStart($conn);
        $ticketModel->expireOldTickets($session_start);
        
        header("Location: /admin/settings/config?status=reset_success");
        exit();
    } elseif ($action === 'update_reset_time') {
        $new_time = $_POST['daily_reset_time'] ?? '00:00:00';
        if (preg_match('/^(?:2[0-3]|[01][0-9]):[0-5][0-9](?::[0-5][0-9])?$/', $new_time)) {
            if (strlen($new_time) === 5) $new_time .= ':00'; // Ensure seconds are included if missing
            $settingModel->updateMultiple(['daily_reset_time' => $new_time]);
            header("Location: /admin/settings/config?status=settings_updated");
            exit();
        }
    }
}

$settings = $settingModel->getAll();

$pageTitle = 'System Settings - Admin Portal';
$activeMenu = 'settings';

require_once __DIR__ . '/../../../includes/header.php';
require_once __DIR__ . '/../../../includes/sidebar_admin.php';
?>

<div class="main-content">
    <h2>System Settings</h2>
    
    <?php if(isset($_GET['status'])): ?>
        <?php
            $status = $_GET['status'];
            $msg = "";
            $color = "";
            if ($status == 'success') { $msg = "Settings updated successfully."; $color = "green"; }
            if ($status == 'error') { $msg = "An error occurred while updating settings."; $color = "red"; }
            if ($status == 'reset_success') { $msg = "Queue has been forcefully reset. All current tickets are now expired."; $color = "green"; }
            if ($status == 'settings_updated') { $msg = "Daily reset time has been updated successfully."; $color = "green"; }
        ?>
        <?php if (!empty($msg)): ?>
        <p style="color: <?php echo $color; ?>; background: #f0f9f0; padding: 10px; border: 1px solid <?php echo $color; ?>; margin-bottom: 15px;">
            <?php echo $msg; ?>
        </p>
        <?php endif; ?>
    <?php endif; ?>

    <div style="display: flex; gap: 20px; flex-wrap: wrap; margin-bottom: 20px;">
        <!-- Queue Management Controls -->
        <div style="background: #fff; padding: 20px; border-radius: 5px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); flex: 1; min-width: 300px; max-width: 600px;">
            <h3 style="margin-top: 0; border-bottom: 1px solid #eee; padding-bottom: 10px;">Queue Reset Controls</h3>
            
            <div style="margin-bottom: 25px;">
                <h4 style="margin-top: 0; color: #dc3545;"><i class="fas fa-exclamation-triangle"></i> Force Reset Queue</h4>
                <p style="font-size: 13px; color: #666; margin-bottom: 15px;">Immediately expire all waiting tickets and reset the sequence to #1 for the next citizen.</p>
                <form method="POST" action="/admin/settings/config" onsubmit="return confirm('Are you sure you want to FORCE RESET the queue? This will expire all waiting and called tickets immediately.');">
                    <input type="hidden" name="action" value="force_reset">
                    <button type="submit" style="background-color: #dc3545; color: white; border: none; padding: 10px 15px; border-radius: 4px; cursor: pointer; font-weight: bold; width: 100%;">
                        Force Reset Now
                    </button>
                </form>
            </div>
            
            <div>
                <h4 style="margin-top: 0; color: #0d6efd;"><i class="fas fa-clock"></i> Automatic Reset Schedule</h4>
                <p style="font-size: 13px; color: #666; margin-bottom: 15px;">Set the exact time of day when the queue automatically resets and expires old tickets.</p>
                <form method="POST" action="/admin/settings/config" style="display: flex; align-items: center; gap: 10px;">
                    <input type="hidden" name="action" value="update_reset_time">
                    <?php $currentResetTime = $settings['daily_reset_time'] ?? '00:00:00'; ?>
                    <input type="time" step="1" name="daily_reset_time" value="<?= htmlspecialchars($currentResetTime) ?>" style="padding: 10px; border: 1px solid #ccc; border-radius: 4px; flex-grow: 1;" required>
                    <button type="submit" style="background-color: #0d6efd; color: white; border: none; padding: 10px 15px; border-radius: 4px; cursor: pointer; font-weight: bold; white-space: nowrap;">
                        Save Time
                    </button>
                </form>
            </div>
        </div>

    <div style="background: #fff; padding: 20px; border-radius: 5px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); flex: 1; min-width: 300px; max-width: 600px;">
        <form action="/admin/settings/config" method="POST">
            <input type="hidden" name="action" value="update_smtp">
            
            <h3 style="margin-top: 0; border-bottom: 1px solid #eee; padding-bottom: 10px;">Email / SMTP Configuration</h3>
            <p style="font-size: 14px; color: #666; margin-bottom: 20px;">Configure the Gmail/SMTP account used to send One-Time Passwords (OTPs) and Welcome links.</p>
            

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
                <input type="email" name="smtp_user" value="<?php echo htmlspecialchars($settings['smtp_user'] ?? ''); ?>" style="width: 100%; padding: 8px;" pattern="^[a-zA-Z0-9._%+-]+@gmail\.com$" title="Please enter a valid @gmail.com address" placeholder="your.account@gmail.com">
            </div>

            <div class="form-group" style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px; font-weight: bold;">SMTP App Password:</label>
                <input type="password" name="smtp_pass" placeholder="Leave blank to keep current password" style="width: 100%; padding: 8px;">
                <small style="color: #888; font-size: 12px; display:block; margin-top:4px;">Use a 16-character Google App Password if using Gmail.</small>
            </div>

            <div class="form-group" style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px; font-weight: bold;">Sender Email (From):</label>
                <input type="email" name="smtp_from_email" value="<?php echo htmlspecialchars($settings['smtp_from_email'] ?? ''); ?>" required style="width: 100%; padding: 8px;" pattern="^[a-zA-Z0-9._%+-]+@gmail\.com$" title="Please enter a valid @gmail.com address" placeholder="your.account@gmail.com">
            </div>

            <div class="form-group" style="margin-bottom: 25px;">
                <label style="display: block; margin-bottom: 5px; font-weight: bold;">Sender Name (From):</label>
                <input type="text" name="smtp_from_name" value="<?php echo htmlspecialchars($settings['smtp_from_name'] ?? ''); ?>" required style="width: 100%; padding: 8px;">
            </div>

            <button type="submit" style="padding: 10px 20px; background-color: blue; color: white; border: none; cursor: pointer;">Save Settings</button>
        </form>
    </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../../includes/footer.php'; ?>
