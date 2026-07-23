<?php
require 'config/database.php';

$db = new Database();
$conn = $db->getConnection();

try {
    $conn->exec("ALTER TABLE tickets ADD COLUMN name VARCHAR(255) NULL DEFAULT NULL AFTER id");
    echo "Successfully added 'name' column to tickets table.\n";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "'name' column already exists in tickets table.\n";
    } else {
        echo "Error: " . $e->getMessage() . "\n";
    }
}
?>
