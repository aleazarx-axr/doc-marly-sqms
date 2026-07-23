<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/models/User.php';

Session::start();

// Redirect to dashboard if already logged in
if (Session::isLoggedIn()) {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (!is_string($username) || !is_string($password) || empty(trim($username)) || empty($password)) {
        $error = 'Username and password are required.';
    } else {
        $db = new Database();
        $conn = $db->getConnection();
        $user = new User($conn);
        $user->username = $username; // For logging if user is not found

        if ($user->findByUsername($username)) {
            if ($user->isLocked()) {
                $user->logAuthEvent('login_failed');
                // Ensure generic error
                $error = 'Invalid username or password.';
            } else {
                if (password_verify($password, $user->password)) {
                    if ($user->status !== 'active') {
                        $user->logAuthEvent('login_failed');
                        $error = 'Invalid username or password.';
                    } else {
                        // Generate OTP and Redirect to Verification Page
                        $otpCode = $user->generateOTP();
                        if ($otpCode) {
                            require_once __DIR__ . '/includes/Mailer.php';
                            $mailer = new Mailer();
                            $mailer->sendOTPEmail($user->email, $user->name, $otpCode);
                            
                            // Set pending session
                            Session::set('pending_otp_user_id', $user->id);
                            
                            header("Location: verify_otp.php");
                            exit();
                        } else {
                            $error = 'Failed to generate security code. Please try again.';
                        }
                    }
                } else {
                    $user->incrementFailedAttempts();
                    if ($user->failed_attempts >= 5) {
                        $user->lockAccount(15);
                        $user->logAuthEvent('account_lockout');
                    } else {
                        $user->logAuthEvent('login_failed');
                    }
                    $error = 'Invalid username or password.';
                }
            }
        } else {
            $user->logAuthEvent('login_failed');
            // Prevent timing attack username enumeration
            password_verify($password, '$2y$12$DUMMYHASHFORANTITIMINGATTACK1234');
            $error = 'Invalid username or password.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doc Marly · Smart Queue Login</title>
    <link rel="icon" type="image/png" href="assets/images/marly1.ico">
    <!-- Font Awesome 6 (free) for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="assets/css/login_ui.css">

</head>

<body>
    <div class="login-card">
        <!-- HEADER WITH BLUE BACKGROUND -->
        <div class="login-header">
            <div class="brand-icon">
                <img src="assets/images/docmarly.png" alt="User Icon" class="mb-3" style="width: 120px; height: 120px; border-radius: 50%; object-fit: cover;">
            </div>
            <h2>Doc Marly</h2>
            <span class="subhead">Smart Queue · login</span>
        </div>

        <!-- FORM CONTENT -->
        <div class="login-form-content">
            <!-- ERROR MESSAGE -->
            <?php if (isset($error) && !empty($error)): ?>
                <div class="error-msg">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php else: ?>
                <div class="error-msg" style="display: none;"></div>
            <?php endif; ?>

            <!-- LOGIN FORM -->
            <form action="login.php" method="post" autocomplete="on">
                <div class="input-group">
                    <label for="username"><i style="margin-right: 6px;"></i> Username</label>
                    <div class="input-wrapper">
                        <i class="fas fa-user"></i>
                        <input type="text" id="username" name="username" placeholder="Enter your username" required autofocus>
                    </div>
                </div>

                <div class="input-group">
                    <label for="password"><i style="margin-right: 6px;"></i> Password</label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" placeholder=" " required>
                        <button type="button" class="toggle-pwd" id="togglePassword" aria-label="Toggle password visibility">
                            <i class="fas fa-eye-slash" id="pwdIcon"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-login">
                    <span>Login</span>
                    <i class="fas fa-arrow-right"></i>
                </button>
            </form>
        </div>
    </div>

    <!-- password toggle JS -->
    <script>
        (function() {
            const toggleBtn = document.getElementById('togglePassword');
            const passwordInput = document.getElementById('password');
            const pwdIcon = document.getElementById('pwdIcon');

            if (toggleBtn && passwordInput && pwdIcon) {
                toggleBtn.addEventListener('click', function() {
                    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordInput.setAttribute('type', type);
                    if (type === 'text') {
                        pwdIcon.classList.remove('fa-eye-slash');
                        pwdIcon.classList.add('fa-eye');
                    } else {
                        pwdIcon.classList.remove('fa-eye');
                        pwdIcon.classList.add('fa-eye-slash');
                    }
                });
            }

            const errorDiv = document.querySelector('.error-msg');
            if (errorDiv && errorDiv.textContent.trim() === '') {
                errorDiv.style.display = 'none';
            }
        })();
    </script>
</body>

</html>