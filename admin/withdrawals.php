<?php
$activePage = 'withdrawals';
$pageTitle = 'Payment Requests';
include 'layouts/header.php';
?>

<div class="admin-content-header" style="margin-bottom: 24px;">
  <div>
    <h2 class="admin-topbar-title">Payment Requests</h2>
    <p class="admin-topbar-date">Manage and process payment requests from delivery partners.</p>
  </div>
</div>

<div class="filters-bar" style="margin-bottom: 20px;">
  <div class="admin-search-bar">
    <span class="search-icon"><i data-lucide="search"></i></span>
    <input type="text" id="withdrawSearch" placeholder="Search by partner name..." onkeyup="PaymentAdmin.handleSearch(this.value)">
  </div>
  
  <div class="filter-tabs" style="display:flex; gap:8px">
    <button class="range-btn active" onclick="PaymentAdmin.setFilter('Pending', this)">Pending</button>
    <button class="range-btn" onclick="PaymentAdmin.setFilter('Approved', this)">Approved</button>
    <button class="range-btn" onclick="PaymentAdmin.setFilter('Rejected', this)">Rejected</button>
    <button class="range-btn" onclick="PaymentAdmin.setFilter('all', this)">All Requests</button>
  </div>
</div>

<div class="admin-card">
  <div class="admin-table-wrapper">
    <table class="admin-table">
      <thead>
        <tr>
          <th>Request ID</th>
          <th>Partner</th>
          <th>Amount</th>
          <th>Request Date</th>
          <th>Status</th>
          <th style="text-align:right">Actions</th>
        </tr>
      </thead>
      <tbody id="adminWithdrawalTable">
        <tr><td colspan="6" style="text-align:center; padding:40px">Loading requests...</td></tr>
      </tbody>
    </table>
  </div>
</div>

<!-- BANK DETAILS MODAL -->
<div class="modal-overlay" id="bankDetailsModal" onclick="Modal.closeOnOverlay(event,'bankDetailsModal')">
  <div class="modal" style="max-width:500px">
    <button class="modal-close" onclick="Modal.close('bankDetailsModal')"><i data-lucide="x"></i></button>
    <div class="modal-title">Payment Details</div>
    <div class="modal-sub">Details for processing the transfer</div>
    
    <div id="bankDetailsContent" style="margin-top:20px; padding:20px; background:var(--bg); border-radius:var(--radius-sm)">
        <!-- Injected by JS -->
    </div>

    <div style="margin-top:24px; padding-top:20px; border-top:1px dashed var(--border)">
        <div style="font-size:11px; color:var(--text-muted); text-transform:uppercase; font-weight:800; margin-bottom:12px">Update Status</div>
        <div style="margin-bottom:15px">
            <textarea id="adminNote" class="form-input" placeholder="Add a note (Transaction ID, reason, etc.)" style="width:100%; min-height:80px; padding:12px"></textarea>
        </div>
        <div id="withdrawalActionButtons" style="display:flex; gap:12px">
            <!-- Buttons -->
        </div>
    </div>
  </div>
</div>

<script>
  window.addEventListener('load', () => {
    if (typeof PaymentAdmin !== 'undefined') {
        PaymentAdmin.init();
    }
  });
</script>

<?php include 'layouts/footer.php'; ?>
