<?php
require_once __DIR__ . '/auth/session.php';

// Require login to access this page
Session::requireLogin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doc Marly Smart Queueing Management System</title>
</head>
<body>
    <h1>Welcome, <?php echo htmlspecialchars(Session::get('username') ?? 'User'); ?>!</h1>
    <p>This is your protected dashboard.</p>
    
    <form action="logout.php" method="post">
        <button type="submit">Logout</button>
    </form>
</body>
</html>
