<?php
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/models/Counter.php';

Session::requireRole('admin');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $db = new Database();
    $conn = $db->getConnection();
    $counter = new Counter($conn);

    $counter_name = $_POST['counter_name'] ?? '';
    $site_id = $_POST['site_id'] ?? '';
    $return_to = $_POST['return_to'] ?? 'office_services';

    if (!empty($counter_name) && !empty($site_id)) {
        $counter->name = $counter_name;
        $counter->site_id = $site_id;

        if ($counter->create()) {
            header("Location: /modules/{$return_to}/index.php?tab=counters&status=added");
            exit();
        }
    }
    
    header("Location: /modules/{$return_to}/index.php?tab=counters&status=error");
    exit();
}
