<?php
require_once __DIR__ . '/../../../auth/session.php';
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../models/Service.php';
require_once __DIR__ . '/../../models/Requirement.php';
require_once __DIR__ . '/../../models/Site.php';
require_once __DIR__ . '/../../models/Counter.php';

// Require 'admin' role to access this page
Session::requireRole('admin');

$db = new Database();
$conn = $db->getConnection();

$serviceModel = new Service($conn);
$reqModel = new Requirement($conn);
$siteModel = new Site($conn);

// Fetch data for Services tab
$stmtServices = $serviceModel->read();
$services = [];
while ($row = $stmtServices->fetch(PDO::FETCH_ASSOC)) {
    $services[] = $row;
}

// Fetch data for Requirements tab
$stmtReqs = $reqModel->read();
$requirements = [];
while ($row = $stmtReqs->fetch(PDO::FETCH_ASSOC)) {
    $requirements[] = $row;
}

// Fetch data for Sites tab
$stmtSites = $siteModel->read();
$sites = [];
while ($row = $stmtSites->fetch(PDO::FETCH_ASSOC)) {
    $sites[] = $row;
}

// Fetch data for Counters tab
$counterModel = new Counter($conn);
$stmtCounters = $counterModel->read();
$counters = [];
while ($row = $stmtCounters->fetch(PDO::FETCH_ASSOC)) {
    $counters[] = $row;
}

$pageTitle = 'Service Management - Admin Portal';
$activeMenu = 'settings';

// Determine active tab from URL or default to 'services'
$activeTab = $_GET['tab'] ?? 'services';

require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar_admin.php';
?>

<style>
    .tab-header {
        display: flex;
        border-bottom: 1px solid #ccc;
        margin-bottom: 20px;
    }
    .tab-btn {
        background: none;
        border: none;
        padding: 10px 20px;
        cursor: pointer;
        font-size: 16px;
        color: #555;
    }
    .tab-btn.active {
        border-bottom: 3px solid blue;
        color: blue;
        font-weight: bold;
    }
    .tab-content {
        display: none;
    }
    .tab-content.active {
        display: block;
    }
