<?php
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/models/Site.php';

Session::requireRole('admin');

$db = new Database();
$conn = $db->getConnection();
$siteModel = new Site($conn);

$stmtSites = $siteModel->read();
$sites = [];
while ($row = $stmtSites->fetch(PDO::FETCH_ASSOC)) {
    $sites[] = $row;
}

$pageTitle = 'Manage Sites - Admin Portal';
$activeMenu = 'sites';

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar_admin.php';
?>

<div class="main-content">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
        <h2>Manage Sites</h2>
        <a href="add.php" style="color: blue; text-decoration: underline; font-size: 16px;">+ Add Site</a>
    </div>

    <?php if(isset($_GET['status'])): ?>
        <?php
            $status = $_GET['status'];
            $msg = "Action completed successfully.";
            $color = "green";
            if ($status == 'error') { $msg = "An error occurred."; $color = "red"; }
            if ($status == 'archived') { $msg = "Site archived successfully."; $color = "orange"; }
            if ($status == 'edited') { $msg = "Site updated successfully."; $color = "blue"; }
            if ($status == 'added') { $msg = "Site added successfully."; $color = "green"; }
        ?>
        <p style="color: <?php echo $color; ?>; background: #f0f9f0; padding: 10px; border: 1px solid <?php echo $color; ?>; margin-bottom: 15px;">
            <?php echo $msg; ?>
        </p>
    <?php endif; ?>

    <table>
        <thead>
            <tr><th>ID</th><th>Site Name</th><th>Type</th><th>Actions</th></tr>
        </thead>
        <tbody>
            <?php if (count($sites) > 0): ?>
                <?php foreach ($sites as $row): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['id']); ?></td>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo ucfirst(htmlspecialchars($row['type'])); ?></td>
                        <td>
                            <a href="edit.php?id=<?php echo $row['id']; ?>" style="margin-right: 5px; color: blue; text-decoration: underline;">Edit</a>
                            <form action="archive.php" method="POST" style="display:inline;" onsubmit="return confirm('Archive this site?');">
                                <input type="hidden" name="site_id" value="<?php echo $row['id']; ?>">
                                <button type="submit" style="cursor: pointer; color: orange; background: none; border: none; text-decoration: underline;">Archive</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="4">No sites found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
