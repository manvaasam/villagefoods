</div> <!-- .admin-content -->
</main>
</div> <!-- .admin-layout -->

<!-- ====== MODALS ====== -->

<!-- ADD PRODUCT MODAL -->
<div class="modal-overlay" id="addProductModal" onclick="Modal.closeOnOverlay(event,'addProductModal')">
  <div class="modal">
    <button class="modal-close" onclick="Modal.close('addProductModal')"><i data-lucide="x"></i></button>
    <div class="modal-title" id="prodModalTitle"><i data-lucide="plus-circle"></i> Add New Product</div>
    <div class="modal-sub">Fill in the product details below</div>
    <div class="form-group"><label class="form-label">Product Name</label><input class="form-input" type="text" id="prodName" placeholder="e.g. Organic Tomatoes"></div>
    <div class="form-grid-2">
      <div class="form-group"><label class="form-label">Category</label>
        <select class="form-input" id="prodCat" style="cursor:pointer">
          <option value="1">Vegetables</option>
          <option value="2">Fruits</option>
          <option value="3">Bakery &amp; Foods</option>
          <option value="4">Chicken &amp; Meats</option>
          <option value="5">Fish &amp; Seafood</option>
          <option value="6">Eggs &amp; Dairy</option>
          <option value="7">Mutton &amp; Lamb</option>
        </select>
      </div>
      <div class="form-group"><label class="form-label">Shop/Vendor</label>
        <select class="form-input" id="prodShop" style="cursor:pointer">
           <option value="">Loading shops...</option>
        </select>
      </div>
    </div>
    <div class="form-grid-2">
      <div class="form-group"><label class="form-label">Selling Price (₹)</label><input class="form-input" type="number" id="prodPrice" placeholder="0.00"></div>
    </div>
    <div class="form-grid-2">
      <div class="form-group"><label class="form-label">Unit/Weight</label><input class="form-input" type="text" id="prodUnit" placeholder="e.g. 500g, 1kg, 12 pcs"></div>
      <div class="form-group"><label class="form-label">Stock Quantity</label><input class="form-input" type="number" id="prodStock" placeholder="100"></div>
    </div>
    <div class="form-grid-2">
      <div class="form-group">
        <label class="form-label">Product Image</label>
        <input class="form-input" type="file" id="prodImageFile" accept="image/*">
        <div id="prodImagePreview" style="margin-top:10px; display:none;">
          <img id="prodImagePreviewImg" src="" style="width:60px; height:60px; border-radius:8px; object-fit:cover; border:1px solid var(--border);" onerror="this.src='../assets/images/placeholder.png';">
        </div>
      </div>
      <div class="form-group"><label class="form-label">Original Price (₹)</label><input class="form-input" type="number" id="prodOldPrice" placeholder="M.R.P or Old price"></div>
      <div class="form-group"><label class="form-label">Product Rating (0-5)</label><input class="form-input" type="number" id="prodRating" step="0.1" min="0" max="5" placeholder="4.5"></div>
    </div>
    <div class="form-grid-2">
      <div class="form-group">
        <label class="form-label">Show in Best Sellers?</label>
        <select class="form-input" id="prodIsBestseller">
          <option value="0">No, Standard Product</option>
          <option value="1">Yes, Top Selling Item</option>
        </select>
      </div>
    </div>
    <button class="form-btn" onclick="ProductAdmin.save()"><i data-lucide="save"></i> Save Product</button>
  </div>
</div>

<!-- ADD DELIVERY BOY MODAL -->
<div class="modal-overlay" id="addDeliveryModal" onclick="Modal.closeOnOverlay(event,'addDeliveryModal')">
  <div class="modal">
    <button class="modal-close" onclick="Modal.close('addDeliveryModal')"><i data-lucide="x"></i></button>
    <div class="modal-title"><i data-lucide="truck"></i> Add Delivery Partner</div>
    <div class="modal-sub">Register a new delivery boy</div>
    <div class="form-group"><label class="form-label">Full Name</label><input class="form-input" type="text" placeholder="Enter full name"></div>
    <div class="form-group"><label class="form-label">Phone Number</label><input class="form-input" type="tel" placeholder="+91 98765 43210"></div>
    <div class="form-grid-2">
      <div class="form-group"><label class="form-label">Vehicle Number</label><input class="form-input" type="text" placeholder="TN-01-AB-1234"></div>
      <div class="form-group"><label class="form-label">Aadhaar Number</label><input class="form-input" type="text" placeholder="XXXX XXXX XXXX"></div>
    </div>
    <div class="form-group"><label class="form-label">Email Address</label><input class="form-input" type="email" placeholder="partner@email.com"></div>
    <button class="form-btn" onclick="Modal.close('addDeliveryModal');Toast.show('Delivery partner registered!','success')"><i data-lucide="user-plus"></i> Register Partner</button>
  </div>
