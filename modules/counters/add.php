<?php
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/models/Counter.php';
require_once __DIR__ . '/../../includes/models/Site.php';

Session::requireRole('admin');

$db = new Database();
$conn = $db->getConnection();
$counter = new Counter($conn);
$siteModel = new Site($conn);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $counter_name = $_POST['counter_name'] ?? '';
    $site_id = $_POST['site_id'] ?? '';

    if (!empty($counter_name) && !empty($site_id)) {
        $counter->name = $counter_name;
        $counter->site_id = $site_id;

        if ($counter->create()) {
            header('Location: index.php?status=added');
            exit();
        }
    }
    header('Location: index.php?status=error');
    exit();
}

$stmtSites = $siteModel->read();
$sites = [];
while ($row = $stmtSites->fetch(PDO::FETCH_ASSOC)) {
    $sites[] = $row;
}

$pageTitle = 'Add Counter - Admin Portal';
$activeMenu = 'counters';

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar_admin.php';
?>

<div class="main-content">
    <h2>Add New Counter</h2>
    <form action="add.php" method="POST" style="max-width: 400px;">
        <div class="form-group" style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px;">Counter / Window Name:</label>
            <input type="text" name="counter_name" placeholder="e.g. Window 1 - Receiving" required style="width: 100%; padding: 8px;">
        </div>
        <div class="form-group" style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px;">Assign to Site:</label>
            <select name="site_id" required style="width: 100%; padding: 8px;">
                <option value="">-- Select a Site --</option>
                <?php foreach ($sites as $site): ?>
                    <option value="<?php echo $site['id']; ?>"><?php echo htmlspecialchars($site['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div style="margin-top: 25px;">
            <button type="submit" style="padding: 10px 20px; background-color: blue; color: white; border: none; cursor: pointer;">Save</button>
            <a href="index.php" style="margin-left: 15px; color: gray; text-decoration: underline;">Cancel</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
