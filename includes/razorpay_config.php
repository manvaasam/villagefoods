<?php
// =============================================
// VILLAGE FOODS — RAZORPAY CONFIGURATION
// =============================================

// Initialize constants with fallback
if (!defined('RAZORPAY_KEY_ID')) {
    try {
        // We use a raw query because Settings::load might not have been called yet in some contexts
        $stmt = $pdo->query("SELECT razorpay_key_id, razorpay_key_secret FROM settings LIMIT 1");
        $settings = $stmt->fetch();
        
        $keyId = trim($settings['razorpay_key_id'] ?? 'rzp_live_SYB25ruVNY6NuD');
        $keySecret = trim($settings['razorpay_key_secret'] ?? '2b3BjyhW38ngP5x1YPagcPbv');

        define('RAZORPAY_KEY_ID', $keyId);
        define('RAZORPAY_KEY_SECRET', $keySecret);
    } catch (Exception $e) {
        define('RAZORPAY_KEY_ID', 'rzp_live_SYB25ruVNY6NuD');
        define('RAZORPAY_KEY_SECRET', '2b3BjyhW38ngP5x1YPagcPbv');
    }

}

// Store name for the checkout modal
define('STORE_NAME', 'Village Foods');
?>
