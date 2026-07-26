<?php
require_once __DIR__ . '/../../../includes/functions.php';
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../includes/models/Ticket.php';
require_once __DIR__ . '/../../../includes/models/Counter.php';
require_once __DIR__ . '/../../../includes/models/Service.php';

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
    header("Location: /service_staff/queue");
    exit();
}

if (isset($_GET['force_takeover'])) {
    $cid = (int)$_GET['force_takeover'];
    $counterModel->forceUnlockCounter($cid);
    $counterModel->lockCounter($cid, $userId);
    $_SESSION['active_counter_id'] = $cid;
    header("Location: /service_staff/queue");
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
    header("Location: /service_staff/queue");
    exit();
}

$just_called = $_SESSION['just_called'] ?? false;
unset($_SESSION['just_called']);

$pageTitle = 'Queue Management - Staff Portal';
$activeMenu = 'queue';

require_once __DIR__ . '/../../../includes/header.php';
if ($role === 'admin') {
    require_once __DIR__ . '/../../../includes/sidebar_admin.php';
} else {
    require_once __DIR__ . '/../../../includes/sidebar_user.php';
}
?>
<div class="main-content">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2>Queue Management</h2>
        <?php if ($currentCounter): ?>
            <div>
                <strong>Current Counter:</strong> 
                <?php if (count($staffCounters) > 1): ?>
                    <form method="GET" action="/service_staff/queue" style="display:inline; margin: 0;">
                        <select name="set_counter" onchange="this.form.submit()">
                            <?php foreach ($staffCounters as $c): ?>
                                <?php 
                                    $isOccupied = ($c['current_staff_id'] !== null && $c['current_staff_id'] != $userId);
                                    $label = htmlspecialchars($c['name']);
                                    if ($isOccupied) {
                                        $label .= " (Occupied by " . htmlspecialchars($c['current_staff_username']) . ")";
                                    }
                                ?>
                                <option value="<?= $c['id'] ?>" <?= $c['id'] == $currentCounter['id'] ? 'selected' : '' ?> <?= $isOccupied ? 'disabled' : '' ?>>
                                    <?= $label ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                <?php else: ?>
                    <?= htmlspecialchars($currentCounter['name']) ?>
                <?php endif; ?>
                
                <form method="POST" action="/service_staff/queue" style="display:inline; margin-left: 10px;">
                    <button type="submit" name="action" value="release_counter" onclick="return confirm('Release this counter?');">
                        Release Counter
                    </button>
                </form>
            </div>
            
            <?php 
            // Check if any counters are occupied by someone else, offer Force Takeover link
            $hasOccupied = false;
            foreach ($staffCounters as $c) {
                if ($c['current_staff_id'] !== null && $c['current_staff_id'] != $userId) {
                    $hasOccupied = true;
                    break;
                }
            }
            if ($hasOccupied):
            ?>
            <div style="font-size: 12px; margin-top: 5px; text-align: right;">
                <a href="#" onclick="document.getElementById('forceTakeoverDiv').style.display='block'; return false;" style="color: #666; text-decoration: underline;">Need to force takeover a stuck counter?</a>
                <div id="forceTakeoverDiv" style="display:none; margin-top: 5px;">
                    <form method="GET" action="/service_staff/queue">
                        <select name="force_takeover" style="padding: 2px;">
                            <option value="">-- Select Counter --</option>
                            <?php foreach ($staffCounters as $c): ?>
                                <?php if ($c['current_staff_id'] !== null && $c['current_staff_id'] != $userId): ?>
                                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?> (Force Unlock)</option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" style="padding: 2px 5px;" onclick="return confirm('Are you sure you want to kick the other user off this counter?');">Takeover</button>
                    </form>
                </div>
            </div>
            <?php endif; ?>
        <?php else: ?>
            <div style="padding: 10px 20px; background: #dc3545; color: #fff; border-radius: 8px; font-weight: 600; margin-bottom: 20px;">
                No assigned counter
            </div>
            
            <form method="GET" action="/service_staff/queue">
                <label style="font-weight: bold;">Select a counter to start:</label>
                <select name="set_counter" onchange="this.form.submit()" style="padding: 5px; margin-left: 10px;">
                    <option value="">-- Select Counter --</option>
                    <?php foreach ($staffCounters as $c): ?>
                        <?php 
                            $isOccupied = ($c['current_staff_id'] !== null && $c['current_staff_id'] != $userId);
                            $label = htmlspecialchars($c['name']);
                            if ($isOccupied) {
                                $label .= " (Occupied by " . htmlspecialchars($c['current_staff_username']) . ")";
                            }
                        ?>
                        <option value="<?= $c['id'] ?>" <?= $isOccupied ? 'disabled' : '' ?>>
                            <?= $label ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        <?php endif; ?>
    </div>

    <?php if ($currentCounter): ?>
        <div style="display: flex; gap: 30px; align-items: flex-start; flex-wrap: wrap;">
            <!-- Left Column: Current Serving Area -->
            <div style="flex: 1; min-width: 320px; background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); border: 1px solid #e2e8f0;">
                <?php if ($currentTicket): ?>
                    <h3 style="margin-top: 0; color: #242364;">Currently <?= ucfirst($currentTicket['status']) ?>: <?= htmlspecialchars($currentTicket['ticket_number']) ?></h3>
                    <p style="font-size: 1.1em; color: #333;">
                        <strong><?= htmlspecialchars($currentTicket['name'] ?? $currentTicket['citizen_category']) ?></strong><br>
                        <span style="color: #666;"><?= htmlspecialchars($currentTicket['service_name']) ?></span>
                    </p>
                    <hr style="border-color: #e2e8f0; margin: 15px 0;">
                    
                    <!-- Inline Requirements Checklist -->
                    <form method="POST" style="margin-bottom: 20px;">
                        <input type="hidden" name="action" value="save_requirements">
                        <p class="fw-bold mb-2" style="color: #242364;">Requirements Checklist:</p>
                        <?php 
                            $rawReqs = $currentTicket['service_requirements'] ?? '';
                            $reqList = preg_split('/[\n,]+/', $rawReqs);
                            $reqList = array_map('trim', $reqList);
                            $reqList = array_filter($reqList);
                            $checkedReqs = json_decode($currentTicket['requirements_checked'] ?? '[]', true) ?: [];
                            
                            if (empty($reqList)):
                        ?>
                            <p class="text-muted" style="font-size: 0.9em; font-style: italic;">No specific requirements listed for this service.</p>
                        <?php else: ?>
                            <div style="background: #f8fafc; padding: 15px; border-radius: 8px; border: 1px solid #e2e8f0;">
                                <?php foreach ($reqList as $index => $req): ?>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="requirements[]" value="<?= htmlspecialchars($req) ?>" id="req_<?= $index ?>" <?= in_array($req, $checkedReqs) ? 'checked' : '' ?>>
                                        <label class="form-check-label" style="font-size: 0.95em;" for="req_<?= $index ?>">
                                            <?= htmlspecialchars($req) ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                                <button type="submit" class="btn btn-primary btn-sm mt-2">Save Checklist</button>
                            </div>
                        <?php endif; ?>
                    </form>
                    
                    <!-- Action Buttons -->
                    <form method="POST" style="margin-bottom: 15px;">
                        <?php if ($currentTicket['status'] === 'called'): ?>
                            <button type="submit" name="action" value="serve" class="btn btn-success w-100 mb-2 fw-bold">Serve Ticket</button>
                            <button type="submit" name="action" value="recall" class="btn btn-warning w-100 mb-2 fw-bold text-dark">Recall Ticket</button>
                        <?php elseif ($currentTicket['status'] === 'serving'): ?>
                            <button type="submit" name="action" value="done" class="btn btn-success w-100 mb-2 fw-bold">Mark Done</button>
                            <button type="submit" name="action" value="hold" class="btn btn-secondary w-100 mb-2 fw-bold" onclick="return confirm('Put this ticket on hold (returns to waiting list)?');">Put on Hold</button>
                        <?php endif; ?>
                        <button type="submit" name="action" value="no_show" class="btn btn-danger w-100 fw-bold" onclick="return confirm('Mark this ticket as no-show?');">No Show</button>
                    </form>
                    
                    <!-- Transfer Form -->
                    <form method="POST" style="background: #f8fafc; padding: 15px; border-radius: 8px; border: 1px solid #e2e8f0;">
                        <p style="margin: 0 0 10px 0; font-weight: 600; font-size: 0.9em; color: #64748b;">Transfer Ticket</p>
                        <input type="hidden" name="action" value="transfer">
                        <select name="transfer_service_id" required class="form-select mb-2">
                            <option value="">-- Select Service --</option>
                            <?php foreach ($allActiveServices as $srv): ?>
                                <?php if ($srv['id'] != $currentTicket['service_id']): ?>
                                    <option value="<?= $srv['id'] ?>"><?= htmlspecialchars($srv['name']) ?></option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="btn btn-outline-secondary w-100 btn-sm" onclick="return confirm('Transfer this ticket to the selected service?');">Transfer</button>
                    </form>
                <?php else: ?>
                    <h3 style="margin-top: 0; color: #242364; text-align: center;">Ready to Call</h3>
                    <p style="text-align: center; color: #64748b; margin-bottom: 20px;">No ticket currently assigned. Click below to call the next available citizen in the queue.</p>
                    <?php 
                        $canCallNext = false;
                        foreach ($waitingList as $t) {
                            if (in_array($t['service_id'], $serviceIds)) {
                                $canCallNext = true;
                                break;
                            }
                        }
                    ?>
                    <form method="POST">
                        <button type="submit" name="action" value="call_next" class="btn btn-primary w-100 py-3 fw-bold" style="font-size: 1.1em; border-radius: 8px;" <?= !$canCallNext ? 'disabled' : '' ?>>
                            Call Next Ticket
                        </button>
                    </form>
                <?php endif; ?>
            </div>
            
            <!-- Right Column: Up Next List -->
            <div style="flex: 2; min-width: 400px; background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); border: 1px solid #e2e8f0;">
                <h3 style="margin-top: 0; color: #242364;">Waiting List</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Ticket No.</th>
                            <th>Name / Category</th>
                            <th>Service</th>
                        </tr>
                    </thead>
                    <tbody id="waiting-list-body">
                        <?php if (!empty($waitingList)): ?>
                            <?php foreach ($waitingList as $ticket): ?>
                                <?php $canCall = in_array($ticket['service_id'], $serviceIds); ?>
                                <tr style="<?= !$canCall ? 'opacity: 0.5; background-color: #f8f9fa;' : '' ?>">
                                    <td><strong><?= htmlspecialchars($ticket['ticket_number']) ?></strong></td>
                                    <td><?= htmlspecialchars($ticket['name'] ?? $ticket['citizen_category']) ?></td>
                                    <td><?= htmlspecialchars($ticket['service_name']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3">No tickets in queue</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php else: ?>
        <p>Please contact an administrator to assign a counter to your account before you can manage queues.</p>
    <?php endif; ?>
</div>

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



<?php require_once __DIR__ . '/../../../includes/footer.php'; ?>
