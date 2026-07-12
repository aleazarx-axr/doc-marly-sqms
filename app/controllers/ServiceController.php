<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../models/Service.php';
require_once __DIR__ . '/../models/Requirement.php';

// Basic protection - must be logged in as admin to modify services
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$action = $_GET['action'] ?? '';

$db = new Database();
$conn = $db->getConnection();
$service = new Service($conn);
$reqModel = new Requirement($conn);

if ($_SERVER["REQUEST_METHOD"] == "POST" && $action == 'add') {
    $service_name = $_POST['service_name'] ?? '';
    
    // Requirements might be an array if checkboxes are used
    $requirements_arr = $_POST['requirements'] ?? [];
    
    // Convert array to comma-separated string
    $requirements_str = implode(", ", $requirements_arr);

    // Save to database
    $service->name = $service_name;
    $service->requirements = $requirements_str;

    if ($service->create()) {
        // Redirect back with success
        header('Location: /admin/services?status=success');
        exit();
    } else {
        // Redirect back with error
        header('Location: /admin/services?status=error');
        exit();
    }
}

// Edit logic
if ($_SERVER["REQUEST_METHOD"] == "POST" && $action == 'edit') {
    $service_id = $_POST['service_id'] ?? '';
    $service_name = $_POST['service_name'] ?? '';
    $requirements_arr = $_POST['requirements'] ?? [];
    $requirements_str = implode(", ", $requirements_arr);

    if (!empty($service_id)) {
        $service->id = $service_id;
        $service->name = $service_name;
        $service->requirements = $requirements_str;

        if ($service->update()) {
            header('Location: /admin/services?status=edited');
            exit();
        }
    }
    header('Location: /admin/services?status=error');
    exit();
}

// Archive logic
if ($_SERVER["REQUEST_METHOD"] == "POST" && $action == 'archive') {
    $service_id = $_POST['service_id'] ?? '';
    if (!empty($service_id)) {
        $service->id = $service_id;
        $service->archive();
    }
    header('Location: /admin/services?status=archived');
    exit();
}

// Fallback redirect
header('Location: /admin/services');
exit();
?>
