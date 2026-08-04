<?php
require_once __DIR__ . '/../config/database.php';

$db = new Database();
$conn = $db->getConnection();

$stmt = $conn->query("UPDATE counters SET current_staff_id = NULL");
echo "All counters unlocked.\n";
