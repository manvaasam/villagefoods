<?php
$pageTitle = 'Checkout — Village Foods';
include 'includes/header.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: index.php');
    exit;
}
$isSimplified = true;
include 'includes/navbar.php';
?>

<!-- Razorpay Checkout Script -->
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>

<main class="container checkout-container">
    <div class="checkout-wrapper">
        <div class="section-header" style="margin-bottom:32px">
            <h2 class="section-title">Complete <span>Your Order</span></h2>
            <a href="index.php" class="section-link">← Continue Shopping</a>
        </div>

        <div class="checkout-layout">
            <!-- Left Column: Details -->
            <div class="checkout-details">
                <!-- Delivery Section -->
                <div class="checkout-card address-card">
                    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:16px">
                        <div style="display:flex; align-items:center; gap:12px">
                            <div style="width:36px; height:36px; background:var(--primary-pale); border-radius:50%; display:flex; align-items:center; justify-content:center; color:var(--primary)">
                                <i data-lucide="map-pin"></i>
                            </div>
                            <h3 style="font-size:18px; font-weight:800">Delivery Address</h3>
                        </div>
                        <button onclick="LocationModal.open()" style="background:none; border:none; color:var(--primary); font-size:13px; font-weight:700; cursor:pointer; padding:4px 8px; border-radius:4px; transition:all 0.2s" onmouseover="this.style.background='var(--primary-pale)'" onmouseout="this.style.background='none'">
                            CHANGE
                        </button>
                    </div>
                    <div id="checkoutAddressDisplay" style="font-size:15px; font-weight:600; color:var(--text-dark); background:var(--bg); padding:16px; border-radius:var(--radius-sm); border:1.5px dashed var(--border)">
                        Loading address...
                    </div>
                </div>

                <!-- Payment Section -->
                <div class="checkout-card payment-card">
                    <div style="display:flex; align-items:center; gap:12px; margin-bottom:20px">
                        <div style="width:36px; height:36px; background:var(--primary-pale); border-radius:50%; display:flex; align-items:center; justify-content:center; color:var(--primary)">
                            <i data-lucide="credit-card"></i>
                        </div>
                        <h3 style="font-size:18px; font-weight:800">Payment Method</h3>
                    </div>

                    <div class="payment-methods-grid">
                        <!-- Online Payment -->
                        <div class="payment-method-option active" id="payMethodOnline" data-method="Online" onclick="selectPaymentMethod(this)">
                            <div class="method-check">
                                <i data-lucide="check-circle" style="width:20px; height:20px"></i>
                            </div>
                            <i data-lucide="credit-card" class="method-icon"></i>
                            <div class="method-label">Online Payment</div>
                            <div class="method-desc">Cards, UPI, NetBanking</div>
                        </div>

                        <?php if (Settings::isEnabled('enable_cod')): ?>
                        <!-- COD Payment -->
                        <div class="payment-method-option" id="payMethodCOD" data-method="COD" onclick="selectPaymentMethod(this)">
                            <div class="method-check" style="display:none">
                                <i data-lucide="check-circle" style="width:20px; height:20px"></i>
                            </div>
                            <i data-lucide="banknote" class="method-icon"></i>
                            <div class="method-label">Cash on Delivery</div>
                            <div class="method-desc">Pay when you receive</div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Right Column: Summary -->
            <div class="checkout-sidebar">
                <div class="checkout-sidebar-inner">
                    <h3 style="font-size:16px; font-weight:800; margin-bottom:20px; color:var(--primary-dark)">Order Summary</h3>
                    
                    <div id="checkoutItemsList" style="margin-bottom:20px; max-height:300px; overflow-y:auto">
                        <!-- Items will be injected here -->
                    </div>

                    <div style="border-top:1.5px solid var(--border); padding-top:20px; margin-top:20px">
                        <div class="cart-row"><span>Bill Total</span><span id="checkoutBillTotal">₹0</span></div>
                        <div class="cart-row"><span>Delivery Fee</span><span id="checkoutDeliveryFee" style="color:var(--primary)">₹0</span></div>
                        <div class="cart-row"><span>Platform Fee</span><span id="checkoutPlatformFee">₹0</span></div>
                        <div class="cart-row"><span>Handling Fee</span><span id="checkoutHandlingFee">₹0</span></div>
                        <div class="cart-row total" style="font-size:18px; margin-top:12px; padding-top:12px; border-top:2.5px solid var(--bg)">
                            <span>To Pay</span>
                            <span id="checkoutGrandTotal">₹0</span>
                        </div>
                    </div>

                    <button class="form-btn" onclick="placeFinalOrder(event)" style="margin-top:24px; height:52px; font-size:16px">
                        Place Order <i data-lucide="arrow-right" style="margin-left:8px"></i>
                    </button>
                    <p style="text-align:center; font-size:11px; color:var(--text-muted); margin-top:12px; font-weight:600">
                        <i data-lucide="shield-check" style="width:12px; height:12px; vertical-align:middle"></i> Secure Checkout
                    </p>
                </div>
            </div>
        </div>
    </div>
