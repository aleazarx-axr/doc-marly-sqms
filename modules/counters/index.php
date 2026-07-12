<?php
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/models/Counter.php';

Session::requireRole('admin');

$db = new Database();
$conn = $db->getConnection();
$counterModel = new Counter($conn);

$stmtCounters = $counterModel->read();
$counters = [];
while ($row = $stmtCounters->fetch(PDO::FETCH_ASSOC)) {
    $counters[] = $row;
}

$pageTitle = 'Manage Counters - Admin Portal';
$activeMenu = 'counters';

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar_admin.php';
?>

<div class="main-content">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
        <h2>Manage Counters / Windows</h2>
        <a href="add.php" style="color: blue; text-decoration: underline; font-size: 16px;">+ Add Counter</a>
    </div>

    <?php if(isset($_GET['status'])): ?>
        <?php
            $status = $_GET['status'];
            $msg = "Action completed successfully.";
            $color = "green";
            if ($status == 'error') { $msg = "An error occurred."; $color = "red"; }
            if ($status == 'archived') { $msg = "Counter archived successfully."; $color = "orange"; }
            if ($status == 'edited') { $msg = "Counter updated successfully."; $color = "blue"; }
            if ($status == 'added') { $msg = "Counter added successfully."; $color = "green"; }
        ?>
        <p style="color: <?php echo $color; ?>; background: #f0f9f0; padding: 10px; border: 1px solid <?php echo $color; ?>; margin-bottom: 15px;">
            <?php echo $msg; ?>
        </p>
    <?php endif; ?>

    <table>
        <thead>
            <tr><th>ID</th><th>Counter Name</th><th>Assigned Site</th><th>Actions</th></tr>
        </thead>
        <tbody>
            <?php if (count($counters) > 0): ?>
                <?php foreach ($counters as $row): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['id']); ?></td>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo htmlspecialchars($row['site_name']); ?></td>
                        <td>
                            <a href="assign.php?id=<?php echo $row['id']; ?>&site_id=<?php echo $row['site_id']; ?>" style="margin-right: 5px; color: purple; text-decoration: underline;">Assign Services</a>
                            <a href="edit.php?id=<?php echo $row['id']; ?>" style="margin-right: 5px; color: blue; text-decoration: underline;">Edit</a>
                            <form action="archive.php" method="POST" style="display:inline;" onsubmit="return confirm('Archive this counter?');">
                                <input type="hidden" name="counter_id" value="<?php echo $row['id']; ?>">
                                <button type="submit" style="cursor: pointer; color: orange; background: none; border: none; text-decoration: underline;">Archive</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="4">No active counters found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
