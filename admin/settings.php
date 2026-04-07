<?php
$activePage = 'settings';
$pageTitle = 'System Settings';
include 'layouts/header.php';
?>

<div class="admin-card">
    <div class="admin-card-header">
        <div class="admin-card-title">
            <i data-lucide="settings" class="header-icon"></i>
            System Settings
        </div>
        <button class="admin-add-btn" onclick="SettingsAdmin.save()">
            <i data-lucide="save"></i> Save All Changes
        </button>
    </div>

    <div class="admin-content">
        <div class="settings-grid" style="grid-template-columns: 1fr; max-width: 800px; margin: 0 auto;">
            <div class="settings-section" style="margin-bottom: 24px;">
                <h3 class="settings-section-title"><i data-lucide="power"></i> Store Availability</h3>
                
                <div class="settings-toggle-group" style="padding: 16px; background: var(--bg-light); border-radius: var(--radius-sm); border: 1px solid var(--border);">
                    <div style="display:flex; flex-direction:column; gap:4px">
                        <span class="settings-toggle-label" style="font-weight:700">Store Master Switch (Open/Closed)</span>
                        <span class="text-muted fs-12">Turn this OFF to stop all orders and show 'Closed' status to customers.</span>
                    </div>
                    <label class="switch">
                        <input type="checkbox" id="shop_status">
                        <span class="slider"></span>
                    </label>
                </div>
            </div>

            <div class="settings-section">
                <h3 class="settings-section-title"><i data-lucide="pie-chart"></i> Fee Configuration</h3>
                
                <div class="form-group">
                    <label class="form-label">Delivery Fee (₹)</label>
                    <input class="form-input" type="number" id="base_delivery_fee" placeholder="40">
                    <p class="text-muted fs-12">Flat delivery charge for every order.</p>
                </div>

                <div class="form-group">
                    <label class="form-label">Handling Fee (₹)</label>
                    <input class="form-input" type="number" id="handling_fee" placeholder="10">
                    <p class="text-muted fs-12">Fixed handling charge per order.</p>
                </div>

                <div class="form-group">
                    <label class="form-label">Platform Fee (₹)</label>
                    <input class="form-input" type="number" id="platform_fee" placeholder="10">
                    <p class="text-muted fs-12">Flat platform service fee per order.</p>
                </div>

                <div class="form-group">
                    <label class="form-label">Admin Commission (%)</label>
                    <input class="form-input" type="number" id="vendor_commission_percentage" placeholder="20">
                    <p class="text-muted fs-12">Percentage taken from vendor per order total.</p>
                </div>
            </div>

            <div class="settings-section" style="margin-top: 24px;">
                <h3 class="settings-section-title"><i data-lucide="zap"></i> Rapid Pickup Configuration</h3>
                
                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label">Bike Base Price (₹)</label>
                        <input class="form-input" type="number" id="rapid_price_bike" placeholder="30">
                        <p class="text-muted fs-12">Starting price for small items.</p>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Heavy Parcel Base Price (₹)</label>
                        <input class="form-input" type="number" id="rapid_price_heavy" placeholder="50">
                        <p class="text-muted fs-12">Starting price for bulky items.</p>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Express Delivery Base Price (₹)</label>
                    <input class="form-input" type="number" id="rapid_price_express" placeholder="70">
                    <p class="text-muted fs-12">Premium price for urgent deliveries.</p>
                </div>

                <div class="form-group" style="margin-top: 16px;">
                    <label class="form-label">Distance Charge (₹ per KM)</label>
                    <input class="form-input" type="number" id="rapid_price_per_km" placeholder="10">
                    <p class="text-muted fs-12">Additional charge for every kilometer after the first 2km.</p>
                </div>

                <div style="margin-top: 20px; padding: 15px; background: #fefce8; border: 1px solid #fef08a; border-radius: 12px;">
                    <div style="display: flex; gap: 10px; align-items: flex-start;">
                        <i data-lucide="info" style="width: 18px; height: 18px; color: #ca8a04; flex-shrink: 0; margin-top: 2px;"></i>
                        <div style="font-size: 13px; color: #854d0e; line-height: 1.5;">
                            <strong style="display: block; margin-bottom: 4px;">Calculation Logic:</strong>
                            Final Price = [Base Price + (Distance - 2km) × KM Rate] × Vehicle Multiplier<br>
                            <span style="font-size: 11px; opacity: 0.8;">(Multipliers: Bike 1.0x, Heavy 1.5x, Express 2.0x)</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="settings-section" style="margin-top: 24px;">
                <h3 class="settings-section-title"><i data-lucide="credit-card"></i> Payment Controls</h3>
                
                <div class="settings-toggle-group" style="padding: 16px; background: var(--bg-light); border-radius: var(--radius-sm); border: 1px solid var(--border);">
                    <div style="display:flex; flex-direction:column; gap:4px">
                        <span class="settings-toggle-label" style="font-weight:700">Enable Cash on Delivery (COD)</span>
                        <span class="text-muted fs-12">Allow customers to pay when order is delivered.</span>
                    </div>
                    <label class="switch">
                        <input type="checkbox" id="enable_cod">
                        <span class="slider"></span>
                    </label>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        if (typeof SettingsAdmin !== 'undefined') {
            SettingsAdmin.init();
        }
    });
</script>

<?php include 'layouts/footer.php'; ?>
