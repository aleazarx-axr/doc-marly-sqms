<?php
require 'config/database.php';
$db = new Database();
$conn = $db->getConnection();
$tables = $conn->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
foreach ($tables as $table) {
    echo "TABLE: $table\n";
    $columns = $conn->query("DESCRIBE $table")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $col) {
        echo "  " . $col['Field'] . " - " . $col['Type'] . "\n";
    }
    echo "\n";
}
