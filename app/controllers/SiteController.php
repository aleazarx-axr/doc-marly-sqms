<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../models/Site.php';

// Basic protection - must be logged in as admin to modify sites
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$action = $_GET['action'] ?? '';

$db = new Database();
$conn = $db->getConnection();
$site = new Site($conn);

if ($_SERVER["REQUEST_METHOD"] == "POST" && $action == 'add') {
    $site_name = $_POST['site_name'] ?? '';
    $site_type = $_POST['site_type'] ?? 'offsite';

    // Save to database
    $site->name = $site_name;
    $site->type = $site_type;

    if ($site->create()) {
        header('Location: /admin/sites?status=success');
        exit();
    } else {
        header('Location: /admin/sites?status=error');
        exit();
    }
}

// Edit logic
if ($_SERVER["REQUEST_METHOD"] == "POST" && $action == 'edit') {
    $site_id = $_POST['site_id'] ?? '';
    $site_name = $_POST['site_name'] ?? '';
    $site_type = $_POST['site_type'] ?? 'offsite';

    if (!empty($site_id)) {
        $site->id = $site_id;
        $site->name = $site_name;
        $site->type = $site_type;

        if ($site->update()) {
            header('Location: /admin/sites?status=edited');
            exit();
        }
    }
    header('Location: /admin/sites?status=error');
    exit();
}

// Archive logic
if ($_SERVER["REQUEST_METHOD"] == "POST" && $action == 'archive') {
    $site_id = $_POST['site_id'] ?? '';
    if (!empty($site_id)) {
        $site->id = $site_id;
        $site->archive();
    }
    header('Location: /admin/sites?status=archived');
    exit();
}

// Fallback redirect
header('Location: /admin/sites');
exit();
?>
