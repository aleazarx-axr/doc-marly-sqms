<?php
require_once __DIR__ . '/../../../includes/functions.php';
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../includes/models/CounterService.php';

Session::requireRole('admin');

$db = new Database();
$conn = $db->getConnection();
$counterServiceModel = new CounterService($conn);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $counter_id = $_POST['counter_id'] ?? '';
    $services = $_POST['services'] ?? [];

    if (!empty($counter_id)) {
        // Clear existing assignments and insert new ones
        $stmt = $conn->prepare("DELETE FROM counter_services WHERE counter_id = ?");
        $stmt->execute([$counter_id]);

        foreach ($services as $service_id) {
            $counterServiceModel->counter_id = $counter_id;
            $counterServiceModel->service_id = $service_id;
            $counterServiceModel->create();
        }
        $return_to = $_REQUEST['return_to'] ?? 'office_services';
        header("Location: ../{$return_to}/index.php?tab=counters&status=edited");
        exit();
    }
    $return_to = $_REQUEST['return_to'] ?? 'office_services';
    header("Location: ../{$return_to}/index.php?tab=counters&status=error");
    exit();
} else {
    $return_to = $_REQUEST['return_to'] ?? 'office_services';
    header("Location: ../{$return_to}/index.php?tab=counters");
    exit();
}
