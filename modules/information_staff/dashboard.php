<?php
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/models/Ticket.php';
require_once __DIR__ . '/../../includes/models/Counter.php';

Session::requireLogin();
$userId = Session::get('user_id');
$role = Session::get('role');

$db = new Database();
$conn = $db->getConnection();
$ticketModel = new Ticket($conn);
$counterModel = new Counter($conn);

$serviceIds = [];
$waitingList = [];
$currentTicket = null;
$currentCounter = null;

if ($role !== 'information_staff') {
    header("Location: /");
    exit();
}

$stmt = $conn->query("
    SELECT s.id, s.name, 
           (SELECT COUNT(*) FROM counter_services cs 
            JOIN counters c ON cs.counter_id = c.id 
            WHERE cs.service_id = s.id AND c.is_archived = 0 AND c.is_active = 1) as active_counters_count
    FROM services s 
    WHERE s.is_archived = 0 
    ORDER BY s.name ASC
");
$activeServices = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch recent tickets for Queue Monitoring (only for the current session)
$session_start = getQueueSessionStart($conn);
$stmt = $conn->prepare("SELECT t.ticket_number, s.name as service_name, t.status, c.name as counter_name 
                        FROM tickets t 
                        LEFT JOIN services s ON t.service_id = s.id 
                        LEFT JOIN counters c ON t.counter_id = c.id 
                        WHERE t.created_at >= ?
                        ORDER BY t.issued_at DESC LIMIT 10");
$stmt->execute([$session_start]);
$recentTickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'issue_ticket') {
        $name = trim($_POST['name'] ?? '');
        $service_id = $_POST['service_id'] ?? '';
        $citizen_category = $_POST['citizen_category'] ?? 'Regular';
        
        if ($service_id) {
            // Verify the service has an active counter
            $checkStmt = $conn->prepare("SELECT COUNT(*) FROM counter_services cs JOIN counters c ON cs.counter_id = c.id WHERE cs.service_id = ? AND c.is_archived = 0 AND c.is_active = 1");
            $checkStmt->execute([$service_id]);
            if ($checkStmt->fetchColumn() > 0) {
                $ticket_number = $ticketModel->createTicket($name, $service_id, $citizen_category);
                header("Location: /information_staff/dashboard?status=issued&ticket=" . urlencode($ticket_number));
                exit();
            } else {
                header("Location: /information_staff/dashboard?status=error_no_counter");
                exit();
            }
        }
    }
}

$pageTitle = 'Staff Dashboard - Doc Marly SQMS';
$activeMenu = 'dashboard';

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar_user.php';
?>

<link rel="stylesheet" href="/assets/css/information_staff.css">
<link rel="stylesheet" href="/assets/css/clockwidget_ui.css">

<div class="main-content">
    <!-- ============================================
   HEADER SECTION - Horizontal Profile Style
   ============================================ -->
