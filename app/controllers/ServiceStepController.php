<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../models/ServiceStep.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$action = $_GET['action'] ?? '';

$db = new Database();
$conn = $db->getConnection();
$stepModel = new ServiceStep($conn);

if ($_SERVER["REQUEST_METHOD"] == "POST" && $action == 'save') {
    $service_id = $_POST['service_id'] ?? '';
    // Steps array sent from the dynamic UI
    $steps_array = $_POST['steps'] ?? [];
    
    // Filter out empty steps
    $steps_array = array_filter($steps_array, function($value) {
        return !empty(trim($value));
    });

    if (!empty($service_id)) {
        if ($stepModel->saveSteps($service_id, $steps_array)) {
            header('Location: /admin/services?status=steps_saved');
            exit();
        }
    }
    header('Location: /admin/services?status=error');
    exit();
}

// API endpoint to get steps via AJAX
if ($_SERVER["REQUEST_METHOD"] == "GET" && $action == 'get') {
    $service_id = $_GET['service_id'] ?? '';
    if (!empty($service_id)) {
        $steps = $stepModel->getStepsByService($service_id);
        header('Content-Type: application/json');
        echo json_encode(['steps' => $steps]);
        exit();
    }
    http_response_code(400);
    exit();
}

header('Location: /admin/services');
exit();
?>
