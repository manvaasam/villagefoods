<?php
require_once 'includes/db.php';

$shop_id = isset($_GET['id']) ? intval($_GET['id']) : null;
$product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : null;

// If product_id is provided, resolve shop_id and set meta tags for sharing
if ($product_id) {
    $stmt = $pdo->prepare("SELECT p.name, p.price, p.old_price, p.image_url, p.shop_id, s.shop_name FROM products p JOIN shops s ON p.shop_id = s.id WHERE p.id = ?");
    $stmt->execute([$product_id]);
    $productMeta = $stmt->fetch();
    
    if ($productMeta) {
        $pName = htmlspecialchars($productMeta['name']);
        $pPrice = htmlspecialchars($productMeta['price']);
        $sName = htmlspecialchars($productMeta['shop_name']);
        
        $pageTitle = "$pName | Village Foods";
        $pageDescription = "Buy $pName for just ₹$pPrice at $sName. Fast delivery from Village Foods!";
        
        if ($productMeta['image_url']) {
            $ogImage = $productMeta['image_url'];
        }
        if (!$shop_id) $shop_id = $productMeta['shop_id'];
    }
}

if (!$shop_id) {
    echo "<script>window.location.href='index.php';</script>";
    exit;
}

if (!isset($pageTitle)) {
    $pageTitle = 'Shop Details — Village Foods';
}

include 'includes/header.php';
include 'includes/navbar.php';
?>
<style>
  .shop-hero-container {
    margin-top: 20px;
    border-radius: 24px;
    overflow: hidden;
    position: relative;
    background: #fff;
    box-shadow: var(--shadow-lg);
    border: 1px solid rgba(0,0,0,0.05);
  }
  .shop-hero-banner {
    height: 300px;
    width: 100%;
    position: relative;
    background: var(--border-light);
    overflow: hidden;
  }
  .shop-info-wrapper {
    padding: 0 32px 32px;
    position: relative;
    margin-top: -45px;
  }
  .shop-avatar-container {
    width: 140px;
    height: 140px;
    border-radius: 24px;
    background: #fff;
    padding: 8px;
    box-shadow: var(--shadow);
    border: 1px solid var(--border-light);
    flex-shrink: 0;
  }
  .shop-details-flex {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    gap: 16px;
  }
  .shop-name-title {
    margin: 0;
    font-size: 36px;
    font-weight: 900;
    color: var(--primary);
    letter-spacing: -0.5px;
  }

  @media (max-width: 768px) {
    .shop-hero-banner {
      height: 200px;
    }
    .shop-info-wrapper {
      padding: 0 20px 24px;
      margin-top: -40px;
    }
    .shop-avatar-container {
      width: 100px;
      height: 100px;
      border-radius: 18px;
      padding: 5px;
    }
    .shop-details-flex {
      flex-direction: column;
      align-items: flex-start;
      gap: 20px;
    }
    .shop-name-title {
      font-size: 26px;
      margin-top: 10px;
    }
    .rating-glass {
      width: 100%;
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 12px 20px !important;
    }
    .features-bar {
      gap: 12px !important;
      margin-top: 0px !important;
      padding-top: 16px !important;
      flex-wrap: nowrap !important;
      overflow-x: auto;
      justify-content: flex-start;
      -webkit-overflow-scrolling: touch;
      padding-bottom: 8px;
    }
    .features-bar::-webkit-scrollbar {
      display: none;
    }
    .features-bar > div {
      flex-shrink: 0;
      white-space: nowrap;
      font-size: 13px !important;
    }
  }
</style>

