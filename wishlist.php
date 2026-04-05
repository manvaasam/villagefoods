<?php
$pageTitle = 'My Wishlist — Village Foods';
include 'includes/header.php';

// Redirect if not logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo "<script>window.location.href='index.php';</script>";
    exit;
}

include 'includes/navbar.php';
?>
<?php
// Fetch wishlist items
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT p.*, c.slug as category_slug, 1 as in_wishlist
                      FROM user_wishlist w
                      JOIN products p ON w.product_id = p.id
                      LEFT JOIN categories c ON p.category_id = c.id
                       WHERE w.user_id = ? AND p.is_available = 1
                       ORDER BY w.created_at DESC");
$stmt->execute([$user_id]);
$wishlistProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<main class="container" style="padding-top:120px; padding-bottom:80px">
    <div style="max-width:1100px; margin:0 auto">
        <div class="section-header">
            <h2 class="section-title">My <span>Wishlist</span></h2>
            <a href="index.php" class="section-link">← Add More Items</a>
        </div>
        
        <?php if (empty($wishlistProducts)): ?>
            <div class="checkout-card" style="background:white; border-radius:var(--radius); padding:48px; box-shadow:var(--shadow-sm); text-align:center">
                <div style="width:100px; height:100px; background:var(--bg); border:2.5px dashed var(--border); border-radius:50%; display:flex; align-items:center; justify-content:center; color:var(--text-muted); margin:0 auto 24px">
                    <i data-lucide="heart" style="width:40px; height:40px; opacity:0.4"></i>
                </div>
                <h3 style="font-size:20px; font-weight:800; color:var(--primary-dark); margin-bottom:12px">Your Wishlist is Empty!</h3>
                <p style="color:var(--text-muted); font-weight:600; margin-bottom:28px">Save your favorite items here to find them easily later.</p>
                <button class="form-btn" style="max-width:260px; margin:0 auto" onclick="window.location.href='index.php'">
                    Find Products <i data-lucide="arrow-right" style="margin-left:8px"></i>
                </button>
            </div>
        <?php else: ?>
            <div class="products-grid" id="wishlistGrid">
                <!-- Data will be rendered via PHP but we can use JS standard if we want -->
                 <!-- For now, let's just use the JS standard by passing the data -->
            </div>
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    const products = <?= json_encode($wishlistProducts) ?>;
                    ProductRenderer.renderGrid(products, 'wishlistGrid');
                });
            </script>
        <?php endif; ?>
    </div>
</main>

<?php
include 'includes/modals.php';
include 'includes/footer.php';
?>
