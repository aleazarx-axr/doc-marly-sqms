<?php
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/models/Service.php';
require_once __DIR__ . '/../../includes/models/Requirement.php';

Session::requireRole('admin');

$db = new Database();
$conn = $db->getConnection();
$service = new Service($conn);
$reqModel = new Requirement($conn);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $service_id = $_POST['service_id'] ?? '';
    $service_name = $_POST['service_name'] ?? '';
    $requirements_arr = $_POST['requirements'] ?? [];
    $requirements_str = implode(", ", $requirements_arr);

    if (!empty($service_id)) {
        $service->id = $service_id;
        $service->name = $service_name;
        $service->requirements = $requirements_str;

        if ($service->update()) {
            header('Location: index.php?status=edited');
            exit();
        }
    }
    header('Location: index.php?status=error');
    exit();
}

$service_id = $_GET['id'] ?? '';
if (empty($service_id)) {
    header('Location: index.php');
    exit();
}

// Fetch the service details
$stmt = $conn->prepare("SELECT * FROM services WHERE id = ?");
$stmt->execute([$service_id]);
$current_service = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$current_service) {
    header('Location: index.php');
    exit();
}

$current_reqs = explode(", ", $current_service['requirements']);

// Fetch requirements for the form
$stmtReqs = $reqModel->read();
$requirements = [];
while ($row = $stmtReqs->fetch(PDO::FETCH_ASSOC)) {
    $requirements[] = $row;
}

$pageTitle = 'Edit Service - Admin Portal';
$activeMenu = 'services';

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar_admin.php';
?>

<div class="main-content">
    <h2>Edit Service</h2>
    <form action="edit.php" method="POST" style="max-width: 500px;">
        <input type="hidden" name="service_id" value="<?php echo htmlspecialchars($current_service['id']); ?>">
        <div class="form-group" style="margin-bottom: 15px;">
            <label for="service_name" style="display: block; margin-bottom: 5px;">Service Name:</label>
            <input type="text" id="service_name" name="service_name" value="<?php echo htmlspecialchars($current_service['name']); ?>" required style="width: 100%; padding: 8px;">
        </div>
        <div class="form-group" style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px;">Select Requirements (Check all that apply):</label>
            <div style="max-height: 200px; overflow-y: auto; border: 1px solid #ddd; padding: 10px;">
                <?php if (count($requirements) > 0): ?>
                    <?php foreach ($requirements as $req): ?>
                        <div style="margin-bottom: 5px;">
                            <label><input type="checkbox" name="requirements[]" value="<?php echo htmlspecialchars($req['name']); ?>" <?php echo in_array($req['name'], $current_reqs) ? 'checked' : ''; ?>> <?php echo htmlspecialchars($req['name']); ?></label>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No requirements found. Please add some first.</p>
                <?php endif; ?>
            </div>
        </div>
        <div style="margin-top: 25px;">
            <button type="submit" style="padding: 10px 20px; background-color: blue; color: white; border: none; cursor: pointer;">Update</button>
            <a href="index.php" style="margin-left: 15px; color: gray; text-decoration: underline;">Cancel</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
