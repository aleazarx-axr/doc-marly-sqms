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
    $name = trim($_POST['name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username)) {
        $error = "Username is required.";
    } else {
        // Check if username is already taken by someone else
        $checkStmt = $conn->prepare("SELECT id FROM users WHERE username = :username AND id != :id");
        $checkStmt->execute([':username' => $username, ':id' => $userId]);
        if ($checkStmt->rowCount() > 0) {
            $error = "Username is already taken by another user.";
        } else {
            // Update logic
            if (!empty($password)) {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $updateStmt = $conn->prepare("UPDATE users SET name = :name, username = :username, email = :email, password = :password WHERE id = :id");
                $updateStmt->execute([
                    ':name' => $name,
                    ':username' => $username,
                    ':email' => $email,
                    ':password' => $hashedPassword,
                    ':id' => $userId
                ]);
            } else {
                $updateStmt = $conn->prepare("UPDATE users SET name = :name, username = :username, email = :email WHERE id = :id");
                $updateStmt->execute([
                    ':name' => $name,
                    ':username' => $username,
                    ':email' => $email,
                    ':id' => $userId
                ]);
            }
            $message = "Profile updated successfully!";
            // Update session if name or username changed
            $_SESSION['name'] = $name;
            $_SESSION['username'] = $username;
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

                        <div class="mb-3">

                            <label class="form-label fw-semibold">
                                New Password
                            </label>

                            <input
                                type="password"
                                class="form-control"
                                name="password"
                                placeholder="Leave blank to keep your current password">

                            <div class="form-text">
                                Your password will only be changed if you enter a new one.
                            </div>

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
