<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'delivery') {
    header('Location: index');
    exit;
}
require_once '../includes/db.php';
$pageTitle = 'Orders — Village Foods';
$bodyClass = 'db-body';
include 'layouts/header.php';

$navTitle = 'Incoming Orders';
include 'layouts/top_nav.php';
?>

<div class="db-content" style="padding: 20px 0 100px;">
    
    <div class="db-section active">
        <div class="db-orders" style="margin-top: 0; box-shadow: none; background: transparent;">
            <div class="db-orders-header" style="padding: 0 16px 20px;">
                <div class="db-filter-tabs">
                    <div class="db-filter-tab active" onclick="DeliveryConsole.setFilter('all',this)">All Orders</div>
                    <div class="db-filter-tab" onclick="DeliveryConsole.setFilter('active',this)">Active</div>
                    <div class="db-filter-tab" onclick="DeliveryConsole.setFilter('done',this)">Completed</div>
                </div>
            </div>

            <div id="deliveryOrdersList" style="padding: 0 16px;">
                <div style="text-align:center; padding:60px 20px; color:var(--text-dim)">
                    <i data-lucide="loader-2" class="animate-spin" style="width:40px; height:40px; margin-bottom:16px; color:var(--primary)"></i>
                    <p style="font-weight: 600;">Searching for assigned orders...</p>
                </div>
            </div>
        </div>
    </div>

</div>

<?php 
include 'layouts/bottom_nav.php';
$extraScripts = '<script src="../assets/js/delivery.js?v=' . time() . '"></script>';
include 'layouts/footer.php'; 
?>
