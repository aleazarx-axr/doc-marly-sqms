<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/models/Ticket.php';
require_once __DIR__ . '/../includes/models/Setting.php';
require_once __DIR__ . '/../includes/functions.php';

$db = new Database();
$conn = $db->getConnection();
$settingModel = new Setting($conn);
$ticketModel = new Ticket($conn);

$now = date('Y-m-d H:i:s');
$settingModel->updateMultiple(['last_reset_time' => $now]);
$ticketModel->expireOldTickets($now);

echo "Forced reset successfully. Session start is now: " . $now;
