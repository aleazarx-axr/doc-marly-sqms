<?php
require 'config/database.php';
$db = new Database();
$conn = $db->getConnection();

echo "--- TICKETS ---\n";
$stmt = $conn->query('DESCRIBE tickets');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

echo "\n--- SERVICES ---\n";
$stmt = $conn->query('DESCRIBE services');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
