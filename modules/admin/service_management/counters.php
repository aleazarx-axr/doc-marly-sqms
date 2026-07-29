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

$errorMessage = '';
$successMessage = '';

// --- HANDLE POST REQUESTS NATIVELY ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        $redirectView = (isset($_GET['view']) && $_GET['view'] === 'archived') ? '?view=archived' : '';

        if ($action === 'save_counter') {
            $c = new Counter($conn);
            if (!empty($_POST['id'])) {
                $c->id = $_POST['id'];
            }
            $c->name = trim($_POST['name'] ?? '');
            $c->counter_type = $_POST['counter_type'] ?? 'General';
            $c->overflow_general = !empty($_POST['overflow_general']) ? 1 : 0;

            if ($c->nameExists()) {
                $errorMessage = "A counter with the name '" . htmlspecialchars($c->name) . "' already exists.";
            } else {
                $success = $c->id ? $c->update() : $c->create();
                
                if (!$c->id) {
                    $c->id = $conn->lastInsertId();
                }

                if ($success) {
                    // Save Categories
                    $ccc = new CounterCitizenCategory($conn);
                    $cats = ($c->counter_type === 'Priority') ? ($_POST['categories'] ?? []) : [];
                    $ccc->saveCategories($c->id, $cats);

                    // Save Services
                    $cs = new CounterService($conn);
                    $cs->saveAssignments($c->id, $_POST['assigned_services'] ?? []);

                    // Save Staff Assignments
                    $c->saveStaffAssignments($c->id, $_POST['staff_ids'] ?? []);

                    header("Location: /admin/counters{$redirectView}");
                    exit;
                } else {
                    $errorMessage = "An error occurred while saving the counter. Please try again.";
                }
            }
        }

        if ($action === 'archive_counter') {
            $c = new Counter($conn);
            $c->id = $_POST['id'];
            $c->archive();
            header("Location: /admin/counters{$redirectView}");
            exit;
        }

        if ($action === 'restore_counter') {
            $c = new Counter($conn);
            $c->id = $_POST['id'];
            $c->restore();
            header("Location: /admin/counters{$redirectView}");
            exit;
        }
    }
}

// Fetch Active Services
$stmtAllActiveServices = $conn->query("SELECT * FROM services WHERE is_active = 1 AND is_archived = 0 ORDER BY name ASC");
$allActiveServices = $stmtAllActiveServices->fetchAll(PDO::FETCH_ASSOC);

// Fetch Counters
$counterModel = new Counter($conn);
$view = $_GET['view'] ?? 'active';
$stmtCounters = ($view === 'archived') ? $counterModel->readArchived() : $counterModel->read();
$counters = $stmtCounters->fetchAll(PDO::FETCH_ASSOC);

// Mappings & Pre-calculations
$csModel = new CounterService($conn);
$cccModel = new CounterCitizenCategory($conn);

$counterServices = [];
$counterCategories = [];
$countActive = 0;
$countInactive = 0;
$countPriority = 0;

foreach ($counters as $c) {
    if ($c['is_active']) {
        $countActive++;
    } else {
        $countInactive++;
    }

    if ($c['counter_type'] === 'Priority') {
        $countPriority++;
        $counterCategories[$c['id']] = $cccModel->getCategories($c['id']);
    }

    $counterServices[$c['id']] = $csModel->getAssignedServices($c['id']);
}

// Fetch Staff
$stmtStaff = $conn->query("SELECT id, username FROM users WHERE role IN ('service_staff', 'information_staff', 'staff') AND status != 'archived' ORDER BY username ASC");
$staffList = $stmtStaff->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = ($view === 'archived') ? 'Archived Counters - Admin Portal' : 'Manage Counters - Admin Portal';
$activeMenu = 'counters';

require_once __DIR__ . '/../../../includes/header.php';
require_once __DIR__ . '/../../../includes/sidebar_admin.php';
?>

<!-- Framework Styles & Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="/assets/css/admin_service_management.css">

