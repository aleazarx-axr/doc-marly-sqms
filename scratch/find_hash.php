<?php
require_once __DIR__ . '/../config/database.php';

$db = new Database();
$conn = $db->getConnection();

$hash = '$2y$12$dhcGzRnzZY9qki7ZFeLwN.97rlV1pNDEWoP0XpL6fCA9D09.Bhaw6';
$stmt = $conn->prepare("SELECT * FROM users WHERE password = :hash");
$stmt->execute(['hash' => $hash]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    print_r($user);
} else {
    echo "Hash not found in the users table.\n";
}
