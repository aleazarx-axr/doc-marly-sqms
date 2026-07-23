<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/models/Ticket.php';

$db = new Database();
$conn = $db->getConnection();
$ticketModel = new Ticket($conn);

// Fetch all active counters
$queryCounters = "SELECT id, name FROM counters WHERE is_archived = 0 ORDER BY name ASC";
$stmtCounters = $conn->query($queryCounters);
$counters = $stmtCounters->fetchAll(PDO::FETCH_ASSOC);

// Fetch "Now Serving" & "Called" tickets
$queryActive = "
    SELECT t.* 
    FROM tickets t
    WHERE t.status IN ('called', 'serving')
    AND t.id = (
        SELECT id FROM tickets t2 
        WHERE t2.counter_id = t.counter_id 
        AND t2.status IN ('called', 'serving')
        ORDER BY t2.issued_at ASC LIMIT 1
    )
";
$stmtActive = $conn->query($queryActive);
$activeTicketsList = $stmtActive->fetchAll(PDO::FETCH_ASSOC);

$activeTicketsByCounter = [];
$activeData = [];
foreach ($activeTicketsList as $t) {
    $activeTicketsByCounter[$t['counter_id']] = $t;
}

// Build activeData mapping ticket number to counter name for the speech API
foreach ($counters as $counter) {
    if (isset($activeTicketsByCounter[$counter['id']])) {
        $ticket = $activeTicketsByCounter[$counter['id']];
        $activeData[$ticket['ticket_number']] = $counter['name'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="refresh" content="5">
    <title>Live Queue Grid</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: sans-serif;
            background: transparent;
        }
        .now-serving {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
            align-content: start;
        }
        .ticket-card {
            border: 1px solid #000;
            padding: 20px;
            text-align: center;
            background: #fff;
        }
        .ticket-card .counter-name {
            font-size: 20px;
            font-weight: bold;
        }
        .ticket-card .ticket-number {
            font-size: 48px;
            font-weight: bold;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="now-serving">
        <?php foreach ($counters as $counter): ?>
            <?php $ticket = $activeTicketsByCounter[$counter['id']] ?? null; ?>
            <div class="ticket-card">
                <div class="counter-name"><?= htmlspecialchars($counter['name']) ?></div>
                
                <?php if ($ticket): ?>
                    <div class="ticket-number">
                        <?= htmlspecialchars($ticket['ticket_number']) ?>
                    </div>
                <?php else: ?>
                    <div class="ticket-number">
                        OPEN
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
    
    <script>
        const currentActiveData = <?= json_encode($activeData) ?>;
        const previousActiveData = JSON.parse(localStorage.getItem('sqms_last_active_data') || '{}');
        
        let newAnnouncements = [];
        
        for (const [ticketNumber, counterName] of Object.entries(currentActiveData)) {
            if (!previousActiveData[ticketNumber]) {
                newAnnouncements.push(`Ticket number ${ticketNumber.replace('-', ' ')}, please proceed to ${counterName}.`);
            }
        }
        
        if (newAnnouncements.length > 0) {
            window.parent.postMessage({ type: 'speak', text: newAnnouncements.join(' ') }, '*');
        }
        
        localStorage.setItem('sqms_last_active_data', JSON.stringify(currentActiveData));
    </script>
</body>
</html>
