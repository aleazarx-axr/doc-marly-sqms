<?php
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/models/User.php';

Session::requireRole('admin');

$db = new Database();
$conn = $db->getConnection();
$userModel = new User($conn);

$stmtUsers = $userModel->read();
$users = [];
while ($row = $stmtUsers->fetch(PDO::FETCH_ASSOC)) {
    $users[] = $row;
}

$pageTitle = 'Manage Users - Admin Portal';
$activeMenu = 'users';

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar_admin.php';
?>

<div class="main-content">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
        <h2>Manage Users</h2>
        <a href="add.php" style="color: blue; text-decoration: underline; font-size: 16px;">+ Add User</a>
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
        ?>
        <p style="color: <?php echo $color; ?>; background: #f0f9f0; padding: 10px; border: 1px solid <?php echo $color; ?>; margin-bottom: 15px;">
            <?php echo $msg; ?>
        </p>
    <?php endif; ?>

    <table>
        <thead>
            <tr><th>ID</th><th>Username</th><th>Role</th><th>Actions</th></tr>
        </thead>
        <tbody>
            <?php if (count($users) > 0): ?>
                <?php foreach ($users as $row): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['id']); ?></td>
                        <td><?php echo htmlspecialchars($row['username']); ?></td>
                        <td><?php echo ucfirst(htmlspecialchars($row['role'])); ?></td>
                        <td>
                            <a href="edit.php?id=<?php echo $row['id']; ?>" style="margin-right: 5px; color: blue; text-decoration: underline;">Edit</a>
                            <?php if ($row['username'] !== $_SESSION['user_id']): ?>
                                <form action="delete.php" method="POST" style="display:inline;" onsubmit="return confirm('Permanently delete this user?');">
                                    <input type="hidden" name="user_id" value="<?php echo $row['id']; ?>">
                                    <button type="submit" style="cursor: pointer; color: red; background: none; border: none; text-decoration: underline;">Delete</button>
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

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
