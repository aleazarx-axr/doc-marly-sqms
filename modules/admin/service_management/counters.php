<?php
require_once __DIR__ . '/../../../includes/functions.php';
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../includes/models/Service.php';
require_once __DIR__ . '/../../../includes/models/Counter.php';

require_once __DIR__ . '/../../../includes/models/User.php';
require_once __DIR__ . '/../../../includes/models/CounterService.php';
require_once __DIR__ . '/../../../includes/models/CounterCitizenCategory.php';

Session::requireRole('admin');

$db = new Database();
$conn = $db->getConnection();

// --- HANDLE POST REQUESTS NATIVELY ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        
        if ($action === 'save_counter') {
            $c = new Counter($conn);
            if (!empty($_POST['id'])) $c->id = $_POST['id'];
            $c->name = $_POST['name'];
            $c->counter_type = $_POST['counter_type'];
            $c->staff_id = !empty($_POST['staff_id']) ? $_POST['staff_id'] : null;
            $c->overflow_general = !empty($_POST['overflow_general']) ? 1 : 0;
            
            if ($c->nameExists()) {
                $errorMessage = "A counter with this name already exists.";
            } else {
                if ($c->id) {
                    $success = $c->update();
                } else {
                    $success = $c->create();
                    $c->id = $conn->lastInsertId();
                }
                
                // Save categories if Priority
                if ($success && $c->counter_type === 'Priority') {
                    $cats = $_POST['categories'] ?? [];
                    $ccc = new CounterCitizenCategory($conn);
                    $ccc->saveCategories($c->id, $cats);
                } elseif ($success && $c->counter_type !== 'Priority') {
                    $ccc = new CounterCitizenCategory($conn);
                    $ccc->saveCategories($c->id, []);
                }
                // Save assigned services
                if ($success) {
                    $assignedServices = $_POST['assigned_services'] ?? [];
                    $cs = new CounterService($conn);
                    $cs->saveAssignments($c->id, $assignedServices);
                }
                $redirectUrl = "counters.php" . (isset($_GET['view']) && $_GET['view'] === 'archived' ? '?view=archived' : '');
                header("Location: $redirectUrl");
                exit;
            }
        }
        
        if ($action === 'archive_counter') {
            $c = new Counter($conn);
            $c->id = $_POST['id'];
            $c->archive();
            $redirectUrl = "counters.php" . (isset($_GET['view']) && $_GET['view'] === 'archived' ? '?view=archived' : '');
            header("Location: $redirectUrl");
            exit;
        }

        if ($action === 'restore_counter') {
            $c = new Counter($conn);
            $c->id = $_POST['id'];
            $c->restore();
            $redirectUrl = "counters.php" . (isset($_GET['view']) && $_GET['view'] === 'archived' ? '?view=archived' : '');
            header("Location: $redirectUrl");
            exit;
        }
    }
}

// Fetch all active services for Assignment dropdown
$stmtAllActiveServices = $conn->query("SELECT * FROM services WHERE is_active = 1 AND is_archived = 0");
$allActiveServices = [];
while ($row = $stmtAllActiveServices->fetch(PDO::FETCH_ASSOC)) {
    $allActiveServices[] = $row;
}

// Fetch Counters
$counterModel = new Counter($conn);
$view = $_GET['view'] ?? 'active';
$stmtCounters = $view === 'archived' ? $counterModel->readArchived() : $counterModel->read();
$counters = [];
while ($row = $stmtCounters->fetch(PDO::FETCH_ASSOC)) {
    $counters[] = $row;
}

// Fetch counter-services assignments
$csModel = new CounterService($conn);
$counterServices = [];
foreach ($counters as $c) {
    $counterServices[$c['id']] = $csModel->getAssignedServices($c['id']);
}

// Fetch counter citizen categories
$cccModel = new CounterCitizenCategory($conn);
$counterCategories = [];
foreach ($counters as $c) {
    if ($c['counter_type'] === 'Priority') {
        $counterCategories[$c['id']] = $cccModel->getCategories($c['id']);
    }
}



// Fetch Staff
$stmtStaff = $conn->query("SELECT id, username FROM users WHERE role IN ('service_staff', 'information_staff', 'staff') AND status != 'archived' ORDER BY username ASC");
$staffList = [];
while ($row = $stmtStaff->fetch(PDO::FETCH_ASSOC)) {
    $staffList[] = $row;
}

$pageTitle = $view === 'archived' ? 'Archived Counters - Admin Portal' : 'Manage Counters - Admin Portal';
$activeMenu = 'counters';

require_once __DIR__ . '/../../../includes/header.php';
require_once __DIR__ . '/../../../includes/sidebar_admin.php';
?>

