<?php
require_once __DIR__ . '/../config/database.php';
$db = new Database();
$conn = $db->getConnection();
$stmt = $conn->query("SELECT CURRENT_TIMESTAMP as mysql_time");
$mysql_time = $stmt->fetchColumn();
echo "PHP Time:   " . date('Y-m-d H:i:s') . "\n";
echo "MySQL Time: " . $mysql_time . "\n";
