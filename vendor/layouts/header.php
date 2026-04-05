<?php
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth_helper.php';
checkPersistentLogin($pdo);
requireRole(['vendor']);

$user_id = $_SESSION['user_id'];
// Fetch shop details for this vendor
$stmt = $pdo->prepare("SELECT * FROM shops WHERE user_id = ?");
$stmt->execute([$user_id]);
$shop = $stmt->fetch();

if (!$shop) {
    die("Error: No shop associated with this vendor account. Please contact admin.");
}
$_SESSION['shop_id'] = $shop['id'];
$_SESSION['shop_name'] = $shop['shop_name'];
?>
<?php
$bodyClass = 'admin-body'; 
$urlPrefix = '../';
$extraStyles = '<link rel="stylesheet" href="'.$urlPrefix.'assets/css/admin.css?v=1.1">
<style>
    :root {
        --primary: #1a9c3e; /* Village Foods Premium Green */
        --primary-light: #22c55e;
        --primary-pale: #f0fdf4;
    }
    .admin-logo-icon {
        background: linear-gradient(135deg, var(--primary), var(--primary-light)) !important;
    }
    .admin-nav-item.active { 
        background: rgba(26, 156, 62, 0.15) !important; 
        color: #1a9c3e !important; 
        box-shadow: inset 3px 0 0 #1a9c3e !important;
    }
</style>';

$pwaName = 'VF Vendor';
$manifestUrl = $urlPrefix . 'vendor/manifest.json';
include $urlPrefix . 'includes/header.php';
?>
<div class="admin-layout">
