<?php
require_once __DIR__ . '/includes/functions.php';

Session::start();
Session::destroy();

header("Location: login.php");
exit();
?>
