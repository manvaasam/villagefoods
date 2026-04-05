<!-- ====== NAVBAR ====== -->
<nav class="navbar <?= isset($isSimplified) && $isSimplified ? 'navbar-simplified' : '' ?>">
  <div class="nav-main">
    <!-- Logo -->
    <a href="index" class="nav-logo">
      <img src="assets/images/logo/VillageFoods Delivery Logo.png" alt="Village Foods" class="nav-logo-img">
    </a>

    <?php if (isset($isSimplified) && $isSimplified): ?>
        <!-- Simplified Header Actions -->
        <div class="nav-actions">
            <div class="secure-badge hide-mobile">
                <i data-lucide="shield-check" style="color:var(--primary)"></i>
                <div style="text-align:left">
                    <div style="font-size:11px; font-weight:800; color:var(--text); line-height:1">SECURE</div>
                    <div style="font-size:9px; color:var(--text-muted); line-height:1">CHECKOUT</div>
                </div>
            </div>
            <a href="index" class="nav-btn nav-btn-outline" style="border-radius:var(--radius-sm); padding:8px 16px">
                <i data-lucide="arrow-left" style="width:16px; height:16px"></i>
                <span class="hide-mobile" style="font-size:13px; font-weight:800">Back to Store</span>
            </a>
        </div>
    <?php else: ?>
        <!-- Location Indicator -->
        <div class="nav-location" onclick="LocationModal.open()">
          <div class="nav-location-circle">
              <i data-lucide="map-pin"></i>
          </div>
          <div class="nav-location-content">
              <span class="nav-location-label">Deliver to</span>
              <strong id="userLocation" class="nav-location-value">Select Address</strong>
          </div>
          <i data-lucide="chevron-down" class="nav-location-chevron"></i>
        </div>

        <!-- Actions -->
        <div class="nav-actions">
          <a href="track-order" class="nav-btn nav-btn-outline hide-mobile" style="border-radius:var(--radius-sm); border-color:transparent">
            <i data-lucide="package" style="width:18px;height:18px"></i>
            <span style="font-size:13px; font-weight:700">Track Order</span>
          </a>
          <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true): ?>
              <div class="user-dropdown">
                  <button class="nav-btn nav-btn-outline" onclick="Auth.toggleUserDropdown()">
                      <div class="user-avatar"><?= strtoupper(substr($_SESSION['user_name'] ?? 'U', 0, 1)) ?></div>
                      <span class="user-name-text hide-mobile"><?= explode(' ', $_SESSION['user_name'] ?? 'User')[0] ?></span>
                      <i data-lucide="chevron-down" style="width:14px;height:14px"></i>
                  </button>
                  <div class="user-dropdown-content" id="userDropdownContent">
                      <a href="profile"><i data-lucide="user" style="width:16px;height:16px"></i> My Account</a>
                      <a href="orders"><i data-lucide="shopping-bag" style="width:16px;height:16px"></i> My Orders</a>
                      <a href="wishlist"><i data-lucide="heart" style="width:16px;height:16px"></i> Wishlist</a>
                      <div class="dropdown-divider"></div>
                      <a href="api/auth/logout.php" class="logout-link"><i data-lucide="log-out" style="width:16px;height:16px"></i> Logout</a>
                  </div>
              </div>
          <?php else: ?>
              <button class="nav-btn nav-btn-outline" onclick="Modal.open('loginModal')">
                <i data-lucide="user" class="show-mobile-only"></i> 
                <span class="hide-mobile">Login / Create Account</span>
              </button>
          <?php endif; ?>

          <button class="cart-btn" onclick="Cart.toggleSidebar()">
            <i data-lucide="shopping-cart"></i> 
            <span class="cart-btn-text hide-mobile">My Cart</span>
            <span class="cart-count" id="cartCount">0</span>
          </button>
        </div>
    <?php endif; ?>
  </div>
</nav>
