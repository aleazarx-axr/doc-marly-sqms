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

<div class="main-content">
    <h2>Profile Settings</h2>
    
    <?php if ($message): ?>
        <p style="color: green;"><strong><?php echo htmlspecialchars($message); ?></strong></p>
    <?php endif; ?>
    <?php if ($error): ?>
        <p style="color: red;"><strong><?php echo htmlspecialchars($error); ?></strong></p>
    <?php endif; ?>

    <form method="POST" action="/profile">
        <div style="margin-bottom: 10px;">
            <label>Name:</label><br>
            <input type="text" name="name" value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>">
        </div>
        <div style="margin-bottom: 10px;">
            <label>Username (Required):</label><br>
            <input type="text" name="username" value="<?php echo htmlspecialchars($user['username'] ?? ''); ?>" required>
        </div>
        <div style="margin-bottom: 10px;">
            <label>Email:</label><br>
            <input type="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>">
        </div>
        <div style="margin-bottom: 15px;">
            <label>New Password (leave blank to keep current):</label><br>
            <input type="password" name="password">
        </div>
        <button type="submit">Save Changes</button>
    </form>
</div>

</body>
</html>
