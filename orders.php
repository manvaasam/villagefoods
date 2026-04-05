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
                    // Get items count for this order
                    $itemStmt = $pdo->prepare("SELECT SUM(quantity) as total_qty FROM order_items WHERE order_id = ?");
                    $itemStmt->execute([$order['id']]);
                    $itemsCount = $itemStmt->fetch(PDO::FETCH_ASSOC)['total_qty'] ?? 0;
                    
                    // Status color logic
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
</main>

<?php
include 'includes/modals.php';
include 'includes/footer.php';
?>
