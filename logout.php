<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/models/User.php';

Session::start();

if (Session::isLoggedIn()) {
    $db = new Database();
    $conn = $db->getConnection();
    $user = new User($conn);
    $user->id = Session::get('user_id');
    $user->username = Session::get('username');
    $user->logAuthEvent('logout');
    
    // Release any counters locked by this user
    require_once __DIR__ . '/includes/models/Counter.php';
    $counter = new Counter($conn);
    $counter->unlockCounter($user->id);
}

Session::destroy();

header("Location: /login");
exit();