<main class="container" style="padding-top: 0;">
  <!-- FREE DELIVERY PROMO BANNER -->
  <!-- <div class="free-delivery-banner fade-in-up">
    <div style="display:flex; align-items:center; justify-content:center; gap:10px;">
      <i data-lucide="zap" style="width:18px; height:18px; fill:white;"></i>
      <span>Free delivery on <strong>eligible orders</strong> above <strong>₹200</strong></span>
    </div>
  </div> -->

  <!-- SHOP HERO SECTION -->
  <div class="shop-hero-container">
    
    <!-- Hero Banner -->
    <div class="shop-hero-banner" id="shopHeroBanner">
      <!-- Main Image -->
      <div id="shopImageCover" style="width: 100%; height: 100%;">
          <div class="spinner-container" style="display:flex; align-items:center; justify-content:center; height:100%;">
              <div class="spinner"></div>
          </div>
      </div>
      <!-- Gradient Overlay for readability -->
      <div style="position: absolute; bottom: 0; left: 0; right: 0; height: 150px; background: linear-gradient(to top, rgba(0,0,0,0.6), transparent);"></div>
    </div>

    <!-- Shop Info Card (Overlapping) -->
    <div class="shop-info-wrapper">
      <div style="display: flex; align-items: flex-end; gap: 24px; flex-wrap: wrap;">
        
        <!-- Shop Avatar -->
        <div id="shopAvatarContainer" class="shop-avatar-container">
            <div id="shopAvatarImage" style="width:100%; height:100%; border-radius: 18px; overflow: hidden; background: var(--bg);">
                 <!-- Placeholder or Logo will be injected here -->
            </div>
        </div>

        <!-- Details -->
        <div style="flex: 1; min-width: 280px; padding-bottom: 5px;">
           <div class="shop-details-flex">
              <div>
                <h1 id="shopNameTitle" class="shop-name-title">Loading...</h1>
                <p id="shopAddressText" style="margin:4px 0 0 0; color: var(--text-muted); font-size: 15px; display: flex; align-items: center; gap: 6px; font-weight: 500;">
                   <i data-lucide="map-pin" style="width:16px; height:16px; color: var(--primary)"></i> <span>...</span>
                </p>
              </div>
              
              <div class="rating-glass" style="background: var(--white); border: 1px solid var(--border-light); padding: 12px 20px; border-radius: 20px; box-shadow: var(--shadow-sm); text-align: center; margin-bottom: 5px;">
                 <div style="color: var(--yellow); font-weight: 900; font-size: 22px; display: flex; align-items: center; justify-content: center; gap: 6px;">
                    <i data-lucide="star" style="width:20px; height:20px; fill: var(--yellow);"></i> <span id="shopRatingText">4.5</span>
                 </div>
                 <div style="font-size: 11px; color: var(--text-muted); font-weight: 700; text-transform: uppercase; margin-top: 2px; letter-spacing: 0.5px;">Avg Rating</div>
              </div>
           </div>
        </div>
      </div>

      <!-- Features Bar -->
      <div class="features-bar" style="display: flex; gap: 24px; margin-top: 32px; padding-top: 24px; border-top: 1px solid var(--border-light);">
        <div style="display: flex; align-items: center; gap: 10px; background: #fff7ed; color: #9a3412; padding: 8px 16px; border-radius: 12px; font-weight: 700; font-size: 14px; border: 1px solid #ffedd5;">
           <i data-lucide="leaf" style="width:18px; height:18px;"></i> 100% Fresh
        </div>
        <div style="display: flex; align-items: center; gap: 10px; color: #374151; font-weight: 600; font-size: 14px;">
           <i data-lucide="clock" style="width:18px; height:18px; color: var(--primary)"></i> 30-45 mins delivery
        </div>
        <div style="display: flex; align-items: center; gap: 10px; color: #374151; font-weight: 600; font-size: 14px;">
           <i data-lucide="check-circle-2" style="width:18px; height:18px; color: #10b981"></i> <span id="shopStatusText">Open</span>
        </div>
      </div>
    </div>
  </div>

  <!-- SHOP PRODUCTS -->
  <div class="section" id="productsSection">
    <div class="section-header">
      <h2 class="section-title">Shop <span>Products</span></h2>
      <span class="section-link" id="productCountHeader">Fetching products...</span>
    </div>

    <div class="products-grid" id="productsGrid">
      <!-- Loading state -->
      <div style="grid-column:1/-1;text-align:center;padding:40px;color:var(--text-muted)">
        <div class="spinner"></div>
        <p>Loading products...</p>
      </div>
    </div>
  </div>
</main>

