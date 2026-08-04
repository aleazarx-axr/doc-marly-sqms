<?php
// Main display page - No refresh here!
// Simple API to get latest videos without refreshing the page
if (isset($_GET['api']) && $_GET['api'] === 'videos') {
    $videoDir = __DIR__ . '/assets/videos';
    $videos = [];
    if (is_dir($videoDir)) {
        $files = scandir($videoDir);
        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'mp4') {
                $mtime = filemtime($videoDir . '/' . $file);
                $videos[] = '/assets/videos/' . $file . '?v=' . $mtime;
            }
        }
    }
    if (empty($videos)) {
        $videos[] = 'https://www.w3schools.com/html/mov_bbb.mp4';
    }
    header('Content-Type: application/json');
    echo json_encode($videos);
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live Queue Display - Doc Marly SQMS</title>
    <link rel="icon" type="image/png" href="assets/images/marly1.ico">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 6 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <style>
        * {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            height: 100vh;
            overflow: hidden;
            background: #0a0e17;
        }

        /* ─── Full-Screen Video Background ─────────────────── */
        .video-background {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            background: #000;
        }

        .video-background video {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .video-background::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(180deg, 
                rgba(10, 14, 23, 0.2) 0%,
                rgba(10, 14, 23, 0.4) 40%,
                rgba(10, 14, 23, 0.7) 70%,
                rgba(10, 14, 23, 0.95) 100%
            );
            pointer-events: none;
        }

        /* ─── Video Controls ────────────────────────────────── */
        .bg-video-controls {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 5;
            display: flex;
            gap: 12px;
            opacity: 0.3;
            transition: opacity 0.3s;
        }

        .bg-video-controls:hover {
            opacity: 1;
        }

        .bg-video-controls button {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: rgba(255, 255, 255, 0.8);
            width: 44px;
            height: 44px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
            font-size: 1.1rem;
            cursor: pointer;
        }

        .bg-video-controls button:hover {
            background: rgba(255, 255, 255, 0.25);
            color: #fff;
            transform: scale(1.1);
        }

        /* ─── Overlay Content ──────────────────────────────── */
        .overlay-content {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            z-index: 1;
            padding: 30px 40px 40px;
            pointer-events: none;
        }

        .overlay-content > * {
            pointer-events: auto;
        }

        /* ─── Header ────────────────────────────────────────── */
        .app-header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 2;
            padding: 20px 40px;
            background: linear-gradient(180deg, rgba(10, 14, 23, 0.85) 0%, transparent 100%);
            backdrop-filter: blur(10px);
            display: flex;
            justify-content: space-between;
            align-items: center;
            pointer-events: none;
        }

        .app-header > * {
            pointer-events: auto;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 18px;
        }

        .brand-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #0d6efd, #0dcaf0);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            color: #fff;
            box-shadow: 0 4px 30px rgba(13, 110, 253, 0.4);
        }

        .brand-text h1 {
            font-size: 2rem;
            font-weight: 800;
            margin: 0;
            color: #fff;
            letter-spacing: -0.5px;
            text-shadow: 0 2px 30px rgba(0, 0, 0, 0.5);
        }

        .brand-text .subtitle {
            font-size: 1rem;
            color: rgba(255, 255, 255, 0.6);
            font-weight: 400;
            letter-spacing: 0.5px;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        }

        .datetime-display {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            padding: 12px 28px;
            border-radius: 50px;
            border: 1px solid rgba(255, 255, 255, 0.15);
            font-size: 1.2rem;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.9);
            display: flex;
            align-items: center;
            gap: 12px;
            letter-spacing: 0.3px;
        }

        .datetime-display i {
            color: #0dcaf0;
            font-size: 1.1rem;
        }

        /* ─── Queue Container - LARGE ──────────────────────── */
        .queue-container {
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(40px);
            border-radius: 32px;
            border: 2px solid rgba(255, 255, 255, 0.12);
            padding: 30px 35px 35px;
            box-shadow: 0 12px 80px rgba(0, 0, 0, 0.6);
            max-width: 1400px;
            margin: 0 auto;
            width: 100%;
        }

        .queue-container .section-title {
            font-size: 1rem;
            text-transform: uppercase;
            letter-spacing: 3px;
            color: rgba(255, 255, 255, 0.5);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 14px;
            font-weight: 600;
        }

        .queue-container .section-title i {
            color: #0dcaf0;
            font-size: 1.2rem;
        }

        .queue-container .section-title::after {
            content: '';
            flex: 1;
            height: 2px;
            background: linear-gradient(90deg, rgba(255, 255, 255, 0.15), transparent);
        }

        /* ─── Queue Grid - LARGE CARDS ────────────────────── */
        .queue-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
        }

        /* ─── Counter Card - LARGE ────────────────────────── */
        .counter-card {
            background: rgba(255, 255, 255, 0.07);
            border-radius: 24px;
            padding: 28px 20px 24px;
            text-align: center;
            border: 2px solid rgba(255, 255, 255, 0.06);
            transition: all 0.4s ease;
            position: relative;
            overflow: hidden;
            min-height: 200px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .counter-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #0d6efd, #0dcaf0, #198754);
            opacity: 0;
            transition: opacity 0.4s ease;
        }

        .counter-card.active::before {
            opacity: 1;
        }

        .counter-card.active {
            background: rgba(255, 255, 255, 0.12);
            border-color: rgba(13, 110, 253, 0.25);
            box-shadow: 0 8px 50px rgba(13, 110, 253, 0.1);
        }

        .counter-card.open {
            opacity: 0.5;
        }

        .counter-card.open .ticket-number {
            color: rgba(255, 255, 255, 0.15);
        }

        .counter-name {
            font-size: 1.1rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: rgba(255, 255, 255, 0.6);
            margin-bottom: 8px;
        }

        .counter-name i {
            margin-right: 8px;
            font-size: 1rem;
        }

        .ticket-number {
            font-size: 5rem;
            font-weight: 900;
            line-height: 1.1;
            color: #fff;
            letter-spacing: -2px;
            margin: 6px 0;
            text-shadow: 0 4px 40px rgba(13, 110, 253, 0.15);
        }

        .ticket-number .prefix {
            font-weight: 700;
            opacity: 0.5;
            font-size: 0.5em;
            margin-right: 4px;
        }

        .ticket-number .number {
            background: linear-gradient(135deg, #fff 40%, #0dcaf0);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .counter-card.open .ticket-number .number {
            -webkit-text-fill-color: rgba(255, 255, 255, 0.15);
        }

        .status-badge {
            font-size: 0.9rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            padding: 6px 20px;
            border-radius: 50px;
            margin-top: 8px;
        }

        .status-badge.serving {
            background: rgba(25, 135, 84, 0.25);
            color: #75b798;
            border: 2px solid rgba(25, 135, 84, 0.2);
        }

        .status-badge.called {
            background: rgba(255, 193, 7, 0.2);
            color: #ffda6a;
            border: 2px solid rgba(255, 193, 7, 0.15);
            animation: pulse-call 2s ease-in-out infinite;
        }

        .status-badge.open {
            background: rgba(255, 255, 255, 0.05);
            color: rgba(255, 255, 255, 0.3);
            border: 2px solid rgba(255, 255, 255, 0.05);
        }

        @keyframes pulse-call {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        .counter-icon-bg {
            position: absolute;
            right: 16px;
            bottom: 16px;
            font-size: 3rem;
            opacity: 0.06;
            color: #0dcaf0;
        }

        .counter-card.active .counter-icon-bg {
            opacity: 0.04;
        }

        /* ─── Responsive - Maintain LARGE sizes ────────────── */
        @media (max-width: 1200px) {
            .queue-grid {
                grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
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

        @media (max-width: 992px) {
            .overlay-content {
                padding: 20px 24px 30px;
            }
            .app-header {
                padding: 16px 24px;
            }
            .brand-text h1 {
                font-size: 1.6rem;
            }
            .brand-icon {
                width: 48px;
                height: 48px;
                font-size: 22px;
            }
            .datetime-display {
                font-size: 1rem;
                padding: 10px 20px;
            }
            .queue-container {
                padding: 24px 28px 28px;
                border-radius: 24px;
            }
            .queue-grid {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
                gap: 14px;
            }
            .ticket-number {
                font-size: 3.4rem;
            }
            .counter-card {
                min-height: 150px;
                padding: 18px 14px 16px;
                border-radius: 20px;
            }
            .counter-name {
                font-size: 0.9rem;
            }
            .status-badge {
                font-size: 0.75rem;
                padding: 4px 16px;
            }
        }

        @media (max-width: 768px) {
            .app-header {
                padding: 12px 16px;
                flex-wrap: wrap;
                gap: 10px;
            }
            .brand-text h1 {
                font-size: 1.2rem;
            }
            .brand-text .subtitle {
                font-size: 0.75rem;
            }
            .brand-icon {
                width: 40px;
                height: 40px;
                font-size: 18px;
                border-radius: 12px;
            }
            .datetime-display {
                font-size: 0.8rem;
                padding: 6px 14px;
            }
            .datetime-display i {
                display: none;
            }
            .overlay-content {
                padding: 12px 12px 16px;
            }
            .queue-container {
                padding: 16px 14px 18px;
                border-radius: 18px;
            }
            .queue-container .section-title {
                font-size: 0.75rem;
                margin-bottom: 12px;
            }
            .queue-grid {
                grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
                gap: 10px;
            }
            .ticket-number {
                font-size: 2.6rem;
            }
            .counter-card {
                min-height: 120px;
                padding: 14px 10px 12px;
                border-radius: 16px;
            }
            .counter-name {
                font-size: 0.7rem;
            }
            .status-badge {
                font-size: 0.6rem;
                padding: 3px 12px;
            }
            .counter-icon-bg {
                font-size: 2rem;
            }
            .bg-video-controls {
                bottom: 12px;
                right: 12px;
                gap: 8px;
            }
            .bg-video-controls button {
                width: 36px;
                height: 36px;
                font-size: 0.85rem;
            }
        }

        @media (max-width: 480px) {
            .queue-grid {
                grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
                gap: 8px;
            }
            .ticket-number {
                font-size: 2rem;
            }
            .counter-card {
                min-height: 100px;
                padding: 10px 8px 10px;
                border-radius: 14px;
            }
            .counter-name {
                font-size: 0.6rem;
                letter-spacing: 0.5px;
            }
            .status-badge {
                font-size: 0.5rem;
                padding: 2px 10px;
            }
        }

        /* ─── Animations ────────────────────────────────────── */
        .counter-card {
            animation: fadeUp 0.5s ease backwards;
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

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(20px) scale(0.96);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .counter-card.active {
            animation: glowPulse 3s ease-in-out infinite;
        }

        @keyframes glowPulse {
            0%, 100% { box-shadow: 0 4px 30px rgba(13, 110, 253, 0.02); }
            50% { box-shadow: 0 8px 60px rgba(13, 110, 253, 0.1); }
        }

        /* ─── Scrollbar ─────────────────────────────────────── */
        ::-webkit-scrollbar {
            width: 4px;
            height: 4px;
        }
        ::-webkit-scrollbar-track {
            background: transparent;
        }
        ::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
        }
    </style>
</head>
<body>

    <!-- ─── Full Screen Video Background ────────────────── -->
    <div class="video-background" id="videoBackground">
        <video id="bgVideo" autoplay playsinline muted></video>
    </div>

    <!-- ─── Background Video Controls ────────────────────── -->
    <div class="bg-video-controls">
        <button id="bgVolume" title="Toggle Sound">
            <i class="fas fa-volume-mute"></i>
        </button>
        <button id="bgPlayPause" title="Play / Pause">
            <i class="fas fa-pause"></i>
        </button>
        <button id="bgNext" title="Next Video">
            <i class="fas fa-step-forward"></i>
        </button>
        <button id="bgPrev" title="Previous Video">
            <i class="fas fa-step-backward"></i>
        </button>
    </div>

    <!-- ─── Header ────────────────────────────────────────── -->
    <header class="app-header">
        <div class="brand">
            <div class="brand-icon">
                <i class="fas fa-queue"></i>
            </div>
            <div class="brand-text">
                <h1>Doc Marly SQMS</h1>
                <div class="subtitle"><i class="fas fa-hospital-user me-1"></i>Smart Queue Management System</div>
            </div>
        </div>
        <div class="datetime-display">
            <i class="fas fa-clock"></i>
            <span id="datetime">Loading...</span>
        </div>
    </header>

    <!-- ─── Overlay Content ──────────────────────────────── -->
    <div class="overlay-content">
        <div class="queue-container">
            <div class="section-title">
                <i class="fas fa-users"></i> Now Serving
                <span style="margin-left: auto; font-size: 0.8rem; opacity: 0.5;">
                    <i class="fas fa-sync-alt fa-fw"></i> Auto-updates every 5s
                </span>
            </div>
            <div class="queue-grid" id="queueGrid">
                <?php
                    require_once __DIR__ . '/includes/functions.php';
                    require_once __DIR__ . '/config/database.php';
                    require_once __DIR__ . '/includes/models/Ticket.php';

                    $db = new Database();
                    $conn = $db->getConnection();
                    $ticketModel = new Ticket($conn);

                    $queryCounters = "SELECT id, name FROM counters WHERE is_archived = 0 ORDER BY name ASC";
                    $stmtCounters = $conn->query($queryCounters);
                    $counters = $stmtCounters->fetchAll(PDO::FETCH_ASSOC);

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

                    foreach ($counters as $counter) {
                        if (isset($activeTicketsByCounter[$counter['id']])) {
                            $ticket = $activeTicketsByCounter[$counter['id']];
                            $activeData[$ticket['ticket_number']] = [
                                'counterName' => $counter['name'],
                                'calledAt' => $ticket['called_at']
                            ];
                        }
                    }

                    foreach ($counters as $counter):
                        $ticket = $activeTicketsByCounter[$counter['id']] ?? null;
                        $isActive = $ticket !== null;
                        $status = $ticket['status'] ?? 'open';
                        
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
                    <div class="counter-card <?= $isActive ? 'active' : 'open' ?>">
                        <div class="counter-icon-bg">
                            <i class="fas <?= $isActive ? 'fa-user-check' : 'fa-door-open' ?>"></i>
                        </div>
                        <div class="counter-name">
                            <i class="fas fa-desktop"></i>
                            <?= htmlspecialchars($counter['name']) ?>
                        </div>
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
                        <div class="status-badge <?= $isActive ? $status : 'open' ?>">
                            <i class="fas <?= $isActive ? ($status === 'serving' ? 'fa-check-circle' : 'fa-bell') : 'fa-hourglass' ?> me-1"></i>
                            <?= $isActive ? ucfirst($status) : 'Available' ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <?php
        $videoDir = __DIR__ . '/assets/videos';
        $bgVideos = [];
        if (is_dir($videoDir)) {
            $files = scandir($videoDir);
            foreach ($files as $file) {
                if (pathinfo($file, PATHINFO_EXTENSION) === 'mp4') {
                    $mtime = filemtime($videoDir . '/' . $file);
                    $bgVideos[] = '/assets/videos/' . $file . '?v=' . $mtime;
                }
            }
        }
        if (empty($bgVideos)) {
            $bgVideos[] = 'https://www.w3schools.com/html/mov_bbb.mp4';
        }
    ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // ─── Clock ────────────────────────────────────────────
        function updateClock() {
            const now = new Date();
            const options = { 
                weekday: 'short', 
                month: 'short', 
                day: 'numeric',
                year: 'numeric',
                hour: '2-digit', 
                minute: '2-digit', 
                second: '2-digit',
                hour12: true 
            };
            document.getElementById('datetime').textContent = now.toLocaleString('en-US', options);
        }
        updateClock();
        setInterval(updateClock, 1000);

        // ─── Background Video Player ─────────────────────────
        const bgVideo = document.getElementById('bgVideo');
        const bgPlayPause = document.getElementById('bgPlayPause');
        const bgNext = document.getElementById('bgNext');
        const bgPrev = document.getElementById('bgPrev');
        const bgVolumeBtn = document.getElementById('bgVolume');

        let bgVideos = <?= json_encode($bgVideos) ?>;
        let bgIndex = 0;
        let bgIsPlaying = true;
        let isMuted = true;

        function loadBgVideo(index, instant = false) {
            if (bgVideos.length === 0) return;
            bgIndex = (index + bgVideos.length) % bgVideos.length;
            
            bgVideo.src = bgVideos[bgIndex];
            bgVideo.currentTime = 0;
            bgVideo.load();
            
            bgVideo.play().catch(() => {
                bgIsPlaying = false;
                updateBgPlayButton();
            });
        }

        function updateBgPlayButton() {
            const icon = bgPlayPause.querySelector('i');
            if (bgIsPlaying && !bgVideo.paused) {
                icon.className = 'fas fa-pause';
            } else {
                icon.className = 'fas fa-play';
            }
        }

        bgVolumeBtn.addEventListener('click', () => {
            isMuted = !isMuted;
            bgVideo.muted = isMuted;
            const icon = bgVolumeBtn.querySelector('i');
            icon.className = isMuted ? 'fas fa-volume-mute' : 'fas fa-volume-up';
            if (!isMuted) {
                bgVideo.volume = 0.3;
            }
        });

        bgVideo.addEventListener('ended', () => {
            if (bgVideos.length > 1) {
                loadBgVideo(bgIndex + 1, true);
            } else {
                bgVideo.currentTime = 0;
                bgVideo.play().catch(() => {});
            }
        });

        bgVideo.addEventListener('error', () => {
            console.error("Error playing video:", bgVideo.src);
            if (bgVideos.length > 1) {
                setTimeout(() => {
                    loadBgVideo(bgIndex + 1, true);
                }, 500);
            }
        });

        bgVideo.addEventListener('play', () => {
            bgIsPlaying = true;
            updateBgPlayButton();
        });

        bgVideo.addEventListener('pause', () => {
            bgIsPlaying = false;
            updateBgPlayButton();
        });

        bgPlayPause.addEventListener('click', () => {
            if (bgVideo.paused) {
                bgVideo.play().catch(() => {});
            } else {
                bgVideo.pause();
            }
        });

        bgNext.addEventListener('click', () => {
            if (bgVideos.length > 1) loadBgVideo(bgIndex + 1, true);
        });

        bgPrev.addEventListener('click', () => {
            if (bgVideos.length > 1) loadBgVideo(bgIndex - 1, true);
        });

        document.addEventListener('keydown', (e) => {
            if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;
            if (e.key === ' ' || e.key === 'Space') {
                e.preventDefault();
                if (bgVideo.paused) {
                    bgVideo.play().catch(() => {});
                } else {
                    bgVideo.pause();
                }
            }
            if (e.key === 'ArrowRight' && bgVideos.length > 1) {
                e.preventDefault();
                loadBgVideo(bgIndex + 1, true);
            }
            if (e.key === 'ArrowLeft' && bgVideos.length > 1) {
                e.preventDefault();
                loadBgVideo(bgIndex - 1, true);
            }
            if (e.key === 'm' || e.key === 'M') {
                bgVolumeBtn.click();
            }
        });

        if (bgVideos.length > 0) {
            loadBgVideo(0);
        }

        // ─── Speech Announcements ────────────────────────────
        const currentActiveData = <?= json_encode($activeData ?? []) ?>;
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
            const text = newAnnouncements.join(' ');
            
            const wasMuted = bgVideo.muted;
            bgVideo.muted = true;
            const originalVolume = bgVideo.volume;
            bgVideo.volume = 0.1;
            
            const restoreAudio = () => {
                if (!wasMuted) {
                    bgVideo.muted = false;
                }
                bgVideo.volume = originalVolume;
            };

            function playChime() {
                return new Promise((resolve) => {
                    const AudioContext = window.AudioContext || window.webkitAudioContext;
                    if (!AudioContext) { resolve(); return; }
                    
                    const ctx = new AudioContext();
                    if (ctx.state === 'suspended') {
                        ctx.resume();
                    }
                    
                    const osc1 = ctx.createOscillator();
                    const gain1 = ctx.createGain();
                    osc1.type = 'sine';
                    osc1.frequency.setValueAtTime(783.99, ctx.currentTime);
                    gain1.gain.setValueAtTime(0, ctx.currentTime);
                    gain1.gain.linearRampToValueAtTime(0.3, ctx.currentTime + 0.05);
                    gain1.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.7);
                    osc1.connect(gain1);
                    gain1.connect(ctx.destination);
                    osc1.start(ctx.currentTime);
                    osc1.stop(ctx.currentTime + 0.7);
                    
                    const osc2 = ctx.createOscillator();
                    const gain2 = ctx.createGain();
                    osc2.type = 'sine';
                    osc2.frequency.setValueAtTime(523.25, ctx.currentTime + 0.3);
                    gain2.gain.setValueAtTime(0, ctx.currentTime + 0.3);
                    gain2.gain.linearRampToValueAtTime(0.3, ctx.currentTime + 0.35);
                    gain2.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 1.3);
                    osc2.connect(gain2);
                    gain2.connect(ctx.destination);
                    osc2.start(ctx.currentTime + 0.3);
                    osc2.stop(ctx.currentTime + 1.3);
                    
                    setTimeout(resolve, 1500);
                });
            }

            playChime().then(() => {
                if ('speechSynthesis' in window) {
                    const utterance = new SpeechSynthesisUtterance(text);
                    utterance.rate = 0.85;
                    utterance.pitch = 1;
                    utterance.volume = 1;
                    utterance.onend = restoreAudio;
                    utterance.onerror = restoreAudio;
                    window.speechSynthesis.speak(utterance);
                } else {
                    restoreAudio();
                }
            }).catch(() => {
                restoreAudio();
            });
        }
        
        localStorage.setItem('sqms_last_active_data', JSON.stringify(currentActiveData));

        // ─── Auto-refresh queue data ─────────────────────────
        setInterval(() => {
            fetch(window.location.href)
                .then(response => response.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const newGrid = doc.querySelector('.queue-grid');
                    if (newGrid) {
                        document.querySelector('.queue-grid').innerHTML = newGrid.innerHTML;
                    }
                })
                .catch(() => {});
        }, 5000);
    </script>
</body>
</html>