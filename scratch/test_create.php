<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/models/Ticket.php';
require_once __DIR__ . '/../includes/functions.php';

$db = new Database();
$conn = $db->getConnection();
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$ticketModel = new Ticket($conn);

try {
    $res = $ticketModel->createTicket('Test Citizen', 4, 'Regular');
    if ($res) {
        echo "Success: $res";
    } else {
        echo "Failed without exception.";
    }
} catch (PDOException $e) {
    echo "PDO Error: " . $e->getMessage();
}
