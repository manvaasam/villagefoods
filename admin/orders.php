<?php
$activePage = 'orders';
$pageTitle = 'Order Management';
include 'layouts/header.php';
?>

<div class="admin-card">
  <div class="admin-card-header">
    <div class="admin-card-title"><i data-lucide="package" class="header-icon"></i> Order Management</div>
    <div style="display:flex;gap:12px">
        <select class="filter-select" id="orderStatusFilter" onchange="OrderAdmin.init()">
          <option value="all">All Status</option>
          <option value="Confirmed" <?php echo ($_GET['status'] ?? '') == 'Confirmed' ? 'selected' : ''; ?>>Confirmed</option>
          <option value="Preparing" <?php echo ($_GET['status'] ?? '') == 'Preparing' ? 'selected' : ''; ?>>Preparing</option>
          <option value="Picked Up" <?php echo ($_GET['status'] ?? '') == 'Picked Up' ? 'selected' : ''; ?>>Picked Up</option>
          <option value="On the Way" <?php echo ($_GET['status'] ?? '') == 'On the Way' ? 'selected' : ''; ?>>On the Way</option>
          <option value="Delivered" <?php echo ($_GET['status'] ?? '') == 'Delivered' ? 'selected' : ''; ?>>Delivered</option>
          <option value="Cancelled" <?php echo ($_GET['status'] ?? '') == 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
          <option value="Refund Pending" <?php echo ($_GET['status'] ?? '') == 'Refund Pending' ? 'selected' : ''; ?>>Refund Pending</option>
        </select>
        <select class="filter-select" id="paymentTypeFilter" onchange="OrderAdmin.init()">
          <option value="all">All Payments</option>
          <option value="cod">Cash on Delivery</option>
          <option value="online">Online Payment</option>
        </select>
        <button class="filter-btn-export" onclick="OrderAdmin.exportCSV()"><i data-lucide="download"></i> Export CSV</button>
      </div>
  </div>
  
  <div class="filters-bar">
    <div class="admin-search-bar" style="max-width:300px">
      <span class="search-icon"><i data-lucide="search"></i></span>
      <input type="text" id="orderSearch" placeholder="Search by order ID or customer..." onkeyup="OrderAdmin.init()">
    </div>
    <input type="date" id="orderDate" class="filter-date" onchange="OrderAdmin.init()">
  </div>

  <div class="admin-table-wrapper">
    <table class="admin-table">
      <thead><tr><th>Order ID</th><th>Customer</th><th>Shop</th><th>Items</th><th>Amount</th><!-- <th>Profit</th> --><th>Method</th><th>Collection</th><th>Delivery Boy</th><th>Status</th><th>Duration</th><th>Date</th><th>Assign</th></tr></thead>
      <tbody id="adminOrdersTable2"></tbody>
    </table>
  </div>
</div>

<script>
  window.addEventListener('load', () => {
    if (typeof AdminTabs !== 'undefined') {
        AdminTabs.refreshTabData('ordersTab');
    }
  });
</script>

<?php include 'layouts/footer.php'; ?>
