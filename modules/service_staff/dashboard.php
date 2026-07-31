<?php
$content = <<<'PHP'
<?php
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/models/Ticket.php';
require_once __DIR__ . '/../../includes/models/Counter.php';
require_once __DIR__ . '/../../includes/models/Service.php';

Session::requireLogin();
$userId = Session::get('user_id');
$role = Session::get('role');

$db = new Database();
$conn = $db->getConnection();
$ticketModel = new Ticket($conn);
$counterModel = new Counter($conn);
$serviceModel = new Service($conn);

// Handle explicit counter selection via URL
if (isset($_GET['set_counter'])) {
    $cid = (int)$_GET['set_counter'];
    $counterModel->lockCounter($cid, $userId);
    $_SESSION['active_counter_id'] = $cid;
    unset($_SESSION['auto_lock_disabled']);
    header("Location: /service_staff/dashboard");
    exit();
}

if (isset($_GET['force_takeover'])) {
    $cid = (int)$_GET['force_takeover'];
    $counterModel->forceUnlockCounter($cid);
    $counterModel->lockCounter($cid, $userId);
    $_SESSION['active_counter_id'] = $cid;
    header("Location: /service_staff/dashboard");
    exit();
}

// Get staff's counters
$staffCounters = $counterModel->getCountersByStaff($userId);
$currentCounter = null;

if (!empty($staffCounters)) {
    // Check if we have an active session selection
    if (isset($_SESSION['active_counter_id'])) {
        foreach ($staffCounters as $c) {
            if ($c['id'] == $_SESSION['active_counter_id']) {
                $currentCounter = $c;
                break;
            }
        }
    }
    
    // If no valid selection was found, try to find an available counter to default to
    if (!$currentCounter && empty($_SESSION['auto_lock_disabled'])) {
        foreach ($staffCounters as $c) {
            if ($c['current_staff_id'] === null || $c['current_staff_id'] == $userId) {
                $currentCounter = $c;
                $_SESSION['active_counter_id'] = $currentCounter['id'];
                $counterModel->lockCounter($currentCounter['id'], $userId);
                break;
            }
        }
    } else {
        // Ensure the lock is maintained in the DB
        if ($currentCounter['current_staff_id'] != $userId) {
            $counterModel->lockCounter($currentCounter['id'], $userId);
        }
    }
}

$serviceIds = [];
$waitingList = [];
$currentTicket = null;

if ($currentCounter) {
    $serviceIds = $counterModel->getCounterServices($currentCounter['id']);
    $waitingList = $ticketModel->getWaitingList(null);
    $currentTicket = $ticketModel->getCurrentTicket($currentCounter['id']);
}

$allActiveServices = [];
$stmtServices = $serviceModel->read();
while ($row = $stmtServices->fetch(PDO::FETCH_ASSOC)) {
    $allActiveServices[] = $row;
}

// Handle POST actions
$just_called = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'call_next' && $currentCounter) {
        $nextTicket = $ticketModel->getNextInLine($serviceIds);
        if ($nextTicket) {
            $ticketModel->updateStatus($nextTicket['id'], 'called', $currentCounter['id']);
            $_SESSION['just_called'] = true;
        }
    } elseif ($action === 'serve' && $currentTicket) {
        $ticketModel->updateStatus($currentTicket['id'], 'serving', $currentCounter['id']);
    } elseif ($action === 'done' && $currentTicket) {
        $ticketModel->updateStatus($currentTicket['id'], 'done', $currentCounter['id']);
    } elseif ($action === 'no_show' && $currentTicket) {
        $ticketModel->updateStatus($currentTicket['id'], 'no-show', $currentCounter['id']);
    } elseif ($action === 'recall' && $currentTicket) {
        $ticketModel->recallTicket($currentTicket['id']);
        $_SESSION['just_called'] = true;
    } elseif ($action === 'hold' && $currentTicket) {
        $ticketModel->holdTicket($currentTicket['id']);
    } elseif ($action === 'transfer' && $currentTicket) {
        $transfer_service_id = $_POST['transfer_service_id'] ?? null;
        if ($transfer_service_id) {
            $ticketModel->transferTicket($currentTicket['id'], $transfer_service_id);
        }
    } elseif ($action === 'release_counter') {
        $counterModel->unlockCounter($userId);
        unset($_SESSION['active_counter_id']);
        $_SESSION['auto_lock_disabled'] = true;
    } elseif ($action === 'save_requirements' && $currentTicket) {
        $reqs = $_POST['requirements'] ?? [];
        $ticketModel->updateRequirementsChecked($currentTicket['id'], json_encode($reqs));
        $currentTicket['requirements_checked'] = json_encode($reqs);
        $_SESSION['just_called'] = true;
    }
    
    // Do not redirect if it's an AJAX call or we want to stay open. But for simplicity, we redirect.
    header("Location: /service_staff/dashboard");
    exit();
}

