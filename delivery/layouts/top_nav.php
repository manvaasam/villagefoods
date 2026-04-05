<div class="db-navbar shadow-sm">
  <div class="db-navbar-left">
    <a href="logout.php" class="db-back-btn" title="Logout"><i data-lucide="log-out" style="width:18px"></i></a>
    <div>
      <div class="db-title"><?php echo $navTitle ?? 'My Dashboard'; ?></div>
      <div class="db-subtitle">Hey <?= htmlspecialchars($_SESSION['user_name'] ?? 'Partner') ?>! <span class="greeting-text">Ready to deliver?</span></div>
    </div>
  </div>
  <div class="online-toggle" onclick="toggleOnlineStatus(this)">
    <div class="online-dot"></div>
    <span class="online-label">Online Status</span>
  </div>
</div>
