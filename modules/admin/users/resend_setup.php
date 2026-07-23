<?php
require_once __DIR__ . '/../../../includes/functions.php';
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../includes/models/User.php';
require_once __DIR__ . '/../../../includes/Mailer.php';

Session::requireRole('admin');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_POST['user_id'] ?? null;

    if ($user_id) {
        $db = new Database();
        $conn = $db->getConnection();
        $userModel = new User($conn);
        
        if ($userModel->findById($user_id)) {
            if ($userModel->generateNewSetupToken()) {
                $mailer = new Mailer();
                $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
                $host = $_SERVER['HTTP_HOST'];
                $setupLink = $protocol . "://" . $host . "/setup.php?token=" . $userModel->setup_token;
                
                if ($mailer->sendWelcomeEmail($userModel->email, $userModel->name, $userModel->username, $setupLink)) {
                    header('Location: index.php?status=resent');
                    exit();
                }
            }
        }
    }
    
    header('Location: index.php?status=resend_error');
    exit();
} else {
    header('Location: index.php');
    exit();
}
?>
