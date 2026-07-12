<?php
require_once __DIR__ . '/config/database.php';

$db = new Database();
$conn = $db->getConnection();

if($conn) {
    try {
        // Drop queue_sessions since we will recreate it to fix the foreign key name
        $conn->exec("DROP TABLE IF EXISTS queue_sessions");
        
        // Rename venues table to sites if venues exists
        $check = $conn->query("SHOW TABLES LIKE 'venues'");
        if ($check->rowCount() > 0) {
            $conn->exec("RENAME TABLE venues TO sites");
            echo "Renamed venues to sites.\n";
        }
        
        // Create queue_sessions referencing sites
        $sql_sessions = "CREATE TABLE IF NOT EXISTS queue_sessions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            site_id INT NOT NULL,
            service_id INT NULL COMMENT 'NULL for office (all services), specific ID for offsite (single service)',
            status ENUM('active', 'closed') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            closed_at TIMESTAMP NULL,
            FOREIGN KEY (site_id) REFERENCES sites(id) ON DELETE CASCADE,
            FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE SET NULL
        )";
        $conn->exec($sql_sessions);
        echo "Recreated queue_sessions referencing sites.\n";
        
    } catch(PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
} else {
    echo "Could not connect to database.";
}
?>