</div>

<!-- ORDER DETAILS MODAL -->
<div class="modal-overlay" id="orderDetailsModal" onclick="Modal.closeOnOverlay(event,'orderDetailsModal')">
  <div class="modal" style="max-width:600px">
    <button class="modal-close" onclick="Modal.close('orderDetailsModal')"><i data-lucide="x"></i></button>
    <div class="modal-title" id="modalOrderNum">#Order ID</div>
    <div class="modal-sub">Order details and management</div>
    
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:20px; padding:15px; background:var(--bg); border-radius:var(--radius-sm)">
        <div>
            <div style="font-size:12px; color:var(--text-muted); text-transform:uppercase; font-weight:800; margin-bottom:4px">Customer Info</div>
            <div id="modalCustName" style="font-weight:700">Name</div>
            <div id="modalCustPhone" style="font-size:13px"></div>
            <div id="modalCustEmail" style="font-size:13px"></div>
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
            <div style="font-size:12px; color:var(--text-muted); text-transform:uppercase; font-weight:800; margin-bottom:4px">Acccepted At</div>
            <div id="modalAcceptedAt" style="font-size:13px; font-weight:700; color:var(--primary)">—</div>
        </div>
        <div>
            <div style="font-size:12px; color:var(--text-muted); text-transform:uppercase; font-weight:800; margin-bottom:4px">Ready For Pickup At</div>
            <div id="modalReadyAt" style="font-size:13px; font-weight:700; color:#10b981">—</div>
        </div>
        <div style="grid-column: span 2">
            <div style="font-size:12px; color:var(--text-muted); text-transform:uppercase; font-weight:800; margin-bottom:4px">Shop / Vendor Info</div>
            <div id="modalShopName" style="font-weight:700; font-size:13px">Shop Name</div>
            <div id="modalShopAddress" style="font-size:12px; color:var(--text-muted); line-height:1.4">Shop Address</div>
            <div id="modalShopPhone" style="font-size:12px; margin-top:4px"></div>
            <div id="modalShopLocation" style="margin-top:8px"></div>
        </div>
        <div>
            <div style="font-size:12px; color:var(--text-muted); text-transform:uppercase; font-weight:800; margin-bottom:4px">Payment Info</div>
            <div id="modalPayment" style="font-size:13px; font-weight:700">COD</div>
        </div>
        <div>
            <div style="font-size:12px; color:var(--text-muted); text-transform:uppercase; font-weight:800; margin-bottom:4px">Bill Summary</div>
            <div style="display:flex; justify-content:space-between; font-size:12px; margin-bottom:4px"><span>Items Total:</span><span id="modalItemsTotal" style="font-weight:700">₹0.00</span></div>
            <div style="display:flex; justify-content:space-between; font-size:12px; margin-bottom:4px; color:#ef4444"><span id="modalCommissionLabel">Commission:</span><span id="modalCommission" style="font-weight:700">-₹0.00</span></div>
            <div style="display:flex; justify-content:space-between; font-size:13px; font-weight:700; color:#10b981; margin-bottom:8px; border-bottom:1px solid var(--border); padding-bottom:4px"><span>Vendor Earning:</span><span id="modalVendorEarning">₹0.00</span></div>
            
            <div style="display:flex; justify-content:space-between; font-size:12px; margin-bottom:2px"><span>Delivery Fee:</span><span id="modalDeliveryFee" style="font-weight:700">₹0.00</span></div>
            <div style="display:flex; justify-content:space-between; font-size:12px; margin-bottom:2px"><span>Platform Fee:</span><span id="modalPlatformFee" style="font-weight:700">₹0.00</span></div>
            <div style="display:flex; justify-content:space-between; font-size:12px; margin-bottom:8px"><span>Handling Fee:</span><span id="modalHandlingFee" style="font-weight:700">₹0.00</span></div>
            <div style="display:flex; justify-content:space-between; font-size:15px; font-weight:900; color:var(--primary); border-top:1px dashed var(--border); padding-top:8px"><span>Grand Total:</span><span id="modalTotal">₹0.00</span></div>
        </div>
    </div>

    <div class="admin-table-wrapper" style="margin-bottom:20px">
        <table class="admin-table">
            <thead><tr><th>Product &amp; Qty</th><th>Price</th><th>Subtotal</th></tr></thead>
            <tbody id="modalItemsTable"></tbody>
        </table>
    </div>

    <div id="refundActionGroup" style="display:none; margin-bottom: 20px; padding: 15px; border: 2px solid #fca5a5; border-radius: 12px; background: #fef2f2;">
        <div style="font-size: 13px; font-weight: 800; color: #b91c1c; margin-bottom: 5px; display: flex; align-items: center; gap: 8px;">
            <i data-lucide="alert-circle" style="width:16px; height:16px"></i> Action Required: Refund Pending
        </div>
        <p style="font-size: 11px; color: #7f1d1d; margin-bottom: 12px;">This order was paid online and has been cancelled. You must process the refund to return the money to the customer.</p>
        <button class="form-btn" id="btnProcessRefund" onclick="OrderAdmin.processRefund()" style="background:#ef4444; width:100%; justify-content: center;">
            <i data-lucide="refresh-ccw"></i> Process Razorpay Refund
        </button>
    </div>

    <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px">
        <div class="form-group">
            <label class="form-label">Update Status</label>
            <select class="filter-select" id="modalStatusSelect" onchange="OrderAdmin.updateStatus(document.getElementById('orderDetailsModal').dataset.orderId, this.value)">
                <!-- Populated dynamically by JS -->
            </select>
        </div>
        <div class="form-group">
            <label class="form-label">Assign Delivery Partner</label>
            <div style="display:flex; gap:8px">
                <select class="form-input" id="modalAssignSelect">
                    <option value="">Select Partner</option>
                </select>
                <button class="tbl-btn tbl-btn-primary" onclick="OrderAdmin.assignDelivery()" style="padding:0 12px; height:42px"><i data-lucide="check"></i></button>
            </div>
        </div>
    </div>
  </div>
