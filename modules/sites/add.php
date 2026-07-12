<?php
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/models/Site.php';

Session::requireRole('admin');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $site_name = $_POST['site_name'] ?? '';
    $site_type = $_POST['site_type'] ?? 'offsite';

    if (!empty($site_name)) {
        $db = new Database();
        $conn = $db->getConnection();
        $site = new Site($conn);
        
        $site->name = $site_name;
        $site->type = $site_type;

        if ($site->create()) {
            header('Location: index.php?status=added');
            exit();
        }
    }
    header('Location: index.php?status=error');
    exit();
}

$pageTitle = 'Add Site - Admin Portal';
$activeMenu = 'sites';

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar_admin.php';
?>

<div class="main-content">
    <h2>Add New Site</h2>
    <form action="add.php" method="POST" style="max-width: 400px;">
        <div class="form-group" style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px;">Site Name:</label>
            <input type="text" name="site_name" required style="width: 100%; padding: 8px;">
        </div>
        <div class="form-group" style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px;">Site Type:</label>
            <select name="site_type" required style="width: 100%; padding: 8px;">
                <option value="offsite">Specific Site (Offsite)</option>
                <option value="office">Office (Main)</option>
            </select>
        </div>
        <div style="margin-top: 25px;">
            <button type="submit" style="padding: 10px 20px; background-color: blue; color: white; border: none; cursor: pointer;">Save</button>
            <a href="index.php" style="margin-left: 15px; color: gray; text-decoration: underline;">Cancel</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
