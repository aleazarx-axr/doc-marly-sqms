<?php
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/models/Ticket.php';
require_once __DIR__ . '/../../includes/models/Counter.php';

// Check login silently
if (!Session::isLoggedIn()) {
    echo '<tr><td colspan="3">Session expired. Please reload.</td></tr>';
    exit();
}

$userId = Session::get('user_id');
$db = new Database();
$conn = $db->getConnection();
$ticketModel = new Ticket($conn);
$counterModel = new Counter($conn);

// Find active counter
$staffCounters = $counterModel->getCountersByStaff($userId);
$currentCounter = null;

if (!empty($staffCounters)) {
    if (isset($_SESSION['active_counter_id'])) {
        foreach ($staffCounters as $c) {
            if ($c['id'] == $_SESSION['active_counter_id']) {
                $currentCounter = $c;
                break;
            }
        }
    }
    if (!$currentCounter && empty($_SESSION['auto_lock_disabled'])) {
        foreach ($staffCounters as $c) {
            if ($c['current_staff_id'] === null || $c['current_staff_id'] == $userId) {
                $currentCounter = $c;
                break;
            }
        }
    }
}

if (!$currentCounter) {
    echo '<tr><td colspan="3">No active counter found.</td></tr>';
    exit();
}

$serviceIds = $counterModel->getCounterServices($currentCounter['id']);
$waitingList = $ticketModel->getWaitingList(null);

if (!empty($waitingList)) {
    foreach ($waitingList as $ticket) {
        $canCall = in_array($ticket['service_id'], $serviceIds);
        echo '<tr style="' . (!$canCall ? 'opacity: 0.5; background-color: #f8f9fa;' : '') . '">';
        echo '<td><strong>' . htmlspecialchars($ticket['ticket_number']) . '</strong></td>';
        echo '<td>' . htmlspecialchars($ticket['name'] ?? $ticket['citizen_category']) . '</td>';
        echo '<td>' . htmlspecialchars($ticket['service_name']) . '</td>';
        echo '</tr>';
    }
} else {
    echo '<tr><td colspan="3">No tickets in queue</td></tr>';
}
