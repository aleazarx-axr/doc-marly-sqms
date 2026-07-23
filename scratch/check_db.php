<?php
require_once __DIR__ . '/../config/database.php';

$db = new Database();
$conn = $db->getConnection();

function showColumns($conn, $table) {
    echo "=== $table ===\n";
    $stmt = $conn->query("DESCRIBE $table");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo $row['Field'] . " | " . $row['Type'] . "\n";
    }
}

showColumns($conn, 'services');
showColumns($conn, 'counters');
showColumns($conn, 'counter_services');
try {
    showColumns($conn, 'tickets');
} catch (Exception $e) { echo "tickets table does not exist\n"; }
