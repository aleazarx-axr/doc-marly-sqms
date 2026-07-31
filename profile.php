<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/database.php';

Session::requireLogin();
$userId = Session::get('user_id');
$role = Session::get('role');

$db = new Database();
$conn = $db->getConnection();

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['request_password_reset'])) {
        require_once __DIR__ . '/includes/models/User.php';
        require_once __DIR__ . '/includes/Mailer.php';
        
        $userModel = new User($conn);
        if ($userModel->findById($userId)) {
            if (empty($userModel->email)) {
                $error = "You do not have a connected email address to send the link to. Please update your profile with an email address first.";
            } else {
                $userModel->generateNewSetupToken(15);
                $mailer = new Mailer();
                $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
                $host = $_SERVER['HTTP_HOST'];
                $resetLink = $protocol . "://" . $host . "/setup.php?token=" . $userModel->setup_token;
                
                $mailer->sendPasswordResetEmail($userModel->email, $userModel->name, $resetLink);
                $message = "A password reset link has been sent to your email address.";
            }
        } else {
            $error = "Failed to initiate password reset.";
        }
    } else {
        $name = trim($_POST['name'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        
        if (empty($username)) {
            $error = "Username is required.";
        } else {
            // Check if username is already taken by someone else
            $checkStmt = $conn->prepare("SELECT id FROM users WHERE username = :username AND id != :id");
            $checkStmt->execute([':username' => $username, ':id' => $userId]);
            if ($checkStmt->rowCount() > 0) {
                $error = "Username is already taken by another user.";
            } else {
                $updateStmt = $conn->prepare("UPDATE users SET name = :name, username = :username, email = :email WHERE id = :id");
                $updateStmt->execute([
                    ':name' => $name,
                    ':username' => $username,
                    ':email' => $email,
                    ':id' => $userId
                ]);
                $message = "Profile updated successfully!";
                // Update session if name or username changed
                $_SESSION['name'] = $name;
                $_SESSION['username'] = $username;
            }
        }
    }
}

// Fetch current user details
$stmt = $conn->prepare("SELECT name, username, email FROM users WHERE id = :id");
$stmt->execute([':id' => $userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$pageTitle = 'Profile Settings';
$activeMenu = 'profile';

require_once __DIR__ . '/includes/header.php';
if ($role === 'admin') {
    require_once __DIR__ . '/includes/sidebar_admin.php';
} else {
    require_once __DIR__ . '/includes/sidebar_user.php';
}
?>

<div class="container py-4">

    <div class="row justify-content-center">
        <div class="col-12 col-lg-9 col-xl-8">
            <div class="card border-0 shadow-sm rounded-5">
 
        <div class="card-header bg-white border-bottom py-3 rounded-top-4">
            <h3 class="mb-0 fw-bold">
                <i class="bi bi-person-circle me-2 text-primary"></i>
                Profile Settings
            </h3>
            <small class="text-muted">
                Update your personal information and password.
            </small>
        </div>

        <div class="card-body p-4">

            <?php if ($message): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    <?php echo htmlspecialchars($message); ?>
                    <button class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="bi bi-exclamation-circle-fill me-2"></i>
                    <?php echo htmlspecialchars($error); ?>
                    <button class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <form method="POST" action="/profile" class="rounded-5">

                <div class="row">


                    <!-- RIGHT COLUMN -->
                    <div class="col-12">

                        <div class="row">

                            <div class="col-md-6 mb-3">

                                <label class="form-label fw-semibold">
                                    Full Name
                                </label>

                                <input
                                    type="text"
                                    class="form-control"
                                    name="name"
                                    value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>">

                            </div>

                            <div class="col-md-6 mb-3">

                                <label class="form-label fw-semibold">
                                    Username
                                </label>

                                <input
                                    type="text"
                                    class="form-control"
                                    name="username"
                                    value="<?php echo htmlspecialchars($user['username'] ?? ''); ?>"
                                    required>

                            </div>

                        </div>

                        <div class="mb-3">

                            <label class="form-label fw-semibold">
                                Email Address
                            </label>

                            <input
                                type="email"
                                class="form-control"
                                name="email"
                                value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>">

                        </div>

                    

                        <h6 class="fw-bold mb-3">
                            Change Password
                        </h6>

                        <div class="mb-4">
                            <p class="text-muted small mb-3">
                                For security purposes, password changes are handled via email verification. Click the button below to receive a password reset link at your connected email address.
                            </p>
                            <button type="submit" name="request_password_reset" value="1" class="btn btn-outline-primary">
                                <i class="bi bi-envelope-check me-1"></i> Send Password Reset Link
                            </button>
                        </div>

                        <div class="text-end">

                            <button class="btn btn-primary px-4">
                                <i class="bi bi-floppy me-2"></i>
                                Save Changes
                            </button>

                        </div>

                    </div>

                </div>

            </form>

        </div>
        
    </div>

</div>
</body>
</html>
