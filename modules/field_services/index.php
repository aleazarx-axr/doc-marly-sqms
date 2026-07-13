<?php
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/models/Service.php';

require_once __DIR__ . '/../../includes/models/Site.php';

Session::requireRole('admin');

$db = new Database();
$conn = $db->getConnection();

// --- DATA FETCHING ---

// 1. Services (Global)
$serviceModel = new Service($conn);
$stmtServices = $serviceModel->read();
$services = [];
while ($row = $stmtServices->fetch(PDO::FETCH_ASSOC)) {
    $services[] = $row;
}

// 2. Counters (Filtered by 'offsite')
$stmtCounters = $conn->prepare("SELECT c.id, c.site_id, c.name as counter_name, c.status, s.name as site_name FROM counters c JOIN sites s ON c.site_id = s.id WHERE s.type = 'offsite' ORDER BY c.id DESC");
$stmtCounters->execute();
$counters = [];
while ($row = $stmtCounters->fetch(PDO::FETCH_ASSOC)) {
    $counters[] = $row;
}

// Settings Data

// Sites (Filtered by 'offsite')
$stmtSites = $conn->prepare("SELECT * FROM sites WHERE type = 'offsite' ORDER BY id DESC");
$stmtSites->execute();
$sites = [];
while ($row = $stmtSites->fetch(PDO::FETCH_ASSOC)) {
    $sites[] = $row;
}

// Active Sites for Assignments (Filtered by 'offsite')
$stmtAssignments = $conn->prepare("SELECT id, name as site_name, type FROM sites WHERE type = 'offsite' AND is_archived = 0 ORDER BY id DESC");
$stmtAssignments->execute();
$activeSites = [];
while ($row = $stmtAssignments->fetch(PDO::FETCH_ASSOC)) {
    $activeSites[] = $row;
}

$pageTitle = 'Field Services - Admin Portal';
$activeMenu = 'field_services';

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
    .settings-section {
        margin-bottom: 40px;
        padding-bottom: 20px;
        border-bottom: 1px dashed #ccc;
    }
    .settings-section h3 {
        margin-bottom: 15px;
        color: #444;
    }
    .modal {
        display: none; 
        position: fixed; 
        z-index: 1000; 
        left: 0; 
        top: 0; 
        width: 100%; 
        height: 100%; 
        overflow: auto; 
        background-color: rgba(0,0,0,0.5); 
    }
    .modal-content {
        background-color: #fefefe;
        margin: 10% auto; 
        padding: 20px;
        border: 1px solid #888;
        width: 80%;
        max-width: 500px;
        border-radius: 8px;
    }
    .close {
        color: #aaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
    }
    .close:hover,
    .close:focus {
        color: black;
        text-decoration: none;
    }
</style>

