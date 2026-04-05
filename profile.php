<?php
$pageTitle = 'My Profile — Village Foods';
include 'includes/header.php';

// Redirect if not logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo "<script>window.location.href='index.php';</script>";
    exit;
}

include 'includes/navbar.php';
?>
<?php
// Fetch saved addresses from DB
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM user_addresses WHERE user_id = ? ORDER BY is_default DESC, id DESC");
$stmt->execute([$user_id]);
$addresses = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<main class="container" style="padding-top:120px; padding-bottom:80px">
    <div class="profile-wrapper">
        <div class="section-header">
            <h2 class="section-title">My <span>Profile</span></h2>
        </div>
        
        <div class="profile-main-card">
            <div class="profile-avatar-lg">
                <?= strtoupper(substr($_SESSION['user_name'] ?? 'U', 0, 1)) ?>
            </div>
            <div class="profile-info">
                <h3 class="profile-name"><?= htmlspecialchars($_SESSION['user_name'] ?? 'User') ?></h3>
                <p class="profile-email"><i data-lucide="mail" style="width:16px;height:16px"></i> <?= htmlspecialchars($_SESSION['user_email'] ?? '') ?></p>
                
                <div class="profile-actions">
                    <button class="nav-btn nav-btn-outline" style="width:100%; border-color:var(--primary); color:var(--primary)" onclick="Profile.openEditName()">
                        <i data-lucide="edit-3" style="width:16px;height:16px;margin-right:8px"></i> Edit Name
                    </button>
                    <button class="nav-btn nav-btn-ghost" style="width:100%; color:var(--accent)" onclick="window.location.href='api/logout.php'">
                        <i data-lucide="log-out" style="width:16px;height:16px;margin-right:8px"></i> Logout
                    </button>
                </div>
            </div>
        </div>

        <div class="section">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px">
                <h3 style="font-size:18px; font-weight:800; color:var(--primary-dark)">Saved Addresses</h3>
                <button class="nav-btn nav-btn-outline" style="padding:6px 14px; font-size:13px" onclick="LocationModal.open()">
                    <i data-lucide="plus" style="width:14px; height:14px; margin-right:4px"></i> Add New
                </button>
            </div>

            <div id="profileAddressList" class="address-grid">
                <?php if (empty($addresses)): ?>
                    <div class="checkout-card" style="grid-column: 1/-1; padding:32px; border:2px dashed var(--border); background:var(--bg); text-align:center; color:var(--text-muted)">
                        <i data-lucide="map-pin" style="width:32px; height:32px; margin-bottom:12px; opacity:0.5"></i>
                        <p style="font-weight:600">Your addresses from orders will appear here.</p>
                        <button class="nav-btn nav-btn-outline" style="margin-top:16px" onclick="LocationModal.open()">Add Your First Address</button>
                    </div>
                <?php else: ?>
                    <?php foreach ($addresses as $addr): ?>
                        <div class="address-card <?= $addr['is_default'] ? 'default' : '' ?>">
                            <?php if ($addr['is_default']): ?>
                                <span class="address-badge">DEFAULT</span>
                            <?php endif; ?>
                            <div style="display:flex; gap:12px">
                                <i data-lucide="home" style="width:18px; height:18px; color:var(--primary); flex-shrink:0; margin-top:2px"></i>
                                <div>
                                    <div style="display:flex; justify-content:space-between; align-items:flex-start">
                                        <h4 style="font-size:14px; font-weight:800; color:var(--primary-dark); margin-bottom:4px">Address</h4>
                                        <div style="display:flex; gap:8px">
                                            <button onclick='Profile.editAddress(<?= json_encode($addr) ?>)' style="background:none; border:none; color:var(--primary); cursor:pointer; padding:4px"><i data-lucide="edit-2" style="width:14px; height:14px"></i></button>
                                            <button onclick="Profile.deleteAddress(<?= $addr['id'] ?>)" style="background:none; border:none; color:var(--accent); cursor:pointer; padding:4px"><i data-lucide="trash-2" style="width:14px; height:14px"></i></button>
                                        </div>
                                    </div>
                                    <p style="font-size:13px; color:var(--text); line-height:1.4">
                                        <?= htmlspecialchars($addr['door_no']) ?>, <?= htmlspecialchars($addr['street']) ?><br>
                                        <?php if ($addr['landmark']): ?>
                                            <span style="font-size:12px; color:var(--text-muted)">Landmark: <?= htmlspecialchars($addr['landmark']) ?></span><br>
                                        <?php endif; ?>
                                        <?= htmlspecialchars($addr['area']) ?>, <?= htmlspecialchars($addr['city']) ?><br>
                                        <?= htmlspecialchars($addr['pincode']) ?>
                                    </p>
                                    <p style="font-size:12px; font-weight:700; color:var(--text-muted); margin-top:8px">
                                        <i data-lucide="phone" style="width:12px; height:12px; margin-right:4px; vertical-align:middle"></i> <?= htmlspecialchars($addr['contact_number']) ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="section" id="rapid-orders" style="margin-top:40px">
            <h3 style="font-size:18px; font-weight:800; color:var(--primary-dark); margin-bottom:20px">My Rapid Pickups</h3>
            <div id="rapidOrdersList" style="display:flex; flex-direction:column; gap:16px">
                <div class="checkout-card" style="padding:48px; text-align:center; color:var(--text-muted)">
                    <div class="spinner" style="width:32px; height:32px; margin:0 auto"></div>
                    <p style="margin-top:12px">Loading your rapid pickups...</p>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Edit Name Modal -->
