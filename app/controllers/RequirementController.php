<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../models/Requirement.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$action = $_GET['action'] ?? '';

$db = new Database();
$conn = $db->getConnection();
$reqModel = new Requirement($conn);

if ($_SERVER["REQUEST_METHOD"] == "POST" && $action == 'add') {
    $req_name = $_POST['name'] ?? '';
    if (!empty($req_name)) {
        $reqModel->name = trim($req_name);
        if ($reqModel->create()) {
            header('Location: /admin/requirements?status=added');
            exit();
        }
    }
    header('Location: /admin/requirements?status=error');
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && $action == 'archive') {
    $req_id = $_POST['req_id'] ?? '';
    if (!empty($req_id)) {
        $reqModel->id = $req_id;
        if ($reqModel->archive()) {
            header('Location: /admin/requirements?status=archived');
            exit();
        }
    }
    header('Location: /admin/requirements?status=error');
    exit();
}

header('Location: /admin/requirements');
exit();
?>
