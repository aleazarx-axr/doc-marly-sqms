<?php
require_once __DIR__ . '/config/database.php';

$db = new Database();
$conn = $db->getConnection();

if($conn) {
    $sql = "CREATE TABLE IF NOT EXISTS counters (
        id INT AUTO_INCREMENT PRIMARY KEY,
        site_id INT NOT NULL,
        name VARCHAR(255) NOT NULL,
        status ENUM('active', 'inactive') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (site_id) REFERENCES sites(id) ON DELETE CASCADE
    )";

    try {
        $conn->exec($sql);
        echo "Counters table created successfully!";
    } catch(PDOException $e) {
        echo "Error creating table: " . $e->getMessage();
    }
} else {
    echo "Could not connect to database.";
}
?>
