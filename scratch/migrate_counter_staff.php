<?php
require 'config/database.php';
$db = new Database();
$conn = $db->getConnection();

try {
    $conn->beginTransaction();

    // 1. Create counter_staff table
    $sql = "CREATE TABLE IF NOT EXISTS `counter_staff` (
        `counter_id` int NOT NULL,
        `staff_id` int NOT NULL,
        PRIMARY KEY (`counter_id`, `staff_id`),
        FOREIGN KEY (`counter_id`) REFERENCES `counters`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`staff_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci";
    $conn->exec($sql);
    echo "Created counter_staff table.\n";

    // 2. Migrate existing staff_id from counters
    $stmt = $conn->query("SELECT id, staff_id FROM counters WHERE staff_id IS NOT NULL");
    $counters = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $insertStmt = $conn->prepare("INSERT IGNORE INTO counter_staff (counter_id, staff_id) VALUES (?, ?)");
    foreach ($counters as $c) {
        $insertStmt->execute([$c['id'], $c['staff_id']]);
    }
    echo "Migrated existing staff assignments.\n";

    $conn->commit();
    echo "Migration completed successfully!\n";
} catch (Exception $e) {
    $conn->rollBack();
    echo "Migration failed: " . $e->getMessage() . "\n";
}