$just_called = $_SESSION['just_called'] ?? false;
unset($_SESSION['just_called']);

$pageTitle = 'Queue Management - Staff Portal';
$activeMenu = 'queue';

require_once __DIR__ . '/../../includes/header.php';
if ($role === 'admin') {
    require_once __DIR__ . '/../../includes/sidebar_admin.php';
} else {
    require_once __DIR__ . '/../../includes/sidebar_user.php';
}
?>
<link rel="stylesheet" href="/assets/css/information_staff.css">
<link rel="stylesheet" href="/assets/css/clockwidget_ui.css">
<style>
/* Service Staff Specific Tweaks */
.action-btn { transition: all 0.2s ease; display: inline-flex; align-items: center; justify-content: center; gap: 8px; font-weight: 600; padding: 12px; border-radius: 8px; }
.action-btn:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
.ticket-display { font-size: 3rem; font-weight: 800; color: #242364; letter-spacing: -1px; margin: 10px 0; font-family: monospace; }
.ticket-label { font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px; color: #64748b; font-weight: 700; }
.queue-table-modern { width: 100%; border-collapse: separate; border-spacing: 0 8px; }
.queue-table-modern th { text-transform: uppercase; font-size: 0.75rem; font-weight: 700; color: #64748b; padding: 0 16px 8px; border-bottom: 2px solid #e2e8f0; text-align: left; }
.queue-table-modern td { padding: 16px; background: #f8fafc; }
.queue-table-modern tr td:first-child { border-radius: 8px 0 0 8px; }
.queue-table-modern tr td:last-child { border-radius: 0 8px 8px 0; }
.custom-checkbox .form-check-input { width: 1.2rem; height: 1.2rem; cursor: pointer; }
.custom-checkbox .form-check-label { cursor: pointer; padding-left: 0.25rem; }
</style>

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
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="#ffde00" stroke="none">
                    <path d="M22.5 12.5c0-1.58-.875-2.95-2.148-3.6.154-.435.238-.905.238-1.4 0-2.21-1.71-4-3.82-4-1.285 0-2.43.615-3.12 1.54-.69-.925-1.835-1.54-3.12-1.54-2.11 0-3.82 1.79-3.82 4 0 .495.084.965.238 1.4-1.273.65-2.148 2.02-2.148 3.6 0 .38.055.745.155 1.1-1.345.85-2.178 2.31-2.178 3.9 0 2.21 1.71 4 3.82 4 2.11 0 3.82-1.79 3.82-4 0-.38-.055-.745-.155-1.1 1.345-.85 2.178-2.31 2.178-3.9 0-.38-.055-.745-.155-1.1z"/>
                </svg>
            </span>
        </div>
        <div class="profile-role">
            <span class="info-staff-badge-horizontal">Service Staff</span>
        </div>
    </div>
    
    <!-- Divider -->
    <div class="profile-divider"></div>
    
    <!-- Stats -->
    <div class="profile-stats-horizontal">
        <div class="stat-item-horizontal">
            <span class="stat-number-horizontal"><?php echo count($waitingList ?? []); ?></span>
            <span class="stat-label-horizontal">Waiting</span>
        </div>
        <div class="stat-item-horizontal">
            <span class="stat-number-horizontal"><?php echo count($allActiveServices ?? []); ?></span>
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
    </div>
</div>
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
        <h2 style="margin: 0; color: #0f172a; font-weight: 700; font-size: 1.75rem;"><i class="bi bi-display me-2"></i> Queue Management</h2>
        <?php if ($currentCounter): ?>
            <div style="background: #242364; color: #fff; padding: 8px 16px; border-radius: 999px; font-weight: 600; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 6px rgba(36,35,100,0.2);">
                <i class="bi bi-person-workspace"></i>
                Counter: <?= htmlspecialchars($currentCounter['name']) ?>
            </div>
        <?php else: ?>
            <div style="padding: 10px 20px; background: #fee2e2; color: #b91c1c; border-radius: 8px; font-weight: 600; border: 1px solid #f87171;">
                <i class="bi bi-exclamation-triangle me-2"></i> No counters available. Please contact an administrator.
            </div>
        <?php endif; ?>
    </div>

    <?php if ($currentCounter): ?>
        <div class="row g-4">
            <!-- Left Column: Current Serving Area -->
            <div class="col-lg-4 col-md-12">
                <div class="info-card">
                    <div class="card-header-custom d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-megaphone me-2"></i> Now Serving</span>
                        <?php if ($currentTicket): ?>
                            <span style="background: #10b981; color: #fff; padding: 4px 10px; border-radius: 999px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase;">Active</span>
                        <?php else: ?>
                            <span style="background: #94a3b8; color: #fff; padding: 4px 10px; border-radius: 999px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase;">Idle</span>
                        <?php endif; ?>
                    </div>
                    <div class="card-body-custom text-center pt-4">
                        <?php if ($currentTicket): ?>
                            <div class="ticket-label">Currently <?= ucfirst($currentTicket['status']) ?></div>
                            <div class="ticket-display"><?= htmlspecialchars($currentTicket['ticket_number']) ?></div>
                            <h4 style="color: #1e293b; font-weight: 700; margin: 10px 0 5px;"><?= htmlspecialchars($currentTicket['name'] ?? $currentTicket['citizen_category']) ?></h4>
                            <p style="color: #64748b; margin-bottom: 24px;"><i class="bi bi-tag-fill me-1"></i> <?= htmlspecialchars($currentTicket['service_name']) ?></p>
                            
                            <hr style="border-color: #e2e8f0; margin: 20px 0;">
                            
                            <form method="POST" action="/service_staff/dashboard" style="text-align: left; margin-bottom: 24px;">
                                <input type="hidden" name="action" value="save_requirements">
                                <p class="ticket-label" style="margin-bottom: 12px;">Requirements Checklist</p>
                                <?php 
                                    $rawReqs = $currentTicket['service_requirements'] ?? '';
                                    $reqList = preg_split('/[\n,]+/', $rawReqs);
                                    $reqList = array_map('trim', $reqList);
                                    $reqList = array_filter($reqList);
                                    $checkedReqs = json_decode($currentTicket['requirements_checked'] ?? '[]', true) ?: [];
                                    
                                    if (empty($reqList)):
                                ?>
                                    <p style="background: #f8fafc; padding: 12px; border-radius: 8px; border: 1px dashed #cbd5e1; color: #64748b; font-size: 0.9rem; font-style: italic;">No specific requirements needed.</p>
                                <?php else: ?>
                                    <div style="background: #f8fafc; padding: 16px; border-radius: 12px; border: 1px solid #e2e8f0;">
                                        <?php foreach ($reqList as $index => $req): ?>
                                            <div class="custom-checkbox" style="display: flex; align-items: center; margin-bottom: 8px;">
                                                <input class="form-check-input" type="checkbox" name="requirements[]" value="<?= htmlspecialchars($req) ?>" id="req_<?= $index ?>" <?= in_array($req, $checkedReqs) ? 'checked' : '' ?> style="margin: 0; margin-right: 8px;">
                                                <label class="form-check-label text-dark" for="req_<?= $index ?>" style="font-size: 0.9rem;">
                                                    <?= htmlspecialchars($req) ?>
                                                </label>
                                            </div>
                                        <?php endforeach; ?>
                                        <button type="submit" style="background: #fff; color: #3b82f6; border: 1px solid #3b82f6; padding: 6px 12px; border-radius: 6px; font-weight: 600; font-size: 0.85rem; cursor: pointer; width: 100%; margin-top: 12px; transition: all 0.2s;" onmouseover="this.style.background='#3b82f6'; this.style.color='#fff';" onmouseout="this.style.background='#fff'; this.style.color='#3b82f6';"><i class="bi bi-save me-1"></i> Save Checklist</button>
                                    </div>
                                <?php endif; ?>
                            </form>
                            
                            <!-- Action Buttons -->
                            <form method="POST" action="/service_staff/dashboard" style="margin-bottom: 24px;">
                                <?php if ($currentTicket['status'] === 'called'): ?>
                                    <button type="submit" name="action" value="serve" class="action-btn" style="background: #10b981; color: #fff; border: none; width: 100%; margin-bottom: 12px;"><i class="bi bi-play-circle-fill"></i> Serve Ticket</button>
                                    <button type="submit" name="action" value="recall" class="action-btn" style="background: #f59e0b; color: #fff; border: none; width: 100%; margin-bottom: 12px;"><i class="bi bi-arrow-repeat"></i> Recall Ticket</button>
                                <?php elseif ($currentTicket['status'] === 'serving'): ?>
                                    <button type="submit" name="action" value="done" class="action-btn" style="background: #242364; color: #fff; border: none; width: 100%; margin-bottom: 12px;"><i class="bi bi-check-circle-fill"></i> Mark Done</button>
                                    <button type="submit" name="action" value="hold" class="action-btn" style="background: #64748b; color: #fff; border: none; width: 100%; margin-bottom: 12px;" onclick="return confirm('Put this ticket on hold (returns to waiting list)?');"><i class="bi bi-pause-circle-fill"></i> Put on Hold</button>
                                <?php endif; ?>
                                <button type="submit" name="action" value="no_show" class="action-btn" style="background: transparent; color: #ef4444; border: 1px solid #ef4444; width: 100%;" onmouseover="this.style.background='#ef4444'; this.style.color='#fff';" onmouseout="this.style.background='transparent'; this.style.color='#ef4444';" onclick="return confirm('Mark this ticket as no-show?');"><i class="bi bi-x-circle-fill"></i> No Show</button>
                            </form>
                            
                            <!-- Transfer Form -->
                            <form method="POST" action="/service_staff/dashboard" style="background: #f8fafc; padding: 16px; border-radius: 12px; border: 1px dashed #cbd5e1; text-align: left;">
                                <p class="ticket-label" style="margin-bottom: 8px;"><i class="bi bi-arrow-left-right me-1"></i> Transfer Ticket</p>
                                <input type="hidden" name="action" value="transfer">
                                <select name="transfer_service_id" required class="form-control-custom" style="margin-bottom: 12px;">
                                    <option value="">-- Select Service --</option>
                                    <?php foreach ($allActiveServices as $srv): ?>
                                        <?php if ($srv['id'] != $currentTicket['service_id']): ?>
                                            <option value="<?= $srv['id'] ?>"><?= htmlspecialchars($srv['name']) ?></option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" style="background: #fff; color: #64748b; border: 1px solid #cbd5e1; padding: 8px; border-radius: 6px; font-weight: 600; width: 100%; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.background='#f1f5f9';" onmouseout="this.style.background='#fff';" onclick="return confirm('Transfer this ticket to the selected service?');">Transfer</button>
                            </form>
                        <?php else: ?>
                            <div style="padding: 40px 0;">
                                <i class="bi bi-inbox text-muted" style="font-size: 4rem; opacity: 0.3;"></i>
                                <h3 style="color: #242364; font-weight: 700; margin-top: 20px;">Ready to Call</h3>
                                <p style="color: #64748b; font-size: 0.95rem; margin-bottom: 24px; padding: 0 20px;">Click below to call the next available citizen in the queue for your assigned services.</p>
                                <?php 
                                    $canCallNext = false;
                                    foreach ($waitingList as $t) {
                                        if (in_array($t['service_id'], $serviceIds)) {
                                            $canCallNext = true;
                                            break;
                                        }
                                    }
                                ?>
                                <form method="POST" action="/service_staff/dashboard">
                                    <button type="submit" name="action" value="call_next" class="action-btn" style="background: <?= $canCallNext ? '#242364' : '#94a3b8' ?>; color: #fff; border: none; width: 100%; font-size: 1.1rem; padding: 16px;" <?= !$canCallNext ? 'disabled' : '' ?>>
                                        <i class="bi bi-volume-up-fill"></i> Call Next Ticket
                                    </button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Right Column: Up Next List -->
            <div class="col-lg-8 col-md-12">
                <div class="info-card">
                    <div class="card-header-custom d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-list-ol me-2"></i> Waiting List</span>
                        <span style="background: #3b82f6; color: #fff; padding: 4px 10px; border-radius: 999px; font-size: 0.75rem; font-weight: 700;"><?= count($waitingList) ?> in queue</span>
                    </div>
                    <div class="card-body-custom">
                        <?php if (empty($waitingList)): ?>
                            <div style="text-align: center; padding: 60px 0;">
                                <i class="bi bi-clipboard2-check text-muted" style="font-size: 3rem; opacity: 0.3;"></i>
                                <p style="color: #64748b; margin-top: 16px; font-weight: 700;">No tickets in queue</p>
                            </div>
                        <?php else: ?>
                            <div style="overflow-x: auto;">
                                <table class="queue-table" style="width:100%; border-collapse:collapse;">
                                    <thead>
                                        <tr style="border-bottom: 2px solid #e9ecef;">
                                            <th style="padding:10px 16px; text-align:left; font-size:11px; text-transform:uppercase; letter-spacing:0.05em; color:#6c757d; font-weight:700;">Ticket No.</th>
                                            <th style="padding:10px 16px; text-align:left; font-size:11px; text-transform:uppercase; letter-spacing:0.05em; color:#6c757d; font-weight:700;">Name / Category</th>
                                            <th style="padding:10px 16px; text-align:left; font-size:11px; text-transform:uppercase; letter-spacing:0.05em; color:#6c757d; font-weight:700;">Service</th>
                                            <th style="padding:10px 16px; text-align:left; font-size:11px; text-transform:uppercase; letter-spacing:0.05em; color:#6c757d; font-weight:700;">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody id="waiting-list-body">
                                        <?php foreach ($waitingList as $ticket): ?>
                                            <?php $canCall = in_array($ticket['service_id'], $serviceIds); ?>
                                            <tr style="<?= !$canCall ? 'opacity: 0.5;' : '' ?>">
                                                <td style="padding:12px 16px; vertical-align:middle;">
                                                    <span class="ticket-number" style="background: <?= $canCall ? '#242364' : '#94a3b8' ?>; color: #fff; padding: 6px 12px; border-radius: 6px; font-weight: 700; font-family: monospace; font-size: 0.9rem;">
                                                        <?= htmlspecialchars($ticket['ticket_number']) ?>
                                                    </span>
                                                </td>
                                                <td style="padding:12px 16px; vertical-align:middle; color:#495057; font-weight:700;">
                                                    <?= htmlspecialchars($ticket['name'] ?? $ticket['citizen_category']) ?>
                                                </td>
                                                <td style="padding:12px 16px; vertical-align:middle; color:#495057;">
                                                    <?= htmlspecialchars($ticket['service_name']) ?>
                                                </td>
                                                <td style="padding:12px 16px; vertical-align:middle;">
                                                    <?php if ($canCall): ?>
                                                        <span class="ticket-status-badge waiting" style="background: rgba(59, 130, 246, 0.1); color: #3b82f6; padding: 4px 8px; border-radius: 6px; font-size: 0.75rem; font-weight: 700; white-space: nowrap;">Matching</span>
                                                    <?php else: ?>
                                                        <span class="ticket-status-badge" style="background: rgba(100, 116, 139, 0.1); color: #64748b; padding: 4px 8px; border-radius: 6px; font-size: 0.75rem; font-weight: 700; white-space: nowrap;">Other Counter</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <p style="color: #64748b; margin-top: 20px;">Please contact an administrator to assign a counter to your account before you can manage queues.</p>
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

<script>
// Auto-refresh the waiting list every 10 seconds using AJAX
setInterval(function() {
    fetch('/api/service_staff/waiting_list')
        .then(response => response.text())
        .then(html => {
            document.getElementById('waiting-list-body').innerHTML = html;
        })
        .catch(err => console.error('Error fetching waiting list:', err));
}, 10000);
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
PHP;

file_put_contents('c:\\Users\\Aleazaaar_\\Desktop\\doc-marly-sqms\\scratch\\dashboard_fix.php', $content);
echo "Written completely to scratch/dashboard_fix.php";
