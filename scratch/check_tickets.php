<?php
require_once __DIR__ . '/../config/database.php';
$db = new Database();
$conn = $db->getConnection();
$stmt = $conn->query("SELECT id, ticket_number, status, created_at FROM tickets ORDER BY id DESC LIMIT 5");
$tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
print_r($tickets);

$stmt2 = $conn->query("SELECT setting_key, setting_value FROM settings WHERE setting_key = 'last_reset_time'");
print_r($stmt2->fetchAll(PDO::FETCH_ASSOC));
