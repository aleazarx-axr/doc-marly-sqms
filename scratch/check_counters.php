<?php
require_once __DIR__ . '/../config/database.php';

$db = new Database();
$conn = $db->getConnection();

$stmt = $conn->query("SELECT id, name, is_active, is_archived, current_staff_id FROM counters ORDER BY name ASC");
$counters = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Counters State:\n";
foreach ($counters as $c) {
    echo "ID: {$c['id']}, Name: {$c['name']}, Active: {$c['is_active']}, Archived: {$c['is_archived']}, Current Staff ID: " . ($c['current_staff_id'] ?? 'NULL') . "\n";
}
