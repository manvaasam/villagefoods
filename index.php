<?php
$pageTitle = 'Village Foods — Fresh & Fast Delivery in Thirupathur';
$pageDescription = 'Order fresh vegetables, premium meats, bakery items, and groceries from local farms. 30-minute delivery in Thirupathur District starting at ₹40. 100% Secure & Fresh.';
$pageKeywords = 'Thirupathur grocery delivery, farm fresh veggies, fresh meat delivery online, Village Foods Thirupathur, online supermarket';
$ogImage = 'assets/images/village_quick-1.png';

include 'includes/header.php';
include 'includes/navbar.php';
?>
<!-- ====== JSON-LD SCHEMA ====== -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "LocalBusiness",
  "name": "Village Foods",
  "image": "https://villagefoods.in/assets/images/logo/VillageFoods Delivery Logo.png",
  "description": "Farm fresh vegetables, premium meats, and bakery items delivered to your doorstep in 30 minutes across Thirupathur District.",
  "url": "https://villagefoods.in",
  "telephone": "+<?php echo Settings::get('store_phone', '916380091001'); ?>",
  "priceRange": "₹₹",
  "address": {
    "@type": "PostalAddress",
    "addressLocality": "Thirupathur",
    "addressRegion": "Tamil Nadu",
    "addressCountry": "IN"
  },
  "potentialAction": {
    "@type": "OrderAction",
    "target": {
      "@type": "EntryPoint",
      "urlTemplate": "https://villagefoods.in/index.php",
      "inLanguage": "en-US",
      "actionPlatform": [
        "http://schema.org/DesktopWebPlatform",
        "http://schema.org/MobileWebPlatform"
      ]
    },
    "deliveryMethod": [
      "http://purl.org/goodrelations/v1#DeliveryModeOwnFleet"
    ]
  }
}
</script>

<!-- ====== HERO ====== -->
<section class="hero">
  <div class="hero-slider">
    <div class="hero-slide"></div>
    <div class="hero-slide"></div>
    <div class="hero-slide"></div>
    <div class="hero-slide"></div>
    <div class="hero-slide"></div>
    <div class="hero-slide"></div>
    <div class="hero-slide"></div>
  </div>
  <div class="hero-inner">
    <div class="hero-content fade-in-up">
      <div class="hero-badge">
        <span class="hero-badge-dot"></span>
        Delivering in 30 minutes or less
      </div>
      <h1>Farm Fresh, Delivered<br>to Your <span>Doorstep</span></h1>
      <div style="display:flex; gap:12px; margin-bottom:24px; flex-wrap:wrap">
        <div style="background:rgba(255,255,255,0.1); backdrop-filter:blur(10px); padding:8px 16px; border-radius:12px; border:1px solid rgba(255,255,255,0.2); display:flex; align-items:center; gap:10px">
          <div style="width:32px; height:32px; background:white; border-radius:8px; display:flex; align-items:center; justify-content:center; overflow:hidden">
            <img src="assets/images/village_quick-1.png" alt="Quick" style="width:100%; height:100%; object-fit:cover">
          </div>
          <div style="text-align:center">
            <div style="font-size:10px; color:rgba(255,255,255,0.6); font-weight:700; text-transform:uppercase; line-height:1">Village</div>
            <div style="font-size:14px; color:white; font-weight:800; line-height:1.1">Quick Service</div>
          </div>
        </div>
        <div style="background:rgba(255,255,255,0.1); backdrop-filter:blur(10px); padding:8px 16px; border-radius:12px; border:1px solid rgba(255,255,255,0.2); display:flex; align-items:center; gap:10px">
          <div style="width:32px; height:32px; background:#fbbf24; border-radius:8px; display:flex; align-items:center; justify-content:center; color:white"><i data-lucide="shield-check" style="width:18px; height:18px"></i></div>
          <div style="text-align:center">
            <div style="font-size:10px; color:rgba(255,255,255,0.6); font-weight:700; text-transform:uppercase; line-height:1">Verified</div>
            <div style="font-size:14px; color:white; font-weight:800; line-height:1.1">Partners</div>
          </div>
        </div>
      </div>
      <p>Order fresh vegetables, meats, bakery & more. From our village farms directly to your kitchen — fast, fresh, and affordable.</p>
      <div class="hero-search-box">
        <input type="text" placeholder="What are you craving today?" id="heroSearchInput" oninput="filterProducts(this.value)" onkeypress="if(event.key === 'Enter') performHeroSearch()">
        <button onclick="performHeroSearch()">Search Now</button>
      </div>
      <div class="hero-stats">
        <div class="hero-stat">
          <div class="hero-stat-num">50K+</div>
          <div class="hero-stat-label">Happy Customers</div>
        </div>
        <div class="hero-stat">
          <div class="hero-stat-num">30 min</div>
          <div class="hero-stat-label">Avg Delivery</div>
        </div>
        <div class="hero-stat">
          <div class="hero-stat-num">500+</div>
          <div class="hero-stat-label">Products</div>
        </div>
        <div class="hero-stat">
          <div class="hero-stat-num">4.8 <i data-lucide="star" style="width:14px;height:14px;display:inline-block;vertical-align:middle;fill:#fbbf24;color:#fbbf24"></i></div>
          <div class="hero-stat-label">App Rating</div>
        </div>
      </div>
    </div>
  </div>
  <div class="hero-wave"></div>
