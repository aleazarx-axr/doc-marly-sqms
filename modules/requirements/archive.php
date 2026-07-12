<?php
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/models/Requirement.php';

Session::requireRole('admin');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $req_id = $_POST['req_id'] ?? '';
    if (!empty($req_id)) {
        $db = new Database();
        $conn = $db->getConnection();
        $reqModel = new Requirement($conn);
        
        $reqModel->id = $req_id;
        $reqModel->archive();
    }
    header('Location: index.php?status=archived');
    exit();
}

header('Location: index.php');
exit();
?>