</div>

<!-- ADD SHOP MODAL -->
<div class="modal-overlay" id="addShopModal" onclick="Modal.closeOnOverlay(event,'addShopModal')">
  <div class="modal">
    <button class="modal-close" onclick="Modal.close('addShopModal')"><i data-lucide="x"></i></button>
    <div class="modal-title" id="shopModalTitle"><i data-lucide="store"></i> Add New Shop</div>
    <div class="modal-sub">Register a single shop/vendor for the marketplace</div>
    <div class="form-group">
      <label class="form-label">Shop Name</label>
      <input class="form-input" type="text" id="shopName" placeholder="e.g. Village Fresh Store">
    </div>
    <div class="form-grid-2">
      <div class="form-group">
        <label class="form-label">Owner Name</label>
        <input class="form-input" type="text" id="shopOwner" placeholder="e.g. Siva">
      </div>
      <div class="form-group">
        <label class="form-label">Shop Email (Optional)</label>
        <input class="form-input" type="email" id="shopEmail" placeholder="e.g. shop@villagefoods.in">
      </div>
    </div>
    <div class="form-group" style="padding:15px; background:var(--bg-light); border:1px solid var(--border); border-radius:12px; margin-bottom:15px;">
      <h4 style="font-size:14px; font-weight:800; margin:0 0 12px 0; color:var(--text);">Vendor Login Details</h4>
      <div class="form-grid-2">
        <div class="form-group" style="margin-bottom:0;">
          <label class="form-label">Vendor Phone (Login)</label>
          <input class="form-input" type="tel" id="shopVendorPhone" placeholder="e.g. 9876543210">
        </div>
        <div class="form-group" style="margin-bottom:0;">
          <label class="form-label">Initial Password</label>
          <div style="position:relative">
            <input class="form-input" type="password" id="shopVendorPassword" placeholder="••••••••" style="padding-right:45px">
            <button type="button" class="btn-toggle-password" onclick="Utils.togglePassword('shopVendorPassword', this)" style="position:absolute; right:12px; top:50%; transform:translateY(-50%); background:none; border:none; color:var(--text-muted); cursor:pointer; display:flex; align-items:center; justify-content:center; padding:4px" tabindex="-1">
              <i data-lucide="eye" style="width:18px;height:18px"></i>
            </button>
          </div>
        </div>
      </div>
      <p style="font-size:11px; color:var(--text-muted); margin-top:8px; margin-bottom:0;">Enter phone number and password to automatically create a login for this shop vendor.</p>
    </div>
    <div class="form-group">
      <label class="form-label">Address / Location</label>
      <input class="form-input" type="text" id="shopAddress" placeholder="e.g. 56 South St">
    </div>
    
    <div style="margin-top:15px; border-top:1px solid var(--border); padding-top:15px;">
      <label class="form-label">Pick Shop Location on Map</label>
      <p style="font-size:12px; color:var(--text-muted); margin-bottom:10px;">Click on the map to set the shop's exact location.</p>
      <div id="shopMap" style="height:400px; border-radius:8px; border:1px solid var(--border); z-index:1;"></div>
      <div class="form-grid-2" style="margin-top:10px;">
        <div class="form-group">
          <label class="form-label">Latitude</label>
          <input class="form-input" type="text" id="shopLat" placeholder="Enter Latitude">
        </div>
        <div class="form-group">
          <label class="form-label">Longitude</label>
          <input class="form-input" type="text" id="shopLng" placeholder="Enter Longitude">
        </div>
      </div>
    </div>
    <div class="form-group">
      <label class="form-label">Status</label>
      <select class="form-input" id="shopStatus">
        <option value="active">Active (Open)</option>
        <option value="inactive">Inactive (Closed)</option>
      </select>
    </div>
    <div class="form-group">
      <label class="form-label">Shop Categories</label>
      <div id="shopCategoriesContainer" style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; padding: 10px; border: 1px solid var(--border); border-radius: 8px; max-height: 150px; overflow-y: auto;">
        <!-- Categories will be loaded here as checkboxes -->
      </div>
    </div>
    <div class="form-group">
      <label class="form-label">Shop Image</label>
      <input class="form-input" type="file" id="shopImageFile" accept="image/*">
      <div id="shopImagePreview" style="margin-top:10px; display:none;">
        <img id="shopImagePreviewImg" src="" style="width:100px; height:80px; border-radius:8px; object-fit:cover; border:1px solid var(--border);" onerror="this.src='../assets/images/placeholder.png';">
      </div>
    </div>
    <button class="form-btn" onclick="ShopAdmin.save()"><i data-lucide="save"></i> Save Shop</button>
  </div>
