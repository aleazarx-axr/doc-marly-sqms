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
    $counter_id = $_POST['counter_id'] ?? '';
    $counter_name = $_POST['counter_name'] ?? '';
    $site_id = $_POST['site_id'] ?? '';

    if (!empty($counter_id) && !empty($counter_name) && !empty($site_id)) {
        $counter->id = $counter_id;
        $counter->name = $counter_name;
        $counter->site_id = $site_id;

        if ($counter->update()) {
            header('Location: index.php?status=edited');
            exit();
        }
    }
    header('Location: index.php?status=error');
    exit();
}

$counter_id = $_GET['id'] ?? '';
if (empty($counter_id)) {
    header('Location: index.php');
    exit();
}

$stmt = $conn->prepare("SELECT * FROM counters WHERE id = ?");
$stmt->execute([$counter_id]);
$current_counter = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$current_counter) {
    header('Location: index.php');
    exit();
}

$stmtSites = $siteModel->read();
$sites = [];
while ($row = $stmtSites->fetch(PDO::FETCH_ASSOC)) {
    $sites[] = $row;
}

$pageTitle = 'Edit Counter - Admin Portal';
$activeMenu = 'counters';

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar_admin.php';
?>

<div class="main-content">
    <h2>Edit Counter</h2>
    <form action="edit.php" method="POST" style="max-width: 400px;">
        <input type="hidden" name="counter_id" value="<?php echo htmlspecialchars($current_counter['id']); ?>">
        <div class="form-group" style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px;">Counter / Window Name:</label>
            <input type="text" name="counter_name" value="<?php echo htmlspecialchars($current_counter['name']); ?>" required style="width: 100%; padding: 8px;">
        </div>
        <div class="form-group" style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px;">Assign to Site:</label>
            <select name="site_id" required style="width: 100%; padding: 8px;">
                <option value="">-- Select a Site --</option>
                <?php foreach ($sites as $site): ?>
                    <option value="<?php echo $site['id']; ?>" <?php echo ($current_counter['site_id'] == $site['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($site['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div style="margin-top: 25px;">
            <button type="submit" style="padding: 10px 20px; background-color: blue; color: white; border: none; cursor: pointer;">Update</button>
            <a href="index.php" style="margin-left: 15px; color: gray; text-decoration: underline;">Cancel</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
