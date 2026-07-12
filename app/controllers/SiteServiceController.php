<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../models/SiteService.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$action = $_GET['action'] ?? '';

$db = new Database();
$conn = $db->getConnection();
$assignmentModel = new SiteService($conn);

if ($_SERVER["REQUEST_METHOD"] == "POST" && $action == 'save') {
    $site_id = $_POST['site_id'] ?? '';
    // Service IDs array from the checkboxes
    $service_ids_array = $_POST['services'] ?? [];

    if (!empty($site_id)) {
        if ($assignmentModel->saveAssignments($site_id, $service_ids_array)) {
            header('Location: /admin/service_assignments?status=assignments_saved');
            exit();
        }
    }
    header('Location: /admin/service_assignments?status=error');
    exit();
}

// API endpoint to get assigned services via AJAX
if ($_SERVER["REQUEST_METHOD"] == "GET" && $action == 'get') {
    $site_id = $_GET['site_id'] ?? '';
    if (!empty($site_id)) {
        $assigned = $assignmentModel->getAssignedServices($site_id);
        header('Content-Type: application/json');
        echo json_encode(['assigned_services' => $assigned]);
        exit();
    }
    http_response_code(400);
    exit();
}

header('Location: /admin/service_assignments');
exit();
?>
