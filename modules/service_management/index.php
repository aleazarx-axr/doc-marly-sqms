<?php
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/models/Service.php';
require_once __DIR__ . '/../../includes/models/Requirement.php';
require_once __DIR__ . '/../../includes/models/Site.php';
require_once __DIR__ . '/../../includes/models/Counter.php';
require_once __DIR__ . '/../../includes/models/SiteService.php';

Session::requireRole('admin');

$db = new Database();
$conn = $db->getConnection();

// Fetch Data for Services
$serviceModel = new Service($conn);
$stmtServices = $serviceModel->read();
$services = [];
while ($row = $stmtServices->fetch(PDO::FETCH_ASSOC)) {
    $services[] = $row;
}

// Fetch Data for Requirements
$reqModel = new Requirement($conn);
$stmtReqs = $reqModel->read();
$requirements = [];
while ($row = $stmtReqs->fetch(PDO::FETCH_ASSOC)) {
    $requirements[] = $row;
}

// Fetch Data for Sites
$siteModel = new Site($conn);
$stmtSites = $siteModel->read();
$sites = [];
while ($row = $stmtSites->fetch(PDO::FETCH_ASSOC)) {
    $sites[] = $row;
}

// Fetch Data for Counters
$stmtCounters = $conn->prepare("SELECT c.id, c.site_id, c.name as counter_name, c.status, s.name as site_name FROM counters c LEFT JOIN sites s ON c.site_id = s.id ORDER BY c.id DESC");
$stmtCounters->execute();
$counters = [];
while ($row = $stmtCounters->fetch(PDO::FETCH_ASSOC)) {
    $counters[] = $row;
}

// Fetch Data for Service Assignments
$stmtAssignments = $conn->prepare("SELECT id, name as site_name, type FROM sites WHERE is_archived = 0 ORDER BY id DESC");
$stmtAssignments->execute();
$activeSites = [];
while ($row = $stmtAssignments->fetch(PDO::FETCH_ASSOC)) {
    $activeSites[] = $row;
}

$pageTitle = 'Service Management - Admin Portal';
$activeMenu = 'service_management';

$activeTab = $_GET['tab'] ?? 'services';

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar_admin.php';
?>

<style>
    .tab-bar {
        display: flex;
        border-bottom: 2px solid #ccc;
        margin-bottom: 20px;
    }
    .tab-btn {
        padding: 10px 20px;
        cursor: pointer;
        background: none;
        border: none;
        font-size: 16px;
        color: #666;
        outline: none;
    }
    .tab-btn:hover {
        color: #333;
    }
    .tab-btn.active {
        color: blue;
        border-bottom: 2px solid blue;
        margin-bottom: -2px;
        font-weight: bold;
    }
    .tab-content {
        display: none;
    }
    .tab-content.active {
        display: block;
    }
</style>

