<?php
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/models/Counter.php';

Session::requireRole('admin');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $db = new Database();
    $conn = $db->getConnection();
    $counter = new Counter($conn);

    $counter_id = $_POST['counter_id'] ?? '';
    $counter_name = $_POST['counter_name'] ?? '';
    $site_id = $_POST['site_id'] ?? '';
    $return_to = $_POST['return_to'] ?? 'office_services';

    if (!empty($counter_id) && !empty($counter_name) && !empty($site_id)) {
        $counter->id = $counter_id;
        $counter->name = $counter_name;
        $counter->site_id = $site_id;

        if ($counter->update()) {
            header("Location: /modules/{$return_to}/index.php?tab=counters&status=edited");
            exit();
        }
    }
    header("Location: /modules/{$return_to}/index.php?tab=counters&status=error");
    exit();
}
