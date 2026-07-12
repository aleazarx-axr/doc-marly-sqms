<?php
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/models/CounterService.php';

Session::requireRole('admin');

$db = new Database();
$conn = $db->getConnection();
$counterServiceModel = new CounterService($conn);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $counter_id = $_POST['counter_id'] ?? '';
    $services = $_POST['services'] ?? [];

    if (!empty($counter_id)) {
        // Clear existing assignments and insert new ones
        $stmt = $conn->prepare("DELETE FROM counter_services WHERE counter_id = ?");
        $stmt->execute([$counter_id]);

        foreach ($services as $service_id) {
            $counterServiceModel->counter_id = $counter_id;
            $counterServiceModel->service_id = $service_id;
            $counterServiceModel->create();
        }
        header('Location: index.php?status=edited');
        exit();
    }
    header('Location: index.php?status=error');
    exit();
}

$counter_id = $_GET['id'] ?? '';
$site_id = $_GET['site_id'] ?? '';
if (empty($counter_id) || empty($site_id)) {
    header('Location: index.php');
    exit();
}

// Fetch counter name
$stmt = $conn->prepare("SELECT name FROM counters WHERE id = ?");
$stmt->execute([$counter_id]);
$counterName = $stmt->fetchColumn();

// Fetch services assigned to the site
$stmtSiteSrv = $conn->prepare("
    SELECT s.id, s.name 
    FROM site_services ss 
    JOIN services s ON ss.service_id = s.id 
    WHERE ss.site_id = ? AND s.status = 'active'
");
$stmtSiteSrv->execute([$site_id]);
$siteServices = [];
while ($row = $stmtSiteSrv->fetch(PDO::FETCH_ASSOC)) {
    $siteServices[] = $row;
}

// Fetch currently assigned services for this counter
$stmtCounterSrv = $conn->prepare("SELECT service_id FROM counter_services WHERE counter_id = ?");
$stmtCounterSrv->execute([$counter_id]);
$assignedServices = [];
while ($row = $stmtCounterSrv->fetch(PDO::FETCH_ASSOC)) {
    $assignedServices[] = $row['service_id'];
}

$pageTitle = 'Assign Services to Counter - Admin Portal';
$activeMenu = 'counters';

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar_admin.php';
?>

<div class="main-content">
    <h2>Assign Services to Counter: <?php echo htmlspecialchars($counterName); ?></h2>
    <p style="font-size: 14px; color: #666;">Only services available at this counter's site are shown below.</p>

    <form action="assign.php" method="POST" style="max-width: 500px;">
        <input type="hidden" name="counter_id" value="<?php echo htmlspecialchars($counter_id); ?>">

        <div style="max-height: 300px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; margin-bottom: 20px;">
            <?php if (count($siteServices) > 0): ?>
                <?php foreach ($siteServices as $srv): ?>
                    <div style="margin-bottom: 5px;">
                        <label>
                            <input type="checkbox" name="services[]" value="<?php echo $srv['id']; ?>" <?php echo in_array($srv['id'], $assignedServices) ? 'checked' : ''; ?>>
                            <?php echo htmlspecialchars($srv['name']); ?>
                        </label>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="color: red;">No services have been assigned to this Site yet! Please assign services to the Site first in Service Assignments.</p>
            <?php endif; ?>
        </div>

        <div style="margin-top: 25px;">
            <button type="submit" style="padding: 10px 20px; background-color: blue; color: white; border: none; cursor: pointer;">Save</button>
            <a href="index.php" style="margin-left: 15px; color: gray; text-decoration: underline;">Cancel</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
