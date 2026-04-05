<?php
$activePage = 'settings';
$pageTitle = 'Shop Settings - Vendor Cabinet';
include 'layouts/header.php';
include 'layouts/sidebar.php';

// Fetch current shop details
$stmt = $pdo->prepare("SELECT * FROM shops WHERE id = ?");
$stmt->execute([$_SESSION['shop_id']]);
$shop = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!-- Leaflet CSS & JS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<main class="admin-main">
<?php 
$topbarTitle = "Shop Settings";
$topbarSubtitle = "Manage your shop profile and status";
ob_start(); ?>
<button type="button" class="admin-header-btn" onclick="document.getElementById('shopSettingsForm').requestSubmit()">
    <i data-lucide="save"></i> <span>SAVE CHANGES</span>
</button>
<?php 
$topbarRight = ob_get_clean();
include 'layouts/topbar.php'; 
?>

    <div class="admin-content">
        <div class="admin-grid-equal">
            <div class="premium-card">
                <div class="admin-card-header">
                    <h3 class="admin-card-title">Shop Profile</h3>
                </div>
                <form id="shopSettingsForm">
                    <div class="form-group">
                        <label class="form-label">Shop Name</label>
                        <input type="text" class="form-input" value="<?php echo $shop['shop_name']; ?>" disabled style="background:#f9fafb">
                        <p style="font-size:11px; color:var(--text-muted); margin-top:4px">Contact admin to change shop name.</p>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Shop Phone</label>
                        <input type="text" id="shopPhone" class="form-input" value="<?php echo $shop['phone']; ?>" placeholder="e.g. 9876543210">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Shop Address</label>
                        <textarea id="shopAddress" class="form-input" style="height:100px; padding:12px"><?php echo $shop['address']; ?></textarea>
                    </div>

                    <!-- Hidden fields for coordinates -->
                    <input type="hidden" id="shopLat" value="<?php echo $shop['latitude']; ?>">
                    <input type="hidden" id="shopLng" value="<?php echo $shop['longitude']; ?>">
                </form>
            </div>

            <div class="premium-card">
                <div class="admin-card-header">
                    <h3 class="admin-card-title">Availability Status</h3>
                </div>
                <div style="padding:20px; text-align:center; background:var(--bg); border-radius:16px; border:1px dashed var(--border)">
                    <p style="margin-bottom:20px; font-size:14px; color:var(--text-muted)">Toggle your shop's visibility on the marketplace.</p>
                    <div style="display:flex; flex-direction:column; align-items:center; gap:16px">
                        <div id="statusIndicator" class="status-pill <?php echo $shop['status'] === 'active' ? 'sp-delivered' : 'sp-pending'; ?>" style="font-size:18px; padding:15px 30px">
                            <i data-lucide="store"></i> <?php echo strtoupper($shop['status'] === 'active' ? 'Shop Open' : 'Shop Closed'); ?>
                        </div>
                        <button class="admin-btn" style="background:<?php echo $shop['status'] === 'active' ? '#ef4444' : '#10b981'; ?>; padding:12px 24px; font-weight:700" onclick="toggleShopStatus('<?php echo $shop['status'] === 'active' ? 'inactive' : 'active'; ?>')">
                            <?php echo $shop['status'] === 'active' ? 'Close Shop' : 'Open Shop'; ?>
                        </button>
                    </div>
                </div>
            </div>

            <div class="premium-card" style="grid-column: span 2">
                <div class="admin-card-header">
                    <h3 class="admin-card-title">Shop Location on Map</h3>
                    <p style="font-size:12px; color:var(--text-muted); margin-top:4px">Click the button below or tap on the map to set your location.</p>
                </div>
                <div style="padding:20px;">
                    <div style="display:flex; gap:16px; margin-bottom:20px;">
                        <button type="button" class="form-btn" id="btnLocateMe" style="background:#10b981; flex:1; height:56px; font-size:16px; font-weight:800; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2);">
                            <i data-lucide="map-pin" style="width:20px; height:20px"></i> SET MY CURRENT LOCATION
                        </button>
                    </div>
                    <div id="shopMap" style="height:400px; border-radius:16px; border:2px solid var(--border); z-index:1;"></div>
                    <div style="display:flex; gap:12px; margin-top:16px; font-size:12px; color:var(--text-muted); background:var(--bg); padding:10px; border-radius:8px">
                        <div><strong>Lat:</strong> <span id="displayLat"><?php echo $shop['latitude'] ?: '—'; ?></span></div>
                        <div><strong>Lng:</strong> <span id="displayLng"><?php echo $shop['longitude'] ?: '—'; ?></span></div>
                        <div style="margin-left:auto"><em>* Don't forget to click "Save Profile Details" after setting location.</em></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<style>
    .sp-delivered { background: #d1fae5; color: #065f46; border: 1px solid #10b981; }
    .sp-pending { background: #fee2e2; color: #991b1b; border: 1px solid #ef4444; }
    .status-pill { border-radius: 999px; font-weight: 700; display: inline-flex; align-items: center; gap: 8px; }
</style>

<script>
    // Initialize Map
    let map = null;
    let marker = null;
    const defaultLoc = [<?php echo $shop['latitude'] ?: '11.1271'; ?>, <?php echo $shop['longitude'] ?: '78.6569'; ?>];

    function initMap() {
        if (map) return;
        map = L.map('shopMap').setView(defaultLoc, <?php echo $shop['latitude'] ? '16' : '13'; ?>);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap'
        }).addTo(map);

        if (<?php echo $shop['latitude'] ? 'true' : 'false'; ?>) {
            setMarker(defaultLoc[0], defaultLoc[1], false);
        }

        map.on('click', (e) => {
            const { lat, lng } = e.latlng;
            setMarker(lat, lng);
        });
    }

    function setMarker(lat, lng, updateInputs = true) {
        if (!map) return;
        if (marker) {
            marker.setLatLng([lat, lng]);
        } else {
            marker = L.marker([lat, lng]).addTo(map);
        }
        
        if (updateInputs) {
            document.getElementById('shopLat').value = lat.toFixed(8);
            document.getElementById('shopLng').value = lng.toFixed(8);
            document.getElementById('displayLat').textContent = lat.toFixed(8);
            document.getElementById('displayLng').textContent = lng.toFixed(8);
        }
    }

    document.getElementById('btnLocateMe').addEventListener('click', () => {
        const btn = document.getElementById('btnLocateMe');
        const originalHtml = btn.innerHTML;
        btn.innerHTML = 'CAPTURE IN PROGRESS...';
        btn.disabled = true;
        
        if (!navigator.geolocation) {
            alert('Geolocation is not supported by your browser');
            btn.innerHTML = originalHtml;
            btn.disabled = false;
            return;
        }

        navigator.geolocation.getCurrentPosition((position) => {
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;
            setMarker(lat, lng);
            map.setView([lat, lng], 16);
            btn.innerHTML = originalHtml;
            btn.disabled = false;
            if(window.Toast) Toast.show('Location captured! Please save your profile.', 'success');
        }, (error) => {
            alert('Error getting location: ' + error.message);
            btn.innerHTML = originalHtml;
            btn.disabled = false;
        }, { enableHighAccuracy: true });
    });

    document.addEventListener('DOMContentLoaded', () => {
        initMap();
        if(window.lucide) lucide.createIcons();
    });

    document.getElementById('shopSettingsForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const shop_phone = document.getElementById('shopPhone').value;
        const shop_address = document.getElementById('shopAddress').value;
        const latitude = document.getElementById('shopLat').value;
        const longitude = document.getElementById('shopLng').value;

        try {
            const resp = await fetch('../api/vendor/shop/update.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ 
                    shop_phone, 
                    shop_address, 
                    latitude, 
                    longitude, 
                    action: 'update_profile' 
                })
            }).then(r => r.json());

            if (resp.success) {
                if(window.Toast) Toast.show('Profile and Location updated successfully!', 'success');
                else alert('Profile updated successfully!');
            } else {
                alert(resp.error || 'Save failed');
            }
        } catch (err) { alert('Error updating profile'); }
    });

    async function toggleShopStatus(newStatus) {
        if (!confirm(`Are you sure you want to ${newStatus === 'active' ? 'open' : 'close'} your shop?`)) return;
        try {
            const resp = await fetch('../api/vendor/shop/update.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ status: newStatus, action: 'toggle_status' })
            }).then(r => r.json());

            if (resp.success) window.location.reload();
            else alert(resp.error);
        } catch (err) { alert('Error toggling status'); }
    }
</script>
<?php include 'layouts/footer.php'; ?>