</div>

<!-- ADD CATEGORY MODAL -->
<div class="modal-overlay" id="addCategoryModal" onclick="Modal.closeOnOverlay(event,'addCategoryModal')">
  <div class="modal">
    <button class="modal-close" onclick="Modal.close('addCategoryModal')"><i data-lucide="x"></i></button>
    <div class="modal-title" id="catModalTitle"><i data-lucide="plus-circle"></i> Add New Category</div>
    <div class="modal-sub">Create a new category for your products</div>
    <div class="form-group">
      <label class="form-label">Category Name</label>
      <input class="form-input" type="text" id="catName" placeholder="e.g. Fruits & Vegetables">
    </div>
    <div class="form-group">
      <label class="form-label">Slug (Optional)</label>
      <input class="form-input" type="text" id="catSlug" placeholder="e.g. fruits-veg">
    </div>
    <div class="form-group">
      <label class="form-label">Category Image (System Upload)</label>
      <input class="form-input" type="file" id="catImageFile" accept="image/*">
      <div id="catImagePreview" style="margin-top:10px; display:none;">
        <img id="catImagePreviewImg" src="" style="width:60px; height:60px; border-radius:50%; object-fit:cover; border:2px solid var(--border);" onerror="this.src='../assets/images/placeholder.png';">
      </div>
    </div>
    <button class="form-btn" onclick="CategoryAdmin.save()"><i data-lucide="save"></i> Save Category</button>
  </div>
</div>

