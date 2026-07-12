<?php
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/models/SiteService.php';
require_once __DIR__ . '/../../includes/models/Service.php';

Session::requireRole('admin');

$db = new Database();
$conn = $db->getConnection();
$siteServiceModel = new SiteService($conn);
$serviceModel = new Service($conn);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $site_id = $_POST['site_id'] ?? '';
    $services = $_POST['services'] ?? [];

    if (!empty($site_id)) {
        // Clear existing assignments and insert new ones
        $stmt = $conn->prepare("DELETE FROM site_services WHERE site_id = ?");
        $stmt->execute([$site_id]);

        foreach ($services as $service_id) {
            $siteServiceModel->site_id = $site_id;
            $siteServiceModel->service_id = $service_id;
            $siteServiceModel->create();
        }
        header('Location: index.php?status=edited');
        exit();
    }
    header('Location: index.php?status=error');
    exit();
}

$site_id = $_GET['id'] ?? '';
if (empty($site_id)) {
    header('Location: index.php');
    exit();
}

// Fetch site name
$stmt = $conn->prepare("SELECT name FROM sites WHERE id = ?");
$stmt->execute([$site_id]);
$siteName = $stmt->fetchColumn();

// Fetch all active services
$stmtServices = $serviceModel->read();
$allServices = [];
while ($row = $stmtServices->fetch(PDO::FETCH_ASSOC)) {
    $allServices[] = $row;
}

// Fetch currently assigned services for this site
$stmtSiteSrv = $conn->prepare("SELECT service_id FROM site_services WHERE site_id = ?");
$stmtSiteSrv->execute([$site_id]);
$assignedServices = [];
while ($row = $stmtSiteSrv->fetch(PDO::FETCH_ASSOC)) {
    $assignedServices[] = $row['service_id'];
}

$pageTitle = 'Assign Services - Admin Portal';
$activeMenu = 'assignments';

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar_admin.php';
?>

<div class="main-content">
    <h2>Assign Services to: <?php echo htmlspecialchars($siteName); ?></h2>
    
    <form action="assign.php" method="POST" style="max-width: 500px;">
        <input type="hidden" name="site_id" value="<?php echo htmlspecialchars($site_id); ?>">

        <div style="max-height: 300px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; margin-bottom: 20px;">
            <?php foreach ($allServices as $srv): ?>
                <div style="margin-bottom: 5px;">
                    <label>
                        <input type="checkbox" name="services[]" value="<?php echo $srv['id']; ?>" <?php echo in_array($srv['id'], $assignedServices) ? 'checked' : ''; ?>> 
                        <?php echo htmlspecialchars($srv['name']); ?>
                    </label>
                </div>
            <?php endforeach; ?>
        </div>

        <div style="text-align: right;">
            <button type="submit" style="padding: 10px 20px; background-color: blue; color: white; border: none; cursor: pointer;">Save</button>
            <a href="index.php" style="margin-left: 15px; color: gray; text-decoration: underline;">Cancel</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
