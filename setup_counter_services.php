<?php
require_once __DIR__ . '/config/database.php';

$db = new Database();
$conn = $db->getConnection();

if($conn) {
    $sql = "CREATE TABLE IF NOT EXISTS counter_services (
        id INT AUTO_INCREMENT PRIMARY KEY,
        counter_id INT NOT NULL,
        service_id INT NOT NULL,
        FOREIGN KEY (counter_id) REFERENCES counters(id) ON DELETE CASCADE,
        FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE,
        UNIQUE KEY unique_counter_service (counter_id, service_id)
    )";

    try {
        $conn->exec($sql);
        echo "Counter Services table created successfully!";
    } catch(PDOException $e) {
        echo "Error creating table: " . $e->getMessage();
    }
} else {
    echo "Could not connect to database.";
}
?>