<div class="modal-overlay" id="editNameModal" onclick="Modal.closeOnOverlay(event,'editNameModal')">
    <div class="modal" style="max-width:400px">
        <button class="modal-close" onclick="Modal.close('editNameModal')"><i data-lucide="x"></i></button>
        <div class="modal-title">Edit Name</div>
        <div class="modal-sub">Update your full name for orders</div>
        
        <div class="form-group" style="margin-top:20px">
            <label class="form-label">Full Name</label>
            <input class="form-input" type="text" id="editProfileName" value="<?= htmlspecialchars($_SESSION['user_name']) ?>">
        </div>
        
        <button class="form-btn" onclick="Profile.saveName()" style="margin-top:10px">Save Changes</button>
    </div>
</div>

<script>
const Profile = {
    openEditName: function() {
        Modal.open('editNameModal');
    },
    saveName: async function() {
        const name = document.getElementById('editProfileName').value.trim();
        if(!name) { Toast.show('Name is required', 'warning'); return; }
        
        try {
            const res = await fetch('api/update_profile.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ name: name })
            });
            const data = await res.json();
            if(data.status === 'success') {
                Toast.show(data.message, 'success');
                setTimeout(() => window.location.reload(), 1000);
            } else {
                Toast.show(data.message, 'error');
            }
        } catch(e) {
            Toast.show('Error updating profile', 'error');
        }
    },
    editAddress: function(addr) {
        // Populate LocationModal with address details for editing
        LocationModal.open(addr);
    },
    deleteAddress: async function(id) {
        if(!confirm('Are you sure you want to delete this address?')) return;
        
        try {
            const res = await fetch('api/delete_address.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ address_id: id })
            });
            const data = await res.json();
            if(data.status === 'success') {
                Toast.show(data.message, 'success');
                setTimeout(() => window.location.reload(), 1000);
            } else {
                Toast.show(data.message, 'error');
            }
        } catch(e) {
            Toast.show('Error deleting address', 'error');
        }
    },
    loadRapidOrders: async function() {
        const container = document.getElementById('rapidOrdersList');
        try {
            const res = await fetch('api/rapid/list.php');
            const data = await res.json();
            
            if(data.status === 'success' && data.orders.length > 0) {
                container.innerHTML = data.orders.map(order => `
                    <div class="checkout-card" style="padding:20px; border-left:4px solid ${order.status === 'Completed' ? '#10b981' : (order.status === 'Cancelled' ? 'var(--accent)' : 'var(--primary)')}">
                        <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:12px">
                            <div>
                                <div style="font-size:12px; font-weight:700; color:var(--text-muted); text-transform:uppercase">Order #${order.id}</div>
                                <div style="font-size:15px; font-weight:800; color:var(--primary-dark)">${order.item_description || 'Package Delivery'}</div>
                            </div>
                            <div style="background:var(--bg); padding:4px 10px; border-radius:12px; font-size:11px; font-weight:800; color:var(--primary)">
                                ${order.status}
                            </div>
                        </div>
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; font-size:13px; color:var(--text-dark); background:var(--bg); padding:12px; border-radius:8px">
                            <div>
                                <div style="font-size:10px; font-weight:800; color:var(--text-muted); margin-bottom:4px">PICKUP</div>
                                <div style="font-weight:600">${order.pickup_address}</div>
                            </div>
                            <div>
                                <div style="font-size:10px; font-weight:800; color:var(--text-muted); margin-bottom:4px">DROP</div>
                                <div style="font-weight:600">${order.drop_address}</div>
                            </div>
                        </div>
                        <div style="margin-top:12px; display:flex; justify-content:space-between; align-items:center">
                            <div style="font-size:12px; color:var(--text-muted)">${new Date(order.created_at).toLocaleDateString('en-GB', { day:'2-digit', month:'short', year:'numeric', hour:'2-digit', minute:'2-digit' })}</div>
                            <div style="font-weight:800; color:var(--primary)">₹${order.price}</div>
                        </div>
                    </div>
                `).join('');
            } else {
                container.innerHTML = `
                    <div class="checkout-card" style="padding:40px; text-align:center; color:var(--text-muted); border:1.5px dashed var(--border)">
                        <i data-lucide="bike" style="width:32px; height:32px; margin-bottom:12px; opacity:0.3"></i>
                        <p>No rapid pickups found. <a href="pickup-drop.php" style="color:var(--primary); font-weight:700">Book one now!</a></p>
                    </div>
                `;
            }
            if (window.lucide) lucide.createIcons();
        } catch(e) {
            container.innerHTML = '<p style="color:var(--accent); text-align:center">Failed to load orders.</p>';
        }
    }
};

document.addEventListener('DOMContentLoaded', () => {
    Profile.loadRapidOrders();
});
</script>

<?php
include 'includes/modals.php';
include 'includes/footer.php';
?>
