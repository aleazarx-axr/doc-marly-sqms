<?php
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/models/Service.php';

Session::requireRole('admin');

header('Content-Type: application/json');

$site_id = $_GET['site_id'] ?? '';

if (empty($site_id)) {
    echo json_encode(['success' => false, 'message' => 'Missing site ID']);
    exit();
}

$db = new Database();
$conn = $db->getConnection();
$serviceModel = new Service($conn);

// Fetch all active services
$stmtServices = $serviceModel->read();
$allServices = [];
while ($row = $stmtServices->fetch(PDO::FETCH_ASSOC)) {
    $allServices[] = [
        'id' => $row['id'],
        'name' => $row['name']
    ];
}

// Fetch currently assigned services for this site
$stmtSiteSrv = $conn->prepare("SELECT service_id, counter_limit FROM site_services WHERE site_id = ?");
$stmtSiteSrv->execute([$site_id]);
$assignedServices = [];
while ($row = $stmtSiteSrv->fetch(PDO::FETCH_ASSOC)) {
    $assignedServices[$row['service_id']] = $row['counter_limit'];
}

echo json_encode([
    'success' => true,
    'allServices' => $allServices,
    'assignedServices' => $assignedServices
]);
