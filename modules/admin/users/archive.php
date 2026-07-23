<?php
require_once __DIR__ . '/../../../includes/functions.php';
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../includes/models/User.php';

Session::requireRole('admin');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_POST['user_id'] ?? null;

    if ($user_id && $user_id != $_SESSION['user_id']) {
        $db = new Database();
        $conn = $db->getConnection();
        $userModel = new User($conn);
        $userModel->id = $user_id;
        
        if ($userModel->archive()) {
            header('Location: index.php?status=archived');
            exit();
        }
    }
}

header('Location: index.php?status=error');
exit();
?>
