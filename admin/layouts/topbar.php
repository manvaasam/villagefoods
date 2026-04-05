    <div class="admin-topbar">
      <div style="display:flex;align-items:center;gap:12px">
        <button class="admin-mobile-toggle" onclick="AdminPanel.toggleSidebar()" style="background:none;border:none;cursor:pointer;color:var(--text);"><i data-lucide="menu"></i></button>
        <div>
          <div class="admin-topbar-title" id="adminPageTitle"><?php echo $pageTitle ?? 'Dashboard'; ?></div>
          <div class="admin-topbar-date"><?php echo date('l, F j, Y'); ?> · Welcome back, Admin</div>
        </div>
      </div>
      <div class="admin-topbar-right">
        <div class="admin-notif-btn" onclick="Notifications.toggle()">
          <i data-lucide="bell"></i><div class="admin-notif-dot" id="notifDot" style="display:none"></div>
        </div>
        
        <!-- Notification Dropdown -->
        <div class="notif-dropdown" id="notifDropdown">
          <div class="notif-header">
            <span>Notifications</span>
            <span class="notif-count-badge" id="notifCountBadge">0</span>
          </div>
          <div class="notif-list" id="notifList">
            <div class="notif-empty">No new notifications</div>
          </div>
          <div class="notif-footer">
            <a href="orders.php">View all orders</a>
          </div>
        </div>

        <div class="admin-avatar-btn" onclick="Toast.show('Profile opened','success')">A</div>
      </div>
    </div>
