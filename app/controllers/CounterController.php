<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../models/Counter.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$action = $_GET['action'] ?? '';

$db = new Database();
$conn = $db->getConnection();
$counter = new Counter($conn);

if ($_SERVER["REQUEST_METHOD"] == "POST" && $action == 'add') {
    $counter->name = $_POST['counter_name'] ?? '';
    $counter->site_id = $_POST['site_id'] ?? '';

    if ($counter->create()) {
        header('Location: /admin/settings?tab=counters&status=success');
        exit();
    }
    header('Location: /admin/settings?tab=counters&status=error');
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && $action == 'edit') {
    $counter->id = $_POST['counter_id'] ?? '';
    $counter->name = $_POST['counter_name'] ?? '';
    $counter->site_id = $_POST['site_id'] ?? '';

    if (!empty($counter->id) && $counter->update()) {
        header('Location: /admin/settings?tab=counters&status=edited');
        exit();
    }
    header('Location: /admin/settings?tab=counters&status=error');
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && $action == 'archive') {
    $counter->id = $_POST['counter_id'] ?? '';
    if (!empty($counter->id) && $counter->archive()) {
        header('Location: /admin/settings?tab=counters&status=archived');
        exit();
    }
    header('Location: /admin/settings?tab=counters&status=error');
    exit();
}

header('Location: /admin/settings?tab=counters');
exit();
?>