<style>
    .counter-card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .counter-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.08) !important;
    }
    .meta-label {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 600;
        color: #6c757d;
    }
    .badge-soft-primary { background-color: #e8f0fe; color: #242364; }
    .badge-soft-danger { background-color: #fce8e6; color: #d93025; }
    .badge-soft-success { background-color: #e6f4ea; color: #137333; }
    .badge-soft-secondary { background-color: #f1f3f4; color: #5f6368; }
</style>

<div class="main-content container-fluid px-4 py-4">

    <!-- PAGE HEADER -->
    <div class="bg-white rounded-3 p-4 shadow-sm border mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <div class="d-flex align-items-center gap-2">
                    <h1 class="h4 fw-bold text-dark mb-0">
                        <?= $view === 'archived' ? 'Archived Counters' : 'Manage Counters'; ?>
                    </h1>
                    <span class="badge rounded-pill bg-light text-dark border"><?= count($counters); ?> Total</span>
                </div>
                <p class="text-muted small mb-0 mt-1">Configure service windows, staff assignments, and priority routing rules.</p>
            </div>

            <!-- Toolbar Actions -->
            <div class="d-flex flex-wrap align-items-center gap-2">
                <div class="input-group" style="width: 250px;">
                    <span class="input-group-text bg-light border-end-0 text-muted">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" id="filterCounterInput" class="form-control bg-light border-start-0 ps-0" onkeyup="filterCounters()" placeholder="Search counter name...">
                </div>

                <?php if ($view === 'archived'): ?>
                    <a href="/admin/counters" class="btn btn-outline-primary d-inline-flex align-items-center">
                        <i class="bi bi-arrow-left me-1"></i> Active Counters
                    </a>
                <?php else: ?>
                    <a href="/admin/counters?view=archived" class="btn btn-warning d-inline-flex align-items-center">
                        <i class="bi bi-archive me-1"></i> View Archives
                    </a>
                    <button type="button" class="btn btn-primary d-inline-flex align-items-center" onclick="openCounterModal()">
                        <i class="bi bi-plus-lg me-1"></i> Add Counter
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- ERROR MESSAGES -->
    <?php if (!empty($errorMessage)): ?>
        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <?= htmlspecialchars($errorMessage); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- KPI METRIC CARDS -->
   <div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm p-3 bg-white h-100">
            <div class="d-flex align-items-center justify-content-between">
                <span class="meta-label me-2">Total Counters</span>
                <span class="h3 fw-bold text-dark mb-0"><?= count($counters); ?></span>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm p-3 bg-white h-100">
            <div class="d-flex align-items-center justify-content-between">
                <span class="meta-label me-2">Active Windows</span>
                <span class="h3 fw-bold text-dark mb-0"><?= $countActive; ?></span>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm p-3 bg-white h-100">
            <div class="d-flex align-items-center justify-content-between">
                <span class="meta-label me-2">Priority Lanes</span>
                <span class="h3 fw-bold text-dark mb-0"><?= $countPriority; ?></span>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm p-3 bg-white h-100">
            <div class="d-flex align-items-center justify-content-between">
                <span class="meta-label me-2">Inactive</span>
                <span class="h3 fw-bold text-dark mb-0"><?= $countInactive; ?></span>
            </div>
        </div>
    </div>
</div>

    <!-- COUNTERS CARD GRID -->
    <div class="row g-4" id="countersGrid">
        <?php if (!empty($counters)): ?>
            <?php foreach ($counters as $c): 
                $assignedServiceIds = $counterServices[$c['id']] ?? [];
                $assignedNames = [];

                foreach ($assignedServiceIds as $srv_id) {
                    foreach ($allActiveServices as $srv) { 
                        if ($srv['id'] == $srv_id) { 
                            $assignedNames[] = $srv['name']; 
                            break; 
                        } 
                    }
                }
                
                $assignedStr = empty($assignedNames) ? 'No services assigned' : implode(', ', $assignedNames);
                $assignedStaff = $counterModel->getCounterStaff($c['id']);

                // Badge styling per counter type
                $typeClass = 'badge-soft-primary';
                if ($c['counter_type'] === 'Priority') {
                    $typeClass = 'badge-soft-danger';
                } elseif ($c['counter_type'] === 'Dedicated') {
                    $typeClass = 'badge-soft-success';
                }
            ?>
                <div class="col-12 col-md-6 col-lg-4 counter-row" 
                     data-status="<?= $c['is_active'] ? 'active' : 'inactive'; ?>" 
                     data-type="<?= $c['counter_type']; ?>" 
                     data-name="<?= htmlspecialchars($c['name']); ?>">
                    <div class="card counter-card h-100 border-0 shadow-sm rounded-3">
                        
                        <!-- Card Header -->
                        <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center gap-2">
                                <span class="bg-light p-2 rounded text-muted">
                                    <i class="bi bi-window-stack"></i>
                                </span>
                                <div>
                                    <h2 class="h6 fw-bold mb-0 text-dark"><?= htmlspecialchars($c['name']); ?></h2>
                                    <span class="small text-muted font-monospace">ID: #<?= $c['id']; ?></span>
                                </div>
                            </div>
                            <?php if ($c['is_active']): ?>
                                <span class="badge px-2 py-1 rounded-pill <?= $typeClass; ?>">
                                    <?= htmlspecialchars($c['counter_type']); ?>
                                </span>
                            <?php else: ?>
                                <span class="badge px-2 py-1 rounded-pill badge-soft-secondary">Inactive</span>
                            <?php endif; ?>
                        </div>

                        <!-- Card Body -->
                        <div class="card-body p-3 d-flex flex-column gap-3">
                            <div>
                                <span class="meta-label d-block mb-1">Assigned Staff</span>
                                <div class="d-flex align-items-center text-dark small">
                                    <i class="bi bi-person-circle text-primary me-2"></i>
                                    <span class="fw-medium text-truncate">
                                        <?= $c['staff_name'] ? htmlspecialchars($c['staff_name']) : '<span class="text-muted fst-italic">Unassigned</span>'; ?>
                                    </span>
                                </div>
                            </div>

                            <div>
                                <span class="meta-label d-block mb-1">Services Handled</span>
                                <div class="d-flex align-items-start text-dark small">
                                    <i class="bi bi-gear-fill text-secondary me-2 mt-1"></i>
                                    <span class="text-secondary text-truncate-2" title="<?= htmlspecialchars($assignedStr); ?>">
                                        <?= htmlspecialchars($assignedStr); ?>
                                    </span>
                                </div>
                            </div>

                            <?php if ($c['counter_type'] === 'Priority' && !empty($counterCategories[$c['id']])): ?>
                                <div class="pt-2 border-top">
                                    <span class="meta-label d-block mb-1">Priority Groups</span>
                                    <div class="d-flex flex-wrap gap-1">
                                        <?php foreach($counterCategories[$c['id']] as $cat): ?>
                                            <span class="badge bg-light text-danger border border-danger-subtle fw-normal">
                                                <?= htmlspecialchars($cat); ?>
                                            </span>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Card Footer -->
                        <div class="card-footer bg-light border-top-0 p-3 pt-0 rounded-bottom">
                            <div class="d-flex justify-content-end gap-2 pt-2">
                                <?php if ($view === 'archived'): ?>
                                    <form method="POST" action="/admin/counters?view=archived" onsubmit="return confirm('Restore this counter?');" class="m-0 w-100">
                                        <input type="hidden" name="action" value="restore_counter">
                                        <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                        <button type="submit" class="btn btn-outline-success btn-sm w-100">
                                            <i class="bi bi-arrow-counterclockwise me-1"></i> Restore Counter
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <button type="button" class="btn btn-outline-primary btn-sm px-3 flex-grow-1" onclick='openCounterModal(<?= json_encode($c); ?>, <?= json_encode($counterCategories[$c['id']] ?? []); ?>, <?= json_encode($assignedServiceIds); ?>, <?= json_encode($assignedStaff); ?>)'>
                                        <i class="bi bi-pencil me-1"></i> Edit
                                    </button>
                                    <form method="POST" action="/admin/counters" onsubmit="return confirm('Archive this counter?');" class="m-0">
                                        <input type="hidden" name="action" value="archive_counter">
                                        <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                        <button type="submit" class="btn btn-warning btn-sm px-3" title="Archive Counter">
                                            <i class="bi bi-archive"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="bg-white border rounded-3 text-center py-5">
                    <i class="bi bi-inbox fs-1 text-muted d-block mb-2"></i>
                    <h5 class="fw-bold text-dark">No Counters Found</h5>
                    <p class="text-muted small mb-0">There are currently no counters registered in this view.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- STATS FOOTER -->
    <div class="mt-4 pt-3 border-top text-muted small d-flex justify-content-between align-items-center">
        <span id="counterStats">Showing <?= count($counters); ?> counters</span>
    </div>
</div>

<!-- MODAL DIALOG: ADD / EDIT COUNTER -->
<?php if ($view !== 'archived'): ?>
<div class="modal fade" id="counterModal" tabindex="-1" aria-labelledby="counterModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <form id="counterForm" method="POST" action="/admin/counters">
                <div class="modal-header border-bottom px-4 py-3">
                    <h5 class="modal-title fw-bold" id="counterModalTitle">Add Counter</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="modal-body p-4">
                    <input type="hidden" name="action" value="save_counter">
                    <input type="hidden" id="c_id" name="id">
                    
                    <!-- Section 1: Basic Information -->
                    <div class="row g-3 mb-4">
                        <div class="col-12 col-md-6">
                            <label for="c_name" class="form-label fw-semibold">Counter Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="c_name" name="name" placeholder="e.g. Window 1 or Priority Station A" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="c_type" class="form-label fw-semibold">Counter Type <span class="text-danger">*</span></label>
                            <select id="c_type" class="form-select" name="counter_type" onchange="togglePriorityOptions()" required>
                                <option value="General">General</option>
                                <option value="Dedicated">Dedicated</option>
                                <option value="Priority">Priority</option>
                            </select>
                        </div>
                    </div>

                    <!-- Section 2: Priority Rules (Dynamic) -->
                    <div id="priority_fields" class="p-3 bg-light rounded border mb-4" style="display: none;">
                        <label class="form-label fw-semibold text-danger mb-2">
                            <i class="bi bi-star-fill me-1"></i> Priority Citizen Groups
                        </label>
                        <div class="row g-2 mb-3">
                            <div class="col-6 col-sm-3">
                                <div class="form-check">
                                    <input class="form-check-input c-cat" type="checkbox" value="Senior Citizen" name="categories[]" id="cat_senior">
                                    <label class="form-check-label small" for="cat_senior">Senior Citizen</label>
                                </div>
                            </div>
                            <div class="col-6 col-sm-3">
                                <div class="form-check">
                                    <input class="form-check-input c-cat" type="checkbox" value="PWD" name="categories[]" id="cat_pwd">
                                    <label class="form-check-label small" for="cat_pwd">PWD</label>
                                </div>
                            </div>
                            <div class="col-6 col-sm-3">
                                <div class="form-check">
                                    <input class="form-check-input c-cat" type="checkbox" value="Pregnant" name="categories[]" id="cat_pregnant">
                                    <label class="form-check-label small" for="cat_pregnant">Pregnant</label>
                                </div>
                            </div>
                            <div class="col-6 col-sm-3">
                                <div class="form-check">
                                    <input class="form-check-input c-cat" type="checkbox" value="Solo Parent" name="categories[]" id="cat_solo">
                                    <label class="form-check-label small" for="cat_solo">Solo Parent</label>
                                </div>
                            </div>
                        </div>
                        <div class="form-check border-top pt-2">
                            <input class="form-check-input" type="checkbox" id="c_overflow" name="overflow_general" value="1">
                            <label class="form-check-label small fw-semibold text-dark" for="c_overflow">
                                Serve general queue when priority queue is empty
                            </label>
                        </div>
                    </div>

                    <!-- Section 3: Staff & Service Mappings -->
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Assigned Staff</label>
                            <div class="border rounded p-2 bg-white" style="max-height: 160px; overflow-y: auto;">
                                <?php foreach($staffList as $st): ?>
                                    <div class="form-check py-1 px-3 mb-0 rounded hover-bg">
                                        <input class="form-check-input c-staff-cb" type="checkbox" value="<?= $st['id']; ?>" name="staff_ids[]" id="assign_staff_<?= $st['id']; ?>">
                                        <label class="form-check-label small" for="assign_staff_<?= $st['id']; ?>">
                                            <?= htmlspecialchars($st['username']); ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Assigned Services</label>
                            <div class="border rounded p-2 bg-white" style="max-height: 160px; overflow-y: auto;">
                                <?php foreach($allActiveServices as $srv): ?>
                                    <div class="form-check py-1 px-3 mb-0 rounded hover-bg">
                                        <input class="form-check-input c-assign" type="checkbox" value="<?= $srv['id']; ?>" name="assigned_services[]" id="assign_srv_<?= $srv['id']; ?>">
                                        <label class="form-check-label small" for="assign_srv_<?= $srv['id']; ?>">
                                            <?= htmlspecialchars($srv['name']); ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="modal-footer bg-light px-4 py-3 border-top gap-2">
                    <button type="button" class="btn btn-outline-secondary px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4">Save Counter</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Bootstrap Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
let counterModalInstance = null;

document.addEventListener('DOMContentLoaded', function() {
    const modalEl = document.getElementById('counterModal');
    if (modalEl) {
        counterModalInstance = new bootstrap.Modal(modalEl);
    }
});

function openCounterModal(c = null, cats = [], assigned = [], assignedStaff = []) {
    document.getElementById('counterForm').reset();
    document.querySelectorAll('.c-cat').forEach(cb => cb.checked = false);
    document.querySelectorAll('.c-assign').forEach(cb => cb.checked = false);
    document.querySelectorAll('.c-staff-cb').forEach(cb => cb.checked = false);
    
    if (c) {
        document.getElementById('counterModalTitle').innerText = 'Edit Counter Details';
        document.getElementById('c_id').value = c.id;
        document.getElementById('c_name').value = c.name;
        document.getElementById('c_type').value = c.counter_type;
        document.getElementById('c_overflow').checked = (c.overflow_general == 1);
        
        if (cats && cats.length) {
            document.querySelectorAll('.c-cat').forEach(cb => {
                if (cats.includes(cb.value)) cb.checked = true;
            });
        }
        if (assigned && assigned.length) {
            document.querySelectorAll('.c-assign').forEach(cb => {
                if (assigned.includes(cb.value) || assigned.includes(parseInt(cb.value))) cb.checked = true;
            });
        }
        if (assignedStaff && assignedStaff.length) {
            document.querySelectorAll('.c-staff-cb').forEach(cb => {
                if (assignedStaff.includes(cb.value) || assignedStaff.includes(parseInt(cb.value))) cb.checked = true;
            });
        }
    } else {
        document.getElementById('counterModalTitle').innerText = 'Add New Counter';
        document.getElementById('c_id').value = '';
    }
    
    togglePriorityOptions();
    counterModalInstance.show();
}

function togglePriorityOptions() {
    const type = document.getElementById('c_type').value;
    document.getElementById('priority_fields').style.display = (type === 'Priority') ? 'block' : 'none';
}

function filterCounters() {
    const input = document.getElementById('filterCounterInput').value.toLowerCase().trim();
    const rows = document.querySelectorAll('.counter-row');
    let totalCount = 0;
    
    rows.forEach(row => {
        const name = row.getAttribute('data-name').toLowerCase();
        if (name.includes(input)) {
            row.classList.remove('d-none');
            totalCount++;
        } else {
            row.classList.add('d-none');
        }
    });
    
    const statsEl = document.getElementById('counterStats');
    if (statsEl) {
        statsEl.innerText = `Showing ${totalCount} matching counters`;
    }
}
</script>

<?php require_once __DIR__ . '/../../../includes/footer.php'; ?>