<div class="header-section profile-horizontal">
    <!-- Avatar -->
    <div class="profile-avatar-wrapper">
        <div class="profile-avatar">
            <span class="avatar-text"><?php echo strtoupper(substr(Session::get('username') ?? 'U', 0, 2)); ?></span>
            <span class="profile-status-dot online"></span>
        </div>
    </div>
    
    <!-- Divider -->
    <div class="profile-divider"></div>
    
    <!-- User Info -->
    <div class="profile-info">
        <div class="profile-name-wrapper">
            <span class="profile-name"><?php echo htmlspecialchars(Session::get('username') ?? 'User'); ?></span>
            <span class="profile-verified-badge">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="#242364" stroke="none">
                    <path d="M22.5 12.5c0-1.58-.875-2.95-2.148-3.6.154-.435.238-.905.238-1.4 0-2.21-1.71-4-3.82-4-1.285 0-2.43.615-3.12 1.54-.69-.925-1.835-1.54-3.12-1.54-2.11 0-3.82 1.79-3.82 4 0 .495.084.965.238 1.4-1.273.65-2.148 2.02-2.148 3.6 0 .38.055.745.155 1.1-1.345.85-2.178 2.31-2.178 3.9 0 2.21 1.71 4 3.82 4 2.11 0 3.82-1.79 3.82-4 0-.38-.055-.745-.155-1.1 1.345-.85 2.178-2.31 2.178-3.9 0-.38-.055-.745-.155-1.1z"/>
                </svg>
            </span>
        </div>
        <div class="profile-role">
            <span class="info-staff-badge-horizontal" style="color: #242364">Information Staff</span>
        </div>
    </div>
    
    <!-- Divider -->
    <div class="profile-divider"></div>
    
    <!-- Stats -->
    <div class="profile-stats-horizontal">
        <div class="stat-item-horizontal">
            <span class="stat-number-horizontal"><?php echo count($recentTickets ?? []); ?></span>
            <span class="stat-label-horizontal">Tickets</span>
        </div>
        <div class="stat-item-horizontal">
            <span class="stat-number-horizontal"><?php echo count($activeServices ?? []); ?></span>
            <span class="stat-label-horizontal">Services</span>
        </div>
        <div class="stat-item-horizontal">
            <span class="stat-number-horizontal" id="online-count">12</span>
            <span class="stat-label-horizontal">Online</span>
        </div>
    </div>
    
    <!-- Divider -->
    <div class="profile-divider"></div>
    
    <!-- Actions -->
    <div class="profile-actions-horizontal">
        <!-- <button class="profile-btn-horizontal" onclick="window.location.reload()" title="Refresh">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 12a9 9 0 11-6.219-8.56"/>
                <polyline points="21 3 21 9 15 9"/>
            </svg>
            <span>Refresh</span>
        </button>
        <button class="profile-btn-horizontal profile-btn-logout" onclick="window.location.href='/logout'" title="Logout">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/>
                <polyline points="16 17 21 12 16 7"/>
                <line x1="21" y1="12" x2="9" y2="12"/>
            </svg>
            <span>Logout</span>
        </button> -->
    </div>
