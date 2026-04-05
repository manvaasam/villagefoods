<!-- ====== CART SIDEBAR ====== -->
<div class="cart-sidebar" id="cartSidebar">
  <div class="cart-header">
    <h3><i data-lucide="shopping-cart" style="width:18px;height:18px;display:inline;vertical-align:middle;margin-right:6px"></i> Your Cart</h3>
    <button class="cart-close" onclick="Cart.closeSidebar()"><i data-lucide="x" style="width:18px;height:18px"></i></button>
  </div>
  <div class="cart-items" id="cartItems">
    <div class="cart-empty">
      <div class="empty-icon"><i data-lucide="shopping-cart" style="width:48px;height:48px"></i></div>
      <div class="empty-title">Your cart is empty</div>
      <div class="empty-sub">Add items to get started</div>
    </div>
  </div>
  <div class="cart-footer" id="cartFooter" style="display:none">
    <div class="cart-summary">
      <!-- Pricing Progress Bar -->
      <div id="pricingProgressBarContainer" style="margin-bottom: 16px; display:none;">
        <div style="display:flex; justify-content:space-between; font-size:11px; font-weight:700; margin-bottom:6px;">
          <span id="progressBarLabel" style="color:var(--text-main)">Progress to Free Delivery</span>
          <span id="progressBarRemaining" style="color:var(--primary)">₹0 more</span>
        </div>
        <div class="pricing-progress-bg" style="height:6px; background:#e2e8f0; border-radius:10px; overflow:hidden;">
          <div id="pricingProgressBarFill" style="height:100%; width:0%; background:var(--primary); transition: width 0.4s ease;"></div>
        </div>
      </div>

      <div class="cart-row"><span>Subtotal</span><span id="cartSubtotal">₹0</span></div>
      <div class="cart-row">
        <span>Delivery Fee</span>
        <span id="cartDeliveryFee">₹0</span>
      </div>
      <div id="cartDeliveryExplanation" style="font-size:11px; margin-top:-2px; margin-bottom:8px; font-weight:700; display:none;"></div>
      
      <div class="cart-row"><span>Platform Fee</span><span id="cartPlatformFee">₹0</span></div>
      <div class="cart-row"><span>Handling Fee</span><span id="cartHandlingFee">₹0</span></div>
      <div class="cart-row total"><span>Total</span><span id="cartTotal">₹0</span></div>
    </div>
    <button class="checkout-btn" onclick="Auth.proceedToCheckout()">
      <span>Proceed to Checkout</span>
      <span id="checkoutTotal">₹0 →</span>
    </button>
  </div>
</div>

<!-- ====== LOGIN MODAL ====== -->
<div class="modal-overlay" id="loginModal" onclick="Modal.closeOnOverlay(event,'loginModal')">
  <div class="modal">
    <button class="modal-close" onclick="Modal.close('loginModal')"><i data-lucide="x"></i></button>
    <div class="modal-icon"><i data-lucide="leaf" style="width:40px;height:40px;color:var(--primary)"></i></div>
    <div class="modal-title" id="loginModalTitle" style="text-align:center">Welcome to Village Foods</div>
    <div class="modal-sub" id="loginModalSub" style="text-align:center">Sign in to sync your cart and checkout</div>
    
    <!-- Email/Name View -->
    <div id="emailView">
        <div class="form-group" id="nameFieldGroup">
            <label class="form-label">Full Name</label>
            <input class="form-input" type="text" id="loginName" placeholder="Enter your name">
            <div class="invalid-feedback" id="loginNameError" style="display:none"></div>
        </div>
        <div class="form-group">
            <label class="form-label">Email Address</label>
            <input class="form-input" type="email" id="loginEmail" placeholder="name@example.com">
        </div>
        <button class="form-btn" id="btnSendOtp" onclick="Auth.handleLoginStep()">Continue</button>
    </div>

    <!-- OTP View (Hidden Initially) -->
    <div id="otpView" style="display:none">
        <div class="form-group">
            <label class="form-label">Enter 6-digit OTP</label>
            <div style="display:flex; gap:8px; justify-content:center">
                <input class="form-input" style="text-align:center; font-size:24px; letter-spacing:8px; width:200px" 
                       type="text" id="loginOtp" maxlength="6" placeholder="000000">
            </div>
            <div style="font-size:12px; text-align:center; margin-top:8px; color:var(--text-muted)">
                OTP sent to <strong id="displayEmail"></strong>
            </div>
        </div>
        <button class="form-btn" id="btnVerifyOtp" onclick="Auth.verifyOtp()">Verify & Continue</button>
        <div class="form-link">Didn't receive code? <a href="javascript:void(0)" onclick="Auth.sendOtp()">Resend</a></div>
    </div>

    <div class="form-link" style="margin-top:20px; font-size:11px">By continuing, you agree to our <a href="#">Terms</a> & <a href="#">Privacy Policy</a></div>
  </div>
