  <aside class="admin-sidebar" id="adminSidebar">
    <a href="../index" class="admin-logo">
      <div class="admin-logo-icon"><i data-lucide="leaf"></i></div>
      <div>
        <div class="admin-logo-text">VillageFoods</div>
        <div class="admin-logo-sub">Admin Panel v2.0</div>
      </div>
    </a>

    <nav class="admin-nav">
      <div class="admin-nav-section">
        <div class="admin-nav-label">Overview</div>
        <a href="dashboard" class="admin-nav-item <?php echo $activePage == 'dashboard' ? 'active' : ''; ?>">
          <span class="nav-icon"><i data-lucide="layout-dashboard"></i></span> Dashboard
        </a>
        <a href="analytics.php" class="admin-nav-item <?php echo $activePage == 'analytics' ? 'active' : ''; ?>">
          <span class="nav-icon"><i data-lucide="bar-chart-2"></i></span> Analytics
        </a>
        <a href="orders" class="admin-nav-item <?php echo $activePage == 'orders' ? 'active' : ''; ?>">
          <span class="nav-icon"><i data-lucide="package"></i></span> Orders
          <span class="nav-badge" id="sidebarOrderBadge">0</span>
          <span class="nav-badge" id="sidebarRefundBadge" style="background:#ef4444; color:white; margin-left:5px; display:none" title="Pending Refunds">!</span>
        </a>
        <a href="rapid-orders" class="admin-nav-item <?php echo $activePage == 'rapid-orders' ? 'active' : ''; ?>">
          <span class="nav-icon"><i data-lucide="bike"></i></span> Rapid Pickup
          <span class="nav-badge rapid" id="sidebarRapidBadge">0</span>
        </a>
      </div>

      <div class="admin-nav-section">
        <div class="admin-nav-label">Manage</div>
        <a href="shops" class="admin-nav-item <?php echo $activePage == 'shops' ? 'active' : ''; ?>">
          <span class="nav-icon"><i data-lucide="store"></i></span> Shops
        </a>
        <a href="products" class="admin-nav-item <?php echo $activePage == 'products' ? 'active' : ''; ?>">
          <span class="nav-icon"><i data-lucide="leaf"></i></span> Products
        </a>
        <a href="categories" class="admin-nav-item <?php echo $activePage == 'categories' ? 'active' : ''; ?>">
          <span class="nav-icon"><i data-lucide="folder"></i></span> Categories
        </a>
        <a href="customers" class="admin-nav-item <?php echo $activePage == 'customers' ? 'active' : ''; ?>">
          <span class="nav-icon"><i data-lucide="users"></i></span> Customers
        </a>
        <a href="delivery-boys" class="admin-nav-item <?php echo $activePage == 'delivery' ? 'active' : ''; ?>">
          <span class="nav-icon"><i data-lucide="bike"></i></span> Delivery Boys
          <span class="nav-badge" id="sidebarPartnerBadge" style="background:#f59e0b; color:white; display:none">0</span>
        </a>
        <a href="withdrawals" class="admin-nav-item <?php echo $activePage == 'withdrawals' ? 'active' : ''; ?>">
          <span class="nav-icon"><i data-lucide="banknote"></i></span> Payments
          <span class="nav-badge" id="sidebarWithdrawalBadge" style="display:none">0</span>
        </a>
      </div>

      <div class="admin-nav-section">
        <div class="admin-nav-label">System</div>
        <a href="settings.php" class="admin-nav-item <?php echo $activePage == 'settings' ? 'active' : ''; ?>">
          <span class="nav-icon"><i data-lucide="settings"></i></span> Settings
        </a>
        <a href="dashboard" class="admin-nav-item">
          <span class="nav-icon"><i data-lucide="bell"></i></span> Notifications
          <span class="nav-badge" id="sidebarNotifBadge" style="display:none">0</span>
        </a>
        <a href="../logout" class="admin-nav-item" style="color:#ef4444">
          <span class="nav-icon"><i data-lucide="log-out"></i></span> Logout
        </a>
      </div>
    </nav>

    <div class="admin-user-info">
      <div class="admin-user-avatar"><?php echo strtoupper(substr($_SESSION['user_name'] ?? 'A', 0, 1)); ?></div>
      <div>
        <div class="admin-user-name"><?php echo $_SESSION['user_name'] ?? 'Admin User'; ?></div>
        <div class="admin-user-role"><?php echo $_SESSION['user_email'] ?? 'admin@villagefoods.in'; ?></div>
      </div>
    </div>
  </aside>
