<?php
$activePage = 'dashboard';
$pageTitle = 'Vendor Dashboard - Village Foods';
include 'layouts/header.php';
include 'layouts/sidebar.php';
?>

<main class="admin-main">
<?php 
$topbarTitle = "Welcome, " . $_SESSION['shop_name'] . "!";
$topbarSubtitle = date('l, j F Y');
ob_start(); ?>
<div id="shopStatusToggle" class="view-all-btn" style="cursor:pointer; background:var(--bg-dark); color:var(--text-muted); padding:8px 16px; border-radius:12px; display:flex; align-items:center; gap:8px; font-weight:800; transition:all 0.3s" onclick="VendorDashboard.toggleShopStatus()">
    <i data-lucide="loader" class="spinner-icon" style="width:18px; height:18px"></i> LOADING...
</div>
<?php 
$topbarRight = ob_get_clean();
include 'layouts/topbar.php'; 
?>

    <div class="admin-content">
        <div class="bento-grid">
            <!-- Stats Cards -->
            <div class="premium-card analytics-card ac-orange" style="grid-column: span 4">
                <div class="analytics-header">
                    <div class="analytics-icon-box">
                        <i data-lucide="shopping-bag"></i>
                    </div>
                </div>
                <div class="analytics-value" id="statTodayOrders">0</div>
                <div class="analytics-label">Today's Orders</div>
            </div>

            <div class="premium-card analytics-card ac-green" style="grid-column: span 4">
                <div class="analytics-header">
                    <div class="analytics-icon-box">
                        <i data-lucide="indian-rupee"></i>
                    </div>
                </div>
                <div class="analytics-value" id="statTodayRevenue">₹0</div>
                <div class="analytics-label">Today's Earnings</div>
            </div>

            <div class="premium-card analytics-card ac-blue" style="grid-column: span 4">
                <div class="analytics-header">
                    <div class="analytics-icon-box">
                        <i data-lucide="clock"></i>
                    </div>
                </div>
                <div class="analytics-value" id="statAvgPrep">--</div>
                <div class="analytics-label">Avg. Prep Time</div>
            </div>

            <!-- Recent Orders Table -->
            <div class="premium-card" style="grid-column: span 12">
                <div class="admin-card-header">
                    <h3 class="admin-card-title">Recent Orders</h3>
                    <a href="orders.php" class="view-all-btn">View All Orders</a>
                </div>
                <div class="admin-table-wrapper">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="vendorDashboardOrders">
                            <tr><td colspan="5" style="text-align:center; padding:40px">Loading fresh orders...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include 'layouts/footer.php'; ?>
<script src="../assets/js/vendor-dashboard.js"></script>
