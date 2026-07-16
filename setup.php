<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/models/User.php';

Session::start();

$db = new Database();
$conn = $db->getConnection();
$user = new User($conn);

$token = $_GET['token'] ?? ($_POST['token'] ?? '');
$error = '';
$success = '';

$is_invalid = false;

if (empty($token)) {
    $is_invalid = true;
} elseif (!$user->findByToken($token)) {
    $is_invalid = true;
}

if (!$is_invalid && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($password) || empty($confirm_password)) {
        $error = 'Please fill out all fields.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 8 || !preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password) || !preg_match('/[^a-zA-Z0-9]/', $password)) {
        $error = 'Password must be at least 8 characters long, contain an uppercase letter, a lowercase letter, a number, and a special character.';
    } else {
        if ($user->updatePasswordAndClearToken($password)) {
            $user->logAuthEvent('password_setup');
            header("Location: login.php?status=setup_complete");
            exit();
        } else {
            $error = 'Failed to update password. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Password - Doc Marly SQMS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="assets/css/login_ui.css">
</head>
<body>
    <div class="login-card">
        <div class="login-header">
            <div class="brand-icon">
                <img src="assets/images/docmarly.png" alt="Doc Marly" class="mb-3" style="width: 120px; height: 120px; border-radius: 50%; object-fit: cover;">
            </div>
            <h2>Doc Marly</h2>
            <span class="subhead">Set Up Your Account</span>
        </div>

        <div class="login-form-content">
            <?php if ($is_invalid): ?>
                <div class="error-msg" style="margin-bottom: 20px;">
                    <i class="fas fa-exclamation-circle"></i>
                    This setup link is invalid, has expired, or your password has already been created. Please contact your administrator.
                </div>
                <a href="login.php" class="btn-login" style="text-align: center; text-decoration: none; display: block;">Go to Login</a>
            <?php else: ?>
                <p style="text-align: center; margin-bottom: 20px;">Welcome, <strong><?php echo htmlspecialchars($user->name); ?></strong>!<br>Please set your password below.</p>
                
                <?php if (!empty($error)): ?>
                    <div class="error-msg">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php else: ?>
                    <div class="error-msg" style="display: none;"></div>
                <?php endif; ?>

                <form action="setup.php" method="POST">
                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                    
                    <div class="input-group">
                        <label for="password"><i style="margin-right: 6px;"></i> New Password</label>
                        <div class="input-wrapper">
                            <i class="fas fa-lock"></i>
                            <input type="password" id="password" name="password" required autofocus>
                        </div>
                        <small style="color: #666; font-size: 11px; display: block; margin-top: 5px;">Must be at least 8 chars, with upper, lower, number & special char.</small>
                    </div>

                    <div class="input-group">
                        <label for="confirm_password"><i style="margin-right: 6px;"></i> Confirm Password</label>
                        <div class="input-wrapper">
                            <i class="fas fa-lock"></i>
                            <input type="password" id="confirm_password" name="confirm_password" required>
                        </div>
                    </div>

                    <button type="submit" class="btn-login">
                        <span>Set Password</span>
                        <i class="fas fa-check"></i>
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