</div>

    <?php if ($role === 'information_staff'): ?>
        <!-- Information Staff Dashboard (Kiosk & Monitoring) -->
        <?php if (isset($_GET['status']) && $_GET['status'] === 'issued'): ?>
            <div class="success-message" style="background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #c3e6cb;">
                Ticket successfully issued! Ticket Number: <strong><?= htmlspecialchars($_GET['ticket'] ?? '') ?></strong>
            </div>
        <?php elseif (isset($_GET['status']) && $_GET['status'] === 'error_no_counter'): ?>
            <div class="error-message" style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #f5c6cb;">
                Cannot issue ticket: There are no active counters currently assigned to this service. Please ensure a counter is active and assigned first.
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <!-- Left: Ticket Issuance Form -->
            <div class="col-lg-4 col-md-12">
                <div class="info-card">
                    <div class="card-header-custom">
                        <i class="bi bi-ticket-perforated me-2"></i> Issue New Ticket
                    </div>
                    <div class="card-body-custom">
                        <form method="POST" action="/information_staff/dashboard">
                            <input type="hidden" name="action" value="issue_ticket">

                            <div class="mb-3">
                                <label class="form-label-custom">Citizen Name <span class="text-muted" style="font-weight:400;">(Optional)</span></label>
                                <input type="text" name="name" class="form-control-custom" placeholder="Leave blank for walk-in" style="font-size:14px;">
                            </div>

                            <div class="mb-3">
                                <label class="form-label-custom">Citizen Category</label>
                                <select name="citizen_category" required class="form-control-custom" style="appearance: auto;">
                                    <option value="Regular">Regular</option>
                                    <option value="Senior Citizen">Senior Citizen</option>
                                    <option value="PWD">PWD</option>
                                    <option value="Pregnant">Pregnant</option>
                                </select>
                            </div>

                            <div class="mb-4">
                                <label class="form-label-custom">Requested Service</label>
                                <select name="service_id" required class="form-control-custom" style="appearance: auto;">
                                    <option value="">-- Select Service --</option>
                                    <?php foreach ($activeServices as $srv): ?>
                                        <option value="<?= $srv['id'] ?>"><?= htmlspecialchars($srv['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <button type="submit" class="btn-issue-ticket">
                                <i class="bi bi-bookmark-plus me-2"></i> Issue Ticket
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Right: Queue Monitoring -->
            <div class="col-lg-8 col-md-12">
                <div class="info-card">
                    <div class="card-header-custom d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-bar-chart-line me-2"></i> Global Queue Monitoring</span>
                        <span class="badge bg-primary rounded-pill px-3 py-2" style="font-size:12px;">
                            <?= count($recentTickets) ?> tickets
                        </span>
                    </div>
                    <div class="card-body-custom">
                        <div style="overflow-x: auto;">
                            <table class="queue-table" style="width:100%; border-collapse:collapse;">
                                <thead>
                                    <tr style="border-bottom: 2px solid #e9ecef;">
                                        <th style="padding:10px 16px; text-align:left; font-size:11px; text-transform:uppercase; letter-spacing:0.05em; color:#6c757d; font-weight:700;">Ticket No.</th>
                                        <th style="padding:10px 16px; text-align:left; font-size:11px; text-transform:uppercase; letter-spacing:0.05em; color:#6c757d; font-weight:700;">Service</th>
                                        <th style="padding:10px 16px; text-align:left; font-size:11px; text-transform:uppercase; letter-spacing:0.05em; color:#6c757d; font-weight:700;">Status</th>
                                    </tr>
                                </thead>
                                <tbody id="global-queue-body">
                                    <?php if (!empty($recentTickets)): ?>
                                        <?php foreach ($recentTickets as $ticket): ?>
                                            <?php
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
                                            ?>
                                            <tr>
                                                <td style="padding:12px 16px; vertical-align:middle;">
                                                    <span class="ticket-number"><?= htmlspecialchars($ticket['ticket_number']) ?></span>
                                                </td>
                                                <td style="padding:12px 16px; vertical-align:middle; color:#495057;">
                                                    <?= htmlspecialchars($ticket['service_name']) ?>
                                                </td>
                                                <td style="padding:12px 16px; vertical-align:middle;">
                                                    <span class="ticket-status-badge <?= $statusClass ?>">
                                                        <?= $statusLabel ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="3">
                                                <div class="queue-empty">
                                                    <i class="bi bi-mailbox" style="font-size:32px; display:block; margin-bottom:8px; color: #adb5bd;"></i>
                                                    No active queue
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- ==========================================
     FLOATING CLOCK WIDGET CONTAINER
     ========================================== -->
<div id="floating-clock-container" style="position: fixed; bottom: 20px; right: 20px; z-index: 9999;">
    <!-- Minimize Toggle Button -->
    <button id="clock-toggle-btn" title="Toggle clock visibility">−</button>

    <!-- Clock Widget -->
    <div class="clock-widget" id="weather-widget">
        <!-- Loading Overlay -->
        <div class="loading-overlay" id="loading-overlay">
            <span>Loading weather...</span>
        </div>

        <section class="info-section">
            <!-- Background design (circles) -->
            <div class="bg-design">
                <div class="bg-circle1"></div>
                <div class="bg-circle2"></div>
                <div class="bg-circle3"></div>
            </div>

            <!-- left side -->
            <div class="left-side">
                <!-- weather row -->
                <div class="weather-row">
                    <div class="weather-icon" id="weather-icon">
                        <svg stroke="#ffffff" fill="#ffffff" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1024 1024">
                            <g stroke-width="0" id="SVGRepo_bgCarrier"></g>
                            <g stroke-linejoin="round" stroke-linecap="round" id="SVGRepo_tracerCarrier"></g>
                            <g id="SVGRepo_iconCarrier">
                                <path d="M512 704a192 192 0 1 0 0-384 192 192 0 0 0 0 384zm0 64a256 256 0 1 1 0-512 256 256 0 0 1 0 512zm0-704a32 32 0 0 1 32 32v64a32 32 0 0 1-64 0V96a32 32 0 0 1 32-32zm0 768a32 32 0 0 1 32 32v64a32 32 0 1 1-64 0v-64a32 32 0 0 1 32-32zM195.2 195.2a32 32 0 0 1 45.248 0l45.248 45.248a32 32 0 1 1-45.248 45.248L195.2 240.448a32 32 0 0 1 0-45.248zm543.104 543.104a32 32 0 0 1 45.248 0l45.248 45.248a32 32 0 0 1-45.248 45.248l-45.248-45.248a32 32 0 0 1 0-45.248zM64 512a32 32 0 0 1 32-32h64a32 32 0 0 1 0 64H96a32 32 0 0 1-32-32zm768 0a32 32 0 0 1 32-32h64a32 32 0 1 1 0 64h-64a32 32 0 0 1-32-32zM195.2 828.8a32 32 0 0 1 0-45.248l45.248-45.248a32 32 0 0 1 45.248 45.248L240.448 828.8a32 32 0 0 1-45.248 0zm543.104-543.104a32 32 0 0 1 0-45.248l45.248-45.248a32 32 0 0 1 45.248 45.248l-45.248 45.248a32 32 0 0 1-45.248 0z" fill="#ffffff"></path>
                            </g>
                        </svg>
                    </div>
                    <div id="weather-condition">Sunny</div>
                </div>

                <!-- temperature -->
                <div class="temperature" id="weather-temp">--°</div>
                <!-- range (feels like) -->
                <div class="temp-range" id="weather-range">--°/--°</div>
            </div>

            <!-- right side -->
            <div class="right-side">
                <div style="display:flex; flex-direction:column; align-items:flex-end;">
                    <div class="time-display" id="clock-time">
                        <span class="time-hours-minutes">
                            <span id="clock-hours">--</span>
                            <span class="time-separator">:</span>
                            <span id="clock-minutes">--</span>
                        </span>
                        <span class="time-separator">:</span>
                        <span class="seconds-display" id="clock-seconds">--</span>
                        <span class="ampm-display" id="clock-ampm">AM</span>
                    </div>
                    <div class="date-display" id="clock-date">--- --</div>
                </div>
                <div class="location" id="weather-location">Zamboanga Sibugay</div>
            </div>
        </section>

        <!-- DAYS SECTION -->
        <section class="days-section">
            <button class="day-btn" onmouseout="this.style.transform='scale(1)'; this.style.borderRadius='0';">
                <span>TUE</span>
                <span class="day-icon">
                    <svg stroke="#ffffff" fill="#ffffff" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1024 1024">
                        <path d="M512 704a192 192 0 1 0 0-384 192 192 0 0 0 0 384zm0 64a256 256 0 1 1 0-512 256 256 0 0 1 0 512zm0-704a32 32 0 0 1 32 32v64a32 32 0 0 1-64 0V96a32 32 0 0 1 32-32zm0 768a32 32 0 0 1 32 32v64a32 32 0 1 1-64 0v-64a32 32 0 0 1 32-32zM195.2 195.2a32 32 0 0 1 45.248 0l45.248 45.248a32 32 0 1 1-45.248 45.248L195.2 240.448a32 32 0 0 1 0-45.248zm543.104 543.104a32 32 0 0 1 45.248 0l45.248 45.248a32 32 0 0 1-45.248 45.248l-45.248-45.248a32 32 0 0 1 0-45.248zM64 512a32 32 0 0 1 32-32h64a32 32 0 0 1 0 64H96a32 32 0 0 1-32-32zm768 0a32 32 0 0 1 32-32h64a32 32 0 1 1 0 64h-64a32 32 0 0 1-32-32zM195.2 828.8a32 32 0 0 1 0-45.248l45.248-45.248a32 32 0 0 1 45.248 45.248L240.448 828.8a32 32 0 0 1-45.248 0zm543.104-543.104a32 32 0 0 1 0-45.248l45.248-45.248a32 32 0 0 1 45.248 45.248l-45.248 45.248a32 32 0 0 1-45.248 0z" fill="#ffffff" />
                    </svg>
                </span>
            </button>
            <button class="day-btn" onmouseout="this.style.transform='scale(1)'; this.style.borderRadius='0';">
                <span>WED</span>
                <span class="day-icon">
                    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="#ffffff">
                        <path d="M16 18.5L15 21M8 18.5L7 21M12 18.5L11 21M7 15C4.23858 15 2 12.7614 2 10C2 7.23858 4.23858 5 7 5C7.03315 5 7.06622 5.00032 7.09922 5.00097C8.0094 3.2196 9.86227 2 12 2C14.5192 2 16.6429 3.69375 17.2943 6.00462C17.3625 6.00155 17.4311 6 17.5 6C19.9853 6 22 8.01472 22 10.5C22 12.9853 19.9853 15 17.5 15C13.7434 15 11.2352 15 7 15Z" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </span>
            </button>
            <button class="day-btn" onmouseout="this.style.transform='scale(1)'; this.style.borderRadius='0';">
                <span>THU</span>
                <span class="day-icon">
                    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="#ffffff">
                        <path d="M16 18.5L15 21M8 18.5L7 21M12 18.5L11 21M7 15C4.23858 15 2 12.7614 2 10C2 7.23858 4.23858 5 7 5C7.03315 5 7.06622 5.00032 7.09922 5.00097C8.0094 3.2196 9.86227 2 12 2C14.5192 2 16.6429 3.69375 17.2943 6.00462C17.3625 6.00155 17.4311 6 17.5 6C19.9853 6 22 8.01472 22 10.5C22 12.9853 19.9853 15 17.5 15C13.7434 15 11.2352 15 7 15Z" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </span>
            </button>
            <button class="day-btn" onmouseout="this.style.transform='scale(1)'; this.style.borderRadius='0';">
                <span>FRI</span>
                <span class="day-icon">
                    <svg stroke="#ffffff" fill="#ffffff" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1024 1024">
                        <path d="M512 704a192 192 0 1 0 0-384 192 192 0 0 0 0 384zm0 64a256 256 0 1 1 0-512 256 256 0 0 1 0 512zm0-704a32 32 0 0 1 32 32v64a32 32 0 0 1-64 0V96a32 32 0 0 1 32-32zm0 768a32 32 0 0 1 32 32v64a32 32 0 1 1-64 0v-64a32 32 0 0 1 32-32zM195.2 195.2a32 32 0 0 1 45.248 0l45.248 45.248a32 32 0 1 1-45.248 45.248L195.2 240.448a32 32 0 0 1 0-45.248zm543.104 543.104a32 32 0 0 1 45.248 0l45.248 45.248a32 32 0 0 1-45.248 45.248l-45.248-45.248a32 32 0 0 1 0-45.248zM64 512a32 32 0 0 1 32-32h64a32 32 0 0 1 0 64H96a32 32 0 0 1-32-32zm768 0a32 32 0 0 1 32-32h64a32 32 0 1 1 0 64h-64a32 32 0 0 1-32-32zM195.2 828.8a32 32 0 0 1 0-45.248l45.248-45.248a32 32 0 0 1 45.248 45.248L240.448 828.8a32 32 0 0 1-45.248 0zm543.104-543.104a32 32 0 0 1 0-45.248l45.248-45.248a32 32 0 0 1 45.248 45.248l-45.248 45.248a32 32 0 0 1-45.248 0z" fill="#ffffff" />
                    </svg>
                </span>
            </button>
        </section>
    </div>
</div>

<!-- Pure JavaScript Weather & Location - Zamboanga Sibugay Specific + Draggable + 12-Hour Format -->
<script>
    // ============================================
    // 1. CLOCK FUNCTION WITH 12-HOUR FORMAT & AM/PM
    // ============================================
    function updateClock() {
        const now = new Date();
        let hours = now.getHours();
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');

        // Determine AM/PM
        const ampm = hours >= 12 ? 'PM' : 'AM';

        // Convert to 12-hour format
        hours = hours % 12;
        hours = hours ? hours : 12; // 12 instead of 0
        const hours12 = String(hours).padStart(2, '0');

        document.getElementById('clock-hours').textContent = hours12;
        document.getElementById('clock-minutes').textContent = minutes;
        document.getElementById('clock-seconds').textContent = seconds;
        document.getElementById('clock-ampm').textContent = ampm;

        const days = ['SUN', 'MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT'];
        const dayName = days[now.getDay()];
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const day = String(now.getDate()).padStart(2, '0');
        document.getElementById('clock-date').textContent = dayName + ' ' + month + '-' + day;
    }

    // ============================================
    // 2. WEATHER ICON MAPPER
    // ============================================
    function getWeatherIcon(condition) {
        const icons = {
            'clear': `<svg stroke="#ffffff" fill="#ffffff" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1024 1024">
                        <path d="M512 704a192 192 0 1 0 0-384 192 192 0 0 0 0 384zm0 64a256 256 0 1 1 0-512 256 256 0 0 1 0 512zm0-704a32 32 0 0 1 32 32v64a32 32 0 0 1-64 0V96a32 32 0 0 1 32-32zm0 768a32 32 0 0 1 32 32v64a32 32 0 1 1-64 0v-64a32 32 0 0 1 32-32zM195.2 195.2a32 32 0 0 1 45.248 0l45.248 45.248a32 32 0 1 1-45.248 45.248L195.2 240.448a32 32 0 0 1 0-45.248zm543.104 543.104a32 32 0 0 1 45.248 0l45.248 45.248a32 32 0 0 1-45.248 45.248l-45.248-45.248a32 32 0 0 1 0-45.248zM64 512a32 32 0 0 1 32-32h64a32 32 0 0 1 0 64H96a32 32 0 0 1-32-32zm768 0a32 32 0 0 1 32-32h64a32 32 0 1 1 0 64h-64a32 32 0 0 1-32-32zM195.2 828.8a32 32 0 0 1 0-45.248l45.248-45.248a32 32 0 0 1 45.248 45.248L240.448 828.8a32 32 0 0 1-45.248 0zm543.104-543.104a32 32 0 0 1 0-45.248l45.248-45.248a32 32 0 0 1 45.248 45.248l-45.248 45.248a32 32 0 0 1-45.248 0z" fill="#ffffff"/>
                    </svg>`,
            'clouds': `<svg stroke="#ffffff" fill="#ffffff" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                        <path d="M16 18.5L15 21M8 18.5L7 21M12 18.5L11 21M7 15C4.23858 15 2 12.7614 2 10C2 7.23858 4.23858 5 7 5C7.03315 5 7.06622 5.00032 7.09922 5.00097C8.0094 3.2196 9.86227 2 12 2C14.5192 2 16.6429 3.69375 17.2943 6.00462C17.3625 6.00155 17.4311 6 17.5 6C19.9853 6 22 8.01472 22 10.5C22 12.9853 19.9853 15 17.5 15C13.7434 15 11.2352 15 7 15Z" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>`,
            'rain': `<svg stroke="#ffffff" fill="#ffffff" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                        <path d="M16 18.5L15 21M8 18.5L7 21M12 18.5L11 21M7 15C4.23858 15 2 12.7614 2 10C2 7.23858 4.23858 5 7 5C7.03315 5 7.06622 5.00032 7.09922 5.00097C8.0094 3.2196 9.86227 2 12 2C14.5192 2 16.6429 3.69375 17.2943 6.00462C17.3625 6.00155 17.4311 6 17.5 6C19.9853 6 22 8.01472 22 10.5C22 12.9853 19.9853 15 17.5 15C13.7434 15 11.2352 15 7 15Z" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M8 19L6 21M12 19L10 21M16 19L14 21" stroke="#ffffff" stroke-width="2" stroke-linecap="round"/>
                    </svg>`
        };
        return icons[condition] || icons['clear'];
    }

    // ============================================
    // 3. WEATHER CONDITION MAPPER
    // ============================================
    function getWeatherClass(condition) {
        const map = {
            'clear': 'weather-sunny',
            'clouds': 'weather-cloudy',
            'rain': 'weather-rainy',
            'drizzle': 'weather-rainy',
            'thunderstorm': 'weather-stormy',
            'snow': 'weather-snowy',
            'mist': 'weather-cloudy',
            'fog': 'weather-cloudy'
        };
        return map[condition] || 'weather-sunny';
    }

    // ============================================
    // 4. GET WEATHER FOR ZAMBOANGA SIBUGAY
    // Coordinates: 7.8000° N, 122.6667° E
    // ============================================
    function getWeather() {
        const loadingOverlay = document.getElementById('loading-overlay');
        const widget = document.getElementById('weather-widget');

        // Zamboanga Sibugay coordinates
        const lat = 7.8000;
        const lon = 122.6667;

        // Set location
        document.getElementById('weather-location').textContent = 'Zamboanga Sibugay';

        // Get weather using Open-Meteo (FREE, no API key)
        const weatherUrl = `https://api.open-meteo.com/v1/forecast?latitude=${lat}&longitude=${lon}&current_weather=true&timezone=Asia/Manila`;

        fetch(weatherUrl)
            .then(response => {
                if (!response.ok) throw new Error('Weather API failed');
                return response.json();
            })
            .then(weatherData => {
                const current = weatherData.current_weather;
                if (!current) throw new Error('No weather data');

                // Update temperature
                const temp = Math.round(current.temperature);
                document.getElementById('weather-temp').textContent = temp + '°';

                // Update feels like (approximate)
                document.getElementById('weather-range').textContent =
                    (temp - 2) + '°/' + (temp + 2) + '°';

                // Determine weather condition from weather code
                const codes = {
                    0: 'clear',
                    1: 'clear',
                    2: 'clouds',
                    3: 'clouds',
                    45: 'fog',
                    48: 'fog',
                    51: 'rain',
                    53: 'rain',
                    55: 'rain',
                    56: 'rain',
                    57: 'rain',
                    61: 'rain',
                    63: 'rain',
                    65: 'rain',
                    66: 'rain',
                    67: 'rain',
                    71: 'snow',
                    73: 'snow',
                    75: 'snow',
                    77: 'snow',
                    80: 'rain',
                    81: 'rain',
                    82: 'rain',
                    85: 'snow',
                    86: 'snow',
                    95: 'thunderstorm',
                    96: 'thunderstorm',
                    99: 'thunderstorm'
                };

                const weatherCode = current.weathercode;
                const condition = codes[weatherCode] || 'clear';

                // Map condition to display name
                const displayNames = {
                    'clear': 'Sunny',
                    'clouds': 'Cloudy',
                    'rain': 'Rainy',
                    'snow': 'Snowy',
                    'fog': 'Foggy',
                    'thunderstorm': 'Stormy'
                };

                document.getElementById('weather-condition').textContent =
                    displayNames[condition] || 'Sunny';

                // Update icon
                const iconMap = {
                    'clear': 'clear',
                    'clouds': 'clouds',
                    'rain': 'rain',
                    'snow': 'rain',
                    'fog': 'clouds',
                    'thunderstorm': 'rain'
                };
                document.getElementById('weather-icon').innerHTML =
                    getWeatherIcon(iconMap[condition] || 'clear');

                // Update widget class for background colors
                widget.className = 'clock-widget ' + getWeatherClass(condition);

                // Hide loading
                loadingOverlay.classList.add('hidden');
            })
            .catch(error => {
                console.error('Weather error:', error);
                loadingOverlay.classList.add('hidden');
                // Set fallback data for Zamboanga Sibugay
                document.getElementById('weather-temp').textContent = '28°';
                document.getElementById('weather-range').textContent = '26°/30°';
                document.getElementById('weather-condition').textContent = 'Sunny';
                document.getElementById('weather-icon').innerHTML = getWeatherIcon('clear');
                widget.className = 'clock-widget weather-sunny';
            });
    }

    // ============================================
    // 5. DRAGGABLE FUNCTIONALITY
    // ============================================
    (function() {
        const container = document.getElementById('floating-clock-container');
        let isDragging = false;
        let startX, startY, offsetX, offsetY;

        function onStart(e) {
            // Don't start drag if clicking on toggle button
            if (e.target.closest('#clock-toggle-btn')) return;

            isDragging = true;
            const touch = e.touches ? e.touches[0] : e;
            startX = touch.clientX;
            startY = touch.clientY;

            const rect = container.getBoundingClientRect();
            offsetX = startX - rect.left;
            offsetY = startY - rect.top;

            container.style.cursor = 'grabbing';
            container.style.transition = 'none';

            e.preventDefault();
        }

        function onMove(e) {
            if (!isDragging) return;

            const touch = e.touches ? e.touches[0] : e;
            const x = touch.clientX - offsetX;
            const y = touch.clientY - offsetY;

            // Keep within viewport
            const maxX = window.innerWidth - container.offsetWidth;
            const maxY = window.innerHeight - container.offsetHeight;
            const newX = Math.max(0, Math.min(x, maxX));
            const newY = Math.max(0, Math.min(y, maxY));

            container.style.left = newX + 'px';
            container.style.top = newY + 'px';
            container.style.right = 'auto';
            container.style.bottom = 'auto';

            e.preventDefault();
        }

        function onEnd() {
            if (isDragging) {
                isDragging = false;
                container.style.cursor = 'grab';
                container.style.transition = 'opacity 0.3s ease';
            }
        }

        // Mouse events
        container.addEventListener('mousedown', onStart);
        document.addEventListener('mousemove', onMove);
        document.addEventListener('mouseup', onEnd);

        // Touch events
        container.addEventListener('touchstart', onStart, {
            passive: false
        });
        document.addEventListener('touchmove', onMove, {
            passive: false
        });
        document.addEventListener('touchend', onEnd);
    })();

    // ============================================
    // 6. MINIMIZE/MAXIMIZE TOGGLE
    // ============================================
    (function() {
        const container = document.getElementById('floating-clock-container');
        const toggleBtn = document.getElementById('clock-toggle-btn');
        let isMinimized = false;

        toggleBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            isMinimized = !isMinimized;
            container.classList.toggle('minimized', isMinimized);
            toggleBtn.textContent = isMinimized ? '+' : '−';
            toggleBtn.title = isMinimized ? 'Expand clock' : 'Minimize clock';
        });
    })();

    // ============================================
    // 7. INITIALIZE EVERYTHING
    // ============================================
    updateClock();
    setInterval(updateClock, 1000);

    // Wait for DOM to load then get weather
    document.addEventListener('DOMContentLoaded', function() {
        getWeather();
    });

    // Auto-refresh the global queue every 5 seconds
    setInterval(function() {
        fetch('/api/information_staff/recent_tickets')
            .then(response => response.text())
            .then(html => {
                document.getElementById('global-queue-body').innerHTML = html;
            })
            .catch(err => console.error('Error fetching recent tickets:', err));
    }, 5000);
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
