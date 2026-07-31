<?php
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/database.php';

if (!Session::isLoggedIn() || Session::get('role') !== 'information_staff') {
    echo '<tr><td colspan="4">Session expired or unauthorized.</td></tr>';
    exit();
}

$db = new Database();
$conn = $db->getConnection();

$session_start = getQueueSessionStart($conn);
$stmt = $conn->prepare("SELECT t.ticket_number, s.name as service_name, t.status, c.name as counter_name 
                        FROM tickets t 
                        LEFT JOIN services s ON t.service_id = s.id 
                        LEFT JOIN counters c ON t.counter_id = c.id 
                        WHERE t.created_at >= ?
                        ORDER BY t.issued_at DESC LIMIT 10");
$stmt->execute([$session_start]);
$recentTickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!empty($recentTickets)) {
    foreach ($recentTickets as $ticket) {
        $statusClass = '';
        $statusLabel = ucfirst(htmlspecialchars($ticket['status']));
        if ($ticket['status'] === 'waiting') {
            $statusClass = 'waiting';
        } elseif ($ticket['status'] === 'called') {
            $statusClass = 'called';
        } elseif ($ticket['status'] === 'serving') {
            $statusClass = 'serving';
        } elseif ($ticket['status'] === 'done') {
            $statusClass = 'done';
        } else {
            $statusClass = 'issued';
        }

        echo '<tr>';
        echo '<td style="padding:12px 16px; vertical-align:middle;"><span class="ticket-number">' . htmlspecialchars($ticket['ticket_number']) . '</span></td>';
        echo '<td style="padding:12px 16px; vertical-align:middle; color:#495057;">' . htmlspecialchars($ticket['service_name']) . '</td>';
        echo '<td style="padding:12px 16px; vertical-align:middle;"><span class="ticket-status-badge ' . $statusClass . '">' . $statusLabel . '</span></td>';
        echo '</tr>';
    }
} else {
    echo '<tr><td colspan="3"><div class="queue-empty"><div style="font-size:32px; margin-bottom:8px;">📭</div>No active queue</div></td></tr>';
}
