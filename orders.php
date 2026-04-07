<?php
$pageTitle = 'My Orders — Village Foods';
include 'includes/header.php';

// Redirect if not logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo "<script>window.location.href='index.php';</script>";
    exit;
}

include 'includes/navbar.php';
?>
<?php
// Fetch user orders
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<main class="container" style="padding-top:120px; padding-bottom:80px">
    <div style="max-width:900px; margin:0 auto">
        <div class="section-header">
            <h2 class="section-title">My <span>Orders</span></h2>
            <a href="index.php" class="section-link">← Continue Shopping</a>
        </div>

        <style>
            .orders-tabs { display: flex; gap: 32px; border-bottom: 2px solid var(--bg); margin-bottom: 32px; }
            .order-tab { padding-bottom: 12px; font-size: 15px; font-weight: 700; color: var(--text-muted); cursor: pointer; position: relative; transition: all 0.2s; }
            .order-tab.active { color: var(--primary); }
            .order-tab.active::after { content: ''; position: absolute; bottom: -2px; left: 0; right: 0; height: 2px; background: var(--primary); }
            .rapid-order-card { background: white; border-radius: var(--radius); padding: 24px; box-shadow: var(--shadow-sm); margin-bottom: 20px; transition: all 0.3s; }
            .rapid-order-card:hover { transform: translateY(-2px); box-shadow: var(--shadow); }
            .rapid-address-flow { display: flex; align-items: flex-start; gap: 12px; margin: 16px 0; position: relative; }
            .rapid-line { position: absolute; left: 6px; top: 20px; bottom: 20px; width: 1.5px; background: repeating-linear-gradient(to bottom, var(--border) 0px, var(--border) 4px, transparent 4px, transparent 8px); }
            .rapid-dot { width: 12px; height: 12px; border-radius: 50%; flex-shrink: 0; z-index: 1; border: 2px solid white; }
            .rapid-dot.pickup { background: var(--primary); }
            .rapid-dot.drop { background: var(--accent); }
            .skeleton { background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%); background-size: 200% 100%; animation: skeleton-loading 1.5s infinite; border-radius: 8px; }
            @keyframes skeleton-loading { 0% { background-position: 200% 0; } 100% { background-position: -200% 0; } }
        </style>

        <div class="orders-tabs">
            <div class="order-tab active" id="tab-food" onclick="showOrders('food')">Food Orders</div>
            <div class="order-tab" id="tab-rapid" onclick="showOrders('rapid')">Rapid Pickups</div>
        </div>
        
        <!-- FOOD ORDERS -->
        <div id="foodOrdersContent">
            <?php if (empty($orders)): ?>
                <div class="checkout-card" style="background:white; border-radius:var(--radius); padding:48px; box-shadow:var(--shadow-sm); text-align:center">
                    <div style="width:100px; height:100px; background:var(--bg); border:2.5px dashed var(--border); border-radius:50%; display:flex; align-items:center; justify-content:center; color:var(--text-muted); margin:0 auto 24px">
                        <i data-lucide="shopping-bag" style="width:40px; height:40px; opacity:0.4"></i>
                    </div>
                    <h3 style="font-size:20px; font-weight:800; color:var(--primary-dark); margin-bottom:12px">No Orders Yet!</h3>
                    <p style="color:var(--text-muted); font-weight:600; margin-bottom:28px">Your orders will appear here once you place them.</p>
                    <button class="form-btn" style="max-width:260px; margin:0 auto" onclick="window.location.href='index.php'">
                        Start Shopping Now <i data-lucide="arrow-right" style="margin-left:8px"></i>
                    </button>
                </div>
            <?php else: ?>
                <div style="display:flex; flex-direction:column; gap:20px">
                    <?php foreach ($orders as $order): ?>
                        <?php
                        $itemStmt = $pdo->prepare("SELECT SUM(quantity) as total_qty FROM order_items WHERE order_id = ?");
                        $itemStmt->execute([$order['id']]);
                        $itemsCount = $itemStmt->fetch(PDO::FETCH_ASSOC)['total_qty'] ?? 0;
                        
                        $statusColor = 'var(--primary)';
                        if ($order['status'] === 'Cancelled') $statusColor = 'var(--accent)';
                        if ($order['status'] === 'Delivered') $statusColor = '#10b981';
                        ?>
                        <div class="checkout-card" style="background:white; border-radius:var(--radius); padding:24px; box-shadow:var(--shadow-sm); cursor:pointer" onclick="window.location.href='track-order.php?order_id=<?= $order['id'] ?>'">
                            <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:16px">
                                <div>
                                    <div style="font-size:14px; font-weight:800; color:var(--primary-dark); margin-bottom:4px">Order #<?= $order['id'] ?></div>
                                    <div style="font-size:12px; color:var(--text-muted); font-weight:600">Placed on <?= date('d M Y, h:i A', strtotime($order['created_at'])) ?></div>
                                </div>
                                <div style="background:<?= $statusColor ?>15; color:<?= $statusColor ?>; font-size:11px; font-weight:800; padding:6px 12px; border-radius:12px; display:flex; align-items:center; gap:6px">
                                    <i data-lucide="package" style="width:12px; height:12px"></i> <?= $order['status'] ?>
                                </div>
                            </div>
                            
                            <div style="display:flex; justify-content:space-between; align-items:center; border-top:1.5px solid var(--bg); padding-top:16px">
                                <div style="font-size:13px; font-weight:700; color:var(--text-dark)">
                                    <?= $itemsCount ?> Items · ₹<?= number_format($order['grand_total'], 2) ?>
                                </div>
                                <div style="color:var(--primary); font-size:13px; font-weight:800; display:flex; align-items:center; gap:4px">
                                    View Details <i data-lucide="chevron-right" style="width:16px; height:16px"></i>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- RAPID ORDERS -->
        <div id="rapidOrdersContent" style="display:none">
            <div id="rapidOrdersList">
                <div style="height:140px; width:100%" class="skeleton"></div>
                <div style="height:140px; width:100%; margin-top:20px" class="skeleton"></div>
            </div>
        </div>
    </div>
