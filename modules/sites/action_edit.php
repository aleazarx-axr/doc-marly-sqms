<?php
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/models/Site.php';

Session::requireRole('admin');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $db = new Database();
    $conn = $db->getConnection();
    $site = new Site($conn);

    $site_id = $_POST['site_id'] ?? '';
    $site_name = $_POST['site_name'] ?? '';
    $site_type = $_POST['site_type'] ?? 'offsite';
    $return_to = $_POST['return_to'] ?? 'field_services';

    if (!empty($site_id) && !empty($site_name)) {
        $site->id = $site_id;
        $site->name = $site_name;
        $site->type = $site_type;

        if ($site->update()) {
            header("Location: /modules/{$return_to}/index.php?tab=settings&status=edited");
            exit();
        }
    }
    header("Location: /modules/{$return_to}/index.php?tab=settings&status=error");
    exit();
}
