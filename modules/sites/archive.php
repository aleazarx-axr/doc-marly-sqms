<?php
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/models/Site.php';

Session::requireRole('admin');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $site_id = $_POST['site_id'] ?? '';
    if (!empty($site_id)) {
        $db = new Database();
        $conn = $db->getConnection();
        $site = new Site($conn);
        
        $site->id = $site_id;
        $site->archive();
    }
    $return_to = $_REQUEST['return_to'] ?? 'office_services';
        header("Location: ../{$return_to}/index.php?tab=sites&status=archived");
    exit();
}

header('Location: ../service_management/index.php?tab=sites&');
exit();
?>
