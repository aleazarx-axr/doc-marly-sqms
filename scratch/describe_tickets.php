<?php
require 'config/database.php';

$db = new Database();
$conn = $db->getConnection();

$stmt = $conn->query("DESCRIBE tickets");
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

print_r($columns);

$stmt = $conn->query("SELECT DISTINCT status FROM tickets");
$statuses = $stmt->fetchAll(PDO::FETCH_ASSOC);
print_r($statuses);
?>
