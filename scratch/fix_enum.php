<?php
require_once __DIR__ . '/../config/database.php';

$db = new Database();
$conn = $db->getConnection();

try {
    $conn->exec("ALTER TABLE `auth_logs` MODIFY `event_type` enum('login_success', 'login_failed', 'account_lockout', 'logout', 'suspicious_activity', 'password_setup') NOT NULL");
    echo "Successfully updated auth_logs event_type enum.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
