    <!-- Sidebar -->
    <div class="sidebar">
        <h3>Menu</h3>
        <ul>
            <li><a href="/user/dashboard" <?php if(isset($activeMenu) && $activeMenu == 'dashboard') echo 'style="font-weight:bold;"'; ?>>Dashboard</a></li>
            <li><a href="#">Queue Management</a></li>
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
