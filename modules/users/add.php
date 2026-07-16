<?php
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/models/User.php';
require_once __DIR__ . '/../../includes/Mailer.php';

Session::requireRole('admin');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $role = 'staff';

    if (!empty($name) && !empty($email)) {
        $db = new Database();
        $conn = $db->getConnection();
        $userModel = new User($conn);
        
        $username = $userModel->generateUsername($name);
        $setup_token = bin2hex(random_bytes(32));
        $token_expires = date('Y-m-d H:i:s', strtotime('+24 hours'));
        
        $userModel->name = $name;
        $userModel->username = $username;
        $userModel->email = $email;
        $userModel->password = password_hash(bin2hex(random_bytes(10)), PASSWORD_BCRYPT); // Dummy password until set
        $userModel->role = $role;
        $userModel->setup_token = $setup_token;
        $userModel->token_expires = $token_expires;

        if ($userModel->create()) {
            $mailer = new Mailer();
            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'];
            $setupLink = $protocol . "://" . $host . "/setup.php?token=" . $setup_token;
            
            $mailer->sendWelcomeEmail($email, $name, $username, $setupLink);
            
            header('Location: index.php?status=added');
            exit();
        }
    }
    header('Location: index.php?status=error');
    exit();
}

$pageTitle = 'Add User - Admin Portal';
$activeMenu = 'users';

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar_admin.php';
?>

<div class="main-content">
    <h2>Add New User</h2>
    <form action="add.php" method="POST" style="max-width: 400px;">
        <div class="form-group" style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px;">Full Name:</label>
            <input type="text" name="name" required style="width: 100%; padding: 8px;" placeholder="e.g. Juan Dela Cruz">
        </div>
        <div class="form-group" style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px;">Email:</label>
            <input type="email" name="email" required style="width: 100%; padding: 8px;" placeholder="e.g. juan@example.com">
        </div>

        <div style="margin-top: 25px;">
            <button type="submit" style="padding: 10px 20px; background-color: blue; color: white; border: none; cursor: pointer;">Save</button>
            <a href="index.php" style="margin-left: 15px; color: gray; text-decoration: underline;">Cancel</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
