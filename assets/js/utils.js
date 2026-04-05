/* =============================================
   VILLAGE FOODS — SHARED UTILITIES JS
   ============================================= */

'use strict';

const Utils = {
  isValidEmail: (email) => {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
  },
  togglePassword: (inputId, btn) => {
    const input = document.getElementById(inputId);
    if (!input) return;
    const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
    input.setAttribute('type', type);
    const icon = btn.querySelector('i');
    if (icon) {
      icon.setAttribute('data-lucide', type === 'text' ? 'eye-off' : 'eye');
      if (window.lucide) {
        // PERF: Only update icons within this button
        lucide.createIcons({
          attrs: { 'data-lucide': true },
          scope: btn
        });
      }
    }
  },
  escapeHTML: (str) => {
    if (!str) return '';
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
  }
};

// ======= TOAST NOTIFICATIONS =======
const Toast = (() => {
  let container;

  function init() {
    container = document.getElementById('toastContainer');
    if (!container) {
      container = document.createElement('div');
      container.id = 'toastContainer';
      container.className = 'toast-container';
      document.body.appendChild(container);
    }
  }

  function show(message, type = '', duration = 2800) {
    if (!container) init();
    const toast = document.createElement('div');
    toast.className = `toast${type ? ' toast-' + type : ''}`;
    toast.textContent = message;
    container.appendChild(toast);

    setTimeout(() => {
      toast.style.opacity = '0';
      toast.style.transform = 'translateY(10px)';
      toast.style.transition = 'all 0.3s';
      setTimeout(() => toast.remove(), 350);
    }, duration);
  }

  return { show, init };
})();

// ======= MODAL SYSTEM =======
const Modal = (() => {
  function open(id) {
    const el = document.getElementById(id);
    if (el) {
      el.classList.add('open');
      if (window.lucide) {
        // PERF: Only update icons within this modal
        lucide.createIcons({
          attrs: { 'data-lucide': true },
          scope: el
        });
      }
    }
  }

  function close(id) {
    const el = document.getElementById(id);
    if (el) el.classList.remove('open');
  }

  function closeOnOverlay(event, id) {
    if (event.target === document.getElementById(id)) close(id);
  }

  function init() {
    // ESC key closes modals
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') {
        document.querySelectorAll('.modal-overlay.open').forEach(el => {
          el.classList.remove('open');
        });
      }
    });
  }

  return { open, close, closeOnOverlay, init };
})();

// ======= GEOLOCATION =======
const Geo = (() => {
  function detect(onSuccess, onError) {
    if (!navigator.geolocation) {
      if (onError) onError('Geolocation not supported');
      return;
    }
    navigator.geolocation.getCurrentPosition(
      (pos) => {
        const { latitude, longitude } = pos.coords;
        if (onSuccess) onSuccess({ latitude, longitude });
      },
      (err) => {
        if (onError) onError(err.message);
      },
      { enableHighAccuracy: true, timeout: 10000 }
    );
  }

  function updateCheckoutLocation() {
    const statusEl = document.getElementById('locationStatus');
    const inputEl = document.getElementById('deliveryAddress');

    if (statusEl) statusEl.innerHTML = '<span style="color:var(--text-muted);font-size:12px;font-weight:600"><i data-lucide="map-pin" style="width:12px;height:12px;display:inline;vertical-align:middle;margin-right:4px"></i> Detecting location...</span>';

    detect(
      ({ latitude, longitude }) => {
        if (inputEl) inputEl.value = `Near: Thirupathur, Tamil Nadu (${latitude.toFixed(4)}, ${longitude.toFixed(4)})`;
        if (statusEl) statusEl.innerHTML = '<span style="color:var(--primary);font-size:12px;font-weight:700"><i data-lucide="check" style="width:12px;height:12px;display:inline;vertical-align:middle;margin-right:4px"></i> Location detected successfully!</span>';
        Toast.show('Location detected!', 'success');
        if (window.lucide) lucide.createIcons();
      },
      () => {
        if (statusEl) statusEl.innerHTML = '<span style="color:var(--accent);font-size:12px;font-weight:600"><i data-lucide="alert-circle" style="width:12px;height:12px;display:inline;vertical-align:middle;margin-right:4px"></i> Could not detect location. Please enter manually.</span>';
        Toast.show('Could not get location', 'error');
        if (window.lucide) lucide.createIcons();
      }
    );
  }

  return { detect, updateCheckoutLocation };
})();

