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
            
            // Handle array of requirements and implode
            $reqs = $_POST['requirements'] ?? [];
            if (!is_array($reqs)) {
                $reqs = [$reqs];
            }
            $reqs = array_map('trim', $reqs);
            $reqs = array_filter($reqs);
            $serviceModel->requirements = implode(", ", $reqs);
            
            // Hardcode defaults for removed fields
            $serviceModel->description = '';
            $serviceModel->prefix = substr(md5(uniqid()), 0, 8);
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
        $redirectUrl = "/admin/services" . (isset($_GET['view']) && $_GET['view'] === 'archived' ? '?view=archived' : '');
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

<!-- DIRECT BOOTSTRAP 5 STYLESHEET FALLBACKS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="/assets/css/admin_service_management.css">

<div class="main-content container-fluid px-4 py-4">
    <!-- PAGE HEADER WITH INTEGRATED SEARCH BAR AND ACTIONS -->
    <div class="header-section d-flex flex-wrap justify-content-between align-items-center mb-4 pb-3 border-bottom gap-3">
        <!-- Title & Subtitle -->
        <div>
            <h1 class=" fw-bold mb-1">
                <?= $view === 'archived' ? 'Archived Services' : 'Manage Services'; ?>
            </h1>
            <p class="text-muted small mb-0">View, create, edit, and archive services offered by the platform.</p>
        </div>

       <!-- Integrated Header Search Bar & Action Buttons -->
        <div class="d-flex flex-wrap align-items-stretch gap-2">
            <!-- Header Search Bar -->
            <div class="input-group" style="width: 250px; height: 38px;">
                <span class="input-group-text bg-white text-muted border-end-0">
                    <i class="bi bi-search"></i>
                </span>
                <input type="text" id="filterServiceInput" class="form-control border-start-0 ps-0" onkeyup="filterServices()" placeholder="Search name or code...">
            </div>

            <!-- Action Buttons -->
            <?php if ($view === 'archived'): ?>
                <a href="/admin/services" class="btn btn-warning px-3 d-inline-flex align-items-center" style="height: 38px;">
                    <i class="bi bi-arrow-left me-1"></i> Active Services
                </a>
            <?php else: ?>
                <a href="/admin/services?view=archived" class="btn btn-warning px-3 d-inline-flex align-items-center" style="height: 38px;">
                    <i class="bi bi-archive me-1"></i> Archives
                </a>
                <button type="button" class="btn btn-primary px-3 d-inline-flex align-items-center" style="height: 38px;" onclick="openCreateModal()">
                    <i class="bi bi-plus-lg me-1"></i> Add Service
                </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- CARDS GRID WITH ENHANCED SPACING (g-4) -->
    <div class="row g-4" id="servicesGrid">
        <?php if (count($services) > 0): ?>
            <?php foreach ($services as $row): ?>
                <div class="  col-12 col-md-6 col-lg-4 service-row" 
                     data-name="<?= htmlspecialchars($row['name']) ?>" 
                     data-code="<?= htmlspecialchars($row['code'] ?? '') ?>"
                     data-requirements="<?= htmlspecialchars($row['requirements'] ?? '') ?>"
                     data-id="<?= $row['id'] ?>">
                    <div class="card h-100 shadow-sm border-0">
                        <!-- Card Header -->
                        <div class="card-header bg-white d-flex justify-content-between align-items-center p-3 border-0">
                            <span class="fw-bold text-primary font-monospace fs-6"><?= htmlspecialchars($row['code'] ?? 'N/A') ?></span>
                            <?php if ($row['is_active']): ?>
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1">Active</span>
                            <?php else: ?>
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-2 py-1">Inactive</span>
                            <?php endif; ?>
                        </div>

                        <!-- Card Body -->
                        <div class="card-body px-3 py-2">
                            <h5 class="card-title h6 fw-bold mb-2"><?= htmlspecialchars($row['name']) ?></h5>
                            <?php if (!empty($row['requirements'])): ?>
                                <p class="card-text small text-muted text-truncate mb-0" title="<?= htmlspecialchars($row['requirements']) ?>">
                                    <strong>Req:</strong> <?= htmlspecialchars($row['requirements']) ?>
                                </p>
                            <?php else: ?>
                                <p class="card-text small text-muted fst-italic mb-0">No requirements specified.</p>
                            <?php endif; ?>
                        </div>

                        <!-- Card Footer with Gap for Buttons -->
                        <div class="card-footer bg-light border-0 d-flex justify-content-between align-items-center p-3 mt-2">
                            <span class="small text-muted font-monospace">ID: #<?= $row['id'] ?></span>
                            <div class="d-flex align-items-center gap-2">
                                <?php if ($view === 'archived'): ?>
                                    <form method="POST" action="/admin/services?view=archived" onsubmit="return confirm('Restore this service?');" class="m-0">
                                        <input type="hidden" name="action" value="restore_service">
                                        <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                        <button type="submit" class="btn btn-outline-success btn-sm px-3">
                                            <i class="bi bi-arrow-counterclockwise me-1"></i> Restore
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <button type="button" class="btn btn-outline-primary btn-sm px-3" onclick="editServiceFromCard(this)">
                                        <i class="bi bi-pencil me-1"></i> Edit
                                    </button>
                                    <form method="POST" action="/admin/services" onsubmit="return confirm('Archive this service?');" class="m-0">
                                        <input type="hidden" name="action" value="archive_service">
                                        <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                        <button type="submit" class="btn btn-warning btn-sm px-3">
                                            <i class="bi bi-archive me-1"></i> Archive
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
                <div class="alert alert-info text-center py-4 mb-0" role="alert">
                    <i class="bi bi-info-circle fs-4 d-block mb-2"></i>
                    No services found in this view.
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- BOOTSTRAP 5 MODAL DIALOG FOR ADD / EDIT -->
<?php if ($view !== 'archived'): ?>
<div class="modal fade" id="serviceModal" tabindex="-1" aria-labelledby="serviceModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="/admin/services" id="addServiceForm">
                <div class="modal-header px-4 pt-4 pb-2 border-0">
                    <h5 class="modal-title fw-bold" id="serviceModalTitle">Add New Service</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-4 py-2">
                    <input type="hidden" name="action" value="save_service">
                    <input type="hidden" name="id" id="serviceId" value="">
                    
                    <div class="mb-3">
                        <label for="serviceName" class="form-label fw-semibold">Service Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="serviceName" name="name" placeholder="e.g., Business Permit Application" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Requirements</label>
                        <div id="requirementsContainer">
                            <!-- Dynamic fields go here -->
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary mt-2" onclick="addRequirementField()">
                            <i class="bi bi-plus-lg"></i> Add Requirement
                        </button>
                    </div>
                </div>
                <div class="modal-footer px-4 pb-4 pt-2 border-0 gap-2">
                    <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-3">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- DIRECT BOOTSTRAP 5 JAVASCRIPT BUNDLE FALLBACK -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
