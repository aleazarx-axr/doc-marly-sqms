<?php
require_once __DIR__ . '/includes/functions.php';

Session::requireLogin();
$role = Session::get('role');

$pageTitle = ($role === 'admin') ? 'Admin Dashboard - Doc Marly SQMS' : 'Staff Portal - Doc Marly SQMS';
$activeMenu = 'dashboard';

require_once __DIR__ . '/includes/header.php';

if ($role === 'admin') {
    require_once __DIR__ . '/includes/sidebar_admin.php';
} else {
    require_once __DIR__ . '/includes/sidebar_user.php';
}
?>


<?php require_once __DIR__ . '/includes/footer.php'; ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
</head>

<body>

    <!-- Main Content -->
    <div class="main-content">
        <div class="header-section">
            <div>
                <h1><?php echo ($role === 'admin') ? 'Admin Portal' : 'Staff Portal'; ?></h1>
                <h2>Welcome, <?php echo htmlspecialchars(Session::get('username') ?? 'User'); ?>!</h2>
            </div>
            <div>
                <strong>Date and Time:</strong> <br>
                <?php echo date('Y-m-d H:i:s'); ?>
            </div>
        </div>

        <?php if ($role === 'admin'): ?>
            <!-- Top Cards for Admin -->
            <div class="dashboard-grid">
                <div class="card">
                    <h3>No. of Services</h3>
                    <div class="value">0</div>
                    <a href="/modules/services/index.php">View Services Page</a>
                </div>
                <div class="card">
                    <h3>No. of Programs</h3>
                    <div class="value">0</div>
                    <a href="#">View Programs Page</a>
                </div>
                <div class="card">
                    <h3>No. of Staffs</h3>
                    <div class="value">0</div>
                    <a href="#">View Users Page</a>
                </div>
                <div class="card" style="display: flex; align-items: center; justify-content: center; flex-direction: column;">
                    <h3>Calendar</h3>
                    <a href="#">📅 View Events</a>
                </div>
            </div>

            <!-- Middle Sections -->
            <div class="dashboard-grid">
                <div class="card">
                    <h3>Available Services</h3>
                    <ul>
                        <li>No services available yet.</li>
                    </ul>
                    <a href="/modules/services/index.php">Open Services Page</a>
                </div>
                <div class="card">
                    <h3>Upcoming Events</h3>
                    <ul>
                        <li>No upcoming events.</li>
                    </ul>
                    <a href="#">Add/Update Events</a>
                </div>
            </div>

            <!-- Bottom Section: Queue Monitoring -->
            <div class="card">
                <h3>Queue Monitoring</h3>

                <h4>Office Queueing</h4>
                <table>
                    <thead>
                        <tr>
                            <th>Queue Number</th>
                            <th>Service</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="3">No active office queue.</td>
                        </tr>
                    </tbody>
                </table>

                <h4 style="margin-top: 20px;">Event Queueing</h4>
                <table>
                    <thead>
                        <tr>
                            <th>Queue Number</th>
                            <th>Event</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="3">No active event queue.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p>This is the protected staff queue management area.</p>
        <?php endif; ?>
    </div>
</body>

</html>