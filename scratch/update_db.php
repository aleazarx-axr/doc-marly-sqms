<?php
require_once __DIR__ . '/../config/database.php';

$db = new Database();
$conn = $db->getConnection();

try {
    $conn->exec("ALTER TABLE `users` ADD COLUMN `status` enum('active','inactive') DEFAULT 'active' AFTER `role`");
    echo "Added status column.\n";
} catch (Exception $e) {
    echo "Error or already exists: " . $e->getMessage() . "\n";
}

try {
    $conn->exec("ALTER TABLE `users` ADD COLUMN `failed_attempts` int DEFAULT 0 AFTER `status`");
    echo "Added failed_attempts column.\n";
} catch (Exception $e) {
    echo "Error or already exists: " . $e->getMessage() . "\n";
}

try {
    $conn->exec("ALTER TABLE `users` ADD COLUMN `locked_until` timestamp NULL DEFAULT NULL AFTER `failed_attempts`");
    echo "Added locked_until column.\n";
} catch (Exception $e) {
    echo "Error or already exists: " . $e->getMessage() . "\n";
}

$createLogTable = "
CREATE TABLE IF NOT EXISTS `auth_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `username` varchar(255) DEFAULT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `event_type` enum('login_success', 'login_failed', 'account_lockout', 'logout', 'suspicious_activity') NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
";
try {
    $conn->exec($createLogTable);
    echo "Created auth_logs table.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
echo "Database update complete.\n";
