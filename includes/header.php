<?php 
// Prevent browser and proxy caching for dynamic session-aware pages
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

require_once 'db.php';
require_once 'auth_helper.php';
require_once 'settings_helper.php';
Settings::load($pdo);
checkPersistentLogin($pdo);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
<?php
  $siteName = 'Village Foods';
  $defaultDesc = 'Farm fresh vegetables, premium meats, and bakery items delivered to your doorstep in 30 minutes across Thirupathur District. Secure, fast, and authentic.';
  $defaultKeywords = 'fresh grocery delivery, online vegetable shopping, meat delivery, village foods, Thirupathur food delivery, farm fresh, quick delivery';
  
  $metaTitle = $pageTitle ?? "$siteName — Fresh & Fast Delivery";
  $metaDesc = $pageDescription ?? $defaultDesc;
  $metaKeywords = $pageKeywords ?? $defaultKeywords;
  
  $currentUrl = (empty($_SERVER['HTTPS']) ? 'http' : 'https') . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
  $ogImgPath = $ogImage ?? (($urlPrefix ?? '') . 'assets/images/village_quick-1.png');
?>
  <title><?php echo htmlspecialchars($metaTitle, ENT_QUOTES, 'UTF-8'); ?></title>
  <meta name="description" content="<?php echo htmlspecialchars($metaDesc, ENT_QUOTES, 'UTF-8'); ?>">
  <meta name="keywords" content="<?php echo htmlspecialchars($metaKeywords, ENT_QUOTES, 'UTF-8'); ?>">
  <meta name="author" content="<?php echo htmlspecialchars($siteName, ENT_QUOTES, 'UTF-8'); ?>">
  <link rel="canonical" href="<?php echo htmlspecialchars($currentUrl, ENT_QUOTES, 'UTF-8'); ?>">

  <!-- Open Graph / Facebook -->
  <meta property="og:type" content="website">
  <meta property="og:url" content="<?php echo htmlspecialchars($currentUrl, ENT_QUOTES, 'UTF-8'); ?>">
  <meta property="og:title" content="<?php echo htmlspecialchars($metaTitle, ENT_QUOTES, 'UTF-8'); ?>">
  <meta property="og:description" content="<?php echo htmlspecialchars($metaDesc, ENT_QUOTES, 'UTF-8'); ?>">
  <meta property="og:image" content="<?php echo htmlspecialchars($ogImgPath, ENT_QUOTES, 'UTF-8'); ?>">
  <meta property="og:site_name" content="<?php echo htmlspecialchars($siteName, ENT_QUOTES, 'UTF-8'); ?>">

  <!-- Twitter -->
  <meta property="twitter:card" content="summary_large_image">
  <meta property="twitter:url" content="<?php echo htmlspecialchars($currentUrl, ENT_QUOTES, 'UTF-8'); ?>">
  <meta property="twitter:title" content="<?php echo htmlspecialchars($metaTitle, ENT_QUOTES, 'UTF-8'); ?>">
  <meta property="twitter:description" content="<?php echo htmlspecialchars($metaDesc, ENT_QUOTES, 'UTF-8'); ?>">
  <meta property="twitter:image" content="<?php echo htmlspecialchars($ogImgPath, ENT_QUOTES, 'UTF-8'); ?>">


  <!-- Favicons -->
  <link rel="apple-touch-icon" href="<?php echo $urlPrefix ?? ''; ?>assets/images/logo/VillageFoods Delivery Logo.png">
  <link rel="icon" type="image/png" href="<?php echo $urlPrefix ?? ''; ?>assets/images/logo/VillageFoods Delivery Logo.png">
  <link rel="shortcut icon" href="<?php echo $urlPrefix ?? ''; ?>assets/images/logo/VillageFoods Delivery Logo.png">
  
  <!-- PWA & Mobile Meta Tags -->
  <link rel="manifest" href="<?php echo $manifestUrl ?? (($urlPrefix ?? '') . 'manifest.json'); ?>">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
  <meta name="apple-mobile-web-app-title" content="Village Foods">
  <meta name="theme-color" content="#1a9c3e">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800;900&family=Sora:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?php echo $urlPrefix ?? ''; ?>assets/css/variables.css">
  <link rel="stylesheet" href="<?php echo $urlPrefix ?? ''; ?>assets/css/components.css?v=1.1">
  <link rel="stylesheet" href="<?php echo $urlPrefix ?? ''; ?>assets/css/lucide-icons.css">
  <script src="https://unpkg.com/lucide@latest"></script>
  <script src="<?php echo $urlPrefix ?? ''; ?>assets/js/pwa.js"></script>
  <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
  <?php 
  if (isset($extraStyles) && is_string($extraStyles) && trim($extraStyles) !== '') {
      echo $extraStyles;
  } else {
      echo '<link rel="stylesheet" href="' . ($urlPrefix ?? '') . 'assets/css/customer.css?v=1.1">';
  }
  ?>
  
  <!-- Google Analytics (Option A - Hardcoded) -->
  <!-- Note: To activate, UNCOMMENT the script block below and replace 'G-XXXXXXXXXX' with your Measurement ID -->
  
  <script async src="https://www.googletagmanager.com/gtag/js?id=G-FDCG4L7ZZ4"></script>
  <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', 'G-FDCG4L7ZZ4');
  </script>
 