<!-- Clear Cart Confirmation Modal -->
<div id="clearCartModal" class="modal-overlay">
  <div class="modal-content" style="max-width: 400px;text-align:center;">
    <div style="background:#fef2f2;width:64px;height:64px;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;">
      <i data-lucide="alert-triangle" style="width:32px;height:32px;color:#ef4444;"></i>
    </div>
    <h2 style="margin:0 0 12px 0;">Replace Cart Content?</h2>
    <p style="color:var(--text-muted);font-size:14px;margin:0 0 24px 0;line-height:1.5">
      Your cart contains items from another shop. Do you want to clear the cart and add this item?
    </p>
    <div style="display:flex;gap:12px;">
      <button onclick="Modal.close('clearCartModal')" class="btn" style="flex:1;background:#f3f4f6;color:#374151;border:none;">Cancel</button>
      <button onclick="confirmClearCart()" class="btn" style="flex:1;background:var(--primary);color:#fff;border:none;">Clear & Add</button>
    </div>
  </div>
</div>

<script>
let currentShopId = <?php echo $shop_id; ?>;
window.currentShopId = currentShopId;
let pendingAddProductId = null;

document.addEventListener("DOMContentLoaded", () => {
    fetchShopDetails();
    ProductRenderer.fetchProducts("all", "", false, currentShopId);
});

async function fetchShopDetails() {
    try {
        const resp = await fetch(`api/shops/get.php?id=${currentShopId}`);
        const result = await resp.json();
        if (result.status === 'success') {
            const shop = result.data;
            document.getElementById('shopNameTitle').textContent = shop.shop_name;
            document.querySelector('#shopAddressText span').textContent = shop.address;
            document.getElementById('shopRatingText').textContent = shop.rating || '4.5';
            if (shop.shop_image) {
                // Set Hero Banner Image
                document.getElementById('shopImageCover').innerHTML = `
                    <img src="${shop.shop_image}" style="width:100%;height:100%;object-fit:cover; filter: blur(0px) brightness(0.9);">
                `;
                // Set Avatar Image
                document.getElementById('shopAvatarImage').innerHTML = `
                    <img src="${shop.shop_image}" style="width:100%;height:100%;object-fit:cover;">
                `;
            } else {
                const placeholder = 'assets/images/placeholder.png';
                document.getElementById('shopImageCover').innerHTML = `<img src="${placeholder}" style="width:100%;height:100%;object-fit:cover;">`;
                document.getElementById('shopAvatarImage').innerHTML = `<img src="${placeholder}" style="width:100%;height:100%;object-fit:cover;">`;
            }

            // Update Status Badge
            const statusText = document.getElementById('shopStatusText');
            const statusIcon = statusText.previousElementSibling;
            const heroBanner = document.getElementById('shopImageCover');
            const shopAvatar = document.getElementById('shopAvatarImage');

            if (shop.status === 'active') {
                statusText.textContent = 'Open Now';
                statusText.style.color = '#10b981';
                statusIcon.style.color = '#10b981';
                statusIcon.setAttribute('data-lucide', 'check-circle-2');
                heroBanner.style.filter = 'none';
                shopAvatar.style.filter = 'none';
            } else {
                statusText.textContent = 'Closed';
                statusText.style.color = '#ef4444';
                statusIcon.style.color = '#ef4444';
                statusIcon.setAttribute('data-lucide', 'x-circle');
                heroBanner.style.filter = 'grayscale(0.8) brightness(0.7)';
                shopAvatar.style.filter = 'grayscale(0.8)';
                
                // Add a "CLOSED" overlay to the avatar if not already there
                if (!document.getElementById('avatarClosedOverlay')) {
                    const overlay = document.createElement('div');
                    overlay.id = 'avatarClosedOverlay';
                    overlay.style = "position:absolute; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); display:flex; align-items:center; justify-content:center; color:#fff; font-weight:900; font-size:16px; text-transform:uppercase; z-index:2; border-radius:18px;";
                    overlay.textContent = "Closed";
                    document.getElementById('shopAvatarContainer').style.position = 'relative';
                    document.getElementById('shopAvatarContainer').appendChild(overlay);
                }
            }
            
            // Re-initialize Lucide icons
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        }
    } catch (e) {
        console.error("Failed to load shop details", e);
    }
}



function promptClearCart(productId) {
    pendingAddProductId = productId;
    Modal.open('clearCartModal');
}

function confirmClearCart() {
    Cart.clear();
    Modal.close('clearCartModal');
    if (pendingAddProductId) {
        Cart.add(pendingAddProductId);
        pendingAddProductId = null;
    }
}
</script>

<?php
include 'includes/modals.php';
include 'includes/footer.php';
?>
