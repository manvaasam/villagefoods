<?php
$activePage = 'customers';
$pageTitle = 'Customer Management';
include 'layouts/header.php';
?>

<div class="admin-content-header" style="margin-bottom: 24px;">
  <div>
    <h2 class="admin-topbar-title">Customer Management</h2>
    <p class="admin-topbar-date">Manage your consumer base, track spending, and handle account status.</p>
  </div>
</div>

<div class="filters-bar" style="margin-bottom: 20px;">
  <div class="admin-search-bar">
    <span class="search-icon"><i data-lucide="search"></i></span>
    <input type="text" id="custSearch" placeholder="Search by name, phone, or email..." onkeyup="CustomerAdmin.handleSearch(this.value)">
  </div>
  
  <div class="filter-tabs" style="display:flex; gap:8px">
    <button class="range-btn active" onclick="CustomerAdmin.setFilter('all', this)">All Customers</button>
    <button class="range-btn" onclick="CustomerAdmin.setFilter('new', this)">New</button>
    <button class="range-btn" onclick="CustomerAdmin.setFilter('top', this)">Top Spenders</button>
    <button class="range-btn" onclick="CustomerAdmin.setFilter('blocked', this)">Blocked</button>
  </div>
</div>

<!-- Customer List Table -->
<div class="admin-card" id="customerListView">
  <div class="admin-table-wrapper">
    <table class="admin-table">
      <thead>
        <tr>
          <th>Customer ID</th>
          <th>Name</th>
          <th>Contact Info</th>
          <th>Total Orders</th>
          <th>Total Spending</th>
          <th>Joined Date</th>
          <th>Status</th>
          <th style="text-align:right">Actions</th>
        </tr>
      </thead>
      <tbody id="adminCustomerTable">
        <!-- Rows will be injected by JS -->
      </tbody>
    </table>
  </div>
</div>

<!-- Customer Profile View (Hidden by default) -->
<div class="admin-card" id="customerProfileView" style="display:none">
  <div class="admin-card-header">
    <button class="filter-btn-export" onclick="CustomerAdmin.closeProfile()" style="padding: 6px 12px; font-size: 12px;">
      <i data-lucide="arrow-left" style="width:14px; height:14px; vertical-align:middle"></i> Back to List
    </button>
    <div class="admin-card-title" id="profileTitle">Customer Profile</div>
  </div>
  <div id="profileContent" class="profile-container">
    <!-- Profile details will be injected here -->
  </div>
</div>

<script>
  window.addEventListener('load', () => {
    if (typeof CustomerAdmin !== 'undefined') {
        CustomerAdmin.init();
    }
  });
</script>

<?php include 'layouts/footer.php'; ?>
