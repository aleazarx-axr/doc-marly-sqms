<?php
require_once __DIR__ . '/../config/database.php';

$db = new Database();
$conn = $db->getConnection();

$new_password = 'password123';
$hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

$stmt = $conn->prepare("UPDATE users SET password = :password, failed_attempts = 0, locked_until = NULL WHERE username = 'michaelmartinez'");
$stmt->execute(['password' => $hashed_password]);

echo "Account unlocked and password reset to: password123\n";