// ======= AUTHENTICATION (EMAIL OTP) =======
const Auth = (() => {
  let userEmail = '';

  async function checkStatus() {
    try {
      const res = await fetch('api/auth/check.php');
      return await res.json();
    } catch (e) { return { logged_in: false }; }
  }

  async function handleLoginStep() {
    const nameEl = document.getElementById('loginName');
    const emailEl = document.getElementById('loginEmail');
    const name = nameEl ? nameEl.value.trim() : '';
    const email = emailEl ? emailEl.value.trim() : '';

    if (!name) { Toast.show('Please enter your full name', 'warning'); return; }
    
    const isNameValid = Validation.validateInput(nameEl, 'alpha', "Only letters and spaces are allowed", document.getElementById('loginNameError'));
    if (!isNameValid) {
        Toast.show('Please enter a valid name (alphabets only)', 'error');
        return;
    }

    if (!email) { Toast.show('Please enter your email', 'warning'); return; }
    if (!Utils.isValidEmail(email)) { Toast.show('Please enter a valid email address', 'error'); return; }

    userEmail = email;
    sendOtp(email, name);
  }

  async function sendOtp(emailInput, nameInput) {
    const email = emailInput || (document.getElementById('loginEmail') ? document.getElementById('loginEmail').value.trim() : '');
    const name = nameInput || (document.getElementById('loginName') ? document.getElementById('loginName').value.trim() : '');

    if (!email) {
      Toast.show('Please enter your email', 'warning');
      return;
    }

    if (!Utils.isValidEmail(email)) {
      Toast.show('Please enter a valid email address', 'error');
      return;
    }

    userEmail = email;

    const btn = document.getElementById('btnSendOtp');
    const originalText = btn.textContent;
    btn.textContent = 'Sending...';
    btn.disabled = true;

    try {
      const res = await fetch('api/auth/send_otp.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email, name })
      });
      const data = await res.json();

      if (data.status === 'success') {
        document.getElementById('displayEmail').textContent = email;
        document.getElementById('emailView').style.display = 'none';
        document.getElementById('otpView').style.display = 'block';
        document.getElementById('loginModalTitle').textContent = 'Verify Email';
        document.getElementById('loginModalSub').textContent = 'Enter the 6-digit code sent to your inbox';
        Toast.show('OTP sent to your email!', 'success');
      } else {
        Toast.show(data.message, 'error');
      }
    } catch (e) {
      Toast.show('Failed to send OTP. Try again.', 'error');
    } finally {
      btn.disabled = false;
      btn.textContent = originalText || 'Continue';
    }
  }

  async function verifyOtp() {
    const otpInput = document.getElementById('loginOtp');
    const otp = otpInput.value.trim();

    if (otp.length !== 6) {
      Toast.show('Please enter the 6-digit OTP', 'error');
      return;
    }

    const btn = document.getElementById('btnVerifyOtp');
    btn.disabled = true;
    btn.textContent = 'Verifying...';

    try {
      const res = await fetch('api/auth/verify_otp.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email: userEmail, otp: otp })
      });
      const data = await res.json();

      if (data.status === 'success') {
        // Save address to localStorage if returned
        if (data.address) {
          localStorage.setItem('userAddress', JSON.stringify(data.address));
        }
        
        Toast.show('Logged in successfully!', 'success');
        Modal.close('loginModal');
        if (data.role === 'delivery') {
          window.location.href = 'delivery-dashboard.php';
        } else if (window._pendingCheckout) {
          window.location.href = 'checkout.php';
        } else {
          location.reload();
        }
      } else {
        Toast.show(data.message, 'error');
      }
    } catch (e) {
      Toast.show('Verification failed. Try again.', 'error');
    } finally {
      btn.disabled = false;
      btn.textContent = 'Verify & Continue';
    }
  }

  async function proceedToCheckout() {
    const status = await checkStatus();
    if (status.logged_in) {
      window.location.href = 'checkout.php';
    } else {
      window._pendingCheckout = true;
      Modal.open('loginModal');
    }
  }

  function toggleUserDropdown() {
    const content = document.getElementById('userDropdownContent');
    if (content) content.classList.toggle('show');
  }

  // Close dropdown on outside click
  document.addEventListener('click', (e) => {
    if (!e.target.closest('.user-dropdown')) {
      const content = document.getElementById('userDropdownContent');
      if (content) content.classList.remove('show');
    }
  });

  return { checkStatus, handleLoginStep, sendOtp, verifyOtp, proceedToCheckout, toggleUserDropdown };
})();

