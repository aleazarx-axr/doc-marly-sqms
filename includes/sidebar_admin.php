    <!-- Sidebar -->
    <div class="sidebar">
        <h3>Menu</h3>
        <ul>
            <li><a href="/index.php" <?php if(isset($activeMenu) && $activeMenu == 'dashboard') echo 'style="font-weight:bold;"'; ?>>Dashboard</a></li>
            <li><a href="/modules/services/index.php" <?php if(isset($activeMenu) && $activeMenu == 'services') echo 'style="font-weight:bold;"'; ?>>Services</a></li>
            <li><a href="/modules/requirements/index.php" <?php if(isset($activeMenu) && $activeMenu == 'requirements') echo 'style="font-weight:bold;"'; ?>>Requirements</a></li>
            <li><a href="/modules/sites/index.php" <?php if(isset($activeMenu) && $activeMenu == 'sites') echo 'style="font-weight:bold;"'; ?>>Sites</a></li>
            <li><a href="/modules/counters/index.php" <?php if(isset($activeMenu) && $activeMenu == 'counters') echo 'style="font-weight:bold;"'; ?>>Counters</a></li>
            <li><a href="/modules/service_assignments/index.php" <?php if(isset($activeMenu) && $activeMenu == 'assignments') echo 'style="font-weight:bold;"'; ?>>Service Assignments</a></li>
            <li><a href="#">Records</a></li>
            <li><a href="#">User management</a></li>
            <li><a href="#">Devices</a></li>
            <li><a href="#">Settings</a></li>
        </ul>
        
        <h3>External Display</h3>
        <ul>
            <li><a href="#">Live Display</a></li>
        </ul>
        
        <ul>
            <li>
                <form action="/logout.php" method="post" style="margin: 0; padding: 10px 0;">
                    <button type="submit" style="background: none; border: none; padding: 0; cursor: pointer; color: #333; text-decoration: underline;">Logout</button>
                </form>
            </li>
        </ul>
    </div>
