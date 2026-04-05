<?php
$activePage = 'delivery';
$pageTitle = 'Delivery Partners';
include 'layouts/header.php';
?>

<div class="admin-stats-grid mb-24" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 24px;">
    <div class="admin-stat-card" style="background: white; border: 1px solid var(--border); border-radius: 16px; padding: 20px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
        <div class="stat-icon" style="background: var(--primary-pale); color: var(--primary); width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
            <i data-lucide="users"></i>
        </div>
        <div class="stat-info" style="margin-left: 15px;">
            <div class="stat-label" style="font-size: 13px; color: var(--text-muted);">Total Partners</div>
            <div class="stat-value" id="totalPartnersCount" style="font-size: 24px; font-weight: 800; color: var(--text);">0</div>
        </div>
    </div>
    <div class="admin-stat-card" style="background: white; border: 1px solid var(--border); border-radius: 16px; padding: 20px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
        <div class="stat-icon" style="background: #fff7ed; color: #c2410c; width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
            <i data-lucide="clock"></i>
        </div>
        <div class="stat-info" style="margin-left: 15px;">
            <div class="stat-label" style="font-size: 13px; color: var(--text-muted);">Pending Verification</div>
            <div class="stat-value" id="pendingPartnersCount" style="font-size: 24px; font-weight: 800; color: var(--text);">0</div>
        </div>
    </div>
    <div class="admin-stat-card" style="background: white; border: 1px solid var(--border); border-radius: 16px; padding: 20px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
        <div class="stat-icon" style="background: #f0fdf4; color: #15803d; width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
            <i data-lucide="check-circle"></i>
        </div>
        <div class="stat-info" style="margin-left: 15px;">
            <div class="stat-label" style="font-size: 13px; color: var(--text-muted);">Active Partners</div>
            <div class="stat-value" id="activePartnersCount" style="font-size: 24px; font-weight: 800; color: var(--text);">0</div>
        </div>
    </div>
</div>

<div class="admin-card">
  <div class="admin-card-header" style="flex-wrap: wrap; gap: 15px; padding: 20px 24px;">
    <div class="admin-card-title"><i data-lucide="bike" class="header-icon"></i> Partner Management</div>
    
    <div style="display: flex; gap: 10px; align-items: center; margin-left: auto;">
        <div class="filter-tabs" style="display: flex; background: var(--bg-light); padding: 4px; border-radius: 10px; border: 1px solid var(--border);">
            <button class="range-btn active" onclick="DeliveryAdmin.setFilter('all', this)">All</button>
            <button class="range-btn" onclick="DeliveryAdmin.setFilter('pending', this)">Pending</button>
            <button class="range-btn" onclick="DeliveryAdmin.setFilter('verified', this)">Verified</button>
        </div>
    </div>
  </div>

  <div class="admin-table-wrapper">
    <table class="admin-table">
      <thead>
          <tr>
              <th style="padding-left: 24px;">DELIVERY PARTNER</th>
              <th>Contact Info</th>
              <th>Doc Status</th>
              <th>Online Status</th>
              <th>Total Deliveries</th>
              <th>Joined Date</th>
              <th style="text-align: right;">Actions</th>
          </tr>
      </thead>
      <tbody id="adminDeliveryTable"></tbody>
    </table>
  </div>
</div>

<script>
  window.addEventListener('load', () => {
    if (typeof AdminTabs !== 'undefined') {
        AdminTabs.refreshTabData('deliveryTab');
    }
  });
</script>

<?php include 'layouts/footer.php'; ?>