<div class="main-content">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
        <h2><?php echo $view === 'archived' ? 'Archived Counters' : 'Manage Counters'; ?></h2>
        <div>
            <?php if ($view === 'archived'): ?>
                <a href="counters.php" style="color: blue; text-decoration: underline; margin-right: 15px;">View Active Counters</a>
            <?php else: ?>
                <a href="counters.php?view=archived" style="color: gray; text-decoration: underline; margin-right: 15px;">View Archives</a>
                <button onclick="openCounterModal()">Add New Counter</button>
            <?php endif; ?>
        </div>
    </div>
    
    <?php if (!empty($errorMessage)): ?>
        <div style="color: red; margin-bottom: 15px; border: 1px solid red; padding: 10px;">
            <?php echo htmlspecialchars($errorMessage); ?>
        </div>
    <?php endif; ?>
        
    <div style="margin-bottom: 15px;">
        <label>Search by Name:</label>
        <input type="text" id="filterCounterInput" onkeyup="filterCounters()" placeholder="Type to search..." style="padding: 5px; width: 250px;">
    </div>

        <div class="mb-4">
            <table id="countersTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Services</th>
                        <th>Staff</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $totalCounters = count($counters);
                    $activeCounters = 0;
                    $inactiveCounters = 0;
                    
                    if ($totalCounters > 0): 
                        foreach ($counters as $index => $c): 
                            if ($c['is_active']) $activeCounters++;
                            else $inactiveCounters++;
                            
                            $assigned = $counterServices[$c['id']] ?? [];
                            $assignedNames = [];
                            foreach ($assigned as $srv_id) {
                                foreach($allActiveServices as $srv) { 
                                    if($srv['id'] == $srv_id) { 
                                        $assignedNames[] = $srv['name']; 
                                        break; 
                                    } 
                                }
                            }
                            $assignedStr = empty($assignedNames) ? '—' : implode(', ', $assignedNames);
                            $typeClass = $c['is_active'] ? ($c['counter_type'] === 'Priority' ? 'text-danger' : ($c['counter_type'] === 'Dedicated' ? 'text-success' : 'text-primary')) : 'text-muted';
                    ?>
                        <tr class="counter-row" data-status="<?php echo $c['is_active'] ? 'active' : 'inactive'; ?>" data-type="<?php echo $c['counter_type']; ?>" data-name="<?php echo htmlspecialchars($c['name']); ?>">
                            <td><?php echo $c['id']; ?></td>
                            <td><strong><?php echo htmlspecialchars($c['name']); ?></strong></td>
                            <td>
                                <span class="fw-bold <?php echo $typeClass; ?>">
                                    <?php echo $c['is_active'] ? $c['counter_type'] : 'Inactive'; ?>
                                </span>
                            </td>
                            <td class="text-muted" style="max-width: 250px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="<?php echo htmlspecialchars($assignedStr); ?>">
                                <?php echo htmlspecialchars($assignedStr); ?>
                            </td>
                            <td><?php echo $c['staff_name'] ? htmlspecialchars($c['staff_name']) : '—'; ?></td>
                            <td>
                                <?php if ($view === 'archived'): ?>
                                    <!-- Restore Form -->
                                    <form method="POST" action="counters.php?view=archived" style="display:inline;" onsubmit="return confirm('Restore this counter?');">
                                        <input type="hidden" name="action" value="restore_counter">
                                        <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                        <button type="submit">Restore</button>
                                    </form>
                                <?php else: ?>
                                    <button onclick='openCounterModal(<?php echo json_encode($c); ?>, <?php echo json_encode($counterCategories[$c['id']] ?? []); ?>, <?php echo json_encode($assigned); ?>)'>
                                        Edit
                                    </button>
                                    <!-- Archive Form -->
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Archive this counter?');">
                                        <input type="hidden" name="action" value="archive_counter">
                                        <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                        <button type="submit">Archive</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php 
                        endforeach; 
                    else: 
                    ?>
                        <tr><td colspan="6" class="text-center text-muted">No counters found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
            
            <div class="mt-3 pt-3 border-top text-muted small d-flex justify-content-between">
                <div>
                    <span id="counterStats"><?php echo $totalCounters; ?> counters total | <?php echo $activeCounters; ?> active | <?php echo $inactiveCounters; ?> inactive</span>
                </div>
            </div>
        </div>

</div>