let serviceModalInstance = null;

document.addEventListener('DOMContentLoaded', function() {
    const modalEl = document.getElementById('serviceModal');
    if (modalEl) {
        serviceModalInstance = new bootstrap.Modal(modalEl);
    }
});

function openCreateModal() {
    document.getElementById('addServiceForm').reset();
    document.getElementById('serviceId').value = '';
    document.getElementById('serviceModalTitle').innerText = 'Add New Service';
    
    // Reset requirements
    const container = document.getElementById('requirementsContainer');
    container.innerHTML = '';
    addRequirementField(); // Add at least one empty field
    
    serviceModalInstance.show();
}

function editServiceFromCard(btn) {
    const cardEl = btn.closest('.service-row');
    const id = cardEl.getAttribute('data-id');
    const name = cardEl.getAttribute('data-name');
    const requirements = cardEl.getAttribute('data-requirements');

    document.getElementById('serviceModalTitle').innerText = 'Edit Service';
    document.getElementById('serviceId').value = id;
    document.getElementById('serviceName').value = name;
    
    // Handle requirements
    const container = document.getElementById('requirementsContainer');
    container.innerHTML = '';
    
    if (requirements && requirements.trim() !== '') {
        const reqArray = requirements.split(/[\n,]+/);
        reqArray.forEach(req => {
            if (req.trim() !== '') {
                addRequirementField(req.trim());
            }
        });
    }
    
    // Add an empty one if there are none
    if (container.children.length === 0) {
        addRequirementField();
    }
    
    serviceModalInstance.show();
}

function addRequirementField(value = '') {
    const container = document.getElementById('requirementsContainer');
    
    const div = document.createElement('div');
    div.className = 'input-group mb-2 requirement-item';
    
    const input = document.createElement('input');
    input.type = 'text';
    input.className = 'form-control';
    input.name = 'requirements[]';
    input.placeholder = 'e.g., Valid ID';
    input.value = value;
    
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'btn btn-outline-danger';
    btn.onclick = function() {
        div.remove();
    };
    btn.innerHTML = '<i class="bi bi-x-lg"></i>';
    
    div.appendChild(input);
    div.appendChild(btn);
    
    container.appendChild(div);
}

function filterServices() {
    const input = document.getElementById('filterServiceInput').value.toLowerCase().trim();
    const rows = document.querySelectorAll('.service-row');
    
    rows.forEach(row => {
        const name = row.getAttribute('data-name').toLowerCase();
        const code = row.getAttribute('data-code').toLowerCase();
        
        if (name.includes(input) || code.includes(input)) {
            row.classList.remove('d-none');
        } else {
            row.classList.add('d-none');
        }
    });
}
</script>

<?php require_once __DIR__ . '/../../../includes/footer.php'; ?>