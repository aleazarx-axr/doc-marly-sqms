<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../models/CounterService.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$action = $_GET['action'] ?? '';

$db = new Database();
$conn = $db->getConnection();
$assignmentModel = new CounterService($conn);

if ($_SERVER["REQUEST_METHOD"] == "POST" && $action == 'save') {
    $counter_id = $_POST['counter_id'] ?? '';
    // Service IDs array from the checkboxes
    $service_ids_array = $_POST['services'] ?? [];

    if (!empty($counter_id)) {
        if ($assignmentModel->saveAssignments($counter_id, $service_ids_array)) {
            header('Location: /admin/settings?tab=counters&status=assignments_saved');
            exit();
        }
    }
    header('Location: /admin/settings?tab=counters&status=error');
    exit();
}

// API endpoint to get assigned services via AJAX
if ($_SERVER["REQUEST_METHOD"] == "GET" && $action == 'get') {
    $counter_id = $_GET['counter_id'] ?? '';
    if (!empty($counter_id)) {
        $assigned = $assignmentModel->getAssignedServices($counter_id);
        header('Content-Type: application/json');
        echo json_encode(['assigned_services' => $assigned]);
        exit();
    }
    http_response_code(400);
    exit();
}

header('Location: /admin/settings?tab=counters');
exit();
?>
