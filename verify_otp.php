<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/models/User.php';

Session::start();

// If already logged in, redirect
if (Session::isLoggedIn()) {
    header("Location: index.php");
    exit();
}

$pending_user_id = Session::get('pending_otp_user_id');

// If no pending OTP, send back to login
if (!$pending_user_id) {
    header("Location: login.php");
    exit();
}

$db = new Database();
$conn = $db->getConnection();
$user = new User($conn);

if (!$user->findById($pending_user_id)) {
    Session::remove('pending_otp_user_id');
    header("Location: login.php");
    exit();
}

$error = '';
$success = '';

if (isset($_GET['action']) && $_GET['action'] === 'resend') {
    $otpCode = $user->generateOTP();
    if ($otpCode) {
        require_once __DIR__ . '/includes/Mailer.php';
        $mailer = new Mailer();
        $mailer->sendOTPEmail($user->email, $user->name, $otpCode);
        $success = "A new verification code has been sent to your email.";
    } else {
        $error = "Failed to resend verification code. Please try again.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $otp_code = trim($_POST['otp_code'] ?? '');

    if (empty($otp_code)) {
        $error = 'Please enter the verification code.';
    } else {
        if ($user->verifyOTP($otp_code)) {
            // Valid OTP
            $user->clearOTP();
            session_regenerate_id(true);
            $user->resetFailedAttempts();
            $user->logAuthEvent('login_success');

            // Establish full session
            Session::remove('pending_otp_user_id');
            Session::set('user_id', $user->id);
            Session::set('username', $user->username);
            Session::set('role', $user->role);
            Session::set('last_activity', time());

            header("Location: index.php");
            exit();
        } else {
            // Invalid OTP (Increment failed attempts? Optional, but good for security)
            $user->incrementFailedAttempts();
            if ($user->failed_attempts >= 5) {
                $user->lockAccount(15);
                $user->logAuthEvent('account_lockout');
                Session::remove('pending_otp_user_id');
                header("Location: login.php?status=locked");
                exit();
            }
            $error = 'Invalid or expired verification code.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Login - Doc Marly SQMS</title>
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
            <span class="subhead">Verification Required</span>
        </div>

        <div class="login-form-content">
            <p style="text-align: center; margin-bottom: 20px;">We've sent a 6-digit code to <strong><?php echo htmlspecialchars(maskEmail($user->email ?? '')); ?></strong>.<br>It will expire in 5 minutes.</p>
            
            <?php if (!empty($success)): ?>
                <div class="success-msg" style="color: green; background-color: #e6f4ea; border: 1px solid #bce2c6; padding: 10px; border-radius: 5px; margin-bottom: 20px; font-size: 14px; text-align: center;">
                    <i class="fas fa-check-circle"></i>
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
                <div class="error-msg">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php else: ?>
                <div class="error-msg" style="display: none;"></div>
            <?php endif; ?>

            <form action="verify_otp.php" method="POST" autocomplete="off">
                <div class="input-group">
                    <label for="otp_code"><i style="margin-right: 6px;"></i> 6-Digit Code</label>
                    <div class="input-wrapper">
                        <i class="fas fa-key"></i>
                        <input type="text" id="otp_code" name="otp_code" placeholder="e.g. 123456" maxlength="6" pattern="\d{6}" required autofocus>
                    </div>
                </div>

                <button type="submit" class="btn-login">
                    <span>Verify & Login</span>
                    <i class="fas fa-check-circle"></i>
                </button>
            </form>
            
            <div style="text-align: center; margin-top: 20px;">
                <p style="font-size: 14px; color: #666; margin-bottom: 10px;">Didn't receive the code? <a href="verify_otp.php?action=resend" style="color: #3498db; text-decoration: underline;">Resend</a></p>
                <a href="login.php" style="color: #666; text-decoration: underline; font-size: 14px;">Back to Login</a>
            </div>
        </div>
    </div>
</body>
</html>