// ======= WISHLIST SYSTEM =======
const Wishlist = (() => {
  async function toggle(event, productId) {
    if (event) {
      event.preventDefault();
      event.stopPropagation();
    }

    try {
      const res = await fetch('api/products/toggle_wishlist.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ product_id: productId })
      });
      const data = await res.json();

      if (data.status === 'success') {
        Toast.show(data.message, 'success');
        
        // Update UI
        const btns = document.querySelectorAll(`button[onclick*="Wishlist.toggle(event, ${productId})"]`);
        btns.forEach(btn => {
          const icon = btn.querySelector('i');
          if (data.action === 'added') {
            btn.classList.add('active');
            if (icon) icon.classList.add('heart-filled');
          } else {
            btn.classList.remove('active');
            if (icon) icon.classList.remove('heart-filled');
          }
        });
        
        // If on wishlist page, remove the item
        if (window.location.pathname.includes('wishlist.php') && data.action === 'removed') {
          const card = document.querySelector(`.product-card[data-id="${productId}"]`);
          if (card) {
            card.classList.add('fade-out');
            setTimeout(() => {
              card.remove();
              if (document.querySelectorAll('.product-card').length === 0) {
                location.reload();
              }
            }, 300);
          }
        }
      } else {
        if (data.message.includes('login')) {
          Modal.open('loginModal');
        }
        Toast.show(data.message, 'error');
      }
    } catch (e) {
      Toast.show('Failed to update wishlist', 'error');
    }
  }

  return { toggle };
})();

