<?php
require_once 'includes/db.php';
$category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : null;
$category_name = isset($_GET['name']) ? htmlspecialchars($_GET['name']) : 'Category';

$pageTitle = "$category_name — Village Foods";
include 'includes/header.php';
include 'includes/navbar.php';
?>

<main class="container">
    <div class="section" style="padding-top: 20px;">
        <div class="section-header" style="margin-bottom: 30px;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <a href="index" class="back-btn" style="width: 40px; height: 40px; background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: var(--text-main); text-decoration: none; transition: all 0.2s;">
                    <i data-lucide="chevron-left"></i>
                </a>
                <h2 class="section-title" style="margin: 0; font-size: 28px; font-weight: 900;">
                    <?php echo $category_name; ?> <span>Shops</span>
                </h2>
            </div>
            <span class="section-link" id="shopCountHeader">Fetching shops...</span>
        </div>

        <div class="shops-grid" id="categoryShopsGrid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 24px;">
            <!-- Skeletons -->
            <?php for($i=0; $i<6; $i++): ?>
            <div class="skeleton-card" style="height: 280px; border-radius: 16px; background: #fff;">
                <div class="skeleton skeleton-img" style="height: 160px;"></div>
                <div class="skeleton skeleton-text" style="margin-top: 15px; margin-inline: 15px;"></div>
                <div class="skeleton skeleton-text short" style="margin-inline: 15px;"></div>
            </div>
            <?php endfor; ?>
        </div>
    </div>
</main>

<script>
document.addEventListener("DOMContentLoaded", async () => {
    const categoryId = <?php echo json_encode($category_id); ?>;
    if (!categoryId) {
        window.location.href = 'index.php';
        return;
    }

    try {
        const resp = await fetch(`api/shops/list.php?category_id=${categoryId}`);
        const data = await resp.json();
        const container = document.getElementById('categoryShopsGrid');
        const header = document.getElementById('shopCountHeader');

        if (data.status === 'success' && data.data.length > 0) {
            header.textContent = `${data.data.length} shops found`;
            container.innerHTML = data.data.map(shop => `
                <div class="shop-card fade-in-up ${shop.status !== 'active' ? 'shop-closed' : ''}" onclick="window.location.href='shop_details.php?id=${shop.id}'" style="background:#fff; border-radius:16px; overflow:hidden; box-shadow:0 4px 12px -2px rgba(0,0,0,0.05); border:1px solid rgba(0,0,0,0.05); cursor:pointer; transition:all 0.3s cubic-bezier(0.4, 0, 0.2, 1); position:relative; ${shop.status !== 'active' ? 'filter: grayscale(0.5); opacity: 0.8;' : ''}" onmouseover="this.style.transform='translateY(-6px)'; this.style.boxShadow='0 12px 24px -4px rgba(0,0,0,0.1)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px -2px rgba(0,0,0,0.05)'">
                    <div style="height:160px; overflow:hidden; position:relative">
                        <img loading="lazy" src="${shop.shop_image || 'assets/images/placeholder.png'}" alt="${shop.shop_name}" style="width:100%; height:100%; object-fit:cover;">
                        ${shop.status !== 'active' ? `
                            <div style="position:absolute; inset:0; background:rgba(0,0,0,0.4); display:flex; align-items:center; justify-content:center; z-index:2">
                                <span style="background:#ef4444; color:#fff; padding:4px 12px; border-radius:6px; font-weight:900; font-size:12px; text-transform:uppercase; letter-spacing:1px;">Closed</span>
                            </div>
                        ` : ''}
                        <div style="position:absolute; top:12px; right:12px; background:rgba(255,255,255,0.95); backdrop-filter:blur(4px); padding:4px 10px; border-radius:20px; font-size:12px; font-weight:700; display:flex; align-items:center; gap:4px; box-shadow:0 2px 8px rgba(0,0,0,0.1);">
                            <i data-lucide="star" style="width:14px; height:14px; color:#fbbf24; fill:#fbbf24"></i> ${shop.rating || 4.5}
                        </div>
                    </div>
                    <div style="padding:20px;">
                        <h3 style="margin:0 0 8px 0; font-size:18px; font-weight:800; color:#111827;">${shop.shop_name}</h3>
                        <div style="color:#6b7280; font-size:14px; margin-bottom:16px; display:flex; align-items:flex-start; gap:6px; line-height:1.4;">
                            <i data-lucide="map-pin" style="width:14px; height:14px; color:var(--primary); margin-top:3px; flex-shrink:0;"></i>
                            <span style="display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden;">${shop.address}</span>
                        </div>
                        <div style="display:flex; align-items:center; justify-content:space-between; margin-top:auto;">
                            <span style="font-size:12px; font-weight:700; color:${shop.status === 'active' ? '#10b981' : '#ef4444'}; background:${shop.status === 'active' ? 'rgba(16,185,129,0.1)' : 'rgba(239,68,68,0.1)'}; padding:4px 10px; border-radius:20px; display:inline-flex; align-items:center; gap:4px">
                                <div style="width:6px; height:6px; border-radius:50%; background:${shop.status === 'active' ? '#10b981' : '#ef4444'}"></div> ${shop.status === 'active' ? 'Open Now' : 'Closed'}
                            </span>
                            <div style="width:32px; height:32px; background:var(--primary); border-radius:50%; display:flex; align-items:center; justify-content:center; color:white;">
                                <i data-lucide="arrow-right" style="width:16px; height:16px"></i>
                            </div>
                        </div>
                    </div>
                </div>
            `).join("");
            if (window.lucide) {
                lucide.createIcons({
                    attrs: { 'data-lucide': true },
                    scope: container
                });
            }
        } else {
            header.textContent = 'No shops found';
            container.innerHTML = `
                <div style="grid-column:1/-1; text-align:center; padding:60px 20px; color:var(--text-muted);">
                    <div style="font-size:64px; margin-bottom:20px;"><i data-lucide="store" style="width:80px; height:80px; margin:0 auto; opacity:0.2;"></i></div>
                    <h3 style="font-size:20px; font-weight:800; color:var(--text-main); margin-bottom:8px;">No shops in this category yet</h3>
                    <p style="font-size:15px;">Check back soon as we add more local partners!</p>
                    <a href="index" class="btn btn-primary" style="margin-top:24px; display:inline-flex; align-items:center; gap:8px;">
                        <i data-lucide="home"></i> Back to Home
                    </a>
                </div>
            `;
            if (window.lucide) lucide.createIcons({ scope: container });
        }
    } catch (e) {
        console.error("Failed to load category shops", e);
    }
});
</script>

<?php
include 'includes/modals.php';
include 'includes/footer.php';
?>
