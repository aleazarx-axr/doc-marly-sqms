<?php
require_once __DIR__ . '/../../../includes/functions.php';
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../includes/models/User.php';
require_once __DIR__ . '/../../../includes/Mailer.php';

Session::requireRole('admin');

$db = new Database();
$conn = $db->getConnection();
$userModel = new User($conn);

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    if ($_POST['action'] === 'save_user') {
        $user_id = $_POST['user_id'] ?? '';
        $name = trim($_POST['name'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'service_staff';

        if (empty($user_id)) {
            // Add Mode
            if (!empty($name) && !empty($email)) {
                if (!preg_match('/@gmail\.com$/i', $email)) {
                    header('Location: /admin/user_management?status=invalid_email');
                    exit();
                }
                if (empty($username)) {
                    $username = $userModel->generateUsername($name);
                }
                $setup_token = bin2hex(random_bytes(32));
                $token_expires = date('Y-m-d H:i:s', strtotime('+24 hours'));
                
                $userModel->name = $name;
                $userModel->username = $username;
                $userModel->email = $email;
                $userModel->password = password_hash(bin2hex(random_bytes(10)), PASSWORD_BCRYPT);
                $userModel->role = $role;
                $userModel->setup_token = $setup_token;
                $userModel->token_expires = $token_expires;

                if ($userModel->create()) {
                    $mailer = new Mailer();
                    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
                    $host = $_SERVER['HTTP_HOST'];
                    $setupLink = $protocol . "://" . $host . "/setup.php?token=" . $setup_token;
                    
                    $mailer->sendWelcomeEmail($email, $name, $username, $setupLink);
                    header('Location: /admin/user_management?status=added');
                    exit();
                }
            }
            header('Location: /admin/user_management?status=error');
            exit();
        } else {
            // Edit Mode
            if (!empty($user_id) && !empty($username)) {
                if (!empty($email) && !preg_match('/@gmail\.com$/i', $email)) {
                    header('Location: /admin/user_management?status=invalid_email');
                    exit();
                }
                
                $userModel->id = $user_id;
                $userModel->name = $name;
                $userModel->username = $username;
                $userModel->email = $email;
                $userModel->role = $role;
                
                if (!empty($password)) {
                    $userModel->password = password_hash($password, PASSWORD_BCRYPT);
                } else {
                    $userModel->password = null;
                }

                if ($userModel->update()) {
                    header('Location: /admin/user_management?status=edited');
                    exit();
                }
            }
            header('Location: /admin/user_management?status=error');
            exit();
        }
    }
}

$view = $_GET['view'] ?? 'active';

$stmtUsers = $userModel->read();
$users = [];
while ($row = $stmtUsers->fetch(PDO::FETCH_ASSOC)) {
    if ($view === 'archived' && $row['status'] === 'archived') {
        $users[] = $row;
    } elseif ($view !== 'archived' && $row['status'] !== 'archived') {
        $users[] = $row;
    }
}

$pageTitle = $view === 'archived' ? 'Archived Users - Admin Portal' : 'Manage Users - Admin Portal';
$activeMenu = 'users';

require_once __DIR__ . '/../../../includes/header.php';
require_once __DIR__ . '/../../../includes/sidebar_admin.php';
?>

<div class="main-content">
    <!-- PAGE HEADER -->
    <div class="bg-white rounded-3 p-4 shadow-sm border mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <div class="d-flex align-items-center gap-2">
                    <h1 class="h4 fw-bold text-dark mb-0">
                        <?= $view === 'archived' ? 'Archived Users' : 'Manage Users'; ?>
                    </h1>
                    <span class="badge rounded-pill bg-light text-dark border"><?= count($users); ?> Total</span>
                </div>
                <p class="text-muted small mb-0 mt-1">
                    <?= $view === 'archived' 
                        ? 'View and restore archived user accounts.' 
                        : 'Manage user accounts, roles, and access permissions.'; ?>
                </p>
            </div>

            <!-- Toolbar Actions -->
            <div class="d-flex flex-wrap align-items-center gap-2">
                <div class="input-group" style="width: 250px;">
                    <span class="input-group-text bg-light border-end-0 text-muted">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" id="filterUserInput" class="form-control bg-light border-start-0 ps-0" onkeyup="filterUsers()" placeholder="Search users...">
                </div>

                <?php if ($view === 'archived'): ?>
                    <a href="/admin/user_management" class="btn btn-outline-primary d-inline-flex align-items-center">
                        <i class="bi bi-arrow-left me-1"></i> Active Users
                    </a>
                <?php else: ?>
                    <a href="/admin/user_management?view=archived" class="btn btn-warning d-inline-flex align-items-center">
                        <i class="bi bi-archive me-1"></i> View Archives
                    </a>
                    <button type="button" class="btn btn-primary d-inline-flex align-items-center" onclick="openUserModal()">
                        <i class="bi bi-plus-lg me-1"></i> Add User
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Status Messages -->
    <?php if(isset($_GET['status'])): ?>
        <?php
            $status = $_GET['status'];
            $msg = "Action completed successfully.";
            $type = "success";
            $icon = "bi-check-circle";
            
            if ($status == 'error') { 
                $msg = "An error occurred."; 
                $type = "danger"; 
                $icon = "bi-x-circle";
            }
            if ($status == 'invalid_email') { 
                $msg = "Only @gmail.com email addresses are allowed."; 
                $type = "danger"; 
                $icon = "bi-exclamation-triangle";
            }
            if ($status == 'deleted') { 
                $msg = "User deleted successfully."; 
                $type = "warning"; 
                $icon = "bi-trash";
            }
            if ($status == 'edited') { 
                $msg = "User updated successfully."; 
                $type = "info"; 
                $icon = "bi-pencil";
            }
            if ($status == 'added') { 
                $msg = "User added successfully. Welcome email sent!"; 
                $type = "success"; 
                $icon = "bi-person-plus";
            }
            if ($status == 'resent') { 
                $msg = "Setup link regenerated and resent."; 
                $type = "success"; 
                $icon = "bi-envelope";
            }
            if ($status == 'resend_error') { 
                $msg = "Failed to resend setup link."; 
                $type = "danger"; 
                $icon = "bi-envelope-exclamation";
            }
            if ($status == 'archived') { 
                $msg = "User successfully archived."; 
                $type = "warning"; 
                $icon = "bi-archive";
            }
            if ($status == 'restored') { 
                $msg = "User successfully restored."; 
                $type = "success"; 
                $icon = "bi-arrow-counterclockwise";
            }
        ?>
        <div class="alert alert-<?php echo $type; ?> alert-dismissible fade show d-flex align-items-center gap-2" role="alert">
            <i class="bi <?php echo $icon; ?> fs-5"></i>
            <span><?php echo $msg; ?></span>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- USER CARDS GRID -->
    <div class="row g-3" id="usersGrid">
        <?php if (!empty($users)): ?>
            <?php foreach ($users as $row): 
                // Determine status badge
                $isArchived = $row['status'] === 'archived';
                $isPending = !empty($row['setup_token']);
                $isExpired = $isPending && strtotime($row['token_expires']) < time();
                $isActive = !$isArchived && !$isPending;
                
                // Role badge styling
                $roleClass = match ($row['role']) {
                    'admin' => 'bg-danger-subtle text-danger border-danger-subtle',
                    'information_staff' => 'bg-info-subtle text-info border-info-subtle',
                    default => 'bg-primary-subtle text-primary border-primary-subtle',
                };
                
                // Status badge
                $statusClass = $isArchived ? 'bg-secondary' : 
                              ($isPending ? 'bg-warning' : 'bg-success');
                $statusText = $isArchived ? 'Archived' : 
                             ($isPending ? 'Pending Setup' : 'Active');
                
                // Status icon
                $statusIcon = $isArchived ? 'bi-archive' : 
                             ($isPending ? 'bi-hourglass-split' : 'bi-check-circle');
                
                // Role display name
                $roleDisplay = ucwords(str_replace('_', ' ', $row['role']));
                
                // Check if current user
                $isCurrentUser = ($row['username'] === $_SESSION['user_id']);
            ?>
                <div class="col-12 col-sm-6 col-md-4 col-lg-3 user-row"
                     data-status="<?= $row['status']; ?>"
                     data-role="<?= $row['role']; ?>"
                     data-username="<?= htmlspecialchars(strtolower($row['username'])); ?>"
                     data-name="<?= htmlspecialchars(strtolower($row['name'] ?? '')); ?>">
                    
                    <div class="card h-100 border shadow-sm rounded-3 overflow-hidden">
                        
                        <!-- Card Header -->
                        <div class="card-header bg-body-tertiary border-bottom px-3 py-2 d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center gap-2 text-truncate">
                                <span class="badge rounded-pill bg-body-secondary text-secondary border px-2 py-1 font-monospace fs-7">
                                    #<?= $row['id']; ?>
                                </span>
                                <h3 class="h6 fw-bold mb-0 text-dark text-truncate" title="<?= htmlspecialchars($row['username']); ?>">
                                    <?= htmlspecialchars($row['username']); ?>
                                </h3>
                                <?php if ($isCurrentUser): ?>
                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-1 py-0 fs-8">You</span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="d-flex align-items-center gap-1 ms-2 flex-shrink-0">
                                <span class="badge rounded-pill border px-2 py-1 <?= $roleClass; ?>">
                                    <?= $roleDisplay; ?>
                                </span>
                            </div>
                        </div>

                        <!-- Card Body -->
                        <div class="card-body p-3 d-flex flex-column gap-3 fs-7">
                            
                            <!-- Name & Email Section -->
                            <div>
                                <div class="d-flex align-items-center text-muted mb-1">
                                    <i class="bi bi-person me-1 fs-7 text-secondary"></i>
                                    <span class="fw-medium">Full Name</span>
                                </div>
                                <p class="mb-2 text-dark fw-medium">
                                    <?= htmlspecialchars($row['name'] ?? '—'); ?>
                                </p>
                                
                                <div class="d-flex align-items-center text-muted mb-1">
                                    <i class="bi bi-envelope me-1 fs-7 text-secondary"></i>
                                    <span class="fw-medium">Email</span>
                                </div>
                                <p class="mb-0 text-secondary bg-light p-2 rounded border border-light-subtle text-truncate" title="<?= htmlspecialchars($row['email'] ?? ''); ?>">
                                    <?= htmlspecialchars($row['email'] ?? '—'); ?>
                                </p>
                            </div>

                            <!-- Status Section -->
                            <div class="pt-2 border-top">
                                <div class="d-flex flex-wrap align-items-center gap-2">
                                    <span class="badge <?= $statusClass; ?> px-2 py-1 d-flex align-items-center gap-1">
                                        <i class="bi <?= $statusIcon; ?>"></i>
                                        <?= $statusText; ?>
                                    </span>
                                    <?php if ($isExpired): ?>
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">
                                            <i class="bi bi-clock me-1"></i> Expired
                                        </span>
                                    <?php endif; ?>
                                    <?php if ($isPending && !$isExpired): ?>
                                        <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1">
                                            <i class="bi bi-hourglass me-1"></i> <?= round((strtotime($row['token_expires']) - time()) / 3600); ?>h left
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>

                        </div>

                        <!-- Card Footer -->
                        <div class="card-footer bg-white border-top p-2">
                            <div class="d-flex justify-content-end gap-2">
                                <?php if ($isArchived): ?>
                                    <form method="POST" action="/admin/user_management/restore" 
                                          onsubmit="return confirm('Restore this user? They will be able to log in again.');" 
                                          class="m-0 w-100">
                                        <input type="hidden" name="user_id" value="<?= $row['id']; ?>">
                                        <button type="submit" class="btn btn-outline-success btn-sm w-100 fw-medium">
                                            <i class="bi bi-arrow-counterclockwise me-1"></i> Restore User
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <button type="button" class="btn btn-outline-primary btn-sm px-3 flex-grow-1 fw-medium" 
                                            onclick='openUserModal(<?= json_encode($row); ?>)'>
                                        <i class="bi bi-pencil me-1"></i> Edit
                                    </button>
                                    
                                    <?php if (!empty($row['setup_token'])): ?>
                                        <form action="/admin/user_management/resend_setup" method="POST" style="display:inline;" 
                                              onsubmit="return confirm('Generate a new setup link and resend email?');">
                                            <input type="hidden" name="user_id" value="<?= $row['id']; ?>">
                                            <button type="submit" class="btn btn-outline-info btn-sm px-2" title="Resend Setup Link">
                                                <i class="bi bi-envelope"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    
                                    <?php if (!$isCurrentUser): ?>
                                        <form method="POST" action="/admin/user_management/archive" 
                                              onsubmit="return confirm('Archive this user? They will no longer be able to log in.');" 
                                              class="m-0">
                                            <input type="hidden" name="user_id" value="<?= $row['id']; ?>">
                                            <button type="submit" class="btn btn-warning btn-sm px-2" title="Archive User">
                                                <i class="bi bi-archive"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>

                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="bg-white border rounded-3 text-center py-5 shadow-sm">
                    <i class="bi bi-inbox fs-1 text-muted d-block mb-2"></i>
                    <h5 class="fw-bold text-dark">No Users Found</h5>
                    <p class="text-muted small mb-0">
                        There are currently no users registered in this view.
                    </p>
                    <?php if ($view !== 'archived'): ?>
                        <button onclick="openUserModal()" class="btn btn-primary btn-sm mt-3">
                            <i class="bi bi-plus-lg"></i> Add Your First User
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- User Modal -->
<div id="userModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000; align-items:center; justify-content:center;">
    <div class="bg-white rounded-3 shadow-lg p-4" style="width:450px; max-width:95%; max-height:90vh; overflow-y:auto;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 id="modalTitle" class="fw-bold mb-0">Add User</h3>
            <button type="button" onclick="closeUserModal()" class="btn-close" aria-label="Close"></button>
        </div>
        
        <form method="POST" action="/admin/user_management">
            <input type="hidden" name="action" value="save_user">
            <input type="hidden" name="user_id" id="modal_user_id">
            
            <div class="mb-3">
                <label class="form-label fw-medium">Full Name <span class="text-danger">*</span></label>
                <input type="text" name="name" id="modal_name" required 
                       class="form-control" placeholder="e.g. John Doe">
            </div>
            
            <div class="mb-3">
                <label class="form-label fw-medium">
                    Username 
                    <span id="username_hint" class="text-muted fw-normal" style="font-size: 12px;">(leave blank to auto-generate)</span>
                </label>
                <input type="text" name="username" id="modal_username" 
                       class="form-control" placeholder="e.g. johndoe">
            </div>
            
            <div class="mb-3">
                <label class="form-label fw-medium">Email <span class="text-danger">*</span></label>
                <input type="email" name="email" id="modal_email" required 
                       class="form-control" 
                       pattern="^[a-zA-Z0-9._%+-]+@gmail\.com$" 
                       title="Please enter a valid @gmail.com address" 
                       placeholder="e.g. user@gmail.com">
                <div class="form-text text-muted">Only @gmail.com addresses are allowed</div>
            </div>
            
            <div class="mb-3">
                <label class="form-label fw-medium">
                    Password 
                    <span id="password_hint" class="text-muted fw-normal" style="font-size: 12px;">(leave blank to keep current)</span>
                </label>
                <input type="password" name="password" id="modal_password" 
                       class="form-control" placeholder="Enter password">
            </div>
            
            <div class="mb-4">
                <label class="form-label fw-medium">Role <span class="text-danger">*</span></label>
                <select name="role" id="modal_role" required class="form-select">
                    <option value="service_staff">Service Staff</option>
                    <option value="information_staff">Information Staff</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            
            <div class="d-flex gap-2 justify-content-end border-top pt-3">
                <button type="button" onclick="closeUserModal()" class="btn btn-secondary">
                    Cancel
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i> Save
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Filter users by search input
function filterUsers() {
    const input = document.getElementById('filterUserInput');
    const filter = input.value.toLowerCase();
    const rows = document.querySelectorAll('.user-row');
    
    rows.forEach(row => {
        const username = row.dataset.username || '';
        const name = row.dataset.name || '';
        const match = username.includes(filter) || name.includes(filter);
        row.style.display = match ? '' : 'none';
    });
}

function openUserModal(user = null) {
    const modal = document.getElementById('userModal');
    modal.style.display = 'flex';
    
    if (user) {
        document.getElementById('modalTitle').textContent = 'Edit User';
        document.getElementById('modal_user_id').value = user.id;
        document.getElementById('modal_name').value = user.name || '';
        document.getElementById('modal_username').value = user.username || '';
        document.getElementById('modal_username').required = true;
        document.getElementById('modal_email').value = user.email || '';
        document.getElementById('modal_role').value = user.role || 'service_staff';
        document.getElementById('modal_password').value = '';
        document.getElementById('modal_password').placeholder = 'Enter new password to change';
        
        document.getElementById('username_hint').style.display = 'none';
        document.getElementById('password_hint').style.display = 'inline';
    } else {
        document.getElementById('modalTitle').textContent = 'Add User';
        document.getElementById('modal_user_id').value = '';
        document.getElementById('modal_name').value = '';
        document.getElementById('modal_username').value = '';
        document.getElementById('modal_username').required = false;
        document.getElementById('modal_email').value = '';
        document.getElementById('modal_role').value = 'service_staff';
        document.getElementById('modal_password').value = '';
        document.getElementById('modal_password').placeholder = 'Enter password';
        
        document.getElementById('username_hint').style.display = 'inline';
        document.getElementById('password_hint').style.display = 'none';
    }
}

function closeUserModal() {
    document.getElementById('userModal').style.display = 'none';
}

// Close modal on background click
document.getElementById('userModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeUserModal();
    }
});

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeUserModal();
    }
});
</script>

<style>
/* Additional styles to match counter cards */
.fs-7 {
    font-size: 0.875rem;
}
.fs-8 {
    font-size: 0.75rem;
}
.card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15) !important;
}
.btn-sm {
    font-size: 0.8125rem;
}
/* Search input focus style */
#filterUserInput:focus {
    box-shadow: none;
    border-color: #ced4da;
    background-color: #f8f9fa;
}
</style>

<?php require_once __DIR__ . '/../../../includes/footer.php'; ?>