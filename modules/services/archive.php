<?php
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/models/Service.php';

Session::requireRole('admin');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $service_id = $_POST['service_id'] ?? '';
    if (!empty($service_id)) {
        $db = new Database();
        $conn = $db->getConnection();
        $service = new Service($conn);
        
        $service->id = $service_id;
        $service->archive();
    }
    header('Location: index.php?status=archived');
    exit();
}

header('Location: index.php');
exit();
?>
