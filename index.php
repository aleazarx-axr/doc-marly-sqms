<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/models/Ticket.php';
require_once __DIR__ . '/includes/models/Counter.php';

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

if ($role === 'admin') {
    // Fetch counts for Admin Dashboard
    $stmt = $conn->query("SELECT COUNT(*) as count FROM services WHERE is_archived = 0");
    $servicesCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    $stmt = $conn->query("SELECT COUNT(*) as count FROM counters WHERE is_archived = 0");
    $countersCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    $stmt = $conn->query("SELECT COUNT(*) as count FROM users");
    $usersCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    $stmt = $conn->query("SELECT COUNT(*) as count FROM tickets");
    $recordsCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    // Fetch recent tickets for Queue Monitoring
    $stmt = $conn->query("SELECT t.ticket_number, s.name as service_name, t.status FROM tickets t LEFT JOIN services s ON t.service_id = s.id ORDER BY t.issued_at DESC LIMIT 5");
    $recentTickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $staffCounters = $counterModel->getCountersByStaff($userId);
    $currentCounter = !empty($staffCounters) ? $staffCounters[0] : null;

    if ($currentCounter) {
        $serviceIds = $counterModel->getCounterServices($currentCounter['id']);
        $waitingList = $ticketModel->getWaitingList($serviceIds);
        $currentTicket = $ticketModel->getCurrentTicket($currentCounter['id']);
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';

        if ($action === 'call_next' && $currentCounter) {
            $nextTicket = $ticketModel->getNextInLine($serviceIds);
            if ($nextTicket) {
                $ticketModel->updateStatus($nextTicket['id'], 'called', $currentCounter['id']);
            }
        } elseif ($action === 'serve' && $currentTicket) {
            $ticketModel->updateStatus($currentTicket['id'], 'serving', $currentCounter['id']);
        } elseif ($action === 'done' && $currentTicket) {
            $ticketModel->updateStatus($currentTicket['id'], 'done', $currentCounter['id']);
        } elseif ($action === 'no_show' && $currentTicket) {
            $ticketModel->updateStatus($currentTicket['id'], 'no-show', $currentCounter['id']);
        }

        header("Location: index.php");
        exit();
    }
}

$pageTitle = ($role === 'admin') ? 'Admin Dashboard - Doc Marly SQMS' : 'Staff Dashboard - Doc Marly SQMS';
$activeMenu = 'dashboard';

require_once __DIR__ . '/includes/header.php';

if ($role === 'admin') {
    require_once __DIR__ . '/includes/sidebar_admin.php';
} else {
    require_once __DIR__ . '/includes/sidebar_user.php';
}
?>

