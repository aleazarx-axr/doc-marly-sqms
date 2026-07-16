<?php
require_once __DIR__ . '/../config/database.php';

$db = new Database();
$conn = $db->getConnection();

try {
    $conn->exec("ALTER TABLE `users` ADD `otp_code` VARCHAR(10) NULL, ADD `otp_expires` DATETIME NULL");
    echo "Successfully added otp columns to users table.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
