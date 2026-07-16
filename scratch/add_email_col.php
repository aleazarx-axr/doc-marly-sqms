<?php
require_once __DIR__ . '/../config/database.php';

$db = new Database();
$conn = $db->getConnection();

try {
    $conn->exec("ALTER TABLE `users` ADD COLUMN `email` varchar(255) NULL AFTER `username`");
    echo "Added email column.\n";
} catch (Exception $e) {
    echo "Error or already exists: " . $e->getMessage() . "\n";
}
echo "Database update complete.\n";
