<?php
// Corrected paths: Go up 3 directory levels to reach the root
require_once __DIR__ . '/../../../includes/functions.php';
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../includes/models/Service.php';

Session::requireRole('admin');

$db = new Database();
$conn = $db->getConnection();
$serviceModel = new Service($conn);

// --- HANDLE POST REQUESTS NATIVELY ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        
        if ($action === 'save_service') {
            if (!empty($_POST['id'])) $serviceModel->id = $_POST['id'];
            $serviceModel->name = trim($_POST['name'] ?? '');
            
            // Auto-generate code from name (uppercase, spaces replaced with underscores)
            $serviceModel->code = strtoupper(preg_replace('/[^a-zA-Z0-9]+/', '_', $serviceModel->name));
            
            $serviceModel->requirements = $_POST['requirements'] ?? '';
            
            // Hardcode defaults for removed fields
            $serviceModel->description = '';
            $serviceModel->prefix = '';
            $serviceModel->starting_number = 1;
            
            $serviceModel->id ? $serviceModel->update() : $serviceModel->create();
        }
        
        if ($action === 'toggle_service') {
            $serviceModel->id = $_POST['id'];
            $serviceModel->toggleStatus();
        }

        if ($action === 'archive_service') {
            $serviceModel->id = $_POST['id'];
            $serviceModel->archive();
        }

        if ($action === 'restore_service') {
            $serviceModel->id = $_POST['id'];
            $serviceModel->restore();
        }
        
        // Refresh page to prevent form resubmission
        $redirectUrl = "services.php" . (isset($_GET['view']) && $_GET['view'] === 'archived' ? '?view=archived' : '');
        header("Location: $redirectUrl");
        exit;
    }
}

// Fetch Services
$view = $_GET['view'] ?? 'active';
$stmtServices = $view === 'archived' ? $serviceModel->readArchived() : $serviceModel->read();
$services = $stmtServices->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = $view === 'archived' ? 'Archived Services - Admin Portal' : 'Manage Services - Admin Portal';
$activeMenu = 'services';
require_once __DIR__ . '/../../../includes/header.php';
require_once __DIR__ . '/../../../includes/sidebar_admin.php';
?>

<div class="main-content">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
        <h2><?php echo $view === 'archived' ? 'Archived Services' : 'Manage Services'; ?></h2>
        <div>
            <?php if ($view === 'archived'): ?>
                <a href="services.php" style="color: blue; text-decoration: underline; margin-right: 15px;">View Active Services</a>
            <?php else: ?>
                <a href="services.php?view=archived" style="color: gray; text-decoration: underline; margin-right: 15px;">View Archives</a>
                <button onclick="document.getElementById('addServiceForm').reset(); document.getElementById('serviceId').value=''; document.getElementById('formTitle').innerText='Add New Service'; document.getElementById('serviceFormContainer').style.display='block';">Add New Service</button>
            <?php endif; ?>
        </div>
    </div>

<?php if ($view !== 'archived'): ?>
<!-- ADD/EDIT SERVICE FORM -->
<div id="serviceFormContainer" style="display: none; margin-bottom: 20px; border: 1px solid #ccc; padding: 15px;">
    <h3 id="formTitle" style="margin-top: 0;">Add New Service</h3>
    <form method="POST" action="services.php" id="addServiceForm">
        <input type="hidden" name="action" value="save_service">
        <input type="hidden" name="id" id="serviceId" value="">
        
        <div style="margin-bottom: 10px;">
            <label>Name:</label><br>
            <input type="text" name="name" required>
        </div>
        <div style="margin-bottom: 10px;">
            <label>Requirements:</label><br>
            <textarea name="requirements" rows="2" cols="40"></textarea>
        </div>
        
        <button type="submit">Save Service</button>
        <button type="button" onclick="document.getElementById('serviceFormContainer').style.display='none'">Cancel</button>
    </form>
</div>
<?php endif; ?>

<!-- FILTER -->
<div style="margin-bottom: 15px;">
    <label>Search by Name or Code:</label>
    <input type="text" id="filterServiceInput" onkeyup="filterServices()" placeholder="Type to search..." style="padding: 5px; width: 250px;">
</div>

<!-- TABLE: LIST SERVICES -->
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Code</th>
            <th>Name</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if (count($services) > 0): ?>
            <?php foreach ($services as $row): ?>
                <tr class="service-row" data-name="<?= htmlspecialchars($row['name']) ?>" data-code="<?= htmlspecialchars($row['code'] ?? '') ?>" data-status="<?= $row['is_active'] ? 'active' : 'inactive' ?>">
                    <td><?= $row['id'] ?></td>
                    <td><strong><?= htmlspecialchars($row['code'] ?? '') ?></strong></td>
                    <td>
                        <?= htmlspecialchars($row['name']) ?>
                    </td>
                    <td><?= $row['is_active'] ? 'Active' : 'Inactive' ?></td>
                    <td>
                        <?php if ($view === 'archived'): ?>
                            <!-- Restore Form -->
                            <form method="POST" action="services.php?view=archived" style="display:inline;" onsubmit="return confirm('Restore this service?');">
                                <input type="hidden" name="action" value="restore_service">
                                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                <button type="submit">Restore</button>
                            </form>
                        <?php else: ?>
                            <!-- Edit Button -->
                            <button onclick="editService(<?= htmlspecialchars(json_encode($row['id'])) ?>, <?= htmlspecialchars(json_encode($row['name'])) ?>, <?= htmlspecialchars(json_encode($row['requirements'] ?? '')) ?>)">Edit</button>
                            
                            <!-- Archive Form -->
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Archive this service?');">
                                <input type="hidden" name="action" value="archive_service">
                                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                <button type="submit">Archive</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="6">No services found.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

</div>

<script>
function editService(id, name, requirements) {
    document.getElementById('formTitle').innerText = 'Edit Service';
    document.getElementById('serviceId').value = id;
    document.querySelector('#addServiceForm input[name="name"]').value = name;
    document.querySelector('#addServiceForm textarea[name="requirements"]').value = requirements;
    document.getElementById('serviceFormContainer').style.display = 'block';
}

function filterServices() {
    const input = document.getElementById('filterServiceInput').value.toLowerCase();
    const rows = document.querySelectorAll('.service-row');
    
    rows.forEach(row => {
        const name = row.getAttribute('data-name').toLowerCase();
        const code = row.getAttribute('data-code').toLowerCase();
        
        const matchSearch = name.includes(input) || code.includes(input);
        
        if (matchSearch) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}
</script>

<?php require_once __DIR__ . '/../../../includes/footer.php'; ?>