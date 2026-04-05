<aside class="admin-sidebar" id="adminSidebar">
    <a href="dashboard" class="admin-logo">
        <div class="admin-logo-icon" style="background:var(--primary)"><i data-lucide="store"></i></div>
        <div>
            <div class="admin-logo-text">Vendor Cabinet</div>
            <div class="admin-logo-sub"><?php echo $_SESSION['shop_name']; ?></div>
        </div>
    </a>

    <nav class="admin-nav">
        <div class="admin-nav-section">
            <div class="admin-nav-label">Overview</div>
            <a href="dashboard" class="admin-nav-item <?php echo $activePage === 'dashboard' ? 'active' : ''; ?>">
                <span class="nav-icon"><i data-lucide="layout-dashboard"></i></span> Dashboard
            </a>
            <a href="orders" class="admin-nav-item <?php echo $activePage === 'orders' ? 'active' : ''; ?>">
                <span class="nav-icon"><i data-lucide="shopping-bag"></i></span> Orders
                <span class="nav-badge" id="vendorNewOrdersBadge" style="display:none">0</span>
            </a>
            <a href="products" class="admin-nav-item <?php echo $activePage === 'products' ? 'active' : ''; ?>">
                <span class="nav-icon"><i data-lucide="box"></i></span> Menu & Stock
            </a>
        </div>

        <div class="admin-nav-section">
            <div class="admin-nav-label">Store</div>
            <a href="settings" class="admin-nav-item <?php echo $activePage === 'settings' ? 'active' : ''; ?>">
                <span class="nav-icon"><i data-lucide="settings"></i></span> Shop Settings
            </a>
            <a href="../logout" class="admin-nav-item" style="color:#ef4444">
                <span class="nav-icon"><i data-lucide="log-out"></i></span> Logout
            </a>
        </div>
    </nav>

    <div class="admin-user-info">
        <div class="admin-user-avatar"><?php echo strtoupper(substr($_SESSION['user_name'] ?? 'V', 0, 1)); ?></div>
        <div>
            <div class="admin-user-name"><?php echo $_SESSION['user_name']; ?></div>
            <div class="admin-user-role"><?php echo $_SESSION['user_email']; ?></div>
        </div>
    </div>
</aside>
