<?php
$activePage = 'categories';
$pageTitle = 'Categories';
include 'layouts/header.php';
?>

<style>
.badge-status {
    padding: 6px 14px;
    border-radius: 50px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    display: inline-flex;
    align-items: center;
    transition: all 0.2s;
}
.st-active { background: #f0fdf4; color: #15803d; border: 1px solid #dcfce7; }
.st-inactive { background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }
.badge-status:hover { transform: scale(1.05); }

.admin-btn {
    display: flex;
    align-items: center;
    gap: 8px;
    border: none;
    cursor: pointer;
    transition: all 0.2s;
    font-family: inherit;
}
.admin-btn:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
</style>

<div id="categoriesTab">
  <div class="header-action admin-card-header" style="margin-bottom: 24px;">
    <div class="header-title-box">
      <h1 class="page-title">Category Management</h1>
      <p class="subtitle">Organize and manage your product categories</p>
    </div>
    <div class="header-search-box">
      <div class="search-input-wrapper">
        <i data-lucide="search" class="search-icon"></i>
        <input type="text" id="catSearch" placeholder="Search categories..." class="admin-search-input" onkeyup="CategoryAdmin.init()">
      </div>
    </div>
    <div class="header-btn-box">
        <button class="nav-btn btn-primary" onclick="CategoryAdmin.resetModal(); Modal.open('addCategoryModal')"><i data-lucide="plus"></i> <span>Add Category</span></button>
    </div>
  </div>

  <div class="stats-summary" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 24px;">
    <div class="stat-card" style="background:#fff; padding: 20px; border-radius:12px; border:1px solid #e5e7eb; display:flex; align-items:center; gap:16px;">
        <div style="background:#f0fdf4; color:#16a34a; padding:12px; border-radius:10px;"><i data-lucide="layers"></i></div>
        <div>
            <div style="font-size:12px; color:#6b7280;">Total Categories</div>
            <div id="totalCatsCount" style="font-size:24px; font-weight:800; color:#111827;">0</div>
        </div>
    </div>
    <div class="stat-card" style="background:#fff; padding: 20px; border-radius:12px; border:1px solid #e5e7eb; display:flex; align-items:center; gap:16px;">
        <div style="background:#eff6ff; color:#2563eb; padding:12px; border-radius:10px;"><i data-lucide="package"></i></div>
        <div>
            <div style="font-size:12px; color:#6b7280;">Global Products</div>
            <div id="totalProdsCount" style="font-size:24px; font-weight:800; color:#111827;">0</div>
        </div>
    </div>
  </div>

  <div class="admin-table-wrapper premium-card" style="padding: 0; overflow-x: auto; -webkit-overflow-scrolling: touch;">
    <table class="admin-table" style="min-width: 700px;">
      <thead>
        <tr>
          <th>Category</th>
          <th>Products</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr><td colspan="4" style="text-align:center; padding: 24px;">Loading categories...</td></tr>
      </tbody>
    </table>
  </div>
</div>

<script>
  window.addEventListener('load', () => {
    if (typeof AdminTabs !== 'undefined') {
        AdminTabs.refreshTabData('categoriesTab');
    }
  });
</script>

<?php include 'layouts/footer.php'; ?>
