<?php
require_once __DIR__ . '/../../../includes/functions.php';
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../includes/models/User.php';

Session::requireRole('admin');

$db = new Database();
$conn = $db->getConnection();
$userModel = new User($conn);

$view = $_GET['view'] ?? 'active';

$stmtUsers = $userModel->read();
$users = [];
while ($row = $stmtUsers->fetch(PDO::FETCH_ASSOC)) {
    if ($view === 'archived' && $row['status'] === 'archived') {
        $users[] = $row;
    } elseif ($view !== 'archived' && $row['status'] !== 'archived') {
        $users[] = $row;
    }
}

$pageTitle = $view === 'archived' ? 'Archived Users - Admin Portal' : 'Manage Users - Admin Portal';
$activeMenu = 'users';

require_once __DIR__ . '/../../../includes/header.php';
require_once __DIR__ . '/../../../includes/sidebar_admin.php';
?>

<div class="main-content">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
        <h2><?php echo $view === 'archived' ? 'Archived Users' : 'Manage Users'; ?></h2>
        <div>
            <?php if ($view === 'archived'): ?>
                <a href="index.php" style="color: blue; text-decoration: underline; margin-right: 15px;">View Active Users</a>
            <?php else: ?>
                <a href="index.php?view=archived" style="color: gray; text-decoration: underline; margin-right: 15px;">View Archives</a>
            <?php endif; ?>
            <a href="add.php" style="color: blue; text-decoration: underline; font-size: 16px;">+ Add User</a>
        </div>
    </div>

    <?php if(isset($_GET['status'])): ?>
        <?php
            $status = $_GET['status'];
            $msg = "Action completed successfully.";
            $color = "green";
            if ($status == 'error') { $msg = "An error occurred."; $color = "red"; }
            if ($status == 'deleted') { $msg = "User deleted successfully."; $color = "orange"; }
            if ($status == 'edited') { $msg = "User updated successfully."; $color = "blue"; }
            if ($status == 'added') { $msg = "User added successfully."; $color = "green"; }
            if ($status == 'resent') { $msg = "Setup link regenerated and resent."; $color = "green"; }
            if ($status == 'resend_error') { $msg = "Failed to resend setup link."; $color = "red"; }
            if ($status == 'archived') { $msg = "User successfully archived."; $color = "orange"; }
            if ($status == 'restored') { $msg = "User successfully restored."; $color = "green"; }
        ?>
        <p style="color: <?php echo $color; ?>; background: #f0f9f0; padding: 10px; border: 1px solid <?php echo $color; ?>; margin-bottom: 15px;">
            <?php echo $msg; ?>
        </p>
    <?php endif; ?>

    <table>
        <thead>
            <tr><th>ID</th><th>Username</th><th>Email</th><th>Role</th><th>Status</th><th>Actions</th></tr>
        </thead>
        <tbody>
            <?php if (count($users) > 0): ?>
                <?php foreach ($users as $row): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['id']); ?></td>
                        <td><?php echo htmlspecialchars($row['username']); ?></td>
                        <td><?php echo htmlspecialchars($row['email'] ?? ''); ?></td>
                        <td><?php echo ucwords(str_replace('_', ' ', htmlspecialchars($row['role']))); ?></td>
                        <td>
                            <?php if ($row['status'] === 'archived'): ?>
                                <span style="color: gray;">Archived</span>
                            <?php elseif (!empty($row['setup_token'])): ?>
                                <span style="color: orange;">Pending Setup</span>
                                <?php if (strtotime($row['token_expires']) < time()): ?>
                                    <span style="color: red; font-size: 12px;">(Expired)</span>
                                <?php endif; ?>
                            <?php else: ?>
                                <span style="color: green;">Active</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($row['status'] !== 'archived'): ?>
                                <a href="edit.php?id=<?php echo $row['id']; ?>" style="margin-right: 5px; color: blue; text-decoration: underline;">Edit</a>
                            <?php endif; ?>
                            
                            <?php if (!empty($row['setup_token']) && $row['status'] !== 'archived'): ?>
                                <form action="resend_setup.php" method="POST" style="display:inline;" onsubmit="return confirm('Generate a new setup link and resend email?');">
                                    <input type="hidden" name="user_id" value="<?php echo $row['id']; ?>">
                                    <button type="submit" style="margin-right: 5px; cursor: pointer; color: #007bff; background: none; border: none; text-decoration: underline;">Resend Link</button>
                                </form>
                            <?php endif; ?>
                            
                            <?php if ($row['status'] === 'archived'): ?>
                                <form action="restore.php" method="POST" style="display:inline;" onsubmit="return confirm('Restore this user? They will be able to log in again.');">
                                    <input type="hidden" name="user_id" value="<?php echo $row['id']; ?>">
                                    <button type="submit" style="cursor: pointer; color: green; background: none; border: none; text-decoration: underline;">Restore</button>
                                </form>
                            <?php elseif ($row['username'] !== $_SESSION['user_id']): ?>
                                <form action="archive.php" method="POST" style="display:inline;" onsubmit="return confirm('Archive this user? They will no longer be able to log in.');">
                                    <input type="hidden" name="user_id" value="<?php echo $row['id']; ?>">
                                    <button type="submit" style="cursor: pointer; color: orange; background: none; border: none; text-decoration: underline;">Archive</button>
                                </form>
                            <?php else: ?>
                                <span style="color: gray; margin-left: 5px;">(You)</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="4">No users found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../../../includes/footer.php'; ?>
