<?php
require 'config/database.php';
$db = new Database();
$conn = $db->getConnection();
try {
    $conn->query('ALTER TABLE counters ADD COLUMN is_archived TINYINT(1) DEFAULT 0');
    echo 'success';
} catch (Exception $e) {
    echo 'error or already exists: ' . $e->getMessage();
}
