<?php
$activePage = 'shops';
$pageTitle = 'Manage Shops - Admin Panel';
include 'layouts/header.php';
require_once '../includes/db.php';
?>

<!-- Leaflet CSS & JS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

<!-- Leaflet Geocoder (Search) -->
<link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css" />
<script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>

<div class="header-action admin-card-header" style="margin-bottom: 28px;">
    <div class="header-title-box">
        <h1 class="page-title">Manage Shops</h1>
        <p class="subtitle">Platform partner management and monitoring</p>
    </div>
    <div class="header-btn-box">
        <button class="nav-btn btn-primary premium-btn" onclick="ShopAdmin.add()">
            <i data-lucide="plus-circle"></i> <span>Add New Shop</span>
        </button>
    </div>
</div>

<!-- Shop Summary Stats -->
<div class="bento-grid" style="margin-bottom: 28px; grid-template-columns: repeat(4, 1fr);">
    <div class="premium-card analytics-card ac-blue">
        <div class="analytics-header">
            <div class="analytics-icon-box"><i data-lucide="store"></i></div>
            <div class="analytics-trend trend-up">Current</div>
        </div>
        <div class="analytics-value" id="statTotalShops">0</div>
        <div class="analytics-label">Total Shops</div>
    </div>
    <div class="premium-card analytics-card ac-green">
        <div class="analytics-header">
            <div class="analytics-icon-box"><i data-lucide="check-circle"></i></div>
            <div class="analytics-trend trend-up">Active</div>
        </div>
        <div class="analytics-value" id="statActiveShops">0</div>
        <div class="analytics-label">Active / Open</div>
    </div>
    <div class="premium-card analytics-card ac-orange">
        <div class="analytics-header">
            <div class="analytics-icon-box"><i data-lucide="x-circle"></i></div>
            <div class="analytics-trend trend-down">Closed</div>
        </div>
        <div class="analytics-value" id="statClosedShops">0</div>
        <div class="analytics-label">Inactive Shops</div>
    </div>
    <div class="premium-card analytics-card ac-purple">
        <div class="analytics-header">
            <div class="analytics-icon-box"><i data-lucide="map-pin"></i></div>
        </div>
        <div class="analytics-value" id="statAreas">0</div>
        <div class="analytics-label">Districts Covered</div>
    </div>
</div>

<div class="admin-table-section premium-card" style="padding: 0; min-height: 400px;">
    <div class="table-controls" style="padding: 20px 24px; border-bottom: 1px solid var(--border-light); display: flex; justify-content: space-between; align-items: center;">
        <h3 style="font-size: 15px; font-weight: 800; color: var(--text);">Shop Directory</h3>
        <div class="header-search-box" style="margin: 0; width: 300px;">
            <div class="search-input-wrapper">
                <i data-lucide="search" class="search-icon"></i>
                <input type="text" id="shopSearchInput" placeholder="Filter by name, owner or location..." class="admin-search-input">
            </div>
        </div>
    </div>
    <div class="admin-table-wrapper" style="overflow-x: auto;">
        <table id="shopsTable" class="premium-table">
            <thead>
                <tr>
                    <th style="padding-left: 24px;">Partner</th>
                    <th>Business Details</th>
                    <th>Location / Address</th>
                    <th>Status</th>
                    <th style="padding-right: 24px;">Actions</th>
                </tr>
            </thead>
            <tbody id="shopsTbody">
                <tr><td colspan="5" style="text-align:center; padding: 60px;">
                    <div class="table-loader"><i data-lucide="loader-2" class="spin"></i> Loading directory...</div>
                </td></tr>
            </tbody>
        </table>
    </div>
</div>
  