<!-- DELIVERY BOY MODAL -->
<div class="modal-overlay" id="deliveryBoyModal" onclick="Modal.closeOnOverlay(event,'deliveryBoyModal')">
    <div class="modal" style="max-width:600px">
        <button class="modal-close" onclick="Modal.close('deliveryBoyModal')"><i data-lucide="x"></i></button>
        <div class="modal-title">Partner Credentials & Verification</div>
        <div class="modal-sub">View and verify partner credentials</div>

        <div id="dbProfileHeader" style="display:flex; align-items:center; gap:15px; margin-bottom:20px; padding:10px; background:var(--bg-light); border-radius:12px; border:1px solid var(--border)">
            <div id="dbAvatar" style="width:60px; height:60px; border-radius:12px; background:var(--primary); color:white; display:flex; align-items:center; justify-content:center; font-size:24px; font-weight:800; overflow:hidden">
                <img id="dbProfileImg" src="" style="width:100%; height:100%; object-fit:cover; display:none">
                <span id="dbAvatarInitial">P</span>
            </div>
            <div>
                <div id="dbHeaderName" style="font-weight:700; font-size:16px; color:var(--text)">Partner Name</div>
                <div id="dbHeaderEmail" style="font-size:12px; color:var(--text-muted)">partner@email.com</div>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Full Name</label>
            <input class="form-input" type="text" id="dbName" placeholder="Enter name">
        </div>
        <div class="form-grid-2">
            <div class="form-group">
                <label class="form-label">Phone Number</label>
                <input class="form-input" type="text" id="dbPhone" placeholder="10-digit phone">
            </div>
            <div class="form-group">
                <label class="form-label">Email Address</label>
                <input class="form-input" type="email" id="dbEmail" placeholder="partner@email.com">
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">New Password (leave blank to keep current)</label>
            <div style="position:relative">
                <input class="form-input" type="password" id="dbPassword" placeholder="••••••••" style="padding-right:45px">
                <button type="button" class="btn-toggle-password" onclick="Utils.togglePassword('dbPassword', this)" style="position:absolute; right:12px; top:50%; transform:translateY(-50%); background:none; border:none; color:var(--text-muted); cursor:pointer; display:flex; align-items:center; justify-content:center; padding:4px" tabindex="-1">
                    <i data-lucide="eye" style="width:18px;height:18px"></i>
                </button>
            </div>
        </div>
        
        <button type="button" class="form-btn" onclick="DeliveryAdmin.save()" style="width:100%; margin-top:20px; background:var(--primary); font-weight:700">
            <i data-lucide="save"></i> Save Profile Changes
        </button>

        <!-- Verification & Extended Details Section -->
        <div id="dbExtendedDetails" style="display:none; border-top:1px solid var(--border); margin-top:15px; padding-top:15px;">
            <div style="font-size:12px; color:var(--text-muted); text-transform:uppercase; font-weight:800; margin-bottom:10px">Extended Profile & Verification</div>
            
            <div style="display:grid; grid-template-columns:repeat(3, 1fr); gap:12px; margin-bottom:15px; font-size:13px;">
                <div class="info-box">
                    <div style="color:var(--text-muted); font-size:11px; margin-bottom:2px">Vehicle</div>
                    <div id="dbVehicleInfo" style="font-weight:700">—</div>
                </div>
                <div class="info-box">
                    <div style="color:var(--text-muted); font-size:11px; margin-bottom:2px">License No.</div>
                    <div id="dbLicenseInfo" style="font-weight:700">—</div>
                </div>
                <div class="info-box">
                    <div style="color:var(--text-muted); font-size:11px; margin-bottom:2px">Verification</div>
                    <div id="dbVerifyStatus" style="font-weight:700">Pending</div>
                </div>
            </div>

            <div style="background:var(--bg-light); border:1px solid var(--border); padding:15px; border-radius:12px; margin-bottom:15px;">
                <div style="color:var(--text-muted); font-size:11px; margin-bottom:10px; text-transform:uppercase; font-weight:800; border-bottom:1px solid var(--border); padding-bottom:5px">Bank Account Details</div>
                <div id="dbBankInfo" style="display:grid; grid-template-columns:1fr 1fr; gap:12px 20px;">
                    <!-- Populated via JS -->
                </div>
            </div>

            <div style="display:flex; gap:8px; margin-bottom:15px;">
                <button type="button" id="btnViewLicense" class="tbl-btn-edit" style="flex:1; padding:8px; font-size:11px"><i data-lucide="file-text"></i> License</button>
                <button type="button" id="btnViewAadhaar" class="tbl-btn-edit" style="flex:1; padding:8px; font-size:11px"><i data-lucide="file-text"></i> Aadhaar</button>
                <button type="button" id="btnViewRC" class="tbl-btn-edit" style="flex:1; padding:8px; font-size:11px"><i data-lucide="file-text"></i> RC</button>
            </div>

            <div id="verifyActions" style="display:flex; gap:10px;">
                <button type="button" class="form-btn" onclick="DeliveryAdmin.verify('Verified')" style="background:var(--primary); flex:1"><i data-lucide="check-circle"></i> Approve Partner</button>
                <button type="button" class="form-btn" onclick="DeliveryAdmin.verify('Rejected')" style="background:#ef4444; flex:1"><i data-lucide="x-circle"></i> Reject</button>
            </div>
        </div>

        </div>
    </div>
</div>

<script src="../assets/js/utils.js"></script>
<script src="../assets/js/admin.js?v=2.0"></script>
<script>
  document.addEventListener('DOMContentLoaded', () => {
    if (typeof lucide !== 'undefined') {
      lucide.createIcons();
    }
    window.addEventListener('load', () => {
      if (typeof NotificationEngine !== 'undefined') {
        NotificationEngine.init();
      }
    });
  });
</script>
</body>
</html>