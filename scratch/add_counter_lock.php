<?php
require 'config/database.php';
$db = new Database();
$conn = $db->getConnection();

try {
    $conn->exec("ALTER TABLE counters ADD COLUMN current_staff_id INT DEFAULT NULL");
    echo "Column current_staff_id added successfully.\n";
    
    // Add foreign key constraint to users table
    $conn->exec("ALTER TABLE counters ADD CONSTRAINT fk_current_staff FOREIGN KEY (current_staff_id) REFERENCES users(id) ON DELETE SET NULL");
    echo "Foreign key constraint added successfully.\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