</main>

<style>
    .checkout-container { padding-top: 120px; padding-bottom: 80px; }
    .checkout-wrapper { max-width: 960px; margin: 0 auto; }
    .checkout-layout { display: grid; grid-template-columns: 1.5fr 1fr; gap: 32px; align-items: start; }
    
    .checkout-card { background: white; border-radius: var(--radius); padding: 24px; box-shadow: var(--shadow-sm); }
    .address-card { margin-bottom: 24px; }
    
    .payment-methods-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    .payment-method-option { padding: 20px; border: 2px solid var(--border); border-radius: var(--radius); text-align: center; background: white; cursor: pointer; position: relative; overflow: hidden; transition: all 0.2s; }
    .payment-method-option .method-check { position: absolute; top: 10px; right: 10px; color: var(--primary); display: none; }
    .payment-method-option.active .method-check { display: block !important; }
    .payment-method-option .method-icon { width: 28px; height: 28px; color: var(--text-muted); margin-bottom: 10px; }
    .payment-method-option .method-label { font-size: 14px; font-weight: 800; color: var(--text-dark); }
    .payment-method-option .method-desc { font-size: 10px; color: var(--text-muted); margin-top: 4px; }
    
    .payment-method-option.active .method-icon, 
    .payment-method-option.active .method-label { color: var(--primary) !important; }
    
    .checkout-sidebar-inner { background: white; border-radius: var(--radius); padding: 24px; box-shadow: var(--shadow-sm); position: sticky; top: 100px; }
    
    .checkout-item { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; }
    .checkout-item-img { width: 40px; height: 40px; border-radius: 8px; background: var(--bg); display: flex; align-items: center; justify-content: center; }
    .checkout-item-info { flex: 1; }
    .checkout-item-name { font-size: 13px; font-weight: 700; color: var(--primary-dark); }
    .checkout-item-qty { font-size: 11px; color: var(--text-muted); font-weight: 600; }
    .checkout-item-price { font-size: 13px; font-weight: 800; color: var(--text-dark); }

    /* Responsive */
    @media (max-width: 992px) {
        .checkout-layout { grid-template-columns: 1fr; gap: 24px; }
        .checkout-sidebar-inner { position: static; }
        .checkout-container { padding-top: 100px; }
    }
    
    @media (max-width: 576px) {
        .checkout-container { padding-top: 90px; padding-bottom: 40px; }
        .checkout-card { padding: 20px 16px; }
        .payment-methods-grid { grid-template-columns: 1fr; }
        .section-title { font-size: 24px; }
    }
</style>

