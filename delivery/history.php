<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'delivery') {
    header('Location: index.php');
    exit;
}
require_once '../includes/db.php';
$pageTitle = 'Delivery History — Village Foods';
$bodyClass = 'db-body';
include 'layouts/header.php';

$navTitle = 'Lifetime History';
include 'layouts/top_nav.php';

$userId = $_SESSION['user_id'];
$pst = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE delivery_boy_id = ? AND status = 'Delivered'");
$pst->execute([$userId]);
$totalFood = $pst->fetchColumn();

$pst = $pdo->prepare("SELECT COUNT(*) FROM rapid_orders WHERE delivery_boy_id = ? AND status = 'Completed'");
$pst->execute([$userId]);
$totalRapid = $pst->fetchColumn();
$totalDelivered = $totalFood + $totalRapid;
?>

<div class="db-content" style="padding: 20px 16px 100px;">
    
    <!-- PERFORMANCE SUMMARY -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 32px;">
        <div class="db-order-card" style="margin:0; padding: 24px;">
            <div style="font-size: 11px; font-weight: 800; color: var(--text-dim); text-transform: uppercase; margin-bottom: 8px;">Completed</div>
            <div style="font-size: 28px; font-weight: 900; color: var(--text-main);"><?= $totalDelivered ?></div>
            <div style="font-size: 12px; color: var(--primary); font-weight: 700;">+12% this week</div>
        </div>
        <div class="db-order-card" style="margin:0; padding: 24px;">
            <div style="font-size: 11px; font-weight: 800; color: var(--text-dim); text-transform: uppercase; margin-bottom: 8px;">Efficiency</div>
            <div style="font-size: 28px; font-weight: 900; color: var(--text-main);">98%</div>
            <div style="font-size: 12px; color: var(--primary); font-weight: 700;">Top 5% Partner</div>
        </div>
    </div>

    <!-- LOGS -->
    <div>
        <div class="db-orders-header" style="padding-bottom: 16px;">
            <div class="db-orders-title"><i data-lucide="list" style="width:18px; color:var(--primary)"></i> Delivery Logs</div>
            <div style="font-size:11px; color:var(--text-dim); font-weight:700; text-transform:uppercase">All Time</div>
        </div>
        
        <div id="historyLogs">
            <div style="text-align:center; padding:60px 20px; color:var(--text-dim); background: var(--glass); border-radius: 24px; border: 1px solid var(--border);">
                 <i data-lucide="calendar-days" style="width:48px; height:48px; opacity:0.1; margin-bottom:16px"></i>
                 <div style="font-size:15px; font-weight: 700; margin-bottom: 4px;">No Recent Records</div>
                 <div style="font-size:13px; opacity: 0.8;">Your delivery milestones will be tracked here.</div>
            </div>
        </div>
    </div>

</div>

<?php 
include 'layouts/bottom_nav.php';
$extraScripts = '<script src="../assets/js/delivery.js?v=' . time() . '"></script>';
include 'layouts/footer.php'; 
?>