// ======= CART SYSTEM =======
const Cart = (() => {
  let items = JSON.parse(localStorage.getItem('villageCart') || '{}');
  let products = [];
  
  // Pricing Constants (with dynamic fallbacks)
  const PLATFORM_FEE = window.APP_SETTINGS?.platformFee ?? 10.00;
  const HANDLING_CHARGE = window.APP_SETTINGS?.handlingFee ?? 10.00;
  const FIXED_DELIVERY_CHARGE = window.APP_SETTINGS?.deliveryFee ?? 40.00;

  function setProducts(prods) {
    products = prods;
  }

  function _getDistance() {
    const saved = localStorage.getItem('userAddress');
    if (!saved) return null;
    const addr = JSON.parse(saved);
    const cartItemsList = getItems();
    if (cartItemsList.length === 0) return null;
    
    // All items are from same shop, so we just take the first one or the shop_id from the first item
    const p = cartItemsList[0].product;
    if (!p.shop_lat || !p.shop_lng) return null;

    // Haversine calculation for JS
    const R = 6371; // km
    const dLat = (p.shop_lat - addr.latitude) * Math.PI / 180;
    const dLon = (p.shop_lng - addr.longitude) * Math.PI / 180;
    const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
              Math.cos(addr.latitude * Math.PI / 180) * Math.cos(p.shop_lat * Math.PI / 180) * 
              Math.sin(dLon/2) * Math.sin(dLon/2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
    return R * c;
  }

  function add(id) {
    id = parseInt(id);
    if (isNaN(id)) return;
    
    const p = _getProduct(id);
    if (!p) return;

    const existingItems = getItems();
    if (existingItems.length > 0) {
      const firstItemShopId = existingItems[0].product.shop_id;
      if (p.shop_id && firstItemShopId && p.shop_id !== firstItemShopId) {
        Toast.show('Your cart contains items from another shop. Please clear it first.', 'warning');
        return;
      }
    }

    const currentQty = items[id] || 0;
    if (p.stock !== undefined && (currentQty + 1) > p.stock) {
      Toast.show(`Only ${p.stock} units available for ${p.name}`, 'warning');
      return;
    }

    items[id] = currentQty + 1;
    _save();
    _onUpdate(id);
    Toast.show(`${p.name} added to cart`, 'success');
  }

  function remove(id) {
    id = parseInt(id);
    if (isNaN(id)) return;
    
    if (items[id] && items[id] > 0) {
      items[id]--;
      if (items[id] <= 0) delete items[id];
    } else {
      delete items[id];
    }
    _save();
    _onUpdate(id);
  }

  function deleteItem(id) {
    id = parseInt(id);
    if (isNaN(id)) return;
    
    if (items[id] !== undefined) {
      const p = _getProduct(id);
      delete items[id];
      _save();
      _onUpdate(id);
      if (p) Toast.show(`${p.name} removed from cart`, 'info');
    }
  }

  function getQty(id) {
    return items[parseInt(id)] || 0;
  }

  function getTotalItems() {
    // Count total quantity instead of unique keys for better UX
    // Also ensures we filter out any junk keys
    return Object.entries(items).reduce((sum, [id, qty]) => {
      const q = parseInt(qty);
      return (!isNaN(parseInt(id)) && q > 0) ? sum + q : sum;
    }, 0);
  }

  function getSubtotal() {
    return Object.entries(items).reduce((sum, [id, qty]) => {
      const p = _getProduct(parseInt(id));
      return sum + (p ? p.price * qty : 0);
    }, 0);
  }

  function getPlatformFee() { return PLATFORM_FEE; }
  function getHandlingCharge() { return HANDLING_CHARGE; }

  function getDeliveryCharge() {
    const sub = getSubtotal();
    return sub > 0 ? FIXED_DELIVERY_CHARGE : 0;
  }

  function getTotal() {
    const sub = getSubtotal();
    if (sub === 0) return 0;
    return sub + getPlatformFee() + getHandlingCharge() + getDeliveryCharge();
  }

  function clear() {
    items = {};
    _save();
    _refreshAll();
    _updateCountBadge();
    _updateSidebar();
  }

  function getItems() {
    return Object.entries(items).map(([id, qty]) => ({
      product: _getProduct(parseInt(id)),
      qty
    })).filter(x => x.product);
  }

  function _getProduct(id) {
    return products.find(p => p.id === id) || null;
  }

  function _onUpdate(id) {
    _updateBtnState(id);
    _updateCountBadge();
    _updateSidebar();
    _animateCartIcon();
  }

  function _save() {
    localStorage.setItem('villageCart', JSON.stringify(items));
  }

  function _updateBtnState(id) {
    const btnDiv = document.getElementById(`btn-${id}`);
    if (!btnDiv) return;
    const qty = getQty(id);
    if (qty === 0) {
      btnDiv.innerHTML = `<button class="add-btn" onclick="Cart.add(${id})">+</button>`;
    } else {
      btnDiv.innerHTML = `
        <div class="qty-ctrl">
          <button class="qty-btn" onclick="Cart.remove(${id})">−</button>
          <span class="qty-num">${qty}</span>
          <button class="qty-btn" onclick="Cart.add(${id})">+</button>
        </div>`;
    }
  }

  function _refreshAll() {
    products.forEach(p => _updateBtnState(p.id));
  }

  function init() {
    _updateCountBadge();
    _updateSidebar();
    _refreshAll();
  }

  function _updateCountBadge() {
    const badge = document.getElementById('cartCount');
    const badgeMobile = document.getElementById('mobileCartCount');
    const count = getTotalItems();
    
    if (badge) {
      badge.textContent = count;
      badge.style.display = count > 0 ? 'flex' : 'none';
    }
    if (badgeMobile) {
      badgeMobile.textContent = count;
      badgeMobile.style.display = count > 0 ? 'flex' : 'none';
    }
  }

  function _updateSidebar() {
    const cartItems = document.getElementById('cartItems');
    const cartFooter = document.getElementById('cartFooter');
    
    // Sidebar elements
    const subtotalEl = document.getElementById('cartSubtotal');
    const deliveryEl = document.getElementById('cartDeliveryFee');
    const platformEl = document.getElementById('cartPlatformFee');
    const handlingEl = document.getElementById('cartHandlingFee');
    const totalEl = document.getElementById('cartTotal');
    const checkoutTotalEl = document.getElementById('checkoutTotal');

    // Checkout modal elements
    const modalSubtotalEl = document.getElementById('modal-subtotal');
    const modalDeliveryEl = document.getElementById('modal-delivery');
    const modalPlatformEl = document.getElementById('modal-platform');
    const modalHandlingEl = document.getElementById('modal-handling');
    const modalTotalEl = document.getElementById('modal-total');

    const subtotal = getSubtotal();
    const delivery = getDeliveryCharge();
    const platform = getPlatformFee();
    const handling = getHandlingCharge();
    const total = getTotal();
    const cartItemsList = getItems();
    const totalQty = getTotalItems();

    if (subtotalEl) subtotalEl.textContent = `₹${subtotal}`;
    if (deliveryEl) deliveryEl.textContent = `₹${delivery.toFixed(2)}`;
    if (platformEl) platformEl.textContent = `₹${platform}`;
    if (handlingEl) handlingEl.textContent = `₹${handling}`;
    if (totalEl) totalEl.textContent = `₹${total.toFixed(2)}`;
    if (checkoutTotalEl) checkoutTotalEl.textContent = `₹${total.toFixed(2)} →`;

    if (modalSubtotalEl) modalSubtotalEl.textContent = `₹${subtotal}`;
    if (modalDeliveryEl) modalDeliveryEl.textContent = `₹${delivery.toFixed(2)}`;
    if (modalPlatformEl) modalPlatformEl.textContent = `₹${platform}`;
    if (modalHandlingEl) modalHandlingEl.textContent = `₹${handling}`;
    if (modalTotalEl) modalTotalEl.textContent = `₹${total.toFixed(2)}`;

    // Hide Pricing Progress Bar (No longer needed)
    const progressContainer = document.getElementById("pricingProgressBarContainer");
    if (progressContainer) progressContainer.style.display = "none";

    const deliveryExplan = document.getElementById("cartDeliveryExplanation");
    const modalDeliveryExplan = document.getElementById("modal-delivery-explanation");

    if (deliveryExplan) deliveryExplan.style.display = "none";
    if (modalDeliveryExplan) modalDeliveryExplan.style.display = "none";

    if (window.lucide) {
      const sidebar = document.getElementById('cartSidebar');
      if (sidebar) {
        lucide.createIcons({
          attrs: { 'data-lucide': true },
          scope: sidebar
        });
      }
    }

    if (cartFooter) cartFooter.style.display = cartItemsList.length > 0 ? 'block' : 'none';

    if (cartItems) {
      if (cartItemsList.length === 0) {
        const isEmptyBecauseOfFiltering = totalQty > 0;
        cartItems.innerHTML = `
          <div class="cart-empty">
            <div class="empty-icon"><i data-lucide="${isEmptyBecauseOfFiltering ? 'alert-circle' : 'shopping-cart'}" style="width:48px;height:48px;color:${isEmptyBecauseOfFiltering ? 'var(--accent)' : 'inherit'}"></i></div>
            <div class="empty-title">${isEmptyBecauseOfFiltering ? 'Items from another shop' : 'Your cart is empty'}</div>
            <div class="empty-sub">${isEmptyBecauseOfFiltering ? 'Your cart contains items that are not in this shop' : 'Add items to get started'}</div>
            ${isEmptyBecauseOfFiltering ? `<button onclick="Cart.clear()" class="btn btn-sm" style="margin-top:16px;background:var(--bg);color:var(--accent);border:1px solid var(--accent);font-size:12px">Clear Cart</button>` : ''}
          </div>`;
      } else {
        cartItems.innerHTML = cartItemsList.map(({ product: p, qty }) => `
          <div class="cart-item">
            <div class="cart-item-img">${p.image_url ? `<img src="${p.image_url}" style="width:100%;height:100%;object-fit:contain;">` : `<i data-lucide="${p.icon_name || 'package'}" style="width:24px;height:24px"></i>`}</div>
            <div class="cart-item-info">
              <div class="cart-item-name">${p.name}</div>
              <div class="cart-item-unit">${p.unit}</div>
              <div class="cart-item-price">₹${p.price * qty}</div>
            </div>
            <div class="cart-item-actions">
              <div class="qty-ctrl light">
                <button class="qty-btn" onclick="Cart.remove(${p.id})">−</button>
                <span class="qty-num">${qty}</span>
                <button class="qty-btn" onclick="Cart.add(${p.id}, null, true)">+</button>
              </div>
              <button class="cart-item-remove" onclick="Cart.deleteItem(${p.id})"><i data-lucide="trash-2" style="width:16px;height:16px"></i></button>
            </div>
          </div>`).join('');
        if (window.lucide) {
          lucide.createIcons({
            attrs: { 'data-lucide': true },
            scope: cartItems
          });
        }
      }
    }
  }

  function _animateCartIcon() {
    const btn = document.querySelector('.cart-btn');
    if (!btn) return;
    btn.classList.remove('cart-bounce');
    void btn.offsetWidth;
    btn.classList.add('cart-bounce');
  }

  function toggleSidebar() {
    const sidebar = document.getElementById('cartSidebar');
    if (sidebar) sidebar.classList.toggle('open');
  }

  function closeSidebar() {
    const sidebar = document.getElementById('cartSidebar');
    if (sidebar) sidebar.classList.remove('open');
  }

  return {
    add, remove, deleteItem, getQty, getTotalItems,
    getSubtotal, getPlatformFee, getHandlingCharge, getDeliveryCharge, getTotal,
    getItems, clear, setProducts,
    toggleSidebar, closeSidebar, init
  };
})();

// ======= PAYMENT SELECTOR =======
function selectPayment(el, type) {
  document.querySelectorAll('.payment-option').forEach(p => {
    p.classList.remove('active');
    p.style.borderColor = 'var(--border)';
    p.style.background = 'white';
    p.querySelector('div:last-child').style.color = 'var(--text-muted)';
  });
  el.classList.add('active');
  el.style.borderColor = 'var(--primary)';
  el.style.background = 'var(--primary-pale)';
  el.querySelector('div:last-child').style.color = 'var(--primary)';
  window._selectedPayment = type;
}

// ======= PLACE ORDER =======
function placeOrder() {
  const address = document.getElementById('deliveryAddress');
  if (!address || !address.value.trim()) {
    Toast.show('Please enter a delivery address', 'error');
    return;
  }
  Modal.close('checkoutModal');
  Cart.closeSidebar();
  Cart.clear();
  Toast.show('Order placed! Estimated delivery: 28 mins', 'success');
  setTimeout(() => Toast.show('Your order is being prepared', ''), 3500);
}

// ======= SKELETON LOADER =======
function showSkeletons(containerId, count = 6) {
  const container = document.getElementById(containerId);
  if (!container) return;
  container.innerHTML = Array(count).fill(`
    <div class="skeleton-card">
      <div class="skeleton skeleton-img"></div>
      <div class="skeleton skeleton-text"></div>
      <div class="skeleton skeleton-text short"></div>
      <div class="skeleton skeleton-text shorter"></div>
    </div>`).join('');
}

// ======= PANEL SWITCHER =======
function switchPanel(name) {
  document.querySelectorAll('.panel').forEach(p => p.classList.remove('active'));
  const panel = document.getElementById('panel-' + name);
  if (panel) panel.classList.add('active');
  
  document.querySelectorAll('.demo-btn').forEach(b => b.classList.remove('active'));
  if (window.event && window.event.target && window.event.target.classList.contains('demo-btn')) {
    window.event.target.classList.add('active');
  } else {
    document.querySelectorAll('.demo-btn').forEach(b => {
      if (b.textContent.toLowerCase().includes(name.toLowerCase())) b.classList.add('active');
    });
  }
  window.scrollTo(0, 0);
}

function switchPanelDirect(name) {
  document.querySelectorAll('.panel').forEach(p => p.classList.remove('active'));
  const panel = document.getElementById('panel-' + name);
  if (panel) panel.classList.add('active');
  
  document.querySelectorAll('.demo-btn').forEach(b => {
    b.classList.remove('active');
    if (b.textContent.toLowerCase().includes(name.split('')[0])) b.classList.add('active');
  });
}

function showCategory(cat) {
  switchPanelDirect('customer');
  const catMap = { vegetables:'veg', bakery:'bakery', meat:'chicken', pickup:'all' };
  const pills = document.querySelectorAll('.cat-pill');
  pills.forEach(p => {
    const pilCat = p.getAttribute('onclick')?.match(/'([^']+)'/)?.[1];
    if (pilCat === (catMap[cat] || 'all')) {
      filterCat(p, catMap[cat] || 'all');
    }
  });
  document.getElementById('productsSection')?.scrollIntoView({ behavior: 'smooth' });
}

