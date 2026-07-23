<?php
require_once __DIR__ . '/../config/database.php';

$db = new Database();
$conn = $db->getConnection();

try {
    echo "Starting migration...\n";

    // 1. Drop foreign keys referencing `sites`
    // Function to safely drop a foreign key
    $dropFk = function($table, $referencedTable) use ($conn) {
        $sql = "SELECT CONSTRAINT_NAME 
                FROM information_schema.KEY_COLUMN_USAGE 
                WHERE TABLE_SCHEMA = DATABASE() 
                  AND TABLE_NAME = '$table' 
                  AND REFERENCED_TABLE_NAME = '$referencedTable'";
        $stmt = $conn->query($sql);
        $fks = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($fks as $fk) {
            $name = $fk['CONSTRAINT_NAME'];
            $conn->exec("ALTER TABLE `$table` DROP FOREIGN KEY `$name`");
            echo "Dropped FK $name from $table\n";
        }
    };

    $dropFk('tickets', 'sites');
    $dropFk('queue_sessions', 'sites');
    $dropFk('counters', 'sites');
    $dropFk('site_services', 'sites');
    $dropFk('site_services', 'services');

    // 2. Drop tables
    $conn->exec("DROP TABLE IF EXISTS site_services");
    echo "Dropped site_services table\n";
    $conn->exec("DROP TABLE IF EXISTS sites");
    echo "Dropped sites table\n";

    // 3. Drop columns safely (one by one and ignore if column doesn't exist to prevent errors, using try-catch)
    $columnsToDrop = [
        'tickets' => ['site_id', 'queue_type', 'field_schedule_id'],
        'queue_sessions' => ['site_id'],
        'counters' => ['site_id', 'location_type'],
        'services' => ['queue_type']
    ];

    foreach ($columnsToDrop as $table => $columns) {
        foreach ($columns as $column) {
            try {
                $conn->exec("ALTER TABLE `$table` DROP COLUMN `$column`");
                echo "Dropped column $column from $table\n";
            } catch (Exception $e) {
                // Column might not exist, ignore
                echo "Could not drop column $column from $table (might not exist)\n";
            }
        }
    }

    echo "Migration completed successfully!\n";
} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
}