</main>

<script>
    function showOrders(type) {
        document.querySelectorAll('.order-tab').forEach(t => t.classList.remove('active'));
        document.getElementById('tab-' + type).classList.add('active');
        
        if (type === 'food') {
            document.getElementById('foodOrdersContent').style.display = 'block';
            document.getElementById('rapidOrdersContent').style.display = 'none';
        } else {
            document.getElementById('foodOrdersContent').style.display = 'none';
            document.getElementById('rapidOrdersContent').style.display = 'block';
            loadRapidOrders();
        }
    }

    async function loadRapidOrders() {
        const list = document.getElementById('rapidOrdersList');
        try {
            const resp = await fetch('api/rapid/list.php');
            const data = await resp.json();
            
            if (data.status === 'success' && data.orders.length > 0) {
                list.innerHTML = data.orders.map(o => `
                    <div class="rapid-order-card">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px">
                            <div style="font-size:13px; font-weight:800; color:var(--primary-dark)">Rapid Pickup #R-${o.id}</div>
                            <div class="status-pill sp-${o.status.toLowerCase()}" style="font-size:10px; padding:4px 10px">${o.status}</div>
                        </div>
                        <div style="background:var(--bg); border-radius:12px; padding:15px">
                            <div class="rapid-address-flow">
                                <div class="rapid-line"></div>
                                <div class="rapid-dot pickup" style="margin-top:4px"></div>
                                <div style="font-size:12px; font-weight:600; color:var(--text)">${o.pickup_address}</div>
                            </div>
                            <div class="rapid-address-flow" style="margin-bottom:0">
                                <div class="rapid-dot drop" style="margin-top:4px"></div>
                                <div style="font-size:12px; font-weight:600; color:var(--text)">${o.drop_address}</div>
                            </div>
                        </div>
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-top:16px; padding-top:12px; border-top:1px solid var(--border)">
                            <div style="font-size:13px; font-weight:800; color:var(--primary)">₹${parseFloat(o.price).toFixed(2)}</div>
                            <div style="font-size:11px; font-weight:600; color:var(--text-muted)">${new Date(o.created_at).toLocaleDateString(undefined, {day:'numeric', month:'short', hour:'2-digit', minute:'2-digit'})}</div>
                        </div>
                    </div>
                `).join('');
            } else {
                list.innerHTML = `
                    <div style="text-align:center; padding:40px; background:white; border-radius:var(--radius); box-shadow:var(--shadow-sm)">
                        <div style="width:70px; height:70px; background:var(--bg); border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 15px; color:var(--text-muted)">
                            <i data-lucide="bike" style="width:30px; height:30px"></i>
                        </div>
                        <p style="font-weight:700; color:var(--text-muted)">No pickup requests found</p>
                        <a href="pickup-drop.php" style="display:inline-block; margin-top:15px; color:var(--primary); font-weight:800; text-decoration:none">Book Your First Pickup →</a>
                    </div>
                `;
            }
            if (window.lucide) lucide.createIcons();
        } catch (e) {
            list.innerHTML = '<p style="text-align:center; color:var(--danger); padding:20px">Failed to load rapid orders</p>';
        }
    }

    // Auto-switch tab from URL
    window.addEventListener('load', () => {
        const params = new URLSearchParams(window.location.search);
        if (params.get('tab') === 'rapid') {
            showOrders('rapid');
        }
    });
</script>

<?php
include 'includes/modals.php';
include 'includes/footer.php';
?>
<!-- new chg -->