<style>
    :root {
        --nexus-bg: #f8fafc;
        --nexus-card-bg: #ffffff;
        --nexus-primary: #242364;
        --nexus-primary-hover: #242364;
        --nexus-text-main: #0f172a;
        --nexus-text-muted: #64748b;
        --nexus-border: #e2e8f0;
        --nexus-radius: 12px;
        --nexus-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
    }

    body {
        background-color: var(--nexus-bg);
        color: var(--nexus-text-main);
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
        padding-bottom: 80px;
        /* Add padding for floating widget */
    }

    .header-section {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        background: var(--nexus-card-bg);
        padding: 24px 30px;
        border-radius: var(--nexus-radius);
        box-shadow: var(--nexus-shadow);
        border: 1px solid var(--nexus-border);
    }

    .header-section h1 {
        font-size: 24px;
        font-weight: 700;
        color: var(--nexus-text-main);
        margin: 0 0 4px 0;
    }

    .header-section h2 {
        font-size: 15px;
        font-weight: 500;
        color: var(--nexus-text-muted);
        margin: 0;
    }

    .dashboard-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .card {
        background: var(--nexus-card-bg);
        border-radius: var(--nexus-radius);
        padding: 24px;
        box-shadow: var(--nexus-shadow);
        border: 1px solid var(--nexus-border);
        transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
        cursor: pointer;
        position: relative;
        text-decoration: none;
        display: block;
        color: inherit;
    }

    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05);
        border-color: var(--nexus-primary);
    }

    .card:active {
        transform: scale(0.98);
    }

    .card h3 {
        font-size: 14px;
        font-weight: 600;
        color: var(--nexus-text-muted);
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin: 0;
        pointer-events: none;
    }

    .card .value {
        font-size: 32px;
        font-weight: 700;
        color: var(--nexus-text-main);
        margin: 0;
        pointer-events: none;
    }

    .card .card-link-hint {
        font-size: 13px;
        font-weight: 600;
        color: var(--nexus-primary);
        display: inline-flex;
        align-items: center;
        gap: 4px;
        pointer-events: none;
        opacity: 0.7;
        transition: opacity 0.2s ease;
    }

    .card:hover .card-link-hint {
        opacity: 1;
    }

    .card-content-wrapper {
        display: flex;
        justify-content: space-between;
        align-items: center;
        width: 100%;
        pointer-events: none;
    }

    .card-left {
        display: flex;
        flex-direction: column;
        gap: 8px;
        pointer-events: none;
    }

    .card-right {
        flex-shrink: 0;
        pointer-events: none;
    }

    /* Tables */
    table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
    }

    th {
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--nexus-text-muted);
        background: #f8fafc;
        padding: 12px 16px;
        border-bottom: 1px solid var(--nexus-border);
    }

    td {
        padding: 14px 16px;
        font-size: 14px;
        border-bottom: 1px solid var(--nexus-border);
        color: var(--nexus-text-main);
    }

    tr:last-child td {
        border-bottom: none;
    }

    /* Badges */
    .nexus-badge {
        display: inline-flex;
        align-items: center;
        padding: 4px 10px;
        border-radius: 9999px;
        font-size: 12px;
        font-weight: 600;
    }

    .badge-called {
        background: #fef3c7;
        color: #d97706;
    }

    .badge-serving {
        background: #e0e7ff;
        color: #4338ca;
    }

    .badge-done {
        background: #dcfce7;
        color: #166534;
    }

    .badge-default {
        background: #f1f5f9;
        color: #475569;
    }

    /* Buttons */
    .btn {
        border: none;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.2s ease, opacity 0.2s ease;
        color: #fff;
    }

    .btn:hover {
        opacity: 0.9;
    }

    .value-large {
        font-size: 42px;
        font-weight: 700;
        color: var(--nexus-primary);
        line-height: 1;
    }

    /* Staff card - non-clickable */
    .card-staff {
        cursor: default;
    }

    .card-staff:hover {
        transform: none;
        border-color: var(--nexus-border);
    }

    .card-staff:active {
        transform: none;
    }

    /* ============================================
       FLOATING CLOCK WIDGET - DRAGGABLE
       ============================================ */
    #floating-clock-container {
        position: fixed;
        bottom: 20px;
        right: 20px;
        z-index: 9999;
        cursor: grab;
        user-select: none;
        touch-action: none;
        transition: opacity 0.3s ease;
    }

    #floating-clock-container:active {
        cursor: grabbing;
    }

    /* Minimize toggle button */
    #clock-toggle-btn {
        position: absolute;
        top: -12px;
        right: -12px;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: #974859;
        color: white;
        border: 2px solid white;
        font-size: 14px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        z-index: 10000;
        transition: transform 0.2s ease, background 0.2s ease;
        padding: 0;
        line-height: 1;
    }

    #clock-toggle-btn:hover {
        transform: scale(1.1);
        background: #a75265;
    }

    /* Minimized state */
    #floating-clock-container.minimized .clock-widget {
        transform: scale(0);
        opacity: 0;
        pointer-events: none;
    }

    #floating-clock-container.minimized {
        width: 48px;
        height: 48px;
        background: #974859;
        border-radius: 50%;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.25);
        border: 2px solid white;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
    }

    #floating-clock-container.minimized #clock-toggle-btn {
        top: -8px;
        right: -8px;
        width: 24px;
        height: 24px;
        font-size: 12px;
    }

    #floating-clock-container.minimized .clock-widget {
        display: none;
    }

    #floating-clock-container.minimized::after {
        content: '🕐';
        font-size: 24px;
        color: white;
    }

    /* Clock Widget Styles (modified from original) */
    .clock-widget {
        display: flex;
        flex-direction: column;
        align-items: center;
        height: 180px;
        width: 280px;
        border-radius: 25px;
        background: lightgrey;
        overflow: hidden;
        transition: 100ms ease;
        box-shadow: rgba(0, 0, 0, 0.25) 4px 6px 12px;
        flex-shrink: 0;
        position: relative;
        pointer-events: auto;
    }

    .clock-widget .loading-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(236, 114, 99, 0.9);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10;
        color: white;
        font-size: 14px;
        font-weight: 500;
        border-radius: 25px;
    }

    .clock-widget .loading-overlay.hidden {
        display: none;
    }

    .clock-widget .info-section {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: space-between;
        width: 100%;
        height: 75%;
        color: white;
    }

    .clock-widget .bg-design {
        position: absolute;
        height: 100%;
        width: 100%;
        background-color: #ec7263;
        overflow: hidden;
        transition: background-color 0.5s ease;
    }

    .clock-widget .bg-circle1 {
        position: absolute;
        top: -80%;
        right: -50%;
        width: 300px;
        height: 300px;
        opacity: 0.4;
        border-radius: 50%;
        background-color: #efc745;
        transition: background-color 0.5s ease;
    }

    .clock-widget .bg-circle2 {
        position: absolute;
        top: -70%;
        right: -30%;
        width: 210px;
        height: 210px;
        opacity: 0.4;
        border-radius: 50%;
        background-color: #efc745;
        transition: background-color 0.5s ease;
    }

    .clock-widget .bg-circle3 {
        position: absolute;
        top: -35%;
        right: -8%;
        width: 100px;
        height: 100px;
        opacity: 1;
        border-radius: 50%;
        background-color: #efc745;
        transition: background-color 0.5s ease;
    }

    .clock-widget .left-side {
        display: flex;
        flex-direction: column;
        justify-content: space-around;
        height: 100%;
        z-index: 1;
        padding-left: 18px;
    }

    .clock-widget .right-side {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        justify-content: space-around;
        height: 100%;
        padding-right: 18px;
        z-index: 1;
    }

    .clock-widget .weather-row {
        display: flex;
        align-items: center;
        justify-content: flex-start;
        gap: 5px;
    }

    .clock-widget .weather-icon {
        display: flex;
        align-items: center;
        width: 40%;
        height: auto;
    }

    .clock-widget .weather-icon svg {
        width: 32px;
        height: 32px;
    }

    .clock-widget .temperature {
        font-size: 34pt;
        font-weight: 500;
        line-height: 8%;
    }

    .clock-widget .temp-range {
        font-size: 0.9rem;
    }

    /* Updated time display with 12-hour format and AM/PM */
    .clock-widget .time-display {
        font-size: 19pt;
        line-height: 1em;
        font-variant-numeric: tabular-nums;
        letter-spacing: 1px;
        display: flex;
        align-items: baseline;
        gap: 2px;
    }

    .clock-widget .time-hours-minutes {
        display: flex;
        align-items: baseline;
    }

    .clock-widget .seconds-display {
        font-size: 11pt;
        opacity: 0.8;
        font-variant-numeric: tabular-nums;
    }

    .clock-widget .ampm-display {
        font-size: 10pt;
        font-weight: 600;
        opacity: 0.9;
        letter-spacing: 0.5px;
        margin-left: 4px;
    }

    .clock-widget .date-display {
        font-size: 15px;
    }

    .clock-widget .location {
        font-size: 0.9rem;
    }

    .clock-widget .days-section {
        display: flex;
        align-items: center;
        justify-content: space-between;
        width: 100%;
        height: 25%;
        background-color: #974859;
        gap: 2px;
        box-shadow: inset 0px 2px 5px #974859;
    }

    .clock-widget .day-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        height: 100%;
        width: 100%;
        background-color: #a75265;
        box-shadow: inset 0px 2px 5px #974859;
        cursor: pointer;
        transition: 100ms ease;
        gap: 5px;
        border: none;
        background: transparent;
        background-color: #a75265;
    }

    .clock-widget .day-btn:hover {
        transform: scale(0.9);
        border-radius: 10px;
    }

    .clock-widget .day-btn span:first-child {
        font-size: 10pt;
        font-weight: 500;
        color: white;
        opacity: 0.7;
    }

    .clock-widget .day-btn .day-icon {
        display: flex;
        align-items: center;
        width: 20px;
        height: 100%;
    }

    .clock-widget .day-btn .day-icon svg {
        width: 20px;
        height: 20px;
    }

    /* Weather condition colors - dynamic via JS */
    .weather-sunny .bg-design {
        background-color: #ec7263;
    }

    .weather-sunny .bg-circle1,
    .weather-sunny .bg-circle2,
    .weather-sunny .bg-circle3 {
        background-color: #efc745;
    }

    .weather-cloudy .bg-design {
        background-color: #8a9ba8;
    }

    .weather-cloudy .bg-circle1,
    .weather-cloudy .bg-circle2,
    .weather-cloudy .bg-circle3 {
        background-color: #a8b8c4;
    }

    .weather-rainy .bg-design {
        background-color: #5b7a8c;
    }

    .weather-rainy .bg-circle1,
    .weather-rainy .bg-circle2,
    .weather-rainy .bg-circle3 {
        background-color: #7a9aaa;
    }

    .weather-snowy .bg-design {
        background-color: #b8c6d4;
    }

    .weather-snowy .bg-circle1,
    .weather-snowy .bg-circle2,
    .weather-snowy .bg-circle3 {
        background-color: #d4dce6;
    }

    .weather-stormy .bg-design {
        background-color: #4a4a4a;
    }

    .weather-stormy .bg-circle1,
    .weather-stormy .bg-circle2,
    .weather-stormy .bg-circle3 {
        background-color: #6a6a6a;
    }

    /* Time separator pulse animation */
    @keyframes pulseOpacity {

        0%,
        100% {
            opacity: 1;
        }

        50% {
            opacity: 0.2;
        }
    }

    .time-separator {
        animation: pulseOpacity 1s step-end infinite;
        display: inline-block;
        margin: 0 1px;
    }
