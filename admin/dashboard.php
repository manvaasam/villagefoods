<?php
$activePage = 'dashboard';
$pageTitle = 'Dashboard';
include 'layouts/header.php';
?>

<div class="bento-grid">
  <!-- 1. Revenue Card -->
  <div class="premium-card analytics-card ac-green col-span-3">
    <div class="analytics-header">
      <div class="analytics-icon-box"><i data-lucide="trending-up"></i></div>
      <div class="analytics-trend trend-up" id="growthRevenue">
        <i data-lucide="arrow-up-right"></i> 0%
      </div>
    </div>
    <div class="analytics-body">
      <div class="analytics-value" id="statsRevenue">₹0.00</div>
      <div class="analytics-label">Total Revenue</div>
    </div>
  </div>

  <!-- 1b. Platform Profit Card -->
  <div class="premium-card analytics-card ac-emerald col-span-3">
    <div class="analytics-header">
      <div class="analytics-icon-box" style="background:rgba(16,185,129,0.1); color:#10b981"><i data-lucide="award"></i></div>
    </div>
    <div class="analytics-body">
      <div class="analytics-value" id="statsPlatformProfit">₹0.00</div>
      <div class="analytics-label">Platform Profit</div>
    </div>
  </div>

  <!-- 2. Orders Card -->
  <div class="premium-card analytics-card ac-blue col-span-3">
    <div class="analytics-header">
      <div class="analytics-icon-box"><i data-lucide="shopping-cart"></i></div>
      <div class="analytics-trend" style="background:var(--blue); color:white; opacity:0.8">Live</div>
    </div>
    <div class="analytics-body">
      <div class="analytics-value" id="statsOrders">0</div>
      <div class="analytics-label">Total Orders</div>
    </div>
  </div>

  <!-- 3. AOV Card -->
  <div class="premium-card analytics-card ac-orange col-span-3">
    <div class="analytics-header">
      <div class="analytics-icon-box"><i data-lucide="wallet"></i></div>
      <div class="analytics-trend" style="background:var(--accent-light); color:var(--text)">Target: 85%</div>
    </div>
    <div class="analytics-body">
      <div class="analytics-value" id="statsAOV">₹0.00</div>
      <div class="analytics-label">Avg. Order Value</div>
    </div>
  </div>

  <!-- 4. Customers Card -->
  <div class="premium-card analytics-card ac-purple col-span-3">
    <div class="analytics-header">
      <div class="analytics-icon-box"><i data-lucide="users"></i></div>
      <div class="analytics-trend trend-up"><i data-lucide="plus"></i> New</div>
    </div>
    <div class="analytics-body">
      <div class="analytics-value" id="statsCustomers">0</div>
      <div class="analytics-label">Total Customers</div>
    </div>
  </div>

  <!-- 4b. Refunds Pending Card (New) -->
  <div class="premium-card analytics-card ac-red col-span-3" id="refundCard" style="display:none">
    <div class="analytics-header">
      <div class="analytics-icon-box" style="background:rgba(239,68,68,0.1); color:#ef4444"><i data-lucide="refresh-ccw"></i></div>
      <div class="analytics-trend trend-down">Action Required</div>
    </div>
    <div class="analytics-body">
      <div class="analytics-value" id="statsRefundPending" style="color:#ef4444">0</div>
      <div class="analytics-label">Refunds Pending</div>
    </div>
  </div>

  <!-- ... (Script restored) ... -->

  <!-- 4c. Rapid Order Stats Row (NEW) -->
  <div class="premium-card analytics-card ac-emerald col-span-12" style="padding: 16px 24px; margin-bottom: -10px;">
    <div class="flex-responsive">
        <div style="display:flex; align-items:center; gap:12px">
            <div class="analytics-icon-box" style="background:#10b981"><i data-lucide="bike" style="color:white"></i></div>
            <div>
                <div style="font-size:16px; font-weight:800; color:var(--text)">Rapid Pickup Monitor</div>
                <div style="font-size:11px; color:var(--text-muted)">Live delivery partners activity</div>
            </div>
        </div>
        <div id="rapidStatsContainer" style="display:flex; gap:32px">
            <!-- Dynamic Rapid Stats -->
        </div>
        <a href="rapid-orders.php" class="nav-btn nav-btn-outline" style="font-size:11px; padding:6px 12px">View Monitor</a>
    </div>
  </div>

  <!-- 5. Sales Analytics Chart -->
  <div class="premium-card col-span-8">
    <div class="admin-card-header">
      <div class="admin-card-title"><i data-lucide="bar-chart-3" class="header-icon"></i> Sales Performance</div>
      <div class="header-right">
          <div class="chart-range-selector">
              <button class="range-btn active" data-range="30d">30D</button>
              <button class="range-btn" data-range="7d">7D</button>
              <button class="range-btn" data-range="12m">1Y</button>
          </div>
      </div>
    </div>
    <!-- ApexCharts CDN -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <div class="revenue-chart-wrapper">
       <div id="revenueChartApex"></div>
    </div>
  </div>

  <!-- 6. Sales by Category -->
  <div class="premium-card col-span-4">
    <div class="admin-card-header">
      <div class="admin-card-title"><i data-lucide="pie-chart" class="header-icon"></i> Top Categories</div>
    </div>
    <div id="topCategoriesList" style="margin-top: 10px;">
       <!-- Dynamic Category Rows -->
    </div>
  </div>

  <!-- 7. Top Selling Products -->
  <div class="premium-card col-span-5">
    <div class="admin-card-header">
      <div class="admin-card-title"><i data-lucide="package-search" class="header-icon"></i> Trending Products</div>
    </div>
    <div id="topProductsTable">
       <!-- Dynamic Product Rows -->
    </div>
  </div>

  <!-- 8. Recent Orders -->
  <div class="premium-card col-span-7">
    <div class="admin-card-header">
      <div class="admin-card-title"><i data-lucide="clock" class="header-icon"></i> Recent Activity</div>
      <a href="orders.php" class="view-all-btn">Show All</a>
    </div>
    <div class="admin-table-wrapper">
      <table class="admin-table">
        <thead><tr><th>Order</th><th>Customer</th><th>Total</th><th>Status</th></tr></thead>
        <tbody id="adminOrdersTable"></tbody>
      </table>
    </div>
  </div>
</div>
</div>

<script>
  window.addEventListener('load', () => {
    if (typeof AdminTabs !== 'undefined') {
        AdminTabs.refreshTabData('dashTab');
    }
  });
</script>

<?php include 'layouts/footer.php'; ?>