<script>
    if(window.lucide) { lucide.createIcons(); }

    let allShops = [];

    function renderShops(shops) {
        const tbody = document.getElementById('shopsTbody');
        
        // Update Stats
        document.getElementById('statTotalShops').textContent = shops.length;
        document.getElementById('statActiveShops').textContent = shops.filter(s => s.status === 'active').length;
        document.getElementById('statClosedShops').textContent = shops.filter(s => s.status !== 'active').length;
        
        const uniqueAreas = new Set(shops.map(s => s.address.split(',').pop().trim()));
        document.getElementById('statAreas').textContent = uniqueAreas.size;

        if (shops.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" style="text-align:center; padding: 60px; color:var(--text-muted);">No shops found matching your search.</td></tr>';
            return;
        }
        
        tbody.innerHTML = shops.map(shop => `
            <tr>
                <td style="padding-left: 24px;">
                    <div class="table-product-avatar" style="display: flex; align-items: center; gap: 10px; height: auto;">
                        <img src="../${shop.shop_image || 'assets/images/placeholder.png'}" alt="${shop.shop_name}" style="width:40px; height:40px; border-radius: 8px; object-fit: cover;">
                        <div>
                            <div style="font-weight:800; color:var(--text); font-size: 13px; line-height: 1.2;">${shop.shop_name}</div>
                            <div style="font-size: 10px; color: var(--primary); font-weight: 700; margin-top: 2px;">#SH-${shop.id}</div>
                        </div>
                    </div>
                </td>
                <td>
                    <div style="font-weight:700; color: var(--text); font-size: 12.5px;">${shop.owner_name}</div>
                    <div style="display:flex; flex-direction:column; gap:4px; margin-top:2px;">
                        <a href="tel:${shop.phone}" style="color:var(--text-muted); text-decoration: none; font-size:11.5px; display: flex; align-items: center; gap: 4px;">
                            <i data-lucide="phone" style="width: 10px; height: 10px;"></i> ${shop.phone}
                        </a>
                        ${shop.email ? `
                        <a href="mailto:${shop.email}" style="color:var(--primary); text-decoration: none; font-size:11px; display: flex; align-items: center; gap: 4px;">
                            <i data-lucide="mail" style="width: 10px; height: 10px;"></i> ${shop.email}
                        </a>` : ''}
                    </div>
                </td>
                <td>
                    <div style="color:var(--text-muted); font-size: 11.5px; max-width: 220px; line-height: 1.4;">${shop.address}</div>
                </td>
                <td>
                    <span class="badge-status ${shop.status === 'active' ? 'st-active' : 'st-inactive'}" style="padding: 4px 10px; font-size: 10px;">
                        ${shop.status === 'active' ? '<i data-lucide="check-circle" style="width:10px;"></i> Open' : '<i data-lucide="x-circle" style="width:10px;"></i> Closed'}
                    </span>
                </td>
                <td style="padding-right: 24px;">
                    <div style="display:flex; gap:6px;">
                        <button class="admin-btn-icon edit" onclick="ShopAdmin.edit(${shop.id})" title="Edit Shop" style="width:30px; height:30px;">
                            <i data-lucide="edit-3" style="width:14px; height:14px;"></i>
                        </button>
                        <button class="admin-btn-icon delete" onclick="ShopAdmin.delete(${shop.id})" title="Delete Shop" style="width:30px; height:30px;">
                            <i data-lucide="trash-2" style="width:14px; height:14px;"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
        if(window.lucide) { lucide.createIcons(); }
    }

    async function loadShops() {
        try {
            const resp = await fetch('../api/shops/list.php?admin=1'); 
            const result = await resp.json();
            if (result.status === 'success') {
                allShops = result.data;
                ShopAdmin.init(allShops);
                renderShops(allShops);
            }
        } catch(e) {
            console.error(e);
        }
    }

    document.getElementById('shopSearchInput').addEventListener('input', (e) => {
        const term = e.target.value.toLowerCase();
        const filtered = allShops.filter(shop => 
            shop.shop_name.toLowerCase().includes(term) || 
            shop.owner_name.toLowerCase().includes(term) || 
            shop.address.toLowerCase().includes(term) ||
            shop.phone.includes(term)
        );
        renderShops(filtered);
    });

    document.addEventListener('DOMContentLoaded', () => {
        loadShops();
        // Load categories for the shop modal select box
        fetch('../api/products/get_categories.php')
            .then(res => res.json())
            .then(cats => {
                const container = document.getElementById('shopCategoriesContainer');
                if(container && cats.length > 0) {
                    container.innerHTML = cats.map(c => `
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 13px;">
                            <input type="checkbox" name="categories[]" value="${c.id}" class="cat-checkbox">
                            ${c.name}
                        </label>
                    `).join('');
                }
            })
            .catch(console.error);
    });
</script>

<?php include 'layouts/footer.php'; ?>
