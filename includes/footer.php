<!-- ====== FOOTER ====== -->
<footer class="footer">
  <div class="container">
    <div class="footer-grid">
      <div>
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:8px">
          <div class="nav-logo-icon" style="width:36px;height:36px;font-size:18px"><i data-lucide="leaf" style="width:24px;height:24px"></i></div>
          <div class="footer-logo-text">Village<span>Foods</span></div>
        </div>
        <p class="footer-desc">Village Foods is your trusted neighborhood partner for farm-fresh groceries and daily essentials, committed to quality and rapid delivery while supporting local farmers.</p>
        <!-- <div>
          <span class="footer-app-btn"><i data-lucide="smartphone" style="width:14px;height:14px;display:inline;vertical-align:middle;margin-right:4px"></i> App Store</span>
          <span class="footer-app-btn"><i data-lucide="play-circle" style="width:14px;height:14px;display:inline;vertical-align:middle;margin-right:4px"></i> Google Play</span>
        </div> -->
      </div>
      <div class="footer-col">
        <h4>Quick Links</h4>
        <a href="index.php" class="footer-link">Home</a>
        <a href="#productsSection" class="footer-link">All Products</a>
        <a href="pickup-drop.php" class="footer-link">Pickup & Drop</a>
        <a href="track-order.php" class="footer-link">Track Order</a>
      </div>
      <div class="footer-col">
        <h4>Support</h4>
        <a href="#" class="footer-link">FAQ</a>
        <a href="#" class="footer-link">Returns Policy</a>
        <a href="#" class="footer-link">Privacy Policy</a>
        <a href="#" class="footer-link">Terms of Service</a>
        <a href="contact-us.php" class="footer-link">Contact Us</a>
      </div>
      <div class="footer-col">
        <h4>Contact</h4>
        <a href="mailto:<?php echo Settings::get('store_email', 'hello@villagefoods.in'); ?>" class="footer-link"><i data-lucide="mail" style="width:14px;height:14px;display:inline-block;vertical-align:middle;margin-right:4px"></i> <?php echo Settings::get('store_email', 'hello@villagefoods.in'); ?></a>
        <a href="tel:+<?php echo Settings::get('store_phone', '916380091001'); ?>" class="footer-link"><i data-lucide="phone" style="width:14px;height:14px;display:inline-block;vertical-align:middle;margin-right:4px"></i> +<?php echo Settings::get('store_phone', '91 63800 91001'); ?></a>
        <a href="#" class="footer-link"><i data-lucide="map-pin" style="width:14px;height:14px;display:inline;vertical-align:middle;margin-right:4px"></i> Thirupathur, Tamil Nadu</a>
        <div class="footer-socials">
          <div class="footer-social-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg></div>
          <div class="footer-social-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg></div>
          <div class="footer-social-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22.54 6.42a2.78 2.78 0 0 0-1.94-2C18.88 4 12 4 12 4s-6.88 0-8.6.42a2.78 2.78 0 0 0-1.94 2C1 8.11 1 12 1 12s0 3.89.46 5.58a2.78 2.78 0 0 0 1.94 2C5.12 20 12 20 12 20s6.88 0 8.6-.42a2.78 2.78 0 0 0 1.94-2C23 15.89 23 12 23 12s0-3.89-.46-5.58z"/><polyline points="9.75 15.02 15.5 12 9.75 8.98 9.75 15.02"/></svg></div>
        </div>
      </div>
    </div>
    <div class="footer-bottom">
      <span><i data-lucide="copyright" style="width:14px;height:14px;display:inline-block;vertical-align:middle;margin-top:-2px;margin-right:4px"></i> 2026 VillageFoods. All rights reserved.</span>
      <span>Made with <i data-lucide="heart" style="width:14px;height:14px;display:inline;vertical-align:middle;margin:0 4px;color:var(--accent);fill:var(--accent)"></i> in Thirupathur, India</span>
    </div>
  </div>
</footer>

<?php 
$waNumber = Settings::get('whatsapp_number') ?? '916380091001';
$waMsg = urlencode("Hi Village Foods, I need assistance with...");
?>
<a href="https://wa.me/<?php echo str_replace(['+', ' '], '', $waNumber); ?>?text=<?php echo $waMsg; ?>" class="whatsapp-sticky" target="_blank">
  <div class="wa-text-box">Chat with us!</div>
  <div class="wa-icon-box">
    <svg viewBox="0 0 24 24" width="30" height="30" stroke="currentColor" stroke-width="0" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
  </div>
</a>

<!-- ====== MOBILE BOTTOM NAV ====== -->
<div class="mobile-bottom-nav">
  <a href="index.php" class="nav-item active">
    <i data-lucide="home"></i>
    <span>Home</span>
  </a>
  <a href="pickup-drop.php" class="nav-item">
    <i data-lucide="bike"></i>
    <span>Quick</span>
  </a>
  <div class="nav-item cart-nav-item" onclick="Cart.toggleSidebar()">
    <div class="cart-floating-icon">
      <i data-lucide="shopping-cart"></i>
      <span class="cart-count nav-cart-count" id="mobileCartCount">0</span>
    </div>
    <span>Cart</span>
  </div>
  <a href="track-order.php" class="nav-item">
    <i data-lucide="package"></i>
    <span>Track</span>
  </a>
  <a href="#" class="nav-item" onclick="Modal.open('loginModal')">
    <i data-lucide="user"></i>
    <span>Profile</span>
  </a>
</div>

<!-- ====== SCRIPTS ====== -->
<script>
window.APP_SETTINGS = {
    deliveryFee: <?= (float)Settings::get('base_delivery_fee', 40.00) ?>,
    handlingFee: <?= (float)Settings::get('handling_fee', 10.00) ?>,
    platformFee: <?= (float)Settings::get('platform_fee', 10.00) ?>,
    enableCod: <?= Settings::isEnabled('enable_cod') ? 'true' : 'false' ?>
};
</script>
<script src="assets/js/utils.js"></script>
<script src="assets/js/location.js"></script>
<?php 
if (isset($extraScripts) && is_string($extraScripts) && trim($extraScripts) !== '') {
    echo $extraScripts;
} else {
    echo '<script src="assets/js/products.js"></script>';
}
?>
<script>
  lucide.createIcons();
</script>
</body>
</html>
