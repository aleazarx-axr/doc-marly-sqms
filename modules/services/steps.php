<?php
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/models/ServiceStep.php';

Session::requireRole('admin');

$db = new Database();
$conn = $db->getConnection();
$serviceStepModel = new ServiceStep($conn);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $service_id = $_POST['service_id'] ?? '';
    $steps = $_POST['steps'] ?? [];

    if (!empty($service_id)) {
        // Clear existing steps and insert new ones
        $stmt = $conn->prepare("DELETE FROM service_steps WHERE service_id = ?");
        $stmt->execute([$service_id]);

        $order = 1;
        foreach ($steps as $stepName) {
            $stepName = trim($stepName);
            if (!empty($stepName)) {
                $serviceStepModel->service_id = $service_id;
                $serviceStepModel->step_name = $stepName;
                $serviceStepModel->step_order = $order;
                $serviceStepModel->create();
                $order++;
            }
        }
        $return_to = $_REQUEST['return_to'] ?? 'office_services';
        header("Location: ../{$return_to}/index.php?tab=services&status=edited");
        exit();
    }
    $return_to = $_REQUEST['return_to'] ?? 'office_services';
        header("Location: ../{$return_to}/index.php?tab=services&status=error");
    exit();
}

$service_id = $_GET['id'] ?? '';
if (empty($service_id)) {
    header('Location: ../service_management/index.php?tab=services&');
    exit();
}

// Fetch the service name
$stmt = $conn->prepare("SELECT name FROM services WHERE id = ?");
$stmt->execute([$service_id]);
$serviceName = $stmt->fetchColumn();

if (!$serviceName) {
    header('Location: ../service_management/index.php?tab=services&');
    exit();
}

// Fetch steps
$stmtSteps = $conn->prepare("SELECT step_name FROM service_steps WHERE service_id = ? ORDER BY step_order ASC");
$stmtSteps->execute([$service_id]);
$currentSteps = [];
while ($row = $stmtSteps->fetch(PDO::FETCH_ASSOC)) {
    $currentSteps[] = $row['step_name'];
}

$pageTitle = 'Manage Process Steps - Admin Portal';
$activeMenu = 'services';

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar_admin.php';
?>

<div class="main-content">
    <h2>Process Steps: <?php echo htmlspecialchars($serviceName); ?></h2>
    <form action="steps.php" method="POST" style="max-width: 600px;">
        <input type="hidden" name="service_id" value="<?php echo htmlspecialchars($service_id); ?>">
        
        <div id="stepsContainer">
            <?php if (count($currentSteps) > 0): ?>
                <?php foreach ($currentSteps as $step): ?>
                    <div style="margin-bottom: 10px;">
                        <input type="text" name="steps[]" value="<?php echo htmlspecialchars($step); ?>" required style="padding:8px; width:70%; margin-right:10px;">
                        <button type="button" onclick="this.parentElement.remove()" style="color:red; background:none; border:none; cursor:pointer; font-weight:bold;">X</button>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="margin-bottom: 10px;">
                    <input type="text" name="steps[]" value="" required style="padding:8px; width:70%; margin-right:10px;">
                    <button type="button" onclick="this.parentElement.remove()" style="color:red; background:none; border:none; cursor:pointer; font-weight:bold;">X</button>
                </div>
            <?php endif; ?>
        </div>

        <button type="button" onclick="addStepRow()" style="cursor: pointer; color: green; background: none; border: none; text-decoration: underline; margin-top: 10px; margin-bottom: 25px; display: block;">+ Add Step</button>

        <div>
            <button type="submit" style="padding: 10px 20px; background-color: blue; color: white; border: none; cursor: pointer;">Save Steps</button>
            <a href="index.php" style="margin-left: 15px; color: gray; text-decoration: underline;">Cancel</a>
        </div>
    </form>
</div>

<script>
function addStepRow() {
    const container = document.getElementById("stepsContainer");
    const row = document.createElement("div");
    row.style.marginBottom = "10px";
    row.innerHTML = `<input type="text" name="steps[]" value="" required style="padding:8px; width:70%; margin-right:10px;">
                     <button type="button" onclick="this.parentElement.remove()" style="color:red; background:none; border:none; cursor:pointer; font-weight:bold;">X</button>`;
    container.appendChild(row);
}
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
