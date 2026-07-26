<?php
require_once __DIR__ . '/../config/database.php';

$db = new Database();
$conn = $db->getConnection();

try {
    // Add composite index for finding next ticket and waiting lists
    $conn->exec("ALTER TABLE tickets ADD INDEX idx_status_service_issued (status, service_id, issued_at)");
    echo "Index idx_status_service_issued added successfully.\n";
} catch (Exception $e) {
    echo "Notice: " . $e->getMessage() . "\n";
}

try {
    // Add index for finding the daily ticket number 
    $conn->exec("ALTER TABLE tickets ADD INDEX idx_service_created (service_id, created_at)");
    echo "Index idx_service_created added successfully.\n";
} catch (Exception $e) {
    echo "Notice: " . $e->getMessage() . "\n";
}

echo "Database indexing complete.\n";