<script>
    window.updateAddressDisplay = function() {
        const savedAddressJson = localStorage.getItem('userAddress');
        const displayEl = document.getElementById('checkoutAddressDisplay');
        const sessionUserId = '<?= $_SESSION['user_id'] ?? '' ?>';
        
        if (!displayEl) return;

        if (savedAddressJson) {
            try {
                const addr = JSON.parse(savedAddressJson);
                
                // If the address in localStorage belongs to a different user, clear it!
                if (addr.user_id && sessionUserId && String(addr.user_id) !== String(sessionUserId)) {
                    console.warn("Address user mismatch. Clearing stale address.");
                    localStorage.removeItem('userAddress');
                    displayEl.innerHTML = `<span style="color:var(--accent)">Stored address is from another account. Please select/add a new one.</span>`;
                    return;
                }

                displayEl.innerHTML = `
                    <div style="font-size:16px; color:var(--primary-dark)">${addr.door_no}, ${addr.street}</div>
                    <div style="font-size:13px; color:var(--text-muted); margin-top:4px">${addr.area}, ${addr.city} - ${addr.pincode}</div>
                    ${addr.landmark ? `<div style="font-size:12px; color:var(--primary); margin-top:4px"><i data-lucide="map-pin" style="width:12px;height:12px"></i> ${addr.landmark}</div>` : ''}
                    <div style="font-size:13px; color:var(--text-dark); font-weight:700; margin-top:8px; display:flex; align-items:center; gap:6px">
                        <i data-lucide="phone" style="width:14px;height:14px"></i> ${addr.contact_number}
                    </div>
                `;
            } catch(e) {
                displayEl.textContent = 'Address not found';
                localStorage.removeItem('userAddress');
            }
        } else {
            displayEl.textContent = 'No delivery address selected';
        }
        if (window.lucide) lucide.createIcons();
    };

    document.addEventListener('DOMContentLoaded', () => {
        updateAddressDisplay();
        // Delay rendering summary to wait for products.js AJAX to fill Cart.products
        setTimeout(renderOrderSummary, 800);
    });

    function renderOrderSummary() {
        const container = document.getElementById('checkoutItemsList');
        const items = Cart.getItems();
        
        if (items.length === 0) {
            window.location.href = 'index.php';
            return;
        }

        container.innerHTML = items.map(({product: p, qty}) => `
            <div class="checkout-item">
                <div class="checkout-item-img">
                    ${p.image_url ? `<img src="${p.image_url}" style="width:100%;height:100%;object-fit:contain;">` : `<i data-lucide="package" style="width:20px;height:20px;color:var(--text-muted)"></i>`}
                </div>
                <div class="checkout-item-info">
                    <div class="checkout-item-name">${p.name}</div>
                    <div class="checkout-item-qty">${p.unit} &times; ${qty}</div>
                </div>
                <div class="checkout-item-price">₹${p.price * qty}</div>
            </div>
        `).join('');

        document.getElementById('checkoutBillTotal').textContent = '₹' + Cart.getSubtotal().toFixed(2);
        const deliveryEl = document.getElementById('checkoutDeliveryFee');
        if (deliveryEl) deliveryEl.textContent = '₹' + Cart.getDeliveryCharge().toFixed(2);
        
        const platformEl = document.getElementById('checkoutPlatformFee');
        if (platformEl) platformEl.textContent = '₹' + Cart.getPlatformFee().toFixed(2);
        
        const handlingEl = document.getElementById('checkoutHandlingFee');
        if (handlingEl) handlingEl.textContent = '₹' + Cart.getHandlingCharge().toFixed(2);
        
        document.getElementById('checkoutGrandTotal').textContent = '₹' + Cart.getTotal().toFixed(2);
        
        if (window.lucide) lucide.createIcons();
    }

    let userSelectedPaymentMethod = 'Online';
    function selectPaymentMethod(el) {
        if (!el) return;
        
        // Reset all options
        document.querySelectorAll('.payment-method-option').forEach(opt => {
            opt.classList.remove('active');
        });
        
        // Activate selected option
        el.classList.add('active');
        
        userSelectedPaymentMethod = el.dataset.method;
    }

    async function placeFinalOrder(event) {
        const savedAddressJson = localStorage.getItem('userAddress');
        if (!savedAddressJson) {
            Toast.show('Please select a delivery address', 'warning');
            LocationModal.open();
            return;
        }

        const addr = JSON.parse(savedAddressJson);
        const addressId = addr.id;
        const cart = JSON.parse(localStorage.getItem('villageCart') || '{}');

        if (Object.keys(cart).length === 0) {
            Toast.show('Your cart is empty', 'warning');
            return;
        }

        const btn = event ? event.target.closest('button') : null;
        const originalHtml = btn ? btn.innerHTML : '';
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<div class="spinner" style="width:20px; height:20px; border-width:3px"></div> Processing...';
        }

        if (userSelectedPaymentMethod === 'COD') {
            // COD Flow
            try {
                const res = await fetch('api/orders/create.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ 
                        cart: cart,
                        address_id: addressId,
                        payment_method: 'COD'
                    })
                });
                const data = await res.json();

                if (data.status === 'success') {
                    Cart.clear();
                    Toast.show('Order placed successfully!', 'success');
                    if (btn) {
                        btn.style.background = '#10b981';
                        btn.innerHTML = '<i data-lucide="check-circle" style="width:24px; height:24px; margin-right:8px"></i> Order Placed!';
                        if (window.lucide) lucide.createIcons();
                    }
                    setTimeout(() => {
                        window.location.replace('track-order.php?order_id=' + data.order_id);
                    }, 500);
                } else {
                    throw new Error(data.message || 'Failed to place order');
                }
            } catch (e) {
                Toast.show(e.message, 'error');
                if (btn) { btn.disabled = false; btn.innerHTML = originalHtml; }
            }
        } else {
            // Razorpay Flow
            try {
                const res = await fetch('api/orders/create_razorpay.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ cart: cart, address_id: addressId })
                });
                const orderData = await res.json();

                if (orderData.status !== 'success') {
                    throw new Error(orderData.message || 'Failed to initialize payment');
                }

                const options = {
                    "key": "<?php require_once 'includes/razorpay_config.php'; echo RAZORPAY_KEY_ID; ?>",
                    "amount": orderData.amount,
                    "currency": "INR",
                    "name": "Village Foods",
                    "description": "Payment for your order",
                    "order_id": orderData.razorpay_order_id,
                    "handler": async function (response) {
                        try {
                            const verifyRes = await fetch('api/orders/verify_payment.php', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({
                                    razorpay_order_id: response.razorpay_order_id,
                                    razorpay_payment_id: response.razorpay_payment_id,
                                    razorpay_signature: response.razorpay_signature,
                                    cart: cart,
                                    address_id: addressId
                                })
                            });
                            const verifyData = await verifyRes.json();

                            if (verifyData.status === 'success') {
                                Cart.clear();
                                Toast.show('Payment successful!', 'success');
                                if (btn) {
                                    btn.style.background = '#10b981';
                                    btn.innerHTML = '<i data-lucide="check-circle" style="width:24px; height:24px; margin-right:8px"></i> Order Received!';
                                    if (window.lucide) lucide.createIcons();
                                }
                                setTimeout(() => {
                                    window.location.replace('track-order.php?order_id=' + verifyData.order_id);
                                }, 500);
                            } else {
                                Toast.show(verifyData.message || 'Payment verification failed', 'error');
                                if (btn) { btn.disabled = false; btn.innerHTML = originalHtml; }
                            }
                        } catch (err) {
                            Toast.show('Error verifying payment', 'error');
                            if (btn) { btn.disabled = false; btn.innerHTML = originalHtml; }
                        }
                    },
                    "prefill": {
                        "name": orderData.user.name,
                        "email": orderData.user.email,
                        "contact": orderData.user.phone
                    },
                    "theme": { "color": "#ff4a38" },
                    "modal": {
                        "ondismiss": function() {
                            if (btn) { btn.disabled = false; btn.innerHTML = originalHtml; }
                            Toast.show('Payment cancelled', 'info');
                        }
                    }
                };
                const rzp = new Razorpay(options);
                rzp.open();
            } catch (e) {
                Toast.show(e.message, 'error');
                if (btn) { btn.disabled = false; btn.innerHTML = originalHtml; }
            }
        }
    }
</script>

<?php
include 'includes/footer.php';
?>
