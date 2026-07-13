    <!-- Sidebar -->
    <div class="sidebar">
        <h3>Menu</h3>
        <ul>
            <li><a href="/index.php" <?php if(isset($activeMenu) && $activeMenu == 'dashboard') echo 'style="font-weight:bold;"'; ?>>Dashboard</a></li>
            <li><a href="#">Queue Management</a></li>
        </ul>
        
        <ul style="margin-top: auto;">
            <li style="border-bottom: none;">
                <form action="/logout.php" method="post" style="margin: 0; padding: 10px 0;">
                    <button type="submit" style="background: none; border: none; padding: 0; cursor: pointer; color: #333; text-decoration: underline;">Logout</button>
                </form>
            </li>
        </ul>
    </div>
