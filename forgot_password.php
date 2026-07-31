<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/models/User.php';
require_once __DIR__ . '/includes/Mailer.php';

Session::start();

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (!preg_match('/@gmail\.com$/i', $email)) {
        $error = 'Only @gmail.com email addresses are allowed.';
    } else {
        $db = new Database();
        $conn = $db->getConnection();
        $user = new User($conn);
        
        if ($user->findByEmail($email)) {
            // Generate a new token that expires in 15 minutes for security
            $user->generateNewSetupToken(15);
            
            $mailer = new Mailer();
            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'];
            $resetLink = $protocol . "://" . $host . "/setup.php?token=" . $user->setup_token;
            
            $mailer->sendPasswordResetEmail($user->email, $user->name, $resetLink);
        }
        
        // Always show the same success message to prevent email enumeration
        $message = "If that email address exists in our system, you will receive a password reset link shortly.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Doc Marly SQMS</title>
    <link rel="icon" type="image/png" href="assets/images/marly1.ico">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/login_ui.css">
</head>
<body>
    <div class="login-card">
        <div class="login-header">
            <div class="brand-icon">
                <img src="/assets/images/docmarly.png" alt="Doc Marly" class="mb-3" style="width: 120px; height: 120px; border-radius: 50%; object-fit: cover;">
            </div>
            <h2>Doc Marly</h2>
            <span class="subhead">Reset Your Password</span>
        </div>

        <div class="login-form-content">
            <?php if (!empty($error)): ?>
                <div class="error-msg">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($message)): ?>
                <div class="alert" style="background-color: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px; text-align: center; border: 1px solid #c3e6cb;">
                    <i class="fas fa-check-circle"></i>
                    <?php echo htmlspecialchars($message); ?>
                </div>
                <div style="text-align: center; margin-top: 20px;">
                    <a href="/login" class="link-secondary" style="color: #666; text-decoration: underline;">Return to Login</a>
                </div>
            <?php else: ?>
                <p style="text-align: center; margin-bottom: 20px; color: #666; font-size: 14px;">Enter your email address below and we'll send you a link to reset your password.</p>
                
                <form action="/forgot_password.php" method="POST">
                    <div class="input-group">
                        <label for="email"><i class="fas fa-envelope" style="margin-right: 6px;"></i> Email Address</label>
                        <div class="input-wrapper">
                            <i class="fas fa-at"></i>
                            <input type="email" id="email" name="email" required autofocus pattern="^[a-zA-Z0-9._%+-]+@gmail\.com$" title="Please enter a valid @gmail.com address" placeholder="name@gmail.com">
                        </div>
                    </div>

                    <button type="submit" class="btn-login">
                        <span>Send Reset Link</span>
                        <i class="fas fa-paper-plane"></i>
                    </button>
                    
                    <div style="text-align: center; margin-top: 20px;">
                        <a href="/login" class="link-secondary" style="color: #666; text-decoration: underline;">Back to Login</a>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
