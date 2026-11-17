<?php
// Fetch Online Status
if (isset($pdo) && isset($_SESSION['user_id'])) {
    $pstStatus = $pdo->prepare("SELECT is_online FROM delivery_details WHERE user_id = ?");
    $pstStatus->execute([$_SESSION['user_id']]);
    $isOnline = $pstStatus->fetchColumn() ?: 0;
} else {
    $isOnline = 0;
}
?>
<div class="db-navbar shadow-sm">
  <div class="db-navbar-left">
    <a href="logout.php" class="db-back-btn" title="Logout"><i data-lucide="log-out" style="width:18px"></i></a>
    <div>
      <div class="db-title"><?php echo $navTitle ?? 'My Dashboard'; ?></div>
      <div class="db-subtitle">Hey <?= htmlspecialchars($_SESSION['user_name'] ?? 'Partner') ?>! <span class="greeting-text">Ready to deliver?</span></div>
    </div>
  </div>
  <div class="online-toggle <?= !$isOnline ? 'offline' : '' ?>" onclick="toggleOnlineStatus(this)">
    <div class="online-dot"></div>
    <span class="online-label"><?= $isOnline ? 'Online' : 'Offline' ?></span>
  </div>
</div>