</style>

<div class="main-content">
    <div class="header-section">
        <div>
            <h1><?php echo ($role === 'admin') ? 'Admin Portal' : 'Staff Portal'; ?></h1>
            <h2>Welcome back, <?php echo htmlspecialchars(Session::get('username') ?? 'User'); ?></h2>

            <?php if ($role !== 'admin' && $currentCounter): ?>
                <div class="d-inline-flex align-items-center gap-2 mt-3 px-3 py-1 bg-primary bg-opacity-10 text-primary rounded-pill fw-semibold" style="font-size: 13px;">
                    <span class="d-inline-block rounded-circle bg-primary" style="width: 8px; height: 8px;"></span>
                    Counter: <?= htmlspecialchars($currentCounter['name']) ?>
                </div>
            <?php elseif ($role !== 'admin'): ?>
                <div class="d-inline-flex align-items-center gap-2 mt-3 px-3 py-1 bg-danger bg-opacity-10 text-danger rounded-pill fw-semibold" style="font-size: 13px;">
                    <span class="d-inline-block rounded-circle bg-danger" style="width: 8px; height: 8px;"></span>
                    No assigned counter
                </div>
            <?php endif; ?>
        </div>

        <!-- Header info removed - clock is now floating -->
    </div>

    <?php if ($role === 'admin'): ?>
        <!-- Top Cards for Admin -->
        <div class="dashboard-grid">
            <a href="/modules/admin/service_management/services.php" class="card">
                <div class="card-content-wrapper">
                    <div class="card-left">
                        <h3>Services Active</h3>
                        <span class="card-link-hint">Manage Services &rarr;</span>
                    </div>
                    <div class="card-right">
                        <div class="value-large"><?= htmlspecialchars($servicesCount) ?></div>
                    </div>
                </div>
            </a>
            <a href="/modules/admin/service_management/counters.php" class="card">
                <div class="card-content-wrapper">
                    <div class="card-left">
                        <h3>Active Counters</h3>
                        <span class="card-link-hint">Manage Counters &rarr;</span>
                    </div>
                    <div class="card-right">
                        <div class="value-large"><?= htmlspecialchars($countersCount) ?></div>
                    </div>
                </div>
            </a>
            <a href="/modules/admin/users/index.php" class="card">
                <div class="card-content-wrapper">
                    <div class="card-left">
                        <h3>Total Users</h3>
                        <span class="card-link-hint">Manage Users &rarr;</span>
                    </div>
                    <div class="card-right">
                        <div class="value-large"><?= htmlspecialchars($usersCount) ?></div>
                    </div>
                </div>
            </a>
            <a href="/modules/admin/records/index.php" class="card">
                <div class="card-content-wrapper">
                    <div class="card-left">
                        <h3>Ticket Records</h3>
                        <span class="card-link-hint">View Records &rarr;</span>
                    </div>
                    <div class="card-right">
                        <div class="value-large"><?= htmlspecialchars($recordsCount) ?></div>
                    </div>
                </div>
            </a>
        </div>

        <!-- Bottom Section: Queue Monitoring -->
        <div class="card card-staff">
            <h3 class="mb-4">Recent Queue Activity</h3>

            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Ticket Number</th>
                            <th>Service</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($recentTickets)): ?>
                            <?php foreach ($recentTickets as $ticket): ?>
                                <?php
                                $statusClass = 'badge-default';
                                if ($ticket['status'] == 'called') $statusClass = 'badge-called';
                                elseif ($ticket['status'] == 'serving') $statusClass = 'badge-serving';
                                elseif ($ticket['status'] == 'done') $statusClass = 'badge-done';
                                ?>
                                <tr>
                                    <td><strong class="text-primary"><?= htmlspecialchars($ticket['ticket_number']) ?></strong></td>
                                    <td><?= htmlspecialchars($ticket['service_name']) ?></td>
                                    <td><span class="nexus-badge <?= $statusClass ?>"><?= ucfirst(htmlspecialchars($ticket['status'])) ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" class="text-center text-muted py-5">No recent queue activity recorded.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php else: ?>
        <!-- Staff Dashboard (Queue Management Integration) -->
        <?php if ($currentCounter): ?>
            <div class="row g-4 mb-4">
                <!-- Current Serving Area -->
                <div class="col-md-6">
                    <div class="card card-staff text-center p-4 h-100 d-flex flex-column justify-content-between">
                        <?php if ($currentTicket): ?>
                            <div>
                                <span class="nexus-badge badge-serving text-uppercase" style="font-size: 11px; letter-spacing: 0.05em; margin-bottom: 15px;">Currently <?= ucfirst($currentTicket['status']) ?></span>
                                <div class="display-1 fw-bold text-primary lh-1 my-3" style="font-size: 72px; letter-spacing: -0.02em;">
                                    <?= htmlspecialchars($currentTicket['ticket_number']) ?>
                                </div>
                                <p class="text-muted mt-2">
                                    <strong class="text-dark fs-5"><?= htmlspecialchars($currentTicket['name'] ?? $currentTicket['citizen_category']) ?></strong><br>
                                    <span class="fs-6"><?= htmlspecialchars($currentTicket['service_name']) ?></span>
                                </p>
                            </div>

                            <form method="POST" class="mt-4 d-flex gap-2 justify-content-center">
                                <?php if ($currentTicket['status'] === 'called'): ?>
                                    <button type="submit" name="action" value="serve" class="btn btn-success px-4 py-2 fw-semibold">Serve Client</button>
                                <?php elseif ($currentTicket['status'] === 'serving'): ?>
                                    <button type="submit" name="action" value="done" class="btn btn-primary px-4 py-2 fw-semibold">Mark Done</button>
                                <?php endif; ?>
                                <button type="submit" name="action" value="no_show" class="btn btn-danger px-3 py-2 fw-semibold" onclick="return confirm('Mark this ticket as no-show?');">No Show</button>
                            </form>
                        <?php else: ?>
                            <div class="py-4">
                                <h3 class="text-muted mb-3">Ready to Call Next</h3>
                                <div class="display-1 fw-bold text-secondary lh-1 my-3" style="font-size: 72px;">
                                    ----
                                </div>
                                <p class="text-muted fs-6">The queue is ready for the next available ticket.</p>
                            </div>
                            <form method="POST" class="mt-3">
                                <button type="submit" name="action" value="call_next" class="btn btn-primary px-4 py-3 fw-semibold w-100" style="max-width: 280px; <?php echo empty($waitingList) ? 'opacity: 0.5; cursor: not-allowed;' : ''; ?>" <?php echo empty($waitingList) ? 'disabled' : ''; ?>>
                                    Call Next Client
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Up Next List -->
                <div class="col-md-6">
                    <div class="card card-staff p-4 h-100">
                        <h3 class="d-flex justify-content-between align-items-center mt-0 mb-3">
                            <span>Waiting List</span>
                            <span class="nexus-badge bg-light text-dark"><?= count($waitingList) ?> in queue</span>
                        </h3>
                        <div class="overflow-auto" style="height: 340px; padding-right: 4px;">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Ticket No.</th>
                                        <th>Name / Category</th>
                                        <th>Service</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($waitingList)): ?>
                                        <?php foreach ($waitingList as $ticket): ?>
                                            <tr>
                                                <td><strong class="text-dark"><?= htmlspecialchars($ticket['ticket_number']) ?></strong></td>
                                                <td><?= htmlspecialchars($ticket['name'] ?? $ticket['citizen_category']) ?></td>
                                                <td><span class="text-muted small"><?= htmlspecialchars($ticket['service_name']) ?></span></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="3" class="text-center text-muted py-5">No tickets currently waiting in queue.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <script>
                // Auto-refresh the page every 10 seconds if we're NOT currently serving a ticket
                <?php if (!$currentTicket): ?>
                    setTimeout(function() {
                        window.location.reload();
                    }, 10000);
                <?php endif; ?>
            </script>
        <?php else: ?>
            <div class="card card-staff text-center p-5">
                <p class="text-muted fs-6 mx-auto" style="max-width: 500px; line-height: 1.5;">
                    Please contact an administrator to assign a counter to your account before you can manage queues.
                </p>
            </div>
        <?php endif; ?>
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
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>