</head>
<body class="<?php echo $bodyClass ?? ''; ?>">
  <?php if (Settings::get('shop_status', '1') == '0' && ($bodyClass ?? '') !== 'admin-body'): ?>
  <!-- ====== STORE CLOSED OVERLAY ====== -->
  <div id="store-closed-overlay" style="position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(255,255,255,0.98); z-index:99999; display:flex; flex-direction:column; align-items:center; justify-content:center; text-align:center; padding:32px; backdrop-filter:blur(10px)">
    <div style="background:var(--primary-light); width:120px; height:120px; border-radius:60px; display:flex; align-items:center; justify-content:center; margin-bottom:24px; color:var(--primary); animation: pulse 2s infinite">
        <i data-lucide="store" style="width:60px; height:60px"></i>
    </div>
    <h1 style="font-size:32px; font-weight:900; color:var(--text-dark); margin-bottom:12px; font-family:'Sora', sans-serif">Shop is Currently Closed</h1>
    <p style="font-size:16px; color:var(--text-muted); max-width:400px; margin-bottom:32px; line-height:1.6">We are not accepting orders at the moment. Please check back later! Thank you for your patience.</p>
    
    <div style="display:flex; gap:12px">
        <?php $wa = Settings::get('whatsapp_number', '916380091001'); ?>
        <a href="tel:<?php echo $wa; ?>" style="background:var(--primary); color:white; padding:12px 24px; border-radius:14px; text-decoration:none; font-weight:700; display:flex; align-items:center; gap:8px">
            <i data-lucide="phone" style="width:20px; height:20px"></i> Call Us
        </a>
        <a href="https://wa.me/<?php echo $wa; ?>" style="background:#25d366; color:white; padding:12px 24px; border-radius:14px; text-decoration:none; font-weight:700; display:flex; align-items:center; gap:8px">
            <i data-lucide="message-circle" style="width:20px; height:20px"></i> WhatsApp
        </a>
    </div>
  </div>
  <style>
    @keyframes pulse {
        0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(26, 156, 62, 0.4); }
        70% { transform: scale(1.05); box-shadow: 0 0 0 20px rgba(26, 156, 62, 0); }
        100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(26, 156, 62, 0); }
    }
  </style>
  <?php endif; ?>
  <!-- ====== TOAST CONTAINER (Dynamically created by utils.js) ====== -->

  <!-- ====== PWA INSTALL BUTTON ====== -->
  <button id="pwa-install-btn" onclick="installPWA()" style="display:none; position:fixed; bottom:100px; left:20px; right:20px; background:var(--primary); color:white; border:none; padding:16px; border-radius:18px; font-weight:800; font-size:14px; align-items:center; justify-content:center; gap:10px; box-shadow:0 10px 30px rgba(26, 156, 62, 0.3); z-index:9999; animation: bounce 2s infinite">
    <i data-lucide="download-cloud"></i> Install <?php echo $pwaName ?? 'Village Foods App'; ?>
  </button>

  <style>
    @keyframes bounce {
        0%, 20%, 50%, 80%, 100% {transform: translateY(0);}
        40% {transform: translateY(-10px);}
        60% {transform: translateY(-5px);}
    }
    /* Hide PWA install button on Desktop */
    @media (min-width: 768px) {
        #pwa-install-btn {
            display: none !important;
        }
    }
  </style>