<div class="main-content">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
        <h2>Service Management Settings</h2>
    </div>

    <?php if(isset($_GET['status'])): ?>
        <?php
            $status = $_GET['status'];
            $msg = "Action completed successfully.";
            $color = "green";
            if ($status == 'error') { $msg = "An error occurred."; $color = "red"; }
            if ($status == 'archived') { $msg = "Item archived successfully."; $color = "orange"; }
            if ($status == 'edited') { $msg = "Item updated successfully."; $color = "blue"; }
            if ($status == 'added') { $msg = "Item added successfully."; $color = "green"; }
        ?>
        <p style="color: <?php echo $color; ?>; background: #f0f9f0; padding: 10px; border: 1px solid <?php echo $color; ?>; margin-bottom: 15px;">
            <?php echo $msg; ?>
        </p>
    <?php endif; ?>

    <div class="tab-bar">
        <button class="tab-btn <?php echo $activeTab === 'services' ? 'active' : ''; ?>" onclick="openTab('services')">Services</button>
        <button class="tab-btn <?php echo $activeTab === 'requirements' ? 'active' : ''; ?>" onclick="openTab('requirements')">Requirements</button>
        <button class="tab-btn <?php echo $activeTab === 'sites' ? 'active' : ''; ?>" onclick="openTab('sites')">Sites</button>
        <button class="tab-btn <?php echo $activeTab === 'counters' ? 'active' : ''; ?>" onclick="openTab('counters')">Counters</button>
        <button class="tab-btn <?php echo $activeTab === 'assignments' ? 'active' : ''; ?>" onclick="openTab('assignments')">Service Assignments</button>
    </div>

    <!-- SERVICES TAB -->
    <div id="services" class="tab-content <?php echo $activeTab === 'services' ? 'active' : ''; ?>">
        <div style="margin-bottom: 15px;">
            <a href="/modules/services/add.php" style="color: blue; text-decoration: underline; font-size: 16px;">+ Add Service</a>
        </div>
        <table>
            <thead>
                <tr><th>ID</th><th>Name</th><th>Requirements</th><th>Status</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php if (count($services) > 0): ?>
                    <?php foreach ($services as $row): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['id']); ?></td>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><?php echo htmlspecialchars($row['requirements']); ?></td>
                            <td><?php echo $row['is_archived'] ? '<span style="color:red;">Archived</span>' : '<span style="color:green;">Active</span>'; ?></td>
                            <td>
                                <a href="/modules/services/edit.php?id=<?php echo $row['id']; ?>" style="margin-right: 5px; color: blue; text-decoration: underline;">Edit</a>
                                <a href="/modules/services/steps.php?id=<?php echo $row['id']; ?>" style="margin-right: 5px; color: purple; text-decoration: underline;">Steps</a>
                                <?php if (!$row['is_archived']): ?>
                                    <form action="/modules/services/archive.php" method="POST" style="display:inline;" onsubmit="return confirm('Archive this service?');">
                                        <input type="hidden" name="service_id" value="<?php echo $row['id']; ?>">
                                        <button type="submit" style="cursor: pointer; color: orange; background: none; border: none; text-decoration: underline;">Archive</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="5">No services found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- REQUIREMENTS TAB -->
    <div id="requirements" class="tab-content <?php echo $activeTab === 'requirements' ? 'active' : ''; ?>">
        <div style="margin-bottom: 15px;">
            <a href="/modules/requirements/add.php" style="color: blue; text-decoration: underline; font-size: 16px;">+ Add Requirement</a>
        </div>
        <table>
            <thead>
                <tr><th>ID</th><th>Name</th><th>Status</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php if (count($requirements) > 0): ?>
                    <?php foreach ($requirements as $row): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['id']); ?></td>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><?php echo $row['is_archived'] ? '<span style="color:red;">Archived</span>' : '<span style="color:green;">Active</span>'; ?></td>
                            <td>
                                <?php if (!$row['is_archived']): ?>
                                    <form action="/modules/requirements/archive.php" method="POST" style="display:inline;" onsubmit="return confirm('Archive this requirement?');">
                                        <input type="hidden" name="requirement_id" value="<?php echo $row['id']; ?>">
                                        <button type="submit" style="cursor: pointer; color: orange; background: none; border: none; text-decoration: underline;">Archive</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="4">No requirements found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- SITES TAB -->
    <div id="sites" class="tab-content <?php echo $activeTab === 'sites' ? 'active' : ''; ?>">
        <div style="margin-bottom: 15px;">
            <a href="/modules/sites/add.php" style="color: blue; text-decoration: underline; font-size: 16px;">+ Add Site</a>
        </div>
        <table>
            <thead>
                <tr><th>ID</th><th>Site Name</th><th>Type</th><th>Status</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php if (count($sites) > 0): ?>
                    <?php foreach ($sites as $row): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['id']); ?></td>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><?php echo ucfirst(htmlspecialchars($row['type'])); ?></td>
                            <td><?php echo $row['is_archived'] ? '<span style="color:red;">Archived</span>' : '<span style="color:green;">Active</span>'; ?></td>
                            <td>
                                <a href="/modules/sites/edit.php?id=<?php echo $row['id']; ?>" style="margin-right: 5px; color: blue; text-decoration: underline;">Edit</a>
                                <?php if (!$row['is_archived']): ?>
                                    <form action="/modules/sites/archive.php" method="POST" style="display:inline;" onsubmit="return confirm('Archive this site?');">
                                        <input type="hidden" name="site_id" value="<?php echo $row['id']; ?>">
                                        <button type="submit" style="cursor: pointer; color: orange; background: none; border: none; text-decoration: underline;">Archive</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="5">No sites found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- COUNTERS TAB -->
    <div id="counters" class="tab-content <?php echo $activeTab === 'counters' ? 'active' : ''; ?>">
        <div style="margin-bottom: 15px;">
            <a href="/modules/counters/add.php" style="color: blue; text-decoration: underline; font-size: 16px;">+ Add Counter</a>
        </div>
        <table>
            <thead>
                <tr><th>ID</th><th>Counter Name</th><th>Site</th><th>Status</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php if (count($counters) > 0): ?>
                    <?php foreach ($counters as $row): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['id']); ?></td>
                            <td><?php echo htmlspecialchars($row['counter_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['site_name'] ?? 'N/A'); ?></td>
                            <td><?php echo ucfirst(htmlspecialchars($row['status'])); ?></td>
                            <td>
                                <a href="/modules/counters/edit.php?id=<?php echo $row['id']; ?>" style="margin-right: 5px; color: blue; text-decoration: underline;">Edit</a>
                                <a href="/modules/counters/assign.php?id=<?php echo $row['id']; ?>&site_id=<?php echo $row['site_id'] ?? ''; ?>" style="margin-right: 5px; color: purple; text-decoration: underline;">Assign Services</a>
                                <?php if ($row['status'] == 'active'): ?>
                                    <form action="/modules/counters/archive.php" method="POST" style="display:inline;" onsubmit="return confirm('Set counter to inactive?');">
                                        <input type="hidden" name="counter_id" value="<?php echo $row['id']; ?>">
                                        <button type="submit" style="cursor: pointer; color: orange; background: none; border: none; text-decoration: underline;">Set Inactive</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="5">No counters found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- ASSIGNMENTS TAB -->
    <div id="assignments" class="tab-content <?php echo $activeTab === 'assignments' ? 'active' : ''; ?>">
        <table>
            <thead>
                <tr><th>Site ID</th><th>Site Name</th><th>Type</th><th>Assigned Services</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php if (count($activeSites) > 0): ?>
                    <?php foreach ($activeSites as $row): ?>
                        <?php
                            $stmtSrv = $conn->prepare("
                                SELECT s.name 
                                FROM site_services ss 
                                JOIN services s ON ss.service_id = s.id 
                                WHERE ss.site_id = ? AND s.is_archived = 0
                            ");
                            $stmtSrv->execute([$row['id']]);
                            $srvs = $stmtSrv->fetchAll(PDO::FETCH_COLUMN);
                            $servicesList = empty($srvs) ? 'None' : implode(', ', $srvs);
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['id']); ?></td>
                            <td><?php echo htmlspecialchars($row['site_name']); ?></td>
                            <td><?php echo ucfirst(htmlspecialchars($row['type'])); ?></td>
                            <td><?php echo htmlspecialchars($servicesList); ?></td>
                            <td>
                                <a href="/modules/service_assignments/assign.php?site_id=<?php echo $row['id']; ?>" style="margin-right: 5px; color: blue; text-decoration: underline;">Manage Assignments</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="5">No active sites found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    function openTab(tabName) {
        // Update URL state without reloading
        const url = new URL(window.location);
        url.searchParams.set('tab', tabName);
        window.history.pushState({}, '', url);

        // Hide all contents
        const contents = document.querySelectorAll('.tab-content');
        contents.forEach(c => c.classList.remove('active'));

        // Remove active class from all buttons
        const buttons = document.querySelectorAll('.tab-btn');
        buttons.forEach(b => b.classList.remove('active'));

        // Show active tab
        document.getElementById(tabName).classList.add('active');
        
        // Find and highlight active button
        const activeBtn = document.querySelector(`.tab-btn[onclick="openTab('${tabName}')"]`);
        if (activeBtn) activeBtn.classList.add('active');
    }
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
