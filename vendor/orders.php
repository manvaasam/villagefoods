<?php
$activePage = 'orders';
$pageTitle = 'Orders Management - Vendor Cabinet';
include 'layouts/header.php';
include 'layouts/sidebar.php';
?>

<main class="admin-main">
<?php 
$topbarTitle = "Orders Management";
$topbarSubtitle = "Manage and track your shop orders";
ob_start(); ?>
<div class="chart-range-selector">
    <button class="range-btn active" onclick="setOrderFilter('all')">All</button>
    <button class="range-btn" onclick="setOrderFilter('active')">Active</button>
    <button class="range-btn" onclick="setOrderFilter('completed')">Completed</button>
</div>
<?php 
$topbarRight = ob_get_clean();
include 'layouts/topbar.php'; 
?>

    <div class="admin-content">
        <div class="premium-card">
            <div class="admin-card-header">
                <h3 class="admin-card-title">All Orders</h3>
                <div class="header-actions" style="flex: 1; max-width: 400px;">
                    <div class="search-input-wrapper">
                        <i data-lucide="search" class="search-icon"></i>
                        <input type="text" id="orderSearch" class="admin-search-input" placeholder="Search Order ID or Customer...">
                    </div>
                </div>
            </div>
            
            <div class="admin-table-wrapper">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Items</th>
                            <th>Total Amount</th>
                            <th>Status</th>
                            <th>Time</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="vendorOrdersList">
                        <tr><td colspan="7" style="text-align:center; padding:40px">Loading orders...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<!-- ORDER DETAILS MODAL -->
<div class="modal-overlay" id="orderDetailsModal" onclick="Modal.closeOnOverlay(event,'orderDetailsModal')">
  <div class="modal" style="max-width:600px">
    <button class="modal-close" onclick="Modal.close('orderDetailsModal')"><i data-lucide="x"></i></button>
    <div class="modal-title" id="modalOrderNum">#Order ID</div>
    <div class="modal-sub">Order details and items</div>
    
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:20px; padding:15px; background:var(--bg); border-radius:var(--radius-sm)">
        <div>
            <div style="font-size:12px; color:var(--text-muted); text-transform:uppercase; font-weight:800; margin-bottom:4px">Customer Info</div>
            <div id="modalCustName" style="font-weight:700">Name</div>
            <div id="modalCustPhone" style="font-size:13px">Phone</div>
        </div>
        <div>
            <div style="font-size:12px; color:var(--text-muted); text-transform:uppercase; font-weight:800; margin-bottom:4px">Order Status</div>
            <span id="modalStatus" class="status-pill sp-pending">Pending</span>
        </div>
        <div style="grid-column: span 2">
            <div style="font-size:12px; color:var(--text-muted); text-transform:uppercase; font-weight:800; margin-bottom:4px">Delivery Address</div>
            <div id="modalAddress" style="font-size:13px; line-height:1.4">Full Address</div>
        </div>
        <div>
            <div style="font-size:12px; color:var(--text-muted); text-transform:uppercase; font-weight:800; margin-bottom:4px">Payment Info</div>
            <div id="modalPayment" style="font-size:13px; font-weight:700">COD</div>
        </div>
        <div>
            <div style="font-size:12px; color:var(--text-muted); text-transform:uppercase; font-weight:800; margin-bottom:4px">Bill Summary</div>
            <div style="display:flex; justify-content:space-between; font-size:13px; margin-bottom:4px"><span>Product Total:</span><span id="modalItemsTotal" style="font-weight:700">₹0.00</span></div>
            <div style="display:flex; justify-content:space-between; font-size:13px; margin-bottom:4px; color:#ef4444"><span id="modalCommissionLabel">Commission:</span><span id="modalCommission" style="font-weight:700">-₹0.00</span></div>
            <div style="display:flex; justify-content:space-between; font-size:14px; font-weight:800; color:#10b981; padding-top:8px"><span>Your Earning:</span><span id="modalVendorEarning">₹0.00</span></div>
        </div>
    </div>

    <div id="modalItemsContainer" style="display:flex; flex-direction:column; gap:10px; margin-bottom:20px">
        <!-- Populated via JS -->
    </div>

    <div style="margin-top:24px; padding-top:20px; border-top:1px dashed var(--border)">
        <div style="font-size:11px; color:var(--text-muted); text-transform:uppercase; font-weight:800; margin-bottom:12px; letter-spacing:0.5px">Quick Actions</div>
        <div id="modalActionContainer" style="display:flex; gap:12px">
            <!-- Populated via JS -->
        </div>
    </div>
  </div>
</div>

<?php include 'layouts/footer.php'; ?>
<script src="../assets/js/vendor-orders.js"></script>
