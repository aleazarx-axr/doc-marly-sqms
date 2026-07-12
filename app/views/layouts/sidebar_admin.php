    <!-- Sidebar -->
    <div class="sidebar">
        <h3>Menu</h3>
        <ul>
            <li><a href="/admin/dashboard" <?php if(isset($activeMenu) && $activeMenu == 'dashboard') echo 'style="font-weight:bold;"'; ?>>Dashboard</a></li>
            <li><a href="/admin/settings" <?php if(isset($activeMenu) && $activeMenu == 'settings') echo 'style="font-weight:bold;"'; ?>>Service Management</a></li>
            <li><a href="#">Records</a></li>
            <li><a href="#">User management</a></li>
            <li><a href="#">Devices</a></li>
            <li><a href="#">Settings</a></li>
        </ul>
        
        <h3>External Display</h3>
        <ul>
            <li><a href="#">Live Display</a></li>
        </ul>
        
        <h3>System</h3>
        <ul>
            <li>
                <form action="/logout" method="post" style="margin: 0; padding: 10px 0;">
                    <button type="submit" style="background: none; border: none; padding: 0; cursor: pointer; color: #333; text-decoration: underline;">Logout</button>
                </form>
            </li>
        </ul>
    </div>
