<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?php echo $pageTitle ?? 'Delivery Dashboard — Village Foods'; ?></title>
    
    <!-- Meta tags for PWA feel -->
    <link rel="manifest" href="manifest.json">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="VF Delivery">
    <meta name="theme-color" content="#16a34a">

    <link rel="stylesheet" href="../assets/css/variables.css">
    <link rel="stylesheet" href="../assets/css/components.css">
    <link rel="stylesheet" href="../assets/css/delivery.css">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800&family=Sora:wght@400;600;700;800&display=swap" rel="stylesheet">
    
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="../assets/js/pwa.js"></script>
</head>
<body class="<?php echo $bodyClass ?? 'db-body'; ?>">
    <!-- ====== TOAST CONTAINER ====== -->
    <div id="toast-container" style="position:fixed; top:20px; right:20px; z-index:1000"></div>

    <!-- ====== PWA INSTALL BUTTON ====== -->
    <button id="pwa-install-btn" onclick="installPWA()" style="display:none; position:fixed; bottom:100px; left:20px; right:20px; background:var(--primary); color:white; border:none; padding:16px; border-radius:18px; font-weight:800; font-size:14px; align-items:center; justify-content:center; gap:10px; box-shadow:0 10px 30px rgba(26, 156, 62, 0.3); z-index:9999; animation: bounce 2s infinite">
        <i data-lucide="download-cloud"></i> Install VF Delivery
    </button>
