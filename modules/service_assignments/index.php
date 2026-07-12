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

$pageTitle = 'Service Assignments - Admin Portal';
$activeMenu = 'assignments';

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar_admin.php';
?>

<div class="main-content">
    <div style="margin-bottom: 15px;">
        <h2>Service Assignments (Deployments)</h2>
        <p style="color: #666; font-size: 14px;">Assign which services are available at each specific site.</p>
    </div>

    <?php if(isset($_GET['status'])): ?>
        <?php
            $status = $_GET['status'];
            $msg = "Action completed successfully.";
            $color = "green";
            if ($status == 'error') { $msg = "An error occurred."; $color = "red"; }
            if ($status == 'edited') { $msg = "Assignments updated successfully."; $color = "blue"; }
        ?>
        <p style="color: <?php echo $color; ?>; background: #f0f9f0; padding: 10px; border: 1px solid <?php echo $color; ?>; margin-bottom: 15px;">
            <?php echo $msg; ?>
        </p>
    <?php endif; ?>

    <table>
        <thead>
            <tr><th>Site Name</th><th>Site Type</th><th>Actions</th></tr>
        </thead>
        <tbody>
            <?php if (count($sites) > 0): ?>
                <?php foreach ($sites as $row): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo ucfirst(htmlspecialchars($row['type'])); ?></td>
                        <td>
                            <a href="assign.php?id=<?php echo $row['id']; ?>" style="margin-right: 5px; color: purple; text-decoration: underline;">Assign Services</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="3">No active sites found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
