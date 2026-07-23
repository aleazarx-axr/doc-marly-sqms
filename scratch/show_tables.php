<?php
require 'config/database.php';
$db = new Database();
$conn = $db->getConnection();
$stmt = $conn->query("SHOW TABLES");
$tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
foreach($tables as $t) {
    echo $t . "\n";
    $stmt2 = $conn->query("SHOW COLUMNS FROM " . $t);
    print_r($stmt2->fetchAll(PDO::FETCH_COLUMN));
}
