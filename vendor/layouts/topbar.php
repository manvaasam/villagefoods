<header class="admin-topbar">
    <div class="topbar-left" style="display:flex; align-items:center; gap:12px;">
        <button class="admin-mobile-toggle" onclick="AdminPanel.toggleSidebar()" style="display:none; border:none; background:white; width:40px; height:40px; border-radius:10px; border:1px solid var(--border) !important; cursor:pointer; color:var(--text); align-items:center; justify-content:center;">
            <i data-lucide="menu"></i>
        </button>
        <div>
            <h1 class="admin-topbar-title"><?php echo $topbarTitle ?? 'Dashboard'; ?></h1>
            <div class="admin-topbar-date"><?php echo $topbarSubtitle ?? date('l, j F Y'); ?></div>
        </div>
    </div>
    <div class="header-right">
        <?php if (isset($topbarRight)) echo $topbarRight; ?>
    </div>
</header>
