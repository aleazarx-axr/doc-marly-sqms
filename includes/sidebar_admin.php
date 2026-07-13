    <!-- Sidebar -->
    <div class="sidebar">
        <h3>Menu</h3>
        <ul>
            <li><a href="/index.php" <?php if(isset($activeMenu) && $activeMenu == 'dashboard') echo 'style="font-weight:bold;"'; ?>>Dashboard</a></li>
            <li>
                <?php $isServiceMenu = isset($activeMenu) && in_array($activeMenu, ['office_services', 'field_services']); ?>
                <details <?php echo $isServiceMenu ? 'open' : ''; ?>>
                    <summary style="font-weight:bold; margin-top: 10px; margin-bottom: 5px; color: #333; cursor: pointer; outline: none; list-style-position: inside;">Service Management</summary>
                    <ul style="margin-left: 15px; margin-bottom: 10px;">
                        <li><a href="/modules/office_services/index.php" <?php if(isset($activeMenu) && $activeMenu == 'office_services') echo 'style="font-weight:bold;"'; ?>>Office Services</a></li>
                        <li><a href="/modules/field_services/index.php" <?php if(isset($activeMenu) && $activeMenu == 'field_services') echo 'style="font-weight:bold;"'; ?>>Field Services</a></li>
                    </ul>
                </details>
            </li>
            <li><a href="#">Records</a></li>
            <li><a href="/modules/users/index.php" <?php if(isset($activeMenu) && $activeMenu == 'users') echo 'style="font-weight:bold;"'; ?>>User Management</a></li>
            <li><a href="#">Devices</a></li>
            <li><a href="#">Settings</a></li>
        </ul>
        
        <h3>External Display</h3>
        <ul>
            <li><a href="#">Live Display</a></li>
        </ul>
        
        <ul style="margin-top: auto;">
            <li style="border-bottom: none;">
                <form action="/logout.php" method="post" style="margin: 0; padding: 10px 0;">
                    <button type="submit" style="background: none; border: none; padding: 0; cursor: pointer; color: #333; text-decoration: underline;">Logout</button>
                </form>
            </li>
        </ul>
    </div>
