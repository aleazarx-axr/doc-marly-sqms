<?php
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/database.php';

if (!Session::isLoggedIn() || Session::get('role') !== 'information_staff') {
    echo '<tr><td colspan="4">Session expired or unauthorized.</td></tr>';
    exit();
}

$db = new Database();
$conn = $db->getConnection();

$stmt = $conn->query("SELECT t.ticket_number, s.name as service_name, t.status, c.name as counter_name FROM tickets t LEFT JOIN services s ON t.service_id = s.id LEFT JOIN counters c ON t.counter_id = c.id ORDER BY t.issued_at DESC LIMIT 10");
$recentTickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!empty($recentTickets)) {
    foreach ($recentTickets as $ticket) {
        echo '<tr>';
        echo '<td><strong>' . htmlspecialchars($ticket['ticket_number']) . '</strong></td>';
        echo '<td>' . htmlspecialchars($ticket['service_name']) . '</td>';
        echo '<td>' . ucfirst(htmlspecialchars($ticket['status'])) . '</td>';
        echo '<td>' . ($ticket['counter_name'] ? htmlspecialchars($ticket['counter_name']) : '-') . '</td>';
        echo '</tr>';
    }
} else {
    echo '<tr><td colspan="4">No active queue.</td></tr>';
}
