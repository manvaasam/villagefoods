<?php
$urlPrefix = '../';

require_once $urlPrefix . 'includes/db.php';
require_once $urlPrefix . 'includes/auth_helper.php';
checkPersistentLogin($pdo);

// Enforce admin role
if (!isset($_SESSION['logged_in']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ' . $urlPrefix . 'admin/index.php');
    exit;
}

$bodyClass = 'admin-body'; // Required for admin styling
$extraStyles = '<link rel="stylesheet" href="'.$urlPrefix.'assets/css/admin.css?v=1.2">';
$pwaName = 'VF Admin';
$manifestUrl = $urlPrefix . 'admin/manifest.json';
include $urlPrefix . 'includes/header.php';
?>
<div class="admin-layout">
<?php include 'sidebar.php'; ?>
<div class="sidebar-overlay" onclick="AdminPanel.toggleSidebar()"></div>
<main class="admin-main">
<?php include 'topbar.php'; ?>
<div class="admin-content">
