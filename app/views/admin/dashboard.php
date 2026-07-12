<?php
require_once __DIR__ . '/../../../auth/session.php';

// Require 'admin' role to access this page
Session::requireRole('admin');
?>
<?php
$pageTitle = 'Admin Dashboard - Doc Marly SQMS';
$activeMenu = 'dashboard';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar_admin.php';
?>

    <!-- Main Content -->
    <div class="main-content">
        <div class="header-section">
            <div>
                <h1>Admin Portal</h1>
                <h2>Welcome, <?php echo htmlspecialchars(Session::get('username') ?? 'Admin'); ?>!</h2>
            </div>
            <div>
                <strong>Date and Time:</strong> <br>
                <?php echo date('Y-m-d H:i:s'); ?>
            </div>
        </div>

        <!-- Top Cards -->
        <div class="dashboard-grid">
            <div class="card">
                <h3>No. of Services</h3>
                <div class="value">0</div>
                <a href="services.php">View Services Page</a>
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
                <a href="services.php">Open Services Page</a>
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
                    <tr><td colspan="3">No active office queue.</td></tr>
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
                    <tr><td colspan="3">No active event queue.</td></tr>
                </tbody>
            </table>
        </div>
    </div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