</section>

<!-- ====== BANNER STRIP ====== -->
<div class="banner-strip">
  <div class="banner-strip-marquee">
    <div class="banner-strip-item"><i data-lucide="rocket"></i> Free delivery on orders above ₹199</div>
    <div class="banner-strip-item"><i data-lucide="leaf"></i> 100% Fresh & Natural Products</div>
    <div class="banner-strip-item"><i data-lucide="zap"></i> Express 30-min delivery</div>
    <div class="banner-strip-item"><i data-lucide="lock"></i> Secure Razorpay Payments</div>
    <!-- Duplicated sets for a perfect, never-ending infinite marquee -->
    <div class="banner-strip-item"><i data-lucide="rocket"></i> Free delivery on orders above ₹199</div>
    <div class="banner-strip-item"><i data-lucide="leaf"></i> 100% Fresh & Natural Products</div>
    <div class="banner-strip-item"><i data-lucide="zap"></i> Express 30-min delivery</div>
    <div class="banner-strip-item"><i data-lucide="lock"></i> Secure Razorpay Payments</div>
    <div class="banner-strip-item"><i data-lucide="rocket"></i> Free delivery on orders above ₹199</div>
    <div class="banner-strip-item"><i data-lucide="leaf"></i> 100% Fresh & Natural Products</div>
    <div class="banner-strip-item"><i data-lucide="zap"></i> Express 30-min delivery</div>
    <div class="banner-strip-item"><i data-lucide="lock"></i> Secure Razorpay Payments</div>
    <div class="banner-strip-item"><i data-lucide="rocket"></i> Free delivery on orders above ₹199</div>
    <div class="banner-strip-item"><i data-lucide="leaf"></i> 100% Fresh & Natural Products</div>
    <div class="banner-strip-item"><i data-lucide="zap"></i> Express 30-min delivery</div>
    <div class="banner-strip-item"><i data-lucide="lock"></i> Secure Razorpay Payments</div>
  </div>
</div>