</style>

    <!-- Main Content -->
    <div class="main-content">
        <h1>Service Management</h1>
        
        <?php if(isset($_GET['status'])): ?>
            <?php
                $status = $_GET['status'];
                $msg = "Action completed successfully.";
                $color = "green";
                if ($status == 'error') { $msg = "An error occurred."; $color = "red"; }
                if ($status == 'archived') { $msg = "Item archived successfully."; $color = "orange"; }
                if ($status == 'edited') { $msg = "Item updated successfully."; $color = "blue"; }
            ?>
            <p style="color: <?php echo $color; ?>; background: #f0f9f0; padding: 10px; border: 1px solid <?php echo $color; ?>; margin-bottom: 15px;">
                <?php echo $msg; ?>
            </p>
        <?php endif; ?>

        <div class="tab-header">
            <button class="tab-btn <?php echo $activeTab == 'services' ? 'active' : ''; ?>" onclick="switchTab('services')">Services</button>
            <button class="tab-btn <?php echo $activeTab == 'requirements' ? 'active' : ''; ?>" onclick="switchTab('requirements')">Requirements</button>
            <button class="tab-btn <?php echo $activeTab == 'sites' ? 'active' : ''; ?>" onclick="switchTab('sites')">Sites</button>
            <button class="tab-btn <?php echo $activeTab == 'counters' ? 'active' : ''; ?>" onclick="switchTab('counters')">Counters</button>
            <button class="tab-btn <?php echo $activeTab == 'assignments' ? 'active' : ''; ?>" onclick="switchTab('assignments')">Service Assignments</button>
        </div>

        <!-- ==================== SERVICES TAB ==================== -->
        <div id="services" class="tab-content <?php echo $activeTab == 'services' ? 'active' : ''; ?>">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <h2>Manage Services</h2>
                <button onclick="openServiceModal()" style="cursor: pointer; color: blue; background: none; border: none; text-decoration: underline; font-size: 16px;">+ Add Service</button>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>ID</th><th>Service Name</th><th>Requirements</th><th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($services) > 0): ?>
                        <?php foreach ($services as $row): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['id']); ?></td>
                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                <td><?php echo htmlspecialchars($row['requirements']); ?></td>
                                <td>
                                    <button onclick="openEditServiceModal(<?php echo $row['id']; ?>, '<?php echo addslashes($row['name']); ?>', '<?php echo addslashes($row['requirements']); ?>')" style="cursor: pointer; margin-right: 5px; background: none; border: none; text-decoration: underline; color: blue;">Edit</button>
                                    <button onclick="openStepsModal(<?php echo $row['id']; ?>, '<?php echo addslashes($row['name']); ?>')" style="cursor: pointer; margin-right: 5px; background: none; border: none; text-decoration: underline; color: purple;">Manage Steps</button>
                                    <form action="/api/services/archive" method="POST" style="display:inline;" onsubmit="return confirm('Archive this service?');">
                                        <input type="hidden" name="service_id" value="<?php echo $row['id']; ?>">
                                        <button type="submit" style="cursor: pointer; color: orange; background: none; border: none; text-decoration: underline;">Archive</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="4">No active services found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- ==================== REQUIREMENTS TAB ==================== -->
        <div id="requirements" class="tab-content <?php echo $activeTab == 'requirements' ? 'active' : ''; ?>">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <h2>Master List of Requirements</h2>
                <button onclick="openReqModal()" style="cursor: pointer; color: blue; background: none; border: none; text-decoration: underline; font-size: 16px;">+ Add Requirement</button>
            </div>
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
                                    <form action="/api/requirements/archive" method="POST" style="display:inline;" onsubmit="return confirm('Archive this requirement?');">
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

        <!-- ==================== SITES TAB ==================== -->
        <div id="sites" class="tab-content <?php echo $activeTab == 'sites' ? 'active' : ''; ?>">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <h2>Manage Sites</h2>
                <button onclick="openSiteModal()" style="cursor: pointer; color: blue; background: none; border: none; text-decoration: underline; font-size: 16px;">+ Add Site</button>
            </div>
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
                                    <button onclick="openEditSiteModal(<?php echo $row['id']; ?>, '<?php echo addslashes($row['name']); ?>', '<?php echo addslashes($row['type']); ?>')" style="cursor: pointer; margin-right: 5px; background: none; border: none; text-decoration: underline; color: blue;">Edit</button>
                                    <form action="/api/sites/archive" method="POST" style="display:inline;" onsubmit="return confirm('Archive this site?');">
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

        <!-- ==================== COUNTERS TAB ==================== -->
        <div id="counters" class="tab-content <?php echo $activeTab == 'counters' ? 'active' : ''; ?>">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <h2>Manage Counters / Windows</h2>
                <button onclick="openCounterModal()" style="cursor: pointer; color: blue; background: none; border: none; text-decoration: underline; font-size: 16px;">+ Add Counter</button>
            </div>
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
                                    <button onclick="openCounterAssignmentModal(<?php echo $row['id']; ?>, '<?php echo addslashes($row['name']); ?>', <?php echo $row['site_id']; ?>)" style="cursor: pointer; margin-right: 5px; background: none; border: none; text-decoration: underline; color: purple;">Assign Services</button>
                                    <button onclick="openEditCounterModal(<?php echo $row['id']; ?>, '<?php echo addslashes($row['name']); ?>', <?php echo $row['site_id']; ?>)" style="cursor: pointer; margin-right: 5px; background: none; border: none; text-decoration: underline; color: blue;">Edit</button>
                                    <form action="/api/counters/archive" method="POST" style="display:inline;" onsubmit="return confirm('Archive this counter?');">
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

        <!-- ==================== ASSIGNMENTS TAB ==================== -->
        <div id="assignments" class="tab-content <?php echo $activeTab == 'assignments' ? 'active' : ''; ?>">
            <div style="margin-bottom: 15px;">
                <h2>Service Assignments (Deployments)</h2>
                <p style="color: #666; font-size: 14px;">Assign which services are available at each specific site.</p>
            </div>
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
                                    <button onclick="openAssignmentModal(<?php echo $row['id']; ?>, '<?php echo addslashes($row['name']); ?>')" style="cursor: pointer; margin-right: 5px; background: none; border: none; text-decoration: underline; color: purple;">Assign Services</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="3">No active sites found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>


        <!-- ==================== MODALS ==================== -->

        <!-- Service Modal -->
        <div id="serviceModal" class="modal">
            <div class="modal-content">
                <span class="close-btn" onclick="closeModals()">&times;</span>
                <h2 id="serviceModalTitle">Add New Service</h2>
                <form id="serviceForm" action="/api/services/add" method="POST">
                    <input type="hidden" name="redirect_tab" value="services">
                    <input type="hidden" id="service_id" name="service_id">
                    <div class="form-group">
                        <label for="service_name">Service Name:</label>
                        <input type="text" id="service_name" name="service_name" required>
                    </div>
                    <div class="form-group">
                        <label>Select Requirements (Check all that apply):</label>
                        <div style="max-height: 200px; overflow-y: auto; border: 1px solid #ddd; padding: 10px;">
                            <?php if (count($requirements) > 0): ?>
                                <?php foreach ($requirements as $req): ?>
                                    <div style="margin-bottom: 5px;">
                                        <label><input type="checkbox" name="requirements[]" value="<?php echo htmlspecialchars($req['name']); ?>" class="req-checkbox"> <?php echo htmlspecialchars($req['name']); ?></label>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p>No requirements found. Please add some first.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div style="margin-top: 25px; text-align: right;">
                        <button type="submit" style="cursor: pointer; color: blue; background: none; border: none; text-decoration: underline;" id="saveServiceBtn">Save</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Steps Modal -->
        <div id="stepsModal" class="modal">
            <div class="modal-content">
                <span class="close-btn" onclick="closeModals()">&times;</span>
                <h2>Process Steps: <span id="stepsModalTitle"></span></h2>
                <form id="stepsForm" action="/api/service_steps/save" method="POST">
                    <input type="hidden" name="redirect_tab" value="services">
                    <input type="hidden" id="step_service_id" name="service_id">
                    <div id="stepsContainer">
                        <p id="loadingSteps" style="display:none;">Loading...</p>
                    </div>
                    <button type="button" onclick="addStepRow()" style="cursor: pointer; color: green; background: none; border: none; text-decoration: underline; margin-top: 10px;">+ Add Step</button>
                    <div style="margin-top: 25px; text-align: right;">
                        <button type="submit" style="cursor: pointer; color: blue; background: none; border: none; text-decoration: underline;">Save Steps</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Requirement Modal -->
        <div id="reqModal" class="modal">
            <div class="modal-content">
                <span class="close-btn" onclick="closeModals()">&times;</span>
                <h2>Add Requirement</h2>
                <form action="/api/requirements/add" method="POST">
                    <input type="hidden" name="redirect_tab" value="requirements">
                    <div class="form-group">
                        <label>Requirement Name:</label>
                        <input type="text" name="req_name" required>
                    </div>
                    <div style="margin-top: 25px; text-align: right;">
                        <button type="submit" style="cursor: pointer; color: blue; background: none; border: none; text-decoration: underline;">Save</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Site Modal -->
        <div id="siteModal" class="modal">
            <div class="modal-content">
                <span class="close-btn" onclick="closeModals()">&times;</span>
                <h2 id="siteModalTitle">Add New Site</h2>
                <form id="siteForm" action="/api/sites/add" method="POST">
                    <input type="hidden" name="redirect_tab" value="sites">
                    <input type="hidden" id="site_id" name="site_id">
                    <div class="form-group">
                        <label>Site Name:</label>
                        <input type="text" id="site_name" name="site_name" required>
                    </div>
                    <div class="form-group">
                        <label>Site Type:</label>
                        <select id="site_type" name="site_type" required>
                            <option value="offsite">Specific Site (Offsite)</option>
                            <option value="office">Office (Main)</option>
                        </select>
                    </div>
                    <div style="margin-top: 25px; text-align: right;">
                        <button type="submit" style="cursor: pointer; color: blue; background: none; border: none; text-decoration: underline;" id="saveSiteBtn">Save</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Counter Modal -->
        <div id="counterModal" class="modal">
            <div class="modal-content">
                <span class="close-btn" onclick="closeModals()">&times;</span>
                <h2 id="counterModalTitle">Add New Counter</h2>
                <form id="counterForm" action="/api/counters/add" method="POST">
                    <input type="hidden" name="redirect_tab" value="counters">
                    <input type="hidden" id="counter_id" name="counter_id">
                    
                    <div class="form-group">
                        <label>Counter / Window Name:</label>
                        <input type="text" id="counter_name" name="counter_name" placeholder="e.g. Window 1 - Receiving" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Assign to Site:</label>
                        <select id="counter_site_id" name="site_id" required>
                            <option value="">-- Select a Site --</option>
                            <?php foreach ($sites as $site): ?>
                                <option value="<?php echo $site['id']; ?>"><?php echo htmlspecialchars($site['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div style="margin-top: 25px; text-align: right;">
                        <button type="submit" style="cursor: pointer; color: blue; background: none; border: none; text-decoration: underline;" id="saveCounterBtn">Save Counter</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Assignment Modal -->
        <div id="assignmentModal" class="modal">
            <div class="modal-content">
                <span class="close-btn" onclick="closeModals()">&times;</span>
                <h2>Assign Services to: <span id="assignmentModalTitle"></span></h2>
                <form action="/api/service_assignments/save" method="POST">
                    <input type="hidden" name="redirect_tab" value="assignments">
                    <input type="hidden" id="assign_site_id" name="site_id">
                    <div id="loadingServices" style="display:none; color: blue;">Loading...</div>
                    <div style="max-height: 300px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; margin-bottom: 20px;">
                        <?php foreach ($services as $srv): ?>
                            <div>
                                <label><input type="checkbox" name="services[]" value="<?php echo $srv['id']; ?>" class="service-assign-cb" id="srv_assign_<?php echo $srv['id']; ?>"> <?php echo htmlspecialchars($srv['name']); ?></label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div style="text-align: right;">
                        <button type="submit" style="cursor: pointer; color: blue; background: none; border: none; text-decoration: underline;">Save</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Counter Assignment Modal -->
        <div id="counterAssignmentModal" class="modal">
            <div class="modal-content">
                <span class="close-btn" onclick="closeModals()">&times;</span>
                <h2>Assign Services to Counter: <span id="counterAssignmentModalTitle"></span></h2>
                <p style="font-size: 14px; color: #666;">Only services available at this counter's site are shown below.</p>
                
                <form action="/api/counter_assignments/save" method="POST">
                    <input type="hidden" name="redirect_tab" value="counters">
                    <input type="hidden" id="assign_counter_id" name="counter_id">
                    
                    <div id="loadingCounterServices" style="display:none; color: blue;">Loading assignments...</div>
                    
                    <div style="max-height: 300px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; margin-bottom: 20px;" id="counterServicesContainer">
                        <?php foreach ($services as $srv): ?>
                            <div class="counter-srv-div" id="counter_srv_div_<?php echo $srv['id']; ?>" style="display: none;">
                                <label><input type="checkbox" name="services[]" value="<?php echo $srv['id']; ?>" class="counter-assign-cb" id="counter_srv_assign_<?php echo $srv['id']; ?>"> <?php echo htmlspecialchars($srv['name']); ?></label>
                            </div>
                        <?php endforeach; ?>
                        <p id="noServicesMsg" style="display: none; color: red;">No services have been assigned to this Site yet! Please assign services to the Site first.</p>
                    </div>
                    
                    <div style="text-align: right;">
                        <button type="submit" style="cursor: pointer; color: blue; background: none; border: none; text-decoration: underline;">Save</button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    <script>
        function switchTab(tabId) {
            document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
            document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
            document.getElementById(tabId).classList.add('active');
            event.currentTarget.classList.add('active');
            
            // Update URL without reloading
            const url = new URL(window.location);
            url.searchParams.set('tab', tabId);
            window.history.pushState({}, '', url);
        }

        function closeModals() {
            document.querySelectorAll('.modal').forEach(el => el.style.display = 'none');
        }

        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                closeModals();
            }
        }

        // Service Modal Logic
        function openServiceModal() {
            document.getElementById("serviceModalTitle").innerText = "Add New Service";
            document.getElementById("serviceForm").action = "/api/services/add";
            document.getElementById("service_id").value = "";
            document.getElementById("service_name").value = "";
            document.querySelectorAll('.req-checkbox').forEach(cb => cb.checked = false);
            document.getElementById("saveServiceBtn").innerText = "Save";
            document.getElementById("serviceModal").style.display = "block";
        }
        
        function openEditServiceModal(id, name, reqString) {
            document.getElementById("serviceModalTitle").innerText = "Edit Service";
            document.getElementById("serviceForm").action = "/api/services/edit";
            document.getElementById("service_id").value = id;
            document.getElementById("service_name").value = name;
            
            const reqArray = reqString.split(', ');
            document.querySelectorAll('.req-checkbox').forEach(cb => {
                cb.checked = reqArray.includes(cb.value);
            });
            
            document.getElementById("saveServiceBtn").innerText = "Update";
            document.getElementById("serviceModal").style.display = "block";
        }

        // Steps Logic
        function openStepsModal(id, name) {
            document.getElementById("stepsModalTitle").innerText = name;
            document.getElementById("step_service_id").value = id;
            const container = document.getElementById("stepsContainer");
            const loader = document.getElementById("loadingSteps");
            
            container.innerHTML = '';
            container.appendChild(loader);
            loader.style.display = "block";
            document.getElementById("stepsModal").style.display = "block";
            
            fetch(`/api/service_steps/get?service_id=${id}`)
                .then(r => r.json())
                .then(data => {
                    loader.style.display = "none";
                    if (data.steps && data.steps.length > 0) {
                        data.steps.forEach(s => addStepRow(s));
                    } else { addStepRow(''); }
                });
        }

        function addStepRow(val = '') {
            const container = document.getElementById("stepsContainer");
            const row = document.createElement("div");
            row.style.marginBottom = "10px";
            row.innerHTML = `<input type="text" name="steps[]" value="${val}" required style="padding:8px; width:70%; margin-right:10px;">
                             <button type="button" onclick="this.parentElement.remove()" style="color:red; background:none; border:none; cursor:pointer;">X</button>`;
            container.appendChild(row);
        }

        // Requirement Modal Logic
        function openReqModal() {
            document.getElementById("reqModal").style.display = "block";
        }

        // Site Modal Logic
        function openSiteModal() {
            document.getElementById("siteModalTitle").innerText = "Add New Site";
            document.getElementById("siteForm").action = "/api/sites/add";
            document.getElementById("site_id").value = "";
            document.getElementById("site_name").value = "";
            document.getElementById("site_type").value = "offsite";
            document.getElementById("saveSiteBtn").innerText = "Save";
            document.getElementById("siteModal").style.display = "block";
        }
        function openEditSiteModal(id, name, type) {
            document.getElementById("siteModalTitle").innerText = "Edit Site";
            document.getElementById("siteForm").action = "/api/sites/edit";
            document.getElementById("site_id").value = id;
            document.getElementById("site_name").value = name;
            document.getElementById("site_type").value = type;
            document.getElementById("saveSiteBtn").innerText = "Update";
            document.getElementById("siteModal").style.display = "block";
        }

        // Assignment Modal Logic
        function openAssignmentModal(id, name) {
            document.getElementById("assignmentModalTitle").innerText = name;
            document.getElementById("assign_site_id").value = id;
            document.querySelectorAll('.service-assign-cb').forEach(cb => cb.checked = false);
            
            const loader = document.getElementById("loadingServices");
            loader.style.display = "block";
            document.getElementById("assignmentModal").style.display = "block";
            
            fetch(`/api/service_assignments/get?site_id=${id}`)
                .then(r => r.json())
                .then(data => {
                    loader.style.display = "none";
                    if (data.assigned_services) {
                        data.assigned_services.forEach(sid => {
                            const cb = document.getElementById(`srv_assign_${sid}`);
                            if (cb) cb.checked = true;
                        });
                    }
                });
        }

        // Counter Modal Logic
        function openCounterModal() {
            document.getElementById("counterModalTitle").innerText = "Add New Counter";
            document.getElementById("counterForm").action = "/api/counters/add";
            document.getElementById("counter_id").value = "";
            document.getElementById("counter_name").value = "";
            document.getElementById("counter_site_id").value = "";
            document.getElementById("saveCounterBtn").innerText = "Save Counter";
            document.getElementById("counterModal").style.display = "block";
        }

        function openEditCounterModal(id, name, site_id) {
            document.getElementById("counterModalTitle").innerText = "Edit Counter";
            document.getElementById("counterForm").action = "/api/counters/edit";
            document.getElementById("counter_id").value = id;
            document.getElementById("counter_name").value = name;
            document.getElementById("counter_site_id").value = site_id;
            document.getElementById("saveCounterBtn").innerText = "Update Counter";
            document.getElementById("counterModal").style.display = "block";
        }

        // Counter Assignment Modal Logic
        function openCounterAssignmentModal(counterId, counterName, siteId) {
            document.getElementById("counterAssignmentModalTitle").innerText = counterName;
            document.getElementById("assign_counter_id").value = counterId;
            
            // Reset UI
            document.querySelectorAll('.counter-assign-cb').forEach(cb => cb.checked = false);
            document.querySelectorAll('.counter-srv-div').forEach(div => div.style.display = 'none');
            document.getElementById('noServicesMsg').style.display = 'none';
            
            const loader = document.getElementById("loadingCounterServices");
            loader.style.display = "block";
            document.getElementById("counterAssignmentModal").style.display = "block";
            
            // Fetch 1: Get available services for this site
            fetch(`/api/service_assignments/get?site_id=${siteId}`)
                .then(r => r.json())
                .then(siteData => {
                    if (siteData.assigned_services && siteData.assigned_services.length > 0) {
                        // Show only the divs for services available at this site
                        siteData.assigned_services.forEach(sid => {
                            const div = document.getElementById(`counter_srv_div_${sid}`);
                            if (div) div.style.display = 'block';
                        });
                        
                        // Fetch 2: Get currently checked services for this counter
                        fetch(`/api/counter_assignments/get?counter_id=${counterId}`)
                            .then(r => r.json())
                            .then(counterData => {
                                loader.style.display = "none";
                                if (counterData.assigned_services) {
                                    counterData.assigned_services.forEach(sid => {
                                        const cb = document.getElementById(`counter_srv_assign_${sid}`);
                                        if (cb) cb.checked = true;
                                    });
                                }
                            });
                    } else {
                        // No services at this site!
                        loader.style.display = "none";
                        document.getElementById('noServicesMsg').style.display = 'block';
                    }
                });
        }
    </script>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
