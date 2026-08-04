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

// Build activeData mapping ticket number to an array of counter name and called_at timestamp for the speech API
foreach ($counters as $counter) {
    if (isset($activeTicketsByCounter[$counter['id']])) {
        $ticket = $activeTicketsByCounter[$counter['id']];
        $activeData[$ticket['ticket_number']] = [
            'counterName' => $counter['name'],
            'calledAt' => $ticket['called_at']
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="refresh" content="5">
    <title>Live Queue Grid - Doc Marly SQMS</title>
    <link rel="icon" type="image/png" href="assets/images/marly1.ico">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 6 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <style>
        * {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 20px;
            background: transparent;
            min-height: 100vh;
        }

        /* ─── Grid Layout - Large for Elders ──────────────── */
        .queue-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
            padding: 4px;
        }

        /* ─── Counter Card - High Contrast ─────────────────── */
        .counter-card {
            background: #ffffff;
            border: 3px solid #d0d0d0;
            border-radius: 20px;
            padding: 28px 20px 24px;
            text-align: center;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            min-height: 200px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }

        .counter-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: #b0b0b0;
            transition: all 0.3s ease;
        }

        /* Active Card - Solid Green */
        .counter-card.active::before {
            background: #2d7d46;
            height: 8px;
        }

        .counter-card.active {
            border-color: #2d7d46;
            box-shadow: 0 8px 40px rgba(45, 125, 70, 0.2);
        }

        /* Called Card - Solid Orange/Amber */
        .counter-card.called {
            border-color: #d4872a;
            background: #fffcf5;
            box-shadow: 0 8px 40px rgba(212, 135, 42, 0.2);
            animation: pulse-called 1.5s ease-in-out infinite;
        }

        .counter-card.called::before {
            background: #d4872a;
            height: 8px;
        }

        /* Open Card - Muted Gray */
        .counter-card.open {
            border-color: #c8c8c8;
            opacity: 0.6;
            background: #f5f5f5;
        }

        .counter-card.open .ticket-number {
            color: #999999;
        }

        .counter-card.open .counter-name {
            color: #888888;
        }

        /* ─── Counter Name - Large & Bold ──────────────────── */
        .counter-name {
            font-size: 1.2rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: #2c3e50;
            margin-bottom: 8px;
        }

        .counter-name i {
            margin-right: 10px;
            font-size: 1rem;
            color: #4a6fa5;
        }

        /* ─── Ticket Number - EXTRA LARGE & SOLID ──────────── */
        .ticket-number {
            font-size: 5.5rem;
            font-weight: 900;
            line-height: 1;
            color: #1a1a2e;
            letter-spacing: -2px;
            margin: 8px 0;
            text-shadow: none;
        }

        .ticket-number .prefix {
            font-weight: 700;
            opacity: 0.5;
            font-size: 0.5em;
            margin-right: 4px;
            color: #4a6fa5;
        }

        .ticket-number .number {
            color: #1a1a2e;
        }

        /* Solid colors - NO gradients */
        .counter-card.active .ticket-number .number {
            color: #2d7d46;
        }

        .counter-card.called .ticket-number .number {
            color: #d4872a;
        }

        .counter-card.open .ticket-number .number {
            color: #999999;
        }

        .counter-card.open .ticket-number .prefix {
            color: #999999;
        }

        /* ─── Status Badge - Large & Clear ─────────────────── */
        .status-badge {
            font-size: 0.95rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            padding: 8px 24px;
            border-radius: 50px;
            margin-top: 8px;
            border: 2px solid transparent;
        }

        .status-badge.serving {
            background: #e8f5e9;
            color: #1b5e20;
            border-color: #2d7d46;
        }

        .status-badge.called {
            background: #fff3e0;
            color: #bf6f00;
            border-color: #d4872a;
            animation: pulse-badge 1.5s ease-in-out infinite;
        }

        .status-badge.open {
            background: #f0f0f0;
            color: #757575;
            border-color: #bdbdbd;
        }

        /* ─── Icon Background ───────────────────────────────── */
        .counter-icon {
            font-size: 2.5rem;
            opacity: 0.06;
            position: absolute;
            right: 16px;
            bottom: 16px;
            color: #4a6fa5;
        }

        .counter-card.active .counter-icon {
            color: #2d7d46;
            opacity: 0.08;
        }

        .counter-card.called .counter-icon {
            color: #d4872a;
            opacity: 0.08;
        }

        /* ─── Animations ────────────────────────────────────── */
        @keyframes pulse-called {
            0%, 100% {
                transform: scale(1);
                box-shadow: 0 8px 40px rgba(212, 135, 42, 0.15);
            }
            50% {
                transform: scale(1.02);
                box-shadow: 0 8px 50px rgba(212, 135, 42, 0.25);
            }
        }

        @keyframes pulse-badge {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.6; }
        }

        /* Entrance Animation */
        .counter-card {
            animation: fadeInUp 0.5s ease backwards;
        }

        .counter-card:nth-child(1) { animation-delay: 0.02s; }
        .counter-card:nth-child(2) { animation-delay: 0.04s; }
        .counter-card:nth-child(3) { animation-delay: 0.06s; }
        .counter-card:nth-child(4) { animation-delay: 0.08s; }
        .counter-card:nth-child(5) { animation-delay: 0.10s; }
        .counter-card:nth-child(6) { animation-delay: 0.12s; }
        .counter-card:nth-child(7) { animation-delay: 0.14s; }
        .counter-card:nth-child(8) { animation-delay: 0.16s; }
        .counter-card:nth-child(9) { animation-delay: 0.18s; }
        .counter-card:nth-child(10) { animation-delay: 0.20s; }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        /* ─── High Contrast Mode for Elders ────────────────── */
        .high-contrast .counter-card {
            border-width: 4px;
            background: #ffffff;
            border-color: #000000;
        }
        .high-contrast .counter-card.active {
            border-color: #1a5c2e;
            background: #ffffff;
        }
        .high-contrast .counter-card.called {
            border-color: #b86b00;
            background: #ffffff;
        }
        .high-contrast .ticket-number .number {
            color: #000000 !important;
        }
        .high-contrast .counter-card.active .ticket-number .number {
            color: #1a5c2e !important;
        }
        .high-contrast .counter-card.called .ticket-number .number {
            color: #b86b00 !important;
        }
        .high-contrast .counter-name {
            color: #000000 !important;
        }
        .high-contrast .status-badge {
            color: #000000 !important;
        }
        .high-contrast .status-badge.serving {
            background: #d4edda;
            border-color: #1a5c2e;
            color: #1a5c2e !important;
        }
        .high-contrast .status-badge.called {
            background: #fff3cd;
            border-color: #b86b00;
            color: #b86b00 !important;
        }

        /* ─── Responsive - Even Larger on Big Screens ──────── */
        @media (min-width: 1400px) {
            .queue-grid {
                grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
                gap: 24px;
            }
            .ticket-number {
                font-size: 6.5rem;
            }
            .counter-card {
                min-height: 240px;
                padding: 32px 24px 28px;
            }
            .counter-name {
                font-size: 1.4rem;
            }
            .status-badge {
                font-size: 1.1rem;
                padding: 10px 28px;
            }
        }

        @media (max-width: 992px) {
            .queue-grid {
                grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
                gap: 16px;
            }
            .ticket-number {
                font-size: 4rem;
            }
            .counter-card {
                min-height: 170px;
                padding: 22px 16px 18px;
            }
        }

        @media (max-width: 768px) {
            body { padding: 14px; }
            .queue-grid {
                grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
                gap: 14px;
            }
            .counter-card {
                padding: 18px 14px 16px;
                min-height: 150px;
                border-radius: 16px;
            }
            .ticket-number {
                font-size: 3.2rem;
            }
            .counter-name {
                font-size: 0.95rem;
            }
            .status-badge {
                font-size: 0.8rem;
                padding: 6px 18px;
            }
        }

        @media (max-width: 480px) {
            body { padding: 10px; }
            .queue-grid {
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
                gap: 10px;
            }
            .counter-card {
                padding: 14px 10px 12px;
                min-height: 130px;
                border-radius: 14px;
            }
            .ticket-number {
                font-size: 2.4rem;
            }
            .counter-name {
                font-size: 0.8rem;
                letter-spacing: 0.8px;
            }
            .status-badge {
                font-size: 0.65rem;
                padding: 4px 14px;
            }
            .counter-icon {
                font-size: 1.8rem;
            }
        }

        /* ─── Scrollbar ─────────────────────────────────────── */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: transparent;
        }
        ::-webkit-scrollbar-thumb {
            background: rgba(0, 0, 0, 0.15);
            border-radius: 10px;
        }

        /* ─── Print Styles ──────────────────────────────────── */
        @media print {
            body { background: #fff; }
            .counter-card {
                border: 2px solid #000 !important;
                background: #fff !important;
                box-shadow: none !important;
            }
            .ticket-number .number {
                color: #000 !important;
            }
        }
    </style>
</head>
<body>
    <div class="queue-grid">
        <?php foreach ($counters as $counter): ?>
            <?php 
                $ticket = $activeTicketsByCounter[$counter['id']] ?? null;
                $isActive = $ticket !== null;
                $status = $ticket['status'] ?? 'open';
                $cardClass = $isActive ? ($status === 'called' ? 'called' : 'active') : 'open';
                
                // Extract prefix and number
                $ticketNumber = $ticket['ticket_number'] ?? '';
                $prefix = '';
                $number = '';
                if (!empty($ticketNumber) && preg_match('/^([A-Z]+)(\d+)$/', $ticketNumber, $matches)) {
                    $prefix = $matches[1];
                    $number = $matches[2];
                } else {
                    $number = $ticketNumber;
                }
            ?>
            <div class="counter-card <?= $cardClass ?>">
                <!-- Background Icon -->
                <div class="counter-icon">
                    <i class="fas <?= $isActive ? 'fa-user-check' : 'fa-door-open' ?>"></i>
                </div>

                <!-- Counter Name -->
                <div class="counter-name">
                    <i class="fas fa-desktop"></i>
                    <?= htmlspecialchars($counter['name']) ?>
                </div>

                <!-- Ticket Number - SOLID COLORS, NO GRADIENTS -->
                <div class="ticket-number">
                    <?php if ($isActive): ?>
                        <?php if (!empty($prefix)): ?>
                            <span class="prefix"><?= htmlspecialchars($prefix) ?></span>
                        <?php endif; ?>
                        <span class="number"><?= htmlspecialchars($number) ?></span>
                    <?php else: ?>
                        <span class="number" style="font-size:0.4em; font-weight:400; opacity:0.3;">—</span>
                    <?php endif; ?>
                </div>

                <!-- Status Badge -->
                <div class="status-badge <?= $status ?>">
                    <i class="fas <?= $isActive ? ($status === 'serving' ? 'fa-check-circle' : 'fa-bell') : 'fa-hourglass' ?> me-2"></i>
                    <?= $isActive ? ucfirst($status) : 'Available' ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <script>
        // ─── Speech Announcements ────────────────────────────
        const currentActiveData = <?= json_encode($activeData) ?>;
        const previousActiveData = JSON.parse(localStorage.getItem('sqms_last_active_data') || '{}');
        
        let newAnnouncements = [];
        
        for (const [ticketNumber, data] of Object.entries(currentActiveData)) {
            const prevData = previousActiveData[ticketNumber];
            if (!prevData || prevData.calledAt !== data.calledAt) {
                const spokenTicket = ticketNumber.replace(/([A-Z]+)(\d+)/, (match, prefix, num) => {
                    return prefix + ' ' + num.split('').join(' ');
                });
                newAnnouncements.push(`Ticket number ${spokenTicket}, please proceed to ${data.counterName}.`);
            }
        }
        
        if (newAnnouncements.length > 0) {
            window.parent.postMessage({ type: 'speak', text: newAnnouncements.join(' ') }, '*');
        }
        
        localStorage.setItem('sqms_last_active_data', JSON.stringify(currentActiveData));

        // ─── Animate New Tickets ─────────────────────────────
        document.querySelectorAll('.counter-card.active, .counter-card.called').forEach((card, index) => {
            card.style.animationDelay = (index * 0.05) + 's';
        });

        // ─── High Contrast Toggle ────────────────────────────
        // Double-click anywhere to toggle high contrast mode
        document.addEventListener('dblclick', () => {
            document.body.classList.toggle('high-contrast');
        });

        // ─── Show hint for high contrast mode ────────────────
        if (!sessionStorage.getItem('contrast-hint-shown')) {
            console.log('💡 Double-click anywhere to toggle High Contrast mode for better visibility!');
            sessionStorage.setItem('contrast-hint-shown', 'true');
        }
    </script>
</body>
</html>