</div>

<!-- ====== CHECKOUT MODAL ====== -->
<div class="modal-overlay" id="checkoutModal" onclick="Modal.closeOnOverlay(event,'checkoutModal')">
  <div class="modal" style="max-width:520px">
    <button class="modal-close" onclick="Modal.close('checkoutModal')"><i data-lucide="x"></i></button>
    <div class="modal-title"><i data-lucide="package" style="width:18px;height:18px;display:inline;vertical-align:middle;margin-right:6px"></i> Complete Your Order</div>
    <div class="modal-sub">Just a few more details!</div>
    <div id="checkoutFreeDeliveryMsg" style="margin-bottom:12px; font-weight:700; color:var(--primary); font-size:13px; display:none;"></div>

    <div class="form-group">
      <label class="form-label"><i data-lucide="map-pin" style="width:14px;height:14px;display:inline;vertical-align:middle;margin-right:4px"></i> Delivery Address</label>
      <input class="form-input" type="text" placeholder="Enter your full delivery address" id="deliveryAddress">
    </div>
    <div id="locationStatus" style="font-size:12px;margin-top:-8px;margin-bottom:16px">
      <button onclick="Geo.updateCheckoutLocation()" style="background:var(--primary-pale);color:var(--primary);border:none;padding:6px 14px;border-radius:6px;font-size:12px;font-weight:700;cursor:pointer">
        <i data-lucide="map-pin" style="width:12px;height:12px;display:inline;vertical-align:middle;margin-right:4px"></i> Auto-detect My Location
      </button>
    </div>

    <div class="form-group">
      <label class="form-label"><i data-lucide="credit-card" style="width:14px;height:14px;display:inline;vertical-align:middle;margin-right:4px"></i> Payment Method</label>
      <div class="payment-exclusive-card" style="padding:16px; border:1.5px solid var(--primary); border-radius:var(--radius-sm); text-align:center; background:var(--primary-pale)">
        <div style="font-size:20px"><i data-lucide="credit-card" style="width:24px; height:24px; color:var(--primary)"></i></div>
        <div style="font-size:13px; font-weight:800; color:var(--primary); margin-top:6px">Razorpay Secure Payment</div>
        <div style="font-size:10px; color:var(--text-muted); margin-top:2px">Cards, UPI, NetBanking, Wallets</div>
      </div>
    </div>

    <div style="background:var(--bg);border-radius:var(--radius-sm);padding:16px;margin-bottom:16px">
      <div class="cart-row"><span>Subtotal</span><span id="modal-subtotal">₹0</span></div>
      <div class="cart-row">
        <span style="display:flex; align-items:center; gap:4px">Delivery <i data-lucide="info" style="width:12px; height:12px; color:var(--text-muted)"></i></span>
        <span id="modal-delivery">₹0</span>
      </div>
      <div id="modal-delivery-explanation" style="font-size:10px; margin-top:-2px; margin-bottom:8px; font-weight:700; display:none;"></div>
      
      <div class="cart-row"><span>Platform Fee</span><span id="modal-platform">₹0</span></div>
      <div class="cart-row"><span>Handling Fee</span><span id="modal-handling">₹0</span></div>
      <div class="cart-row total"><span>Total Amount</span><span id="modal-total">₹0</span></div>
    </div>

    <button class="form-btn" onclick="placeOrder()"><i data-lucide="shield-check" style="width:16px;height:16px;display:inline;vertical-align:middle;margin-right:6px"></i> Pay Securely &amp; Place Order</button>
  </div>
</div>
