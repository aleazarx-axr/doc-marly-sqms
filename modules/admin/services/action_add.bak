<?php
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/models/Service.php';

Session::requireRole('admin');

$db = new Database();
$conn = $db->getConnection();
$service = new Service($conn);

$return_to = $_REQUEST['return_to'] ?? 'office_services';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $service_name = $_POST['service_name'] ?? '';
    // Filter out empty requirements
    $requirements_arr = array_filter($_POST['requirements'] ?? [], function($val) {
        return trim($val) !== '';
    });
    $requirements_str = implode(", ", $requirements_arr);

    $service->name = $service_name;
    $service->requirements = $requirements_str;

    if ($service->create()) {
        header("Location: ../{$return_to}/index.php?tab=services&status=added");
        exit();
    } else {
        header("Location: ../{$return_to}/index.php?tab=services&status=error");
        exit();
    }
} else {
    header("Location: ../{$return_to}/index.php?tab=services");
    exit();
}