<!-- ====== MAIN CONTENT ====== -->
<main class="container">

  <!-- FREE DELIVERY PROMO BANNER -->
  <!-- <div class="free-delivery-banner fade-in-up" style="margin-bottom:24px;">
    <div style="display:flex; align-items:center; justify-content:center; gap:10px;">
      <i data-lucide="zap" style="width:18px; height:18px; fill:white;"></i>
      <span>Free delivery on <strong>eligible orders</strong> above <strong>₹200</strong></span>
    </div>
  </div> -->

  <!-- CATEGORIES -->
  <div class="section">
    <div class="section-header">
      <h2 class="section-title">Shop by <span>Category</span></h2>
      <a href="#productsSection" class="section-link">View All →</a>
    </div>
    <div class="categories-grid" id="categoriesGrid">
      <!-- Skeletons for initial load -->
      <?php for($i=0; $i<6; $i++): ?>
      <div class="category-item">
        <div class="category-circle skeleton"></div>
        <div class="skeleton-text short" style="margin: 0 auto"></div>
      </div>
      <?php endfor; ?>
    </div>
  </div>

  <!-- PROMO BANNERS -->
  <div class="section" style="padding-top:0;padding-bottom:24px">
    <div class="promo-grid">
      <div class="promo-card promo-main">
        <div class="promo-emoji"><i data-lucide="utensils"></i></div>
        <div class="promo-card-label">Today's Special</div>
        <div class="promo-card-title">Farm Fresh Chicken<br>at ₹169/kg</div>
        <div class="promo-card-sub">Order before 6PM — Limited stock available daily</div>
        <div class="promo-btn" onclick="goToCategory('chicken')">Shop Now <i data-lucide="arrow-right"></i></div>
      </div>
      <div class="promo-sub-grid">
        <div class="promo-card promo-sub" onclick="goToCategory('bakery')">
          <div class="promo-emoji" style="font-size:40px;top:50%;right:24px"><i data-lucide="wheat" style="width:48px;height:48px"></i></div>
          <div class="promo-card-label">Bakery Fresh</div>
          <div class="promo-card-title" style="font-size:18px">Freshly Baked Daily</div>
          <div class="promo-btn" style="font-size:11px;padding:6px 14px">Explore <i data-lucide="arrow-right"></i></div>
        </div>
        <div class="promo-card promo-sub2" onclick="goToCategory('veg')">
          <div class="promo-emoji" style="font-size:40px;top:50%;right:24px"><i data-lucide="carrot" style="width:48px;height:48px"></i></div>
          <div class="promo-card-label">Organic Range</div>
          <div class="promo-card-title" style="font-size:18px">10% Off on Organics</div>
          <div class="promo-btn" style="font-size:11px;padding:6px 14px">Shop <i data-lucide="arrow-right"></i></div>
        </div>
      </div>
    </div>
  </div>

  <!-- NEARBY SHOPS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
  <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
  
  <div class="section" style="padding-top:0" id="nearbyShopsSection">
    <div class="section-header" style="align-items: center; justify-content: space-between;">
      <h2 class="section-title"><i data-lucide="store" style="color:var(--primary);vertical-align:middle;margin-right:8px"></i> Nearby <span>Shops</span></h2>
      <div style="display:flex; align-items:center; gap:12px;">
        <div class="swiper-button-prev-custom" style="width:36px; height:36px; background:#fff; border:1px solid #e5e7eb; border-radius:50%; display:flex; align-items:center; justify-content:center; cursor:pointer; box-shadow:0 2px 4px rgba(0,0,0,0.05); color:var(--text-main); transition: all 0.2s;"><i data-lucide="chevron-left" style="width:20px;height:20px;"></i></div>
        <div class="swiper-button-next-custom" style="width:36px; height:36px; background:#fff; border:1px solid #e5e7eb; border-radius:50%; display:flex; align-items:center; justify-content:center; cursor:pointer; box-shadow:0 2px 4px rgba(0,0,0,0.05); color:var(--text-main); transition: all 0.2s;"><i data-lucide="chevron-right" style="width:20px;height:20px;"></i></div>
        <a href="category_shops" class="section-link" style="margin-left:8px">View All Shops →</a>
      </div>
    </div>
    
    <!-- Swiper Container -->
    <div class="swiper nearby-shops-swiper" style="padding: 10px 0 20px 0; margin: -10px 0 -20px 0; overflow: hidden;">
      <div class="swiper-wrapper" id="homeShopsGrid">
          <!-- Shop Skeletons -->
          <?php for($i=0; $i<4; $i++): ?>
          <div class="swiper-slide">
            <div class="skeleton-card" style="height: 240px; border-radius: 16px;">
              <div class="skeleton skeleton-img" style="height: 140px;"></div>
              <div class="skeleton skeleton-text"></div>
              <div class="skeleton skeleton-text short"></div>
            </div>
          </div>
          <?php endfor; ?>
      </div>
    </div>
  </div>

  <!-- PRODUCTS -->
  <div class="section" style="padding-top:0" id="productsSection">
    <div class="section-header">
      <h2 class="section-title"><i data-lucide="flame" style="color:var(--accent);vertical-align:middle;margin-right:8px"></i> Best Sellers</h2>
      <span class="section-link" id="productCountHeader">Fetching products...</span>
    </div>

    <!-- Category Pills -->
    <div class="cat-pills" id="catPills" style="margin-bottom:20px">
        <!-- Loaded via AJAX -->
    </div>

    <div class="products-grid" id="productsGrid">
      <!-- Skeletons for initial load -->
      <?php for($i=0; $i<8; $i++): ?>
      <div class="skeleton-card">
        <div class="skeleton skeleton-img"></div>
        <div class="skeleton skeleton-text"></div>
        <div class="skeleton skeleton-text short"></div>
        <div class="skeleton skeleton-text shorter"></div>
      </div>
      <?php endfor; ?>
    </div>
  </div>

  <!-- PICKUP & DROP PROMO -->
  <div class="section" style="padding-top:0">
    <div class="pickup-section">
      <div class="pickup-content">
        <div class="pickup-badge"><i data-lucide="zap" style="width:14px;height:14px;display:inline;vertical-align:middle;margin-right:4px"></i> VILLAGE QUICK SERVICE AVAILABLE</div>
        <h2>Village Quick <span>Pickup & Drop</span></h2>
        <p>Experience ultra-fast delivery! Send documents, food parcels, clothes &amp; anything across Thirupathur District. Secure &amp; Reliable!</p>
        <div class="pickup-features">
          <div class="pickup-feat"><i data-lucide="clock" style="width:14px;height:14px"></i> Within 30 mins</div>
          <div class="pickup-feat"><i data-lucide="map-pin" style="width:14px;height:14px"></i> All Thirupathur Areas</div>
          <div class="pickup-feat"><i data-lucide="indian-rupee" style="width:14px;height:14px"></i> Starting &#8377;40</div>
        </div>
        <a href="pickup-drop" class="pickup-submit" style="display:inline-flex;align-items:center;gap:8px;text-decoration:none;margin-top:8px">
          <i data-lucide="bike" style="width:16px;height:16px"></i> Book a Pickup
        </a>
      </div>

      <!-- ANIMATED DELIVERY SCENE -->
      <div class="pickup-anim-wrap">
        <!-- Road -->
        <div class="pd-road">
          <div class="pd-road-line"></div>
        </div>

        <!-- Start Pin -->
        <div class="pd-pin pd-pin-start">
          <div class="pd-pin-dot pd-pin-pulse"></div>
          <div class="pd-pin-label">Pickup</div>
        </div>

        <!-- End Pin -->
        <div class="pd-pin pd-pin-end">
          <div class="pd-pin-dot pd-pin-end-dot">
            <svg viewBox="0 0 16 16" fill="white" width="10" height="10"><path d="M8 1L1 6v9h5v-4h4v4h5V6z"/></svg>
          </div>
          <div class="pd-pin-label">Drop</div>
        </div>

        <!-- Client Provided Delivery Image -->
        <div class="pd-bike-rider">
          <img src="assets/images/village_quick-1.png" alt="Village Quick" style="width:100px; height:auto;">
          <div class="pd-speed-lines">
            <span></span><span></span><span></span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- ORDER TRACKING PREVIEW -->
  <?php
  $latestOrder = null;
  if (isset($_SESSION['user_id'])) {
      $stmt = $pdo->prepare("SELECT o.*, u.name as delivery_name, u.phone as delivery_phone 
                             FROM orders o 
                             LEFT JOIN users u ON o.delivery_boy_id = u.id 
                             WHERE o.user_id = ? 
                             ORDER BY o.created_at DESC LIMIT 1");
      $stmt->execute([$_SESSION['user_id']]);
      $latestOrder = $stmt->fetch();
  }

  if ($latestOrder):
      $status = $latestOrder['status'];
      $orderNum = $latestOrder['order_number'] ?: 'VF-' . $latestOrder['id'];
      $placedAt = date('g:i A', strtotime($latestOrder['created_at']));
      ?>
      <script>
                function getStatusLabel(status) {
                    const map = {
                        'Placed': 'Placed',
                        'Confirmed': 'Confirmed',
                        'Preparing': 'Preparing',
                        'Picked Up': 'Picked Up',
                        'On the Way': 'On the Way',
                        'Delivered': 'Delivered',
                        'Cancelled': 'Cancelled',
                        'Pending': 'Confirmed',
                        'Processing': 'Preparing'
                    };
                    return map[status] || status;
                }
      </script>
      <?php
      // Improved Status Mapping
      $steps_map = [
          'Placed' => 1,
          'Pending' => 1,
          'Confirmed' => 2,
          'Accepted' => 2,
          'Preparing' => 2,
          'Processing' => 2,
          'Ready' => 2,
          'Ready for Pickup' => 2,
          'Picked Up' => 3,
          'On the Way' => 3,
          'Delivered' => 4,
          'Cancelled' => 0
      ];
      $currentStep = $steps_map[$status] ?? 1;
  ?>
  <div class="section" style="padding-top:0">
    <div class="section-header">
      <h2 class="section-title"><i data-lucide="package" style="color:var(--accent);vertical-align:middle;margin-right:8px"></i> Track Your Order</h2>
      <a href="track-order" class="section-link">Full Tracking →</a>
    </div>
    <div class="tracking-card">
      <div class="tracking-header">
        <div>
          <div class="tracking-order-id">Order <?php echo $orderNum; ?> · Placed at <?php echo $placedAt; ?></div>
          <div style="font-size:15px;font-weight:800;margin-top:4px">₹<?php echo number_format($latestOrder['grand_total'], 2); ?> · Paid via <?php echo $latestOrder['payment_method']; ?> <i data-lucide="check" style="width:14px;height:14px;display:inline-vertical;vertical-align:middle;color:var(--primary)"></i></div>
        </div>
        <div class="tracking-status-badge">
            <?php if($status == 'On the Way' || $status == 'Picked Up'): ?>
                <i data-lucide="bike" style="width:16px;height:16px;display:inline-vertical;vertical-align:middle;margin-right:4px"></i>
            <?php elseif($status == 'Delivered'): ?>
                <i data-lucide="check-circle" style="width:16px;height:16px;display:inline-vertical;vertical-align:middle;margin-right:4px"></i>
            <?php else: ?>
                <i data-lucide="clock" style="width:16px;height:16px;display:inline-vertical;vertical-align:middle;margin-right:4px"></i>
            <?php endif; ?>
            <?php echo $status; ?>
        </div>
      </div>
      <div class="tracking-steps">
        <div class="tracking-step <?php echo $currentStep >= 1 ? 'done' : ''; ?>">
          <div class="tracking-step-dot"><i data-lucide="check" style="width:16px;height:16px;color:white"></i></div>
          <div class="tracking-step-label">Confirmed</div>
          <div class="tracking-step-time"><?php echo $placedAt; ?></div>
        </div>
        <div class="tracking-step <?php echo $currentStep >= 2 ? ($currentStep > 2 ? 'done' : 'active') : ''; ?>">
          <div class="tracking-step-dot">
            <i data-lucide="<?php echo $currentStep > 2 ? 'check' : 'package'; ?>" style="width:16px;height:16px;color:<?php echo $currentStep >= 2 ? 'white' : '#999'; ?>"></i>
          </div>
          <div class="tracking-step-label">Processing</div>
          <div class="tracking-step-time">
            <?php 
              if($currentStep > 2) echo 'Done';
              elseif($currentStep == 2) echo 'In Progress';
              else echo 'Pending';
            ?>
          </div>
        </div>
        <div class="tracking-step <?php echo $currentStep >= 3 ? ($currentStep > 3 ? 'done' : 'active') : ''; ?>">
          <div class="tracking-step-dot">
            <i data-lucide="bike" style="width:16px;height:16px;color:<?php echo $currentStep >= 3 ? 'white' : '#999'; ?>"></i>
          </div>
          <div class="tracking-step-label">On the Way</div>
          <div class="tracking-step-time">
            <?php 
              if($currentStep > 3) echo 'Done';
              elseif($currentStep == 3) echo 'In Transit';
              else echo 'Soon';
            ?>
          </div>
        </div>
        <div class="tracking-step <?php echo $currentStep >= 4 ? 'done' : ''; ?>">
          <div class="tracking-step-dot">
            <i data-lucide="home" style="width:16px;height:16px;color:<?php echo $currentStep >= 4 ? 'white' : '#999'; ?>"></i>
          </div>
          <div class="tracking-step-label">Delivered</div>
          <div class="tracking-step-time"><?php echo $currentStep >= 4 ? 'Arrived' : '—'; ?></div>
        </div>
      </div>
      
      <?php if ($latestOrder['delivery_boy_id']): ?>
      <div class="tracking-delivery-info">
        <div class="delivery-partner">
          <div class="delivery-partner-icon"><i data-lucide="user" style="width:32px;height:32px;color:white"></i></div>
          <div>
            <div class="delivery-partner-name"><?php echo $latestOrder['delivery_name']; ?></div>
            <div class="delivery-partner-sub">Your delivery partner · <i data-lucide="star" style="width:12px;height:12px;display:inline;vertical-align:middle;color:#fbbf24"></i> 4.9</div>
          </div>
        </div>
        <div class="delivery-actions">
          <button class="delivery-call-btn" onclick="Toast.show('Calling <?php echo $latestOrder['delivery_name']; ?>...','success')"><i data-lucide="phone" style="width:16px;height:16px;display:inline;vertical-align:middle;margin-right:4px"></i> Call</button>
          <button class="delivery-chat-btn" onclick="Toast.show('Opening chat...','success')"><i data-lucide="message-circle" style="width:16px;height:16px;display:inline;vertical-align:middle;margin-right:4px"></i> Chat</button>
        </div>
      </div>
      <?php endif; ?>
    </div>
  </div>
  <?php endif; ?>

</main>

<script>
  function goToCategory(cat) {
    const productsSection = document.getElementById('productsSection');
    if (productsSection) {
      productsSection.scrollIntoView({ behavior: 'smooth' });
    }
    setTimeout(() => {
      const pill = document.querySelector(`.cat-pill[onclick*="'${cat}'"]`);
      if (pill && typeof filterCat === 'function') filterCat(pill, cat);
    }, 300);
  }

  function goToPickup() {
    const pickup = document.getElementById('homePickup').value.trim();
    const drop   = document.getElementById('homeDrop').value.trim();
    let url = 'pickup-drop.php';
    const params = new URLSearchParams();
    if (pickup) params.set('pickup', pickup);
    if (drop)   params.set('drop', drop);
    if (params.toString()) url += '?' + params.toString();
    window.location.href = url;
  }

  function performHeroSearch() {
    const query = document.getElementById('heroSearchInput').value.trim();
    const productsSection = document.getElementById('productsSection');
    
    // Always scroll to products section
    if (productsSection) {
      productsSection.scrollIntoView({ behavior: 'smooth' });
    }
    
    // Show skeletons in products grid before filtering
    if (typeof showSkeletons === 'function') {
      showSkeletons('productsGrid', 8);
    }
    
    // If query exists, filter products
    if (typeof filterProducts === 'function') {
      filterProducts(query);
    }
  }

  // Fetch Nearby Shops on Homepage
  document.addEventListener("DOMContentLoaded", async () => {
    try {
        const resp = await fetch('api/shops/list.php');
        const data = await resp.json();
        const container = document.getElementById('homeShopsGrid');
        if (data.status === 'success' && data.data.length > 0) {
            container.innerHTML = data.data.map(shop => `
                <div class="swiper-slide" style="height:auto;">
                  <div class="shop-card fade-in-up ${shop.status !== 'active' ? 'shop-closed' : ''}" onclick="window.location.href='shop_details.php?id=${shop.id}'" style="height:100%; display:flex; flex-direction:column; background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 12px -2px rgba(0,0,0,0.05);border:1px solid rgba(0,0,0,0.05);cursor:pointer;transition:all 0.3s cubic-bezier(0.4, 0, 0.2, 1); position:relative; ${shop.status !== 'active' ? 'filter: grayscale(0.5); opacity: 0.8;' : ''}" onmouseover="this.style.transform='translateY(-6px)'; this.style.boxShadow='0 12px 24px -4px rgba(0,0,0,0.1)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px -2px rgba(0,0,0,0.05)'">
                      <div style="height:150px;overflow:hidden;position:relative">
                          <div style="position:absolute; top:0; left:0; width:100%; height:100%;">
                              <img loading="lazy" decoding="async" src="${typeof urlPrefix !== 'undefined' ? urlPrefix : ''}${shop.shop_image || 'assets/images/placeholder.jpg'}" alt="${shop.shop_name}" style="width:100%; height:100%; object-fit:cover;">
                              <div style="position:absolute; inset:0; background:linear-gradient(to top, rgba(0,0,0,0.4) 0%, transparent 60%);"></div>
                          </div>
                          ${shop.status !== 'active' ? `
                              <div style="position:absolute; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.4); display:flex; align-items:center; justify-content:center; z-index:2">
                                  <span style="background:#ef4444; color:#fff; padding:4px 12px; border-radius:6px; font-weight:900; font-size:12px; text-transform:uppercase; letter-spacing:1px; box-shadow:0 4px 10px rgba(239,68,68,0.3)">Closed</span>
                              </div>
                          ` : ''}
                          <div style="position:absolute;top:10px;right:10px;background:rgba(255,255,255,0.95);backdrop-filter:blur(4px);padding:4px 10px;border-radius:20px;font-size:12px;font-weight:700;display:flex;align-items:center;gap:4px;box-shadow:0 2px 8px rgba(0,0,0,0.1); z-index:3">
                              <i data-lucide="star" style="width:14px;height:14px;color:#fbbf24;fill:#fbbf24"></i> ${shop.rating || 4.5}
                          </div>
                      </div>
                      <div style="padding:16px; flex:1; display:flex; flex-direction:column;">
                          <h3 style="margin:0 0 6px 0;font-size:17px;font-weight:800;color:#111827;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;font-family:'Sora', sans-serif">${shop.shop_name}</h3>
                          <div style="color:#6b7280;font-size:13px;margin-bottom:16px;display:flex;align-items:center;gap:6px; flex:1;">
                              <i data-lucide="map-pin" style="width:14px;height:14px;color:initial;flex-shrink:0;"></i> <span style="display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;line-height:1.4;">${shop.address}</span>
                          </div>
                          <div style="display:flex; align-items:center; justify-content:space-between; margin-top:auto;">
                              <span style="font-size:12px;font-weight:700;color:${shop.status === 'active' ? '#10b981' : '#ef4444'};background:${shop.status === 'active' ? 'rgba(16,185,129,0.1)' : 'rgba(239,68,68,0.1)'};padding:4px 10px;border-radius:20px;display:inline-flex;align-items:center;gap:4px">
                                <div style="width:6px;height:6px;border-radius:50%;background:${shop.status === 'active' ? '#10b981' : '#ef4444'}"></div> ${shop.status === 'active' ? 'Open Now' : 'Closed'}
                              </span>
                              <div style="width:32px; height:32px; background:${shop.status === 'active' ? 'var(--primary)' : '#9ca3af'}; border-radius:50%; display:flex; align-items:center; justify-content:center; color:white; transition:background 0.2s;">
                                <i data-lucide="arrow-right" style="width:16px;height:16px"></i>
                              </div>
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
            
            // Wait for DOM to digest Swiper HTML
            setTimeout(() => {
                new Swiper('.nearby-shops-swiper', {
                  slidesPerView: 1.2,
                  spaceBetween: 16,
                  loop: true,
                  speed: 600,
                  autoplay: {
                    delay: 2500,
                    disableOnInteraction: false,
                    pauseOnMouseEnter: true
                  },
                  navigation: {
                    nextEl: '.swiper-button-next-custom',
                    prevEl: '.swiper-button-prev-custom',
                  },
                  grabCursor: true,
                  breakpoints: {
                    480: { slidesPerView: 1.5, spaceBetween: 16 },
                    640: { slidesPerView: 2.2, spaceBetween: 20 },
                    768: { slidesPerView: 3.2, spaceBetween: 20 },
                    1024: { slidesPerView: 4, spaceBetween: 24 }
                  }
                });
            }, 50);
            
            // Appended hover styles dynamically to avoid JS event listeners which cause jank
            if (!document.getElementById('swiper-hover-styles')) {
                const style = document.createElement('style');
                style.id = 'swiper-hover-styles';
                style.textContent = '.swiper-button-prev-custom:hover, .swiper-button-next-custom:hover { transform: scale(1.05); }';
                document.head.appendChild(style);
            }
            
        } else {
            document.getElementById('nearbyShopsSection').style.display = 'none';
        }
    } catch (e) {
        console.error("Failed to load home shops", e);
    }
  });
</script>

<?php
include 'includes/modals.php';
include 'includes/footer.php';
?>

<!-- Navbar Scroll Effect -->
<script>
window.addEventListener('scroll', () => {
    const nav = document.querySelector('.navbar');
    if (window.scrollY > 20) {
        nav.classList.add('scrolled');
    } else {
        nav.classList.remove('scrolled');
    }
});
</script>