<div class="main-content">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
        <h2>Field Services</h2>
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
        <a href="?tab=services" class="tab-link <?php echo $activeTab === 'services' ? 'active' : ''; ?>">Field Services</a>
        <a href="?tab=settings" class="tab-link <?php echo $activeTab === 'settings' ? 'active' : ''; ?>">Settings</a>
    </div>

    <!-- SERVICES TAB -->
    <div id="services" class="tab-content <?php echo $activeTab === 'services' ? 'active' : ''; ?>">
        <div style="margin-bottom: 15px;">
            <button onclick="openModal('addServiceModal')" style="color: blue; border: none; background: none; cursor: pointer; text-decoration: underline; font-size: 16px; padding: 0;">+ Add Service</button>
        </div>
        <div class="table-container">
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
                                    <button onclick="openEditService(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars(addslashes($row['name'])); ?>', '<?php echo htmlspecialchars(addslashes($row['requirements'])); ?>')" style="margin-right: 5px; color: blue; cursor: pointer; background: none; border: none; text-decoration: underline; padding: 0;">Edit</button>
                                    <?php if (!$row['is_archived']): ?>
                                        <button onclick="openArchiveModal('/modules/services/archive.php', 'service_id', <?php echo $row['id']; ?>, 'field_services', '<?php echo htmlspecialchars(addslashes($row['name'])); ?>')" style="cursor: pointer; color: orange; background: none; border: none; text-decoration: underline; padding: 0;">Archive</button>
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
    </div>

    <!-- SETTINGS TAB -->
    <div id="settings" class="tab-content <?php echo $activeTab === 'settings' ? 'active' : ''; ?>">
        
        <!-- Sites Section -->
        <div class="settings-section">
            <h3>Field Sites</h3>
            <div style="margin-bottom: 15px;">
                <button onclick="openModal('addSiteModal')" style="color: blue; border: none; background: none; cursor: pointer; text-decoration: underline; font-size: 16px; padding: 0;">+ Add Field Site</button>
            </div>
            <div class="table-container">
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
                                        <button onclick="openEditSiteModal(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars(addslashes($row['name'])); ?>', '<?php echo htmlspecialchars(addslashes($row['type'])); ?>')" style="margin-right: 5px; color: blue; cursor: pointer; background: none; border: none; text-decoration: underline; padding: 0;">Edit</button>
                                        <?php if (!$row['is_archived']): ?>
                                            <button onclick="openArchiveModal('/modules/sites/archive.php', 'site_id', <?php echo $row['id']; ?>, 'field_services', '<?php echo htmlspecialchars(addslashes($row['name'])); ?>')" style="cursor: pointer; color: orange; background: none; border: none; text-decoration: underline; padding: 0;">Archive</button>
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
        </div>

        <!-- Service Assignments Section -->
        <div class="settings-section">
            <h3>Service Assignments (Field)</h3>
            <div class="table-container">
                <table>
                    <thead>
                        <tr><th>Site Name</th><th>Assigned Services</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        <?php if (count($activeSites) > 0): ?>
                            <?php foreach ($activeSites as $row): ?>
                                <?php
                                    $stmtSrv = $conn->prepare("
                                        SELECT s.name, ss.counter_limit 
                                        FROM site_services ss 
                                        JOIN services s ON ss.service_id = s.id 
                                        WHERE ss.site_id = ? AND s.is_archived = 0
                                    ");
                                    $stmtSrv->execute([$row['id']]);
                                    $srvs = [];
                                    while ($srvRow = $stmtSrv->fetch(PDO::FETCH_ASSOC)) {
                                        $srvs[] = $srvRow['name'] . ' (' . $srvRow['counter_limit'] . ')';
                                    }
                                    $servicesList = empty($srvs) ? 'None' : implode(', ', $srvs);
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['site_name']); ?></td>
                                    <td><?php echo htmlspecialchars($servicesList); ?></td>
                                    <td>
                                        <button onclick="openAssignSiteModal(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars(addslashes($row['site_name'])); ?>', 'field_services')" style="margin-right: 5px; color: purple; cursor: pointer; background: none; border: none; text-decoration: underline; padding: 0;">Manage Assignments</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="3">No active sites found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>



    </div>

    <!-- ADD SERVICE MODAL -->
    <div id="addServiceModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('addServiceModal')">&times;</span>
            <h2>Add New Service</h2>
            <form action="/modules/services/action_add.php" method="POST">
                <input type="hidden" name="return_to" value="field_services">
                <div class="form-group" style="margin-bottom: 15px;">
                    <label for="add_service_name" style="display: block; margin-bottom: 5px;">Service Name:</label>
                    <input type="text" id="add_service_name" name="service_name" required style="width: 100%; padding: 8px;">
                </div>
                <div class="form-group" style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px;">Requirements Checklist:</label>
                    <div id="add-requirements-container" style="border: 1px solid #ddd; padding: 10px; border-radius: 4px; background: #f9f9f9;">
                        <div class="req-item" style="margin-bottom: 8px; display: flex;">
                            <input type="text" name="requirements[]" placeholder="e.g. Valid ID" style="flex: 1; padding: 8px;">
                            <button type="button" onclick="removeReq(this)" style="margin-left: 5px; color: red; cursor: pointer; border: none; background: none; font-size: 16px;">✖</button>
                        </div>
                    </div>
                    <button type="button" onclick="addReq('add-requirements-container')" style="margin-top: 10px; font-size: 14px; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc; background: white;">+ Add Requirement</button>
                </div>
                <div style="margin-top: 25px;">
                    <button type="submit" style="padding: 10px 20px; background-color: blue; color: white; border: none; cursor: pointer;">Save</button>
                </div>
            </form>
        </div>
    </div>

    <!-- EDIT SERVICE MODAL -->
    <div id="editServiceModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('editServiceModal')">&times;</span>
            <h2>Edit Service</h2>
            <form action="/modules/services/action_edit.php" method="POST">
                <input type="hidden" name="return_to" value="field_services">
                <input type="hidden" name="service_id" id="edit_service_id">
                <div class="form-group" style="margin-bottom: 15px;">
                    <label for="edit_service_name" style="display: block; margin-bottom: 5px;">Service Name:</label>
                    <input type="text" id="edit_service_name" name="service_name" required style="width: 100%; padding: 8px;">
                </div>
                <div class="form-group" style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px;">Requirements Checklist:</label>
                    <div id="edit-requirements-container" style="border: 1px solid #ddd; padding: 10px; border-radius: 4px; background: #f9f9f9;">
                        <!-- Populated via JS -->
                    </div>
                    <button type="button" onclick="addReq('edit-requirements-container')" style="margin-top: 10px; font-size: 14px; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc; background: white;">+ Add Requirement</button>
                </div>
                <div style="margin-top: 25px;">
                    <button type="submit" style="padding: 10px 20px; background-color: blue; color: white; border: none; cursor: pointer;">Update</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ASSIGN COUNTER MODAL -->
    <div id="assignCounterModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('assignCounterModal')">&times;</span>
            <h2 id="assign_modal_title">Assign Services</h2>
            <p style="font-size: 14px; color: #666;">Only services available at this counter's site are shown below.</p>
            <form action="/modules/counters/action_assign.php" method="POST">
                <input type="hidden" name="return_to" id="assign_return_to">
                <input type="hidden" name="counter_id" id="assign_counter_id">
                
                <div id="assign-checkboxes-container" style="max-height: 300px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; margin-bottom: 20px;">
                    <p>Loading...</p>
                </div>
                
                <div style="margin-top: 25px;">
                    <button type="submit" id="assign_save_btn" style="padding: 10px 20px; background-color: blue; color: white; border: none; cursor: pointer;">Save</button>
                </div>
            </form>
        </div>
    </div>





    <!-- ARCHIVE MODAL -->
    <div id="archiveModal" class="modal">
        <div class="modal-content" style="max-width: 400px;">
            <span class="close" onclick="closeModal('archiveModal')">&times;</span>
            <h2>Confirm Archive</h2>
            <p id="archive_message" style="margin-bottom: 20px;">Are you sure you want to archive this?</p>
            <form id="archive_form" action="" method="POST">
                <input type="hidden" name="return_to" id="archive_return_to">
                <div id="archive_id_container"></div>
                
                <div style="margin-top: 25px;">
                    <button type="submit" style="padding: 10px 20px; background-color: orange; color: white; border: none; cursor: pointer;">Archive</button>
                    <button type="button" onclick="closeModal('archiveModal')" style="padding: 10px 20px; background-color: gray; color: white; border: none; cursor: pointer; margin-left: 10px;">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ADD SITE MODAL -->
    <div id="addSiteModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('addSiteModal')">&times;</span>
            <h2>Add New Site</h2>
            <form action="/modules/sites/action_add.php" method="POST">
                <input type="hidden" name="return_to" value="field_services">
                <input type="hidden" name="site_type" value="offsite">
                
                <div class="form-group" style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px;">Site Name:</label>
                    <input type="text" name="site_name" required style="width: 100%; padding: 8px;">
                </div>
                
                <div style="margin-top: 25px;">
                    <button type="submit" style="padding: 10px 20px; background-color: blue; color: white; border: none; cursor: pointer;">Save</button>
                </div>
            </form>
        </div>
    </div>

    <!-- EDIT SITE MODAL -->
    <div id="editSiteModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('editSiteModal')">&times;</span>
            <h2>Edit Site</h2>
            <form action="/modules/sites/action_edit.php" method="POST">
                <input type="hidden" name="return_to" value="field_services">
                <input type="hidden" name="site_id" id="edit_site_id">
                <input type="hidden" name="site_type" id="edit_site_type">
                
                <div class="form-group" style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px;">Site Name:</label>
                    <input type="text" name="site_name" id="edit_site_name" required style="width: 100%; padding: 8px;">
                </div>
                
                <div style="margin-top: 25px;">
                    <button type="submit" style="padding: 10px 20px; background-color: blue; color: white; border: none; cursor: pointer;">Update</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ASSIGN SITE SERVICES MODAL -->
    <div id="assignSiteModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('assignSiteModal')">&times;</span>
            <h2 id="assignSiteModalTitle">Assign Services</h2>
            <form action="/modules/service_assignments/action_assign.php" method="POST">
                <input type="hidden" name="return_to" id="assign_site_return_to">
                <input type="hidden" name="site_id" id="assign_site_id">
                
                <div id="assign_site_checkboxes" style="max-height: 300px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; margin-bottom: 20px;">
                    Loading services...
                </div>
                
                <div style="margin-top: 25px;">
                    <button type="submit" style="padding: 10px 20px; background-color: blue; color: white; border: none; cursor: pointer;">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openModal(id) {
        document.getElementById(id).style.display = 'block';
    }
    function closeModal(id) {
        document.getElementById(id).style.display = 'none';
    }
    window.onclick = function(event) {
        if (event.target.classList.contains('modal')) {
            event.target.style.display = "none";
        }
    }
    function addReq(containerId) {
        const container = document.getElementById(containerId);
        const div = document.createElement('div');
        div.className = 'req-item';
        div.style.marginBottom = '8px';
        div.style.display = 'flex';
        div.innerHTML = `
            <input type="text" name="requirements[]" placeholder="e.g. Valid ID" style="flex: 1; padding: 8px;">
            <button type="button" onclick="removeReq(this)" style="margin-left: 5px; color: red; cursor: pointer; border: none; background: none; font-size: 16px;">✖</button>
        `;
        container.appendChild(div);
    }
    function removeReq(btn) {
        const container = btn.closest('div[id$="-requirements-container"]');
        if (container.children.length > 1) {
            btn.parentElement.remove();
        } else {
            btn.parentElement.querySelector('input').value = '';
        }
    }
    function openArchiveModal(actionUrl, idName, idValue, returnTo, itemName) {
        document.getElementById('archive_form').action = actionUrl;
        document.getElementById('archive_return_to').value = returnTo;
        document.getElementById('archive_id_container').innerHTML = `<input type="hidden" name="${idName}" value="${idValue}">`;
        document.getElementById('archive_message').innerText = `Are you sure you want to archive '${itemName}'?`;
        openModal('archiveModal');
    }

    function toggleCounterLimit(checkbox, serviceId) {
        let input = document.getElementById('counter_limit_' + serviceId);
        if (checkbox.checked) {
            input.style.display = 'inline-block';
            if (input.value === '' || input.value === '0') input.value = 1;
        } else {
            input.style.display = 'none';
        }
    }

    function openEditSiteModal(id, name, type) {
        document.getElementById('edit_site_id').value = id;
        document.getElementById('edit_site_name').value = name;
        document.getElementById('edit_site_type').value = type;
        openModal('editSiteModal');
    }

    function openAssignSiteModal(siteId, siteName, returnTo) {
        document.getElementById('assignSiteModalTitle').innerText = `Assign Services to: ${siteName}`;
        document.getElementById('assign_site_id').value = siteId;
        document.getElementById('assign_site_return_to').value = returnTo;
        
        let container = document.getElementById('assign_site_checkboxes');
        container.innerHTML = 'Loading...';
        
        openModal('assignSiteModal');

        fetch(`/modules/service_assignments/api_get_assignments.php?site_id=${siteId}`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    let html = '';
                    if (data.allServices.length === 0) {
                        html = 'No services available.';
                    } else {
                        data.allServices.forEach(srv => {
                            let isChecked = data.assignedServices[srv.id] !== undefined;
                            let checkedStr = isChecked ? 'checked' : '';
                            let limitVal = isChecked ? data.assignedServices[srv.id] : 1;
                            let displayStyle = isChecked ? 'inline-block' : 'none';
                            
                            html += `<div style="margin-bottom: 10px; display: flex; align-items: center;">
                                <label style="flex: 1;">
                                    <input type="checkbox" name="services[]" value="${srv.id}" ${checkedStr} onchange="toggleCounterLimit(this, ${srv.id})"> 
                                    ${srv.name}
                                </label>
                                <input type="number" name="counter_limits[${srv.id}]" id="counter_limit_${srv.id}" value="${limitVal}" min="1" style="display: ${displayStyle}; width: 60px; padding: 4px; margin-left: 10px;" title="Number of counters">
                            </div>`;
                        });
                    }
                    container.innerHTML = html;
                } else {
                    container.innerHTML = `<span style="color:red">Error: ${data.message}</span>`;
                }
            })
            .catch(err => {
                container.innerHTML = `<span style="color:red">Failed to load services.</span>`;
                console.error(err);
            });
    }

    function openEditService(id, name, requirementsStr) {
        document.getElementById('edit_service_id').value = id;
        document.getElementById('edit_service_name').value = name;
        
        const container = document.getElementById('edit-requirements-container');
        container.innerHTML = '';
        
        let reqs = requirementsStr.split(',').map(r => r.trim()).filter(r => r !== '');
        if (reqs.length === 0) reqs = [''];
        
        reqs.forEach(req => {
            const div = document.createElement('div');
            div.className = 'req-item';
            div.style.marginBottom = '8px';
            div.style.display = 'flex';
            
            const input = document.createElement('input');
            input.type = 'text';
            input.name = 'requirements[]';
            input.value = req;
            input.style.flex = '1';
            input.style.padding = '8px';
            
            const button = document.createElement('button');
            button.type = 'button';
            button.onclick = function() { removeReq(this); };
            button.style.marginLeft = '5px';
            button.style.color = 'red';
            button.style.cursor = 'pointer';
            button.style.border = 'none';
            button.style.background = 'none';
            button.style.fontSize = '16px';
            button.textContent = '✖';
            
            div.appendChild(input);
            div.appendChild(button);
            container.appendChild(div);
        });
        
        openModal('editServiceModal');
    }

    function openAssignModal(counterId, siteId, counterName, returnTo) {
        if (!siteId) {
            alert('This counter is not assigned to a site yet.');
            return;
        }

        document.getElementById('assign_modal_title').innerText = 'Assign Services: ' + counterName;
        document.getElementById('assign_counter_id').value = counterId;
        document.getElementById('assign_return_to').value = returnTo;
        
        const container = document.getElementById('assign-checkboxes-container');
        container.innerHTML = '<p>Loading services...</p>';
        
        openModal('assignCounterModal');

        fetch(`/modules/counters/api_get_assignments.php?counter_id=${counterId}&site_id=${siteId}`)
            .then(res => res.json())
            .then(data => {
                if (data.error) {
                    container.innerHTML = `<p style="color: red;">Error: ${data.error}</p>`;
                    return;
                }
                
                container.innerHTML = '';
                if (data.site_services.length === 0) {
                    container.innerHTML = '<p style="color: red;">No services have been assigned to this Site yet! Please assign services to the Site first.</p>';
                    return;
                }
                
                data.site_services.forEach(srv => {
                    const isChecked = data.assigned_services.includes(srv.id) ? 'checked' : '';
                    const div = document.createElement('div');
                    div.style.marginBottom = '5px';
                    div.innerHTML = `
                        <label>
                            <input type="checkbox" name="services[]" value="${srv.id}" ${isChecked}>
                            ${srv.name.replace(/</g, "&lt;").replace(/>/g, "&gt;")}
                        </label>
                    `;
                    container.appendChild(div);
                });
            })
            .catch(err => {
                container.innerHTML = `<p style="color: red;">Failed to load data.</p>`;
            });
    }

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
