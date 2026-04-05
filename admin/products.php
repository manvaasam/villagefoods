<?php
$activePage = 'products';
$pageTitle = 'Product Catalog';
include 'layouts/header.php';
?>

<div class="admin-card">
  <div class="header-action">
    <div class="header-title-box">
      <h1 class="page-title"><i data-lucide="leaf" class="header-icon"></i> Product Catalog</h1>
      <p class="subtitle">Manage your inventory and shop visibility</p>
    </div>
    <div class="header-search-box">
      <div class="search-input-wrapper">
        <i data-lucide="search" class="search-icon"></i>
        <input type="text" id="prodSearch" class="admin-search-input" placeholder="Search products..." onkeyup="ProductAdmin.init()">
      </div>
    </div>
    <div class="header-btn-box">
      <button class="nav-btn btn-primary" onclick="ProductAdmin.resetModal(); Modal.open('addProductModal')"><i data-lucide="plus"></i> <span>Add Product</span></button>
    </div>
  </div>
  
  <div class="filters-bar" style="margin-bottom: 24px; display: flex; gap: 12px; flex-wrap: wrap;">
    <select class="filter-select" id="prodCatFilter" onchange="ProductAdmin.init()" style="flex: 1; min-width: 140px;">
      <option value="all">All Categories</option>
    </select>
    <select class="filter-select" id="prodShopFilter" onchange="ProductAdmin.init()" style="flex: 1; min-width: 140px;">
      <option value="all">All Shops</option>
    </select>
  </div>
  <div class="admin-table-wrapper" style="overflow-x: auto; -webkit-overflow-scrolling: touch; border-radius: 12px; border: 1px solid var(--border);">
    <table class="admin-table" style="min-width: 900px;">
      <thead><tr><th>Product</th><th>Category</th><th>Price</th><th>Stock</th><th>Status</th><th>Rating</th><th>Actions</th></tr></thead>
      <tbody id="adminProductTable"></tbody>
    </table>
  </div>
</div>

<script>
  window.addEventListener('load', () => {
    if (typeof AdminTabs !== 'undefined') {
        AdminTabs.refreshTabData('productsTab');
    }
  });
</script>

<?php include 'layouts/footer.php'; ?>
