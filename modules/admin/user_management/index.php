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
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
        <h2><?php echo $view === 'archived' ? 'Archived Users' : 'Manage Users'; ?></h2>
        <div>
            <?php if ($view === 'archived'): ?>
                <a href="/admin/user_management" style="color: blue; text-decoration: underline; margin-right: 15px;">View Active Users</a>
            <?php else: ?>
                <a href="/admin/user_management?view=archived" style="color: gray; text-decoration: underline; margin-right: 15px;">View Archives</a>
            <?php endif; ?>
            <button onclick="openUserModal()" style="color: blue; background: none; border: none; text-decoration: underline; font-size: 16px; cursor: pointer;">+ Add User</button>
        </div>
    </div>

    <?php if(isset($_GET['status'])): ?>
        <?php
            $status = $_GET['status'];
            $msg = "Action completed successfully.";
            $color = "green";
            if ($status == 'error') { $msg = "An error occurred."; $color = "red"; }
            if ($status == 'invalid_email') { $msg = "Only @gmail.com email addresses are allowed."; $color = "red"; }
            if ($status == 'deleted') { $msg = "User deleted successfully."; $color = "orange"; }
            if ($status == 'edited') { $msg = "User updated successfully."; $color = "blue"; }
            if ($status == 'added') { $msg = "User added successfully."; $color = "green"; }
            if ($status == 'resent') { $msg = "Setup link regenerated and resent."; $color = "green"; }
            if ($status == 'resend_error') { $msg = "Failed to resend setup link."; $color = "red"; }
            if ($status == 'archived') { $msg = "User successfully archived."; $color = "orange"; }
            if ($status == 'restored') { $msg = "User successfully restored."; $color = "green"; }
        ?>
        <p style="color: <?php echo $color; ?>; background: #f0f9f0; padding: 10px; border: 1px solid <?php echo $color; ?>; margin-bottom: 15px;">
            <?php echo $msg; ?>
        </p>
    <?php endif; ?>

    <table>
        <thead>
            <tr><th>ID</th><th>Username</th><th>Email</th><th>Role</th><th>Status</th><th>Actions</th></tr>
        </thead>
        <tbody>
            <?php if (count($users) > 0): ?>
                <?php foreach ($users as $row): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['id']); ?></td>
                        <td><?php echo htmlspecialchars($row['username']); ?></td>
                        <td><?php echo htmlspecialchars($row['email'] ?? ''); ?></td>
                        <td><?php echo ucwords(str_replace('_', ' ', htmlspecialchars($row['role']))); ?></td>
                        <td>
                            <?php if ($row['status'] === 'archived'): ?>
                                <span style="color: gray;">Archived</span>
                            <?php elseif (!empty($row['setup_token'])): ?>
                                <span style="color: orange;">Pending Setup</span>
                                <?php if (strtotime($row['token_expires']) < time()): ?>
                                    <span style="color: red; font-size: 12px;">(Expired)</span>
                                <?php endif; ?>
                            <?php else: ?>
                                <span style="color: green;">Active</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($row['status'] !== 'archived'): ?>
                                <button onclick='openUserModal(<?php echo json_encode($row); ?>)' style="margin-right: 5px; color: blue; background: none; border: none; text-decoration: underline; cursor: pointer; padding: 0;">Edit</button>
                            <?php endif; ?>
                            
                            <?php if (!empty($row['setup_token']) && $row['status'] !== 'archived'): ?>
                                <form action="/admin/user_management/resend_setup" method="POST" style="display:inline;" onsubmit="return confirm('Generate a new setup link and resend email?');">
                                    <input type="hidden" name="user_id" value="<?php echo $row['id']; ?>">
                                    <button type="submit" style="margin-right: 5px; cursor: pointer; color: #007bff; background: none; border: none; text-decoration: underline;">Resend Link</button>
                                </form>
                            <?php endif; ?>
                            
                            <?php if ($row['status'] === 'archived'): ?>
                                <form action="/admin/user_management/restore" method="POST" style="display:inline;" onsubmit="return confirm('Restore this user? They will be able to log in again.');">
                                    <input type="hidden" name="user_id" value="<?php echo $row['id']; ?>">
                                    <button type="submit" style="cursor: pointer; color: green; background: none; border: none; text-decoration: underline;">Restore</button>
                                </form>
                            <?php elseif ($row['username'] !== $_SESSION['user_id']): ?>
                                <form action="/admin/user_management/archive" method="POST" style="display:inline;" onsubmit="return confirm('Archive this user? They will no longer be able to log in.');">
                                    <input type="hidden" name="user_id" value="<?php echo $row['id']; ?>">
                                    <button type="submit" style="cursor: pointer; color: orange; background: none; border: none; text-decoration: underline;">Archive</button>
                                </form>
                            <?php else: ?>
                                <span style="color: gray; margin-left: 5px;">(You)</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="6">No users found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- User Modal -->