// ======= INIT =======
document.addEventListener('DOMContentLoaded', () => {
  Toast.init();
  Modal.init();
  if (typeof Cart !== 'undefined') {
    Cart.init();
  }

  // Add real-time validation for login name
  const loginNameEl = document.getElementById('loginName');
  if (loginNameEl) {
    loginNameEl.addEventListener('input', e => Validation.validateInput(e.target, 'alpha', "Only letters and spaces are allowed", document.getElementById('loginNameError')));
  }
});

/**
 * Validation Helper
 */
const Validation = {
    // Only alphabets and spaces
    isAlphaSpace: (val) => /^[a-zA-Z\s]*$/.test(val),
    
    // Numbers only
    isNumeric: (val) => /^[0-9]*$/.test(val),
    
    // Exactly 6 digits
    isPincode: (val) => /^[0-9]{6}$/.test(val),
    
    // Exactly 10 digits
    isPhone: (val) => /^[0-9]{10}$/.test(val),

    // Real-time validation handler
    validateInput: (inputEl, type, errorMsg, feedbackEl) => {
        const val = inputEl.value.trim();
        let isValid = true;

        if (val === '') {
            isValid = false; 
            errorMsg = "This field is required";
        } else {
            switch(type) {
                case 'alpha': isValid = Validation.isAlphaSpace(val); break;
                case 'numeric': isValid = Validation.isNumeric(val); break;
                case 'pincode': isValid = Validation.isPincode(val); break;
                case 'phone': isValid = Validation.isPhone(val); break;
            }
        }

        if (!isValid) {
            inputEl.classList.add('is-invalid');
            if (feedbackEl) {
                feedbackEl.textContent = errorMsg;
                feedbackEl.style.display = 'block';
            }
        } else {
            inputEl.classList.remove('is-invalid');
            if (feedbackEl) {
                feedbackEl.style.display = 'none';
            }
        }
        return isValid;
    }
};
