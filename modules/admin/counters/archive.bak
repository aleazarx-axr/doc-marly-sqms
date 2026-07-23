<?php
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/models/Counter.php';

Session::requireRole('admin');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $counter_id = $_POST['counter_id'] ?? '';
    if (!empty($counter_id)) {
        $db = new Database();
        $conn = $db->getConnection();
        $counter = new Counter($conn);
        
        $counter->id = $counter_id;
        $counter->archive();
    }
    $return_to = $_REQUEST['return_to'] ?? 'office_services';
        header("Location: ../{$return_to}/index.php?tab=counters&status=archived");
    exit();
}

header('Location: ../service_management/index.php?tab=counters&');
exit();
?>
