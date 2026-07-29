<?php
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/database.php';

Session::requireRole('admin');

$db = new Database();
$conn = $db->getConnection();

// Fetch auth logs joined with users to get their roles, latest first
$query = "
    SELECT 
        l.id, 
        l.username, 
        l.ip_address, 
        l.user_agent, 
        l.event_type, 
        l.created_at,
        u.role,
        u.name
    FROM auth_logs l
    LEFT JOIN users u ON l.user_id = u.id
    ORDER BY l.created_at DESC
    LIMIT 500
";
$stmt = $conn->prepare($query);
$stmt->execute();
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = 'Activity Logs - Admin Portal';
$activeMenu = 'logs';

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar_admin.php';
?>

<div class="main-content">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
        <h2>Activity Logs</h2>
    </div>
    
    <p class="text-muted mb-4">Showing the latest authentication events (login, logout, lockouts, etc.).</p>

    <div class="table-responsive">
        <table class="table table-striped table-hover align-middle bg-white shadow-sm rounded">
            <thead class="table-light">
                <tr>
                    <th>Date & Time</th>
                    <th>User</th>
                    <th>Role</th>
                    <th>Event</th>
                    <th>IP Address</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($logs) > 0): ?>
                    <?php foreach ($logs as $log): ?>
                        <tr>
                            <td><?php echo date('M d, Y h:i A', strtotime($log['created_at'])); ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($log['name'] ?? $log['username']); ?></strong><br>
                                <small class="text-muted">@<?php echo htmlspecialchars($log['username']); ?></small>
                            </td>
                            <td>
                                <?php 
                                    if (!empty($log['role'])) {
                                        echo ucwords(str_replace('_', ' ', htmlspecialchars($log['role']))); 
                                    } else {
                                        echo '<span class="text-muted">Unknown</span>';
                                    }
                                ?>
                            </td>
                            <td>
                                <?php 
                                    $event = $log['event_type'];
                                    $badgeClass = 'bg-secondary';
                                    if ($event === 'login_success') $badgeClass = 'bg-success';
                                    if ($event === 'login_failed') $badgeClass = 'bg-warning text-dark';
                                    if ($event === 'account_lockout') $badgeClass = 'bg-danger';
                                    if ($event === 'logout') $badgeClass = 'bg-info text-dark';
                                ?>
                                <span class="badge <?php echo $badgeClass; ?>">
                                    <?php echo ucwords(str_replace('_', ' ', htmlspecialchars($event))); ?>
                                </span>
                            </td>
                            <td>
                                <small><?php echo htmlspecialchars($log['ip_address']); ?></small>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center py-4">No activity logs found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
