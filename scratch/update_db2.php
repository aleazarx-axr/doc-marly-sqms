<?php
require_once __DIR__ . '/../config/database.php';

$db = new Database();
$conn = $db->getConnection();

try {
    $conn->exec("ALTER TABLE `users` ADD COLUMN `name` varchar(255) NULL AFTER `id`");
    echo "Added name column.\n";
} catch (Exception $e) {
    echo "Error or already exists: " . $e->getMessage() . "\n";
}

try {
    $conn->exec("ALTER TABLE `users` ADD COLUMN `setup_token` varchar(64) NULL AFTER `locked_until`");
    echo "Added setup_token column.\n";
} catch (Exception $e) {
    echo "Error or already exists: " . $e->getMessage() . "\n";
}

try {
    $conn->exec("ALTER TABLE `users` ADD COLUMN `token_expires` timestamp NULL DEFAULT NULL AFTER `setup_token`");
    echo "Added token_expires column.\n";
} catch (Exception $e) {
    echo "Error or already exists: " . $e->getMessage() . "\n";
}

echo "Database update complete.\n";
