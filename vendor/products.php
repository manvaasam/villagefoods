<?php
$activePage = 'products';
$pageTitle = 'Menu & Stock - Vendor Cabinet';
include 'layouts/header.php';
include 'layouts/sidebar.php';
?>

<main class="admin-main">
<?php 
$topbarTitle = "Menu & Stock Management";
$topbarSubtitle = "Manage your product availability and inventory";
ob_start(); ?>
<div style="display:flex; gap:10px">
    <button class="admin-header-btn" onclick="VendorProducts.resetModal(); Modal.open('addProductModal')">
        <i data-lucide="plus"></i> <span>Add New Product</span>
    </button>
    <button class="view-all-btn" onclick="VendorProducts.loadProducts()">
        <i data-lucide="refresh-cw"></i> Refresh
    </button>
</div>
<?php 
$topbarRight = ob_get_clean();
include 'layouts/topbar.php'; 
?>

    <div class="admin-content">
        <div class="premium-card">
            <div class="admin-card-header">
                <h3 class="admin-card-title">My Products</h3>
                <div class="header-actions">
                    <input type="text" id="productSearch" class="form-input" placeholder="Search products..." style="width:250px; padding:8px 15px; font-size:13px">
                </div>
            </div>
            
            <div class="admin-table-wrapper">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Product Info</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Stock (Qty)</th>
                            <th>Availability Status</th>
                            <th style="text-align:right">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="vendorProductsList">
                        <tr><td colspan="6" style="text-align:center; padding:40px">Loading products...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<!-- ADD/EDIT PRODUCT MODAL -->
<div class="modal-overlay" id="addProductModal" onclick="Modal.closeOnOverlay(event,'addProductModal')">
  <div class="modal">
    <button class="modal-close" onclick="Modal.close('addProductModal')"><i data-lucide="x"></i></button>
    <div class="modal-title" id="prodModalTitle"><i data-lucide="plus-circle"></i> Add New Product</div>
    <div class="modal-sub">Fill in the product details below</div>
    
    <input type="hidden" id="prodId">
    
    <div class="form-group">
        <label class="form-label">Product Name</label>
        <input class="form-input" type="text" id="prodName" placeholder="e.g. Organic Tomatoes">
    </div>
    
    <div class="form-grid-2">
      <div class="form-group">
        <label class="form-label">Category</label>
        <select class="form-input" id="prodCat" style="cursor:pointer">
          <option value="1">Fruits & Vegetables</option>
          <option value="2">Non-Veg (Meats & Fish)</option>
          <option value="3">Foods (Bakery & Snacks)</option>
          <option value="7">Bakery & Cakes</option>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Unit/Weight</label>
        <input class="form-input" type="text" id="prodUnit" placeholder="e.g. 1kg, 500g">
      </div>
    </div>
    
    <div class="form-grid-2">
      <div class="form-group">
        <label class="form-label">Selling Price (₹)</label>
        <input class="form-input" type="number" id="prodPrice" placeholder="0.00">
      </div>
      <div class="form-group">
        <label class="form-label">Original Price (₹)</label>
        <input class="form-input" type="number" id="prodOldPrice" placeholder="M.R.P (Optional)">
      </div>
    </div>

    <div class="form-grid-2">
      <div class="form-group">
        <label class="form-label">Stock Quantity</label>
        <input class="form-input" type="number" id="prodStock" placeholder="100">
      </div>
      <div class="form-group">
        <label class="form-label">Product Rating (0-5)</label>
        <input class="form-input" type="number" id="prodRating" step="0.1" min="0" max="5" value="4.5">
      </div>
    </div>

    <div class="form-group">
      <label class="form-label">Product Image</label>
      <input class="form-input" type="file" id="prodImageFile" accept="image/*" onchange="VendorProducts.handleImagePreview(this)">
      <div id="prodImagePreview" style="margin-top:10px; display:none;">
        <img src="" style="width:60px; height:60px; border-radius:8px; object-fit:cover;">
      </div>
    </div>

    <button class="form-btn" onclick="VendorProducts.save()" style="margin-top:10px">
        <i data-lucide="save"></i> Save Product
    </button>
  </div>
</div>

<!-- DELETE CONFIRMATION MODAL -->
<div class="modal-overlay" id="deleteConfirmModal" onclick="Modal.closeOnOverlay(event,'deleteConfirmModal')">
  <div class="modal" style="max-width: 400px; text-align: center; padding: 32px;">
    <div style="background:#fef2f2; width:64px; height:64px; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 20px;">
      <i data-lucide="alert-triangle" style="width:32px; height:32px; color:#ef4444;"></i>
    </div>
    <h2 style="margin:0 0 12px 0; font-size:22px; font-weight:800;">Delete Product?</h2>
    <p style="color:var(--text-muted); font-size:14px; margin:0 0 24px 0; line-height:1.6">
      Are you sure you want to delete this product? This action is permanent and cannot be undone.
    </p>
    
    <input type="hidden" id="deleteProdId">
    
    <div style="display:flex; gap:12px;">
      <button onclick="Modal.close('deleteConfirmModal')" class="form-btn" style="flex:1; background:#f3f4f6; color:#374151; margin-top:0">Cancel</button>
      <button onclick="VendorProducts.confirmDelete()" class="form-btn" style="flex:1; background:#ef4444; color:#fff; margin-top:0">Yes, Delete</button>
    </div>
  </div>
</div>

<?php include 'layouts/footer.php'; ?>
<script src="../assets/js/vendor-products.js"></script>