<?php if ($view !== 'archived'): ?>
<!-- Counter Form -->
<div id="counterFormContainer" style="display: none; margin-bottom: 20px; border: 1px solid #ccc; padding: 15px;">
    <h3 id="counterModalTitle" style="margin-top: 0;">Add Counter</h3>
    <form id="counterForm" method="POST" action="counters.php">
        <input type="hidden" name="action" value="save_counter">
        <input type="hidden" id="c_id" name="id">
        
        <div style="margin-bottom: 10px;">
            <label>Name:</label><br>
            <input type="text" id="c_name" name="name" required placeholder="e.g. Window 1">
        </div>

        <div style="margin-bottom: 10px;">
            <label>Counter Type:</label><br>
            <select id="c_type" name="counter_type" onchange="togglePriorityOptions()" required>
                <option value="General">General</option>
                <option value="Dedicated">Dedicated</option>
                <option value="Priority">Priority</option>
            </select>
        </div>
        
        <div style="margin-bottom: 10px;">
            <label>Staff:</label><br>
            <select id="c_staff" name="staff_id">
                <option value="">-- Unassigned --</option>
                <?php foreach($staffList as $st): ?>
                    <option value="<?php echo $st['id']; ?>"><?php echo htmlspecialchars($st['username']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Priority specific fields -->
        <div id="priority_fields" style="display: none; background: #fff1f2; padding: 15px; border-radius: 8px; margin-bottom: 15px;">
            <label>Priority Categories:</label><br>
            <div style="margin-bottom: 10px;">
                <input class="c-cat" type="checkbox" value="Senior Citizen" name="categories[]"> Senior Citizen
                <input class="c-cat" type="checkbox" value="PWD" name="categories[]"> PWD
                <input class="c-cat" type="checkbox" value="Pregnant" name="categories[]"> Pregnant
                <input class="c-cat" type="checkbox" value="Solo Parent" name="categories[]"> Solo Parent
            </div>
            <div>
                <input type="checkbox" id="c_overflow" name="overflow_general" value="1">
                <label for="c_overflow">Serves general citizens when idle</label>
            </div>
        </div>

        <div style="margin-bottom: 10px;">
            <label>Assigned Services:</label><br>
            <div style="border: 1px solid #ccc; padding: 10px; max-height: 150px; overflow-y: auto;">
                <?php foreach($allActiveServices as $srv): ?>
                <div>
                    <input class="c-assign" type="checkbox" value="<?php echo $srv['id']; ?>" name="assigned_services[]" id="assign_srv_<?php echo $srv['id']; ?>">
                    <label for="assign_srv_<?php echo $srv['id']; ?>"><?php echo htmlspecialchars($srv['name']); ?></label>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <button type="submit">Save Counter</button>
        <button type="button" onclick="document.getElementById('counterFormContainer').style.display='none'">Cancel</button>
    </form>
</div>
<?php endif; ?>





<script>

    /* MODAL TRIGGERS */
    function openCounterModal(c = null, cats = [], assigned = []) {
        document.getElementById('counterForm').reset();
        document.querySelectorAll('.c-cat').forEach(cb => cb.checked = false);
        document.querySelectorAll('.c-assign').forEach(cb => cb.checked = false);
        
        if (c) {
            document.getElementById('counterModalTitle').innerText = 'Edit Counter';
            document.getElementById('c_id').value = c.id;
            document.getElementById('c_name').value = c.name;

            document.getElementById('c_type').value = c.counter_type;
            document.getElementById('c_staff').value = c.staff_id || '';
            document.getElementById('c_overflow').checked = c.overflow_general == 1;
            
            if (cats && cats.length) {
                document.querySelectorAll('.c-cat').forEach(cb => {
                    if (cats.includes(cb.value)) cb.checked = true;
                });
            }
            if (assigned && assigned.length) {
                document.querySelectorAll('.c-assign').forEach(cb => {
                    // assigned is an array of IDs (strings or ints)
                    if (assigned.includes(cb.value) || assigned.includes(parseInt(cb.value))) cb.checked = true;
                });
            }
        } else {
            document.getElementById('counterModalTitle').innerText = 'Add Counter';
            document.getElementById('c_id').value = '';
        }
        
        togglePriorityOptions();
        document.getElementById('counterFormContainer').style.display = 'block';
    }

    function togglePriorityOptions() {
        const type = document.getElementById('c_type').value;
        document.getElementById('priority_fields').style.display = (type === 'Priority') ? 'block' : 'none';
    }



    function filterCounters() {
        const input = document.getElementById('filterCounterInput').value.toLowerCase();
        
        const rows = document.querySelectorAll('.counter-row');
        let activeCount = 0;
        let inactiveCount = 0;
        let totalCount = 0;
        
        rows.forEach(row => {
            const status = row.getAttribute('data-status');
            const name = row.getAttribute('data-name').toLowerCase();
            
            let matchSearch = name.includes(input);
            
            if (matchSearch) {
                row.style.display = '';
                totalCount++;
                if (status === 'active') activeCount++;
                else inactiveCount++;
            } else {
                row.style.display = 'none';
            }
        });
        
        document.getElementById('counterStats').innerText = `${totalCount} counters total | ${activeCount} active | ${inactiveCount} inactive`;
    }

</script>

<?php require_once __DIR__ . '/../../../includes/footer.php'; ?>
