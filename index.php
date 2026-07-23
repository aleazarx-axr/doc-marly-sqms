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

<div class="main-content">
    <div class="header-section">
        <div>
            <h1><?php echo ($role === 'admin') ? 'Admin Portal' : 'Staff Portal'; ?></h1>
            <h2>Welcome, <?php echo htmlspecialchars(Session::get('username') ?? 'User'); ?>!</h2>
            
            <?php if ($role !== 'admin' && $currentCounter): ?>
                <div style="display: inline-block; margin-top: 10px; padding: 5px 15px; background: #2a296f; color: #fff; border-radius: 8px; font-weight: 600;">
                    Current Counter: <?= htmlspecialchars($currentCounter['name']) ?>
                </div>
            <?php elseif ($role !== 'admin'): ?>
                <div style="display: inline-block; margin-top: 10px; padding: 5px 15px; background: #dc3545; color: #fff; border-radius: 8px; font-weight: 600;">
                    No assigned counter
                </div>
            <?php endif; ?>
        </div>
        <div>
            <strong>Date and Time:</strong> <br>
            <?php echo date('Y-m-d H:i:s'); ?>
        </div>
    </div>

    <?php if ($role === 'admin'): ?>
        <!-- Top Cards for Admin -->
        <div class="dashboard-grid">
            <div class="card">
                <h3>No. of Services</h3>
                <div class="value"><?= htmlspecialchars($servicesCount) ?></div>
                <a href="/modules/admin/service_management/services.php">View Services Page</a>
            </div>
            <div class="card">
                <h3>No. of Counters</h3>
                <div class="value"><?= htmlspecialchars($countersCount) ?></div>
                <a href="/modules/admin/service_management/counters.php">View Counters Page</a>
            </div>
            <div class="card">
                <h3>No. of Users</h3>
                <div class="value"><?= htmlspecialchars($usersCount) ?></div>
                <a href="/modules/admin/users/index.php">View Users Page</a>
            </div>
            <div class="card">
                <h3>No. of Records</h3>
                <div class="value"><?= htmlspecialchars($recordsCount) ?></div>
                <a href="/modules/admin/records/index.php">View Records Page</a>
            </div>
        </div>

        <!-- Bottom Section: Queue Monitoring -->
        <div class="card">
            <h3>Recent Queue Activity</h3>

            <table>
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
                            <tr>
                                <td><strong><?= htmlspecialchars($ticket['ticket_number']) ?></strong></td>
                                <td><?= htmlspecialchars($ticket['service_name']) ?></td>
                                <td><?= ucfirst(htmlspecialchars($ticket['status'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" style="text-align: center; color: #999; padding: 20px;">No recent queue activity.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <!-- Staff Dashboard (Queue Management Integration) -->
        <?php if ($currentCounter): ?>
            <div style="display: flex; gap: 20px; margin-bottom: 20px;">
                <!-- Current Serving Area -->
                <div class="card" style="flex: 1; text-align: center; padding: 40px 20px; border: 2px solid #2a296f;">
                    <?php if ($currentTicket): ?>
                        <h3 style="color: #666; margin-top: 0;">Currently <?= ucfirst($currentTicket['status']) ?></h3>
                        <div style="font-size: 64px; font-weight: bold; color: #2a296f; margin: 20px 0;">
                            <?= htmlspecialchars($currentTicket['ticket_number']) ?>
                        </div>
                        <p style="font-size: 18px; color: #555;">
                            <strong><?= htmlspecialchars($currentTicket['name'] ?? $currentTicket['citizen_category']) ?></strong><br>
                            <?= htmlspecialchars($currentTicket['service_name']) ?>
                        </p>
                        
                        <form method="POST" style="margin-top: 30px; display: flex; gap: 10px; justify-content: center;">
                            <?php if ($currentTicket['status'] === 'called'): ?>
                                <button type="submit" name="action" value="serve" class="btn" style="background: #28a745; font-size: 16px; padding: 10px 30px; height: auto;">Serve</button>
                            <?php elseif ($currentTicket['status'] === 'serving'): ?>
                                <button type="submit" name="action" value="done" class="btn" style="background: #007bff; font-size: 16px; padding: 10px 30px; height: auto;">Mark Done</button>
                            <?php endif; ?>
                            <button type="submit" name="action" value="no_show" class="btn" style="background: #dc3545; font-size: 16px; padding: 10px 20px; height: auto;" onclick="return confirm('Mark this ticket as no-show?');">No Show</button>
                        </form>
                    <?php else: ?>
                        <h3 style="color: #999; margin-top: 0;">Ready to Call</h3>
                        <div style="font-size: 64px; font-weight: bold; color: #ddd; margin: 20px 0;">
                            ----
                        </div>
                        <form method="POST" style="margin-top: 30px;">
                            <button type="submit" name="action" value="call_next" class="btn" style="background: #2a296f; font-size: 18px; padding: 15px 40px; height: auto; <?php echo empty($waitingList) ? 'opacity: 0.5; cursor: not-allowed;' : ''; ?>" <?php echo empty($waitingList) ? 'disabled' : ''; ?>>
                                Call Next
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
                
                <!-- Up Next List -->
                <div class="card" style="flex: 1;">
                    <h3 style="margin-top: 0; border-bottom: 1px solid #eee; padding-bottom: 10px;">Waiting List</h3>
                    <div style="height: 300px; overflow-y: auto;">
                        <table style="width: 100%;">
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
                                            <td><strong><?= htmlspecialchars($ticket['ticket_number']) ?></strong></td>
                                            <td><?= htmlspecialchars($ticket['name'] ?? $ticket['citizen_category']) ?></td>
                                            <td><?= htmlspecialchars($ticket['service_name']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3" style="text-align: center; color: #999; padding: 20px;">No tickets in queue</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
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
            <p style="padding: 20px; text-align: center; color: #666;">Please contact an administrator to assign a counter to your account before you can manage queues.</p>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>