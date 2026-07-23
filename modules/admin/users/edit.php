<?php
require_once __DIR__ . '/../../../includes/functions.php';
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../includes/models/User.php';

Session::requireRole('admin');

$db = new Database();
$conn = $db->getConnection();
$userModel = new User($conn);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_POST['user_id'] ?? '';
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'service_staff';

    if (!empty($user_id) && !empty($username)) {
        $userModel->id = $user_id;
        $userModel->username = $username;
        $userModel->email = $email;
        $userModel->role = $role;
        
        if (!empty($password)) {
            $userModel->password = password_hash($password, PASSWORD_BCRYPT);
        } else {
            $userModel->password = null;
        }

        if ($userModel->update()) {
            header('Location: index.php?status=edited');
            exit();
        }
    }
    header('Location: index.php?status=error');
    exit();
}

$user_id = $_GET['id'] ?? '';
if (empty($user_id)) {
    header('Location: index.php');
    exit();
}

$stmt = $conn->prepare("SELECT id, username, email, role FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$current_user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$current_user) {
    header('Location: index.php');
    exit();
}

$pageTitle = 'Edit User - Admin Portal';
$activeMenu = 'users';

require_once __DIR__ . '/../../../includes/header.php';
require_once __DIR__ . '/../../../includes/sidebar_admin.php';
?>

<div class="main-content">
    <h2>Edit User</h2>
    <form action="edit.php" method="POST" style="max-width: 400px;">
        <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($current_user['id']); ?>">
        
        <div class="form-group" style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px;">Username:</label>
            <input type="text" name="username" value="<?php echo htmlspecialchars($current_user['username']); ?>" required style="width: 100%; padding: 8px;">
        </div>

        <div class="form-group" style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px;">Email:</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($current_user['email'] ?? ''); ?>" style="width: 100%; padding: 8px;">
        </div>
        
        <div class="form-group" style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px;">Password (Leave blank to keep current password):</label>
            <input type="password" name="password" style="width: 100%; padding: 8px;">
        </div>
        
        <div class="form-group" style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px;">Role:</label>
            <select name="role" required style="width: 100%; padding: 8px;">
                <option value="service_staff" <?php echo ($current_user['role'] === 'service_staff' || $current_user['role'] === 'staff') ? 'selected' : ''; ?>>Service Staff</option>
                <option value="information_staff" <?php echo ($current_user['role'] === 'information_staff') ? 'selected' : ''; ?>>Information Staff</option>
                <option value="admin" <?php echo ($current_user['role'] === 'admin') ? 'selected' : ''; ?>>Admin</option>
            </select>
        </div>
        
        <div style="margin-top: 25px;">
            <button type="submit" style="padding: 10px 20px; background-color: blue; color: white; border: none; cursor: pointer;">Update</button>
            <a href="index.php" style="margin-left: 15px; color: gray; text-decoration: underline;">Cancel</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../../../includes/footer.php'; ?>
