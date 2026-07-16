<?php
require_once __DIR__ . '/../config/database.php';

$db = new Database();
$conn = $db->getConnection();

try {
    $conn->exec("ALTER TABLE `users` MODIFY `status` enum('active', 'inactive', 'archived') NOT NULL DEFAULT 'active'");
    echo "Successfully updated users status enum to include archived.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
