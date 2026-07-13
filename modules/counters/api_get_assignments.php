<?php
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/database.php';

Session::requireRole('admin');

header('Content-Type: application/json');

$counter_id = $_GET['counter_id'] ?? '';
$site_id = $_GET['site_id'] ?? '';

if (empty($counter_id) || empty($site_id)) {
    echo json_encode(['error' => 'Missing counter_id or site_id']);
    exit();
}

$db = new Database();
$conn = $db->getConnection();

// Fetch services assigned to the site
$stmtSiteSrv = $conn->prepare("
    SELECT s.id, s.name 
    FROM site_services ss 
    JOIN services s ON ss.service_id = s.id 
    WHERE ss.site_id = ? AND s.is_archived = 0
");
$stmtSiteSrv->execute([$site_id]);
$siteServices = [];
while ($row = $stmtSiteSrv->fetch(PDO::FETCH_ASSOC)) {
    $siteServices[] = $row;
}

// Fetch currently assigned services for this counter
$stmtCounterSrv = $conn->prepare("SELECT service_id FROM counter_services WHERE counter_id = ?");
$stmtCounterSrv->execute([$counter_id]);
$assignedServices = [];
while ($row = $stmtCounterSrv->fetch(PDO::FETCH_ASSOC)) {
    $assignedServices[] = $row['service_id'];
}

echo json_encode([
    'site_services' => $siteServices,
    'assigned_services' => $assignedServices
]);
