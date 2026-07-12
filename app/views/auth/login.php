<?php
require_once __DIR__ . '/../../../auth/session.php';
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../models/User.php';

Session::start();

// Redirect to dashboard if already logged in
if (Session::isLoggedIn()) {
    $role = Session::get('role');
    if ($role === 'admin') {
        header("Location: /admin/dashboard");
        exit();
    } else if ($role === 'staff') {
        header("Location: /user/dashboard");
        exit();
    }
    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doc Marly Smart Queueing Management System</title>
</head>
<body>
    <?php if ($error = Session::get('error')): ?>
        <div style="color: red; margin-bottom: 10px;">
            <?php 
                echo htmlspecialchars($error); 
                Session::set('error', null); // Clear the error after displaying
            ?>
        </div>
    <?php endif; ?>

    <form action="/login" method="post">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username">
        <br>
        <label for="password">Password:</label>
        <input type="password" id="password" name="password">
        <br>
        <input type="submit" value="Login">
    </form>
</body>
</html>