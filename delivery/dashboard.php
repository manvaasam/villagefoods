<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once '../includes/db.php';
require_once '../includes/auth_helper.php';
checkPersistentLogin($pdo);

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'delivery') {
    header('Location: index');
    exit;
}

$pageTitle = 'Partner Home — Village Foods';
$bodyClass = 'db-body';
include 'layouts/header.php';

$userId = $_SESSION['user_id'];
$userName = $_SESSION['user_name'];

// Fetch Profile Details
$pst = $pdo->prepare("SELECT email, phone FROM users WHERE id = ?");
$pst->execute([$userId]);
$userProfile = $pst->fetch();
$userEmail = $userProfile['email'] ?? '';
$userPhone = $userProfile['phone'] ?? '';

// Fetch Stats
$pst = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE delivery_boy_id = ? AND status = 'Delivered'");
$pst->execute([$userId]);
$totalFood = $pst->fetchColumn();

$pst = $pdo->prepare("SELECT COUNT(*) FROM rapid_orders WHERE delivery_boy_id = ? AND status = 'Completed'");
$pst->execute([$userId]);
$totalRapid = $pst->fetchColumn();

$totalDelivered = $totalFood + $totalRapid;

$navTitle = 'Partner Home';
include 'layouts/top_nav.php';
?>

<!-- DASHBOARD CONTENT -->
<div class="db-content" style="padding-bottom: 120px;">

    <!-- WELCOME SECTION -->
    <div class="welcome-section">
      <div class="welcome-text">Good <?= (date('H') < 12 ? 'Morning' : (date('H') < 17 ? 'Afternoon' : 'Evening')) ?>,</div>
      <div class="welcome-name"><?= explode(' ', $userName)[0] ?>! 👋</div>
    </div>
    
    <!-- WALLET SUMMARY CARD -->
    <div class="wallet-wrapper">
      <div class="premium-card">
        <div class="db-route-label" style="color:rgba(255,255,255,0.7); font-size: 11px;">Today's Earnings</div>
        <div class="wallet-amount" id="totalEarnings">₹0</div>
        <div class="db-subtitle" style="color:rgba(255,255,255,0.6); margin-top:4px; font-size: 12px;" id="deliverySummary">0 deliveries completed</div>
        
        <div class="wallet-actions">
          <button class="wallet-btn" onclick="window.location.href='wallet.php'">
            <i data-lucide="wallet"></i> Wallet
          </button>
          <button class="wallet-btn" onclick="window.location.href='history.php'">
            <i data-lucide="history"></i> History
          </button>
        </div>
      </div>
    </div>

    <!-- Verification Alert -->
    <div id="verificationAlert" style="display:none; margin: 0 20px 20px;"></div>

    <!-- QUICK STATS -->
    <div class="db-stats">
      <div class="db-stat-card">
        <div class="stat-icon icon-pending"><i data-lucide="clock"></i></div>
        <div class="db-stat-val" id="pendingCount">0</div>
        <div class="db-stat-lbl">Active</div>
      </div>
      <div class="db-stat-card">
        <div class="stat-icon icon-delivered"><i data-lucide="award"></i></div>
        <div class="db-stat-val" id="historyStatCount">0</div>
        <div class="db-stat-lbl">History</div>
      </div>
      <div class="db-stat-card">
        <div class="stat-icon icon-online"><i data-lucide="zap"></i></div>
        <div class="db-stat-val" id="onlineHours">0h</div>
        <div class="db-stat-lbl">Online</div>
      </div>
    </div>

    <!-- ACTIVE TASKS SECTION (DYNAMO) -->
    <div class="db-section-title">
        Active Deliveries
        <span id="activeCount">0 Assigned</span>
    </div>
    <div id="deliveryOrdersList" class="db-orders-container">
        <!-- Rendered via delivery.js -->
        <div style="text-align:center; padding:40px; color:var(--text-dim)">
            <i data-lucide="loader-2" class="animate-spin" style="width:32px; height:32px; color:var(--primary); margin: 0 auto 12px; display:block"></i>
            <p style="font-size:13px">Loading orders...</p>
        </div>
    </div>

</div>

<?php 
include 'layouts/bottom_nav.php';
echo '<script src="../assets/js/delivery.js?v=' . time() . '"></script>';
include 'layouts/footer.php'; 
?>
