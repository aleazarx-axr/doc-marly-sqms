<?php
require_once __DIR__ . '/config/database.php';

$db = new Database();
$conn = $db->getConnection();

if($conn) {
    $sql = "CREATE TABLE IF NOT EXISTS service_steps (
        id INT AUTO_INCREMENT PRIMARY KEY,
        service_id INT NOT NULL,
        step_order INT NOT NULL,
        step_name VARCHAR(255) NOT NULL,
        FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE
    )";

    try {
        $conn->exec($sql);
        echo "Service Steps table created successfully!";
    } catch(PDOException $e) {
        echo "Error creating table: " . $e->getMessage();
    }
} else {
    echo "Could not connect to database.";
}
?>
