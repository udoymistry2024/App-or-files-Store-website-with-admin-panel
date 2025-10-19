<?php
// This file assumes a session has been started
$current_page = basename($_SERVER['PHP_SELF']);
?>
<aside class="sidebar">
    <h3>Admin Panel</h3>
    <nav>
        <a href="index.php" class="<?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">Dashboard</a>
        <a href="add_app.php" class="<?php echo ($current_page == 'add_app.php') ? 'active' : ''; ?>">Add New App</a>
        <a href="manage_apps.php" class="<?php echo ($current_page == 'manage_apps.php') ? 'active' : ''; ?>">Manage Apps</a>
        <a href="manage_categories.php" class="<?php echo ($current_page == 'manage_categories.php') ? 'active' : ''; ?>">Manage Categories</a>
        
        <?php if (isset($_SESSION['admin_role']) && $_SESSION['admin_role'] === 'owner'): ?>
            <a href="manage_users.php" class="<?php echo ($current_page == 'manage_users.php') ? 'active' : ''; ?>">Manage Users</a>
            <a href="settings.php" class="<?php echo ($current_page == 'settings.php') ? 'active' : ''; ?>">Site Settings</a>
            <!-- নতুন মেনু যোগ করা হয়েছে -->
            <a href="footer_settings.php" class="<?php echo ($current_page == 'footer_settings.php') ? 'active' : ''; ?>">Footer & Contact</a>
        <?php endif; ?>
        
        <a href="logout.php">Logout</a>
    </nav>
</aside>