<div id="userModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000; align-items:center; justify-content:center;">
    <div style="background:white; padding:20px; border-radius:8px; width:400px; max-width:90%;">
        <h3 id="modalTitle" style="margin-top:0;">Add User</h3>
        <form method="POST" action="/admin/user_management">
            <input type="hidden" name="action" value="save_user">
            <input type="hidden" name="user_id" id="modal_user_id">
            
            <div class="form-group" style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px;">Full Name:</label>
                <input type="text" name="name" id="modal_name" required style="width: 100%; padding: 8px;">
            </div>
            <div class="form-group" style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px;">Username <span id="username_hint" style="font-size: 12px; color: #666;">(leave blank to auto-generate)</span>:</label>
                <input type="text" name="username" id="modal_username" style="width: 100%; padding: 8px;">
            </div>
            <div class="form-group" style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px;">Email:</label>
                <input type="email" name="email" id="modal_email" required style="width: 100%; padding: 8px;" pattern="^[a-zA-Z0-9._%+-]+@gmail\.com$" title="Please enter a valid @gmail.com address" placeholder="e.g. user@gmail.com">
            </div>
            <div class="form-group" style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px;">Password <span id="password_hint" style="font-size: 12px; color: #666;">(leave blank to keep current)</span>:</label>
                <input type="password" name="password" id="modal_password" style="width: 100%; padding: 8px;">
            </div>
            <div class="form-group" style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px;">Role:</label>
                <select name="role" id="modal_role" required style="width: 100%; padding: 8px;">
                    <option value="service_staff">Service Staff</option>
                    <option value="information_staff">Information Staff</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            
            <div style="margin-top: 25px; text-align: right;">
                <button type="button" onclick="closeUserModal()" style="padding: 10px 20px; background-color: #ccc; border: none; cursor: pointer; border-radius: 4px; margin-right: 10px;">Cancel</button>
                <button type="submit" style="padding: 10px 20px; background-color: blue; color: white; border: none; cursor: pointer; border-radius: 4px;">Save</button>
            </div>
        </form>
    </div>
</div>

<script>
function openUserModal(user = null) {
    document.getElementById('userModal').style.display = 'flex';
    if (user) {
        document.getElementById('modalTitle').textContent = 'Edit User';
        document.getElementById('modal_user_id').value = user.id;
        document.getElementById('modal_name').value = user.name || '';
        document.getElementById('modal_username').value = user.username || '';
        document.getElementById('modal_email').value = user.email || '';
        document.getElementById('modal_role').value = user.role || 'service_staff';
        document.getElementById('modal_password').value = '';
        document.getElementById('username_hint').style.display = 'none';
        document.getElementById('password_hint').style.display = 'inline';
        document.getElementById('modal_username').required = true;
    } else {
        document.getElementById('modalTitle').textContent = 'Add User';
        document.getElementById('modal_user_id').value = '';
        document.getElementById('modal_name').value = '';
        document.getElementById('modal_username').value = '';
        document.getElementById('modal_email').value = '';
        document.getElementById('modal_role').value = 'service_staff';
        document.getElementById('modal_password').value = '';
        document.getElementById('username_hint').style.display = 'inline';
        document.getElementById('password_hint').style.display = 'none';
        document.getElementById('modal_username').required = false;
    }
}
function closeUserModal() {
    document.getElementById('userModal').style.display = 'none';
}
</script>

<?php require_once __DIR__ . '/../../../includes/footer.php'; ?>
