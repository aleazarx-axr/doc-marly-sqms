<?php
require_once __DIR__ . '/includes/functions.php';

Session::requireLogin();
$role = Session::get('role');

if ($role === 'admin') {
    header("Location: /admin/dashboard");
    exit();
} elseif ($role === 'information_staff') {
    header("Location: /information_staff/dashboard");
    exit();
} else {
    // Service Staff (or default staff)
    header("Location: /service_staff/dashboard");
    exit();
}
?>
