<?php
// Bottom Navigation Layout for Delivery Dashboard
$current_page = basename($_SERVER['PHP_SELF']);
?>
<div class="db-bottom-nav">
  <a href="dashboard" class="db-nav-item <?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>">
    <span class="db-nav-icon"><i data-lucide="layout-dashboard" style="width:20px"></i></span>
    <span class="db-nav-label">Home</span>
  </a>
  <a href="orders" class="db-nav-item <?php echo ($current_page == 'orders.php') ? 'active' : ''; ?>">
    <span class="db-nav-icon"><i data-lucide="package" style="width:20px"></i></span>
    <span class="db-nav-label">Orders</span>
  </a>
  <a href="wallet" class="db-nav-item <?php echo ($current_page == 'wallet.php') ? 'active' : ''; ?>">
    <span class="db-nav-icon"><i data-lucide="indian-rupee" style="width:20px"></i></span>
    <span class="db-nav-label">Wallet</span>
  </a>
  <a href="history" class="db-nav-item <?php echo ($current_page == 'history.php') ? 'active' : ''; ?>">
    <span class="db-nav-icon"><i data-lucide="calendar" style="width:20px"></i></span>
    <span class="db-nav-label">History</span>
  </a>
  <a href="profile" class="db-nav-item <?php echo ($current_page == 'profile.php') ? 'active' : ''; ?>">
    <span class="db-nav-icon"><i data-lucide="user" style="width:20px"></i></span>
    <span class="db-nav-label">Profile</span>
  </a>
</div>

<script>
    // Just a helper to initialize icons if they were added dynamically
    if (window.lucide) {
        lucide.createIcons();
    }
</script>
