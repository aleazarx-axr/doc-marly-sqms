<?php
require 'config/database.php';

$db = new Database();
$conn = $db->getConnection();

$stmt = $conn->query("DESCRIBE counters");
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

print_r($columns);
?>
