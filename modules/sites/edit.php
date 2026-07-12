<?php
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/models/Site.php';

Session::requireRole('admin');

$db = new Database();
$conn = $db->getConnection();
$site = new Site($conn);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $site_id = $_POST['site_id'] ?? '';
    $site_name = $_POST['site_name'] ?? '';
    $site_type = $_POST['site_type'] ?? 'offsite';

    if (!empty($site_id) && !empty($site_name)) {
        $site->id = $site_id;
        $site->name = $site_name;
        $site->type = $site_type;

        if ($site->update()) {
            header('Location: index.php?status=edited');
            exit();
        }
    }
    header('Location: index.php?status=error');
    exit();
}

$site_id = $_GET['id'] ?? '';
if (empty($site_id)) {
    header('Location: index.php');
    exit();
}

$stmt = $conn->prepare("SELECT * FROM sites WHERE id = ?");
$stmt->execute([$site_id]);
$current_site = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$current_site) {
    header('Location: index.php');
    exit();
}

$pageTitle = 'Edit Site - Admin Portal';
$activeMenu = 'sites';

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar_admin.php';
?>

<div class="main-content">
    <h2>Edit Site</h2>
    <form action="edit.php" method="POST" style="max-width: 400px;">
        <input type="hidden" name="site_id" value="<?php echo htmlspecialchars($current_site['id']); ?>">
        <div class="form-group" style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px;">Site Name:</label>
            <input type="text" name="site_name" value="<?php echo htmlspecialchars($current_site['name']); ?>" required style="width: 100%; padding: 8px;">
        </div>
        <div class="form-group" style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px;">Site Type:</label>
            <select name="site_type" required style="width: 100%; padding: 8px;">
                <option value="offsite" <?php echo ($current_site['type'] === 'offsite') ? 'selected' : ''; ?>>Specific Site (Offsite)</option>
                <option value="office" <?php echo ($current_site['type'] === 'office') ? 'selected' : ''; ?>>Office (Main)</option>
            </select>
        </div>
        <div style="margin-top: 25px;">
            <button type="submit" style="padding: 10px 20px; background-color: blue; color: white; border: none; cursor: pointer;">Update</button>
            <a href="index.php" style="margin-left: 15px; color: gray; text-decoration: underline;">Cancel</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
