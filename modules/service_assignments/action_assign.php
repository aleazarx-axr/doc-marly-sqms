<?php
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/models/SiteService.php';

Session::requireRole('admin');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $site_id = $_POST['site_id'] ?? '';
    $services = $_POST['services'] ?? [];
    $counter_limits = $_POST['counter_limits'] ?? [];
    $return_to = $_POST['return_to'] ?? 'field_services';

    if (!empty($site_id)) {
        $db = new Database();
        $conn = $db->getConnection();
        $siteServiceModel = new SiteService($conn);

        // Save assignments using the model
        $siteServiceModel->saveAssignments($site_id, $services, $counter_limits);
        
        header("Location: /modules/{$return_to}/index.php?tab=settings&status=edited");
        exit();
    }
    
    header("Location: /modules/{$return_to}/index.php?tab=settings&status=error");
    exit();
}
