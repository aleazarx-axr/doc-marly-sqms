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
        $opacity = !$canCall ? 'opacity: 0.5;' : '';
        $bg = $canCall ? '#242364' : '#94a3b8';
        
        echo '<tr>';
        echo '<td style="width: 20%; min-width: 120px; white-space: nowrap; padding:12px 16px; vertical-align:middle; ' . $opacity . '"><span style="display: inline-block; min-width: 80px; text-align: center; white-space: nowrap; word-break: keep-all; background: ' . $bg . '; color: #fff; padding: 6px 12px; border-radius: 6px; font-weight: 700; font-family: monospace; font-size: 0.9rem;">' . htmlspecialchars($ticket['ticket_number']) . '</span></td>';
        echo '<td style="width: 35%; padding:12px 16px; vertical-align:middle; font-weight: 700; color: #1e293b; ' . $opacity . '">' . htmlspecialchars(!empty($ticket['name']) ? $ticket['name'] : $ticket['citizen_category']) . '</td>';
        echo '<td style="width: 25%; padding:12px 16px; vertical-align:middle; color: #64748b; ' . $opacity . '"><i class="bi bi-tag me-1"></i> ' . htmlspecialchars($ticket['service_name']) . '</td>';
        
        if ($canCall) {
            echo '<td style="width: 20%; padding:12px 16px; vertical-align:middle; ' . $opacity . '"><span style="background: rgba(59, 130, 246, 0.1); color: #3b82f6; padding: 4px 8px; border-radius: 6px; font-size: 0.75rem; font-weight: 700; white-space: nowrap;">Matching</span></td>';
        } else {
            echo '<td style="width: 20%; padding:12px 16px; vertical-align:middle; ' . $opacity . '"><span style="background: rgba(100, 116, 139, 0.1); color: #64748b; padding: 4px 8px; border-radius: 6px; font-size: 0.75rem; font-weight: 700; white-space: nowrap;">Other Counter</span></td>';
        }
        echo '</tr>';
    }
} else {
    echo '<tr><td colspan="4"><div style="text-align: center; padding: 60px 0;"><i class="bi bi-clipboard2-check text-muted" style="font-size: 3rem; opacity: 0.3;"></i><p style="color: #64748b; margin-top: 16px; font-weight: 700;">No tickets in queue</p></div></td></tr>';
}
