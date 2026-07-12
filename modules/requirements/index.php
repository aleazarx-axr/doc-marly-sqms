<?php
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/models/Requirement.php';

Session::requireRole('admin');

$db = new Database();
$conn = $db->getConnection();
$reqModel = new Requirement($conn);

$stmtReqs = $reqModel->read();
$requirements = [];
while ($row = $stmtReqs->fetch(PDO::FETCH_ASSOC)) {
    $requirements[] = $row;
}

$pageTitle = 'Master List of Requirements - Admin Portal';
$activeMenu = 'requirements';

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar_admin.php';
?>

<div class="main-content">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
        <h2>Master List of Requirements</h2>
        <a href="add.php" style="color: blue; text-decoration: underline; font-size: 16px;">+ Add Requirement</a>
    </div>

    <?php if(isset($_GET['status'])): ?>
        <?php
            $status = $_GET['status'];
            $msg = "Action completed successfully.";
            $color = "green";
            if ($status == 'error') { $msg = "An error occurred."; $color = "red"; }
            if ($status == 'archived') { $msg = "Requirement archived successfully."; $color = "orange"; }
            if ($status == 'added') { $msg = "Requirement added successfully."; $color = "green"; }
        ?>
        <p style="color: <?php echo $color; ?>; background: #f0f9f0; padding: 10px; border: 1px solid <?php echo $color; ?>; margin-bottom: 15px;">
            <?php echo $msg; ?>
        </p>
    <?php endif; ?>

    <table>
        <thead>
            <tr><th>ID</th><th>Requirement Name</th><th>Actions</th></tr>
        </thead>
        <tbody>
            <?php if (count($requirements) > 0): ?>
                <?php foreach ($requirements as $row): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['id']); ?></td>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td>
                            <form action="archive.php" method="POST" style="display:inline;" onsubmit="return confirm('Archive this requirement?');">
                                <input type="hidden" name="req_id" value="<?php echo $row['id']; ?>">
                                <button type="submit" style="cursor: pointer; color: orange; background: none; border: none; text-decoration: underline;">Archive</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="3">No requirements found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
