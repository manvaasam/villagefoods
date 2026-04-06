/* =============================================
   VILLAGE FOODS — ADMIN PANEL JS
   ============================================= */

'use strict';

const AdminPanel = {
  assetPrefix: '../',
  fixPath: (path) => {
    if (!path) return '';
    if (path.startsWith('http') || path.startsWith('/') || path.startsWith('data:')) return path;
    return AdminPanel.assetPrefix + path;
  },
  toggleSidebar: () => {
    document.querySelector('.admin-sidebar').classList.toggle('open');
    document.querySelector('.sidebar-overlay').classList.toggle('open');
  },
  downloadCSV: (headers, data, filename) => {
    const csvRows = [headers.map(h => `"${h}"`).join(',')];
    data.forEach(row => {
        csvRows.push(row.map(val => {
            const str = String(val === null || val === undefined ? '' : val).replace(/"/g, '""');
            return `"${str}"`;
        }).join(','));
    });
    
    const csvContent = csvRows.join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement("a");
    const url = URL.createObjectURL(blob);
    link.setAttribute("href", url);
    link.setAttribute("download", filename);
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  }
};

// ======= ADMIN API =======
const AdminAPI = (() => {
  async function fetchJSON(url, options = {}) {
    try {
      const headers = { ...options.headers };
      // Only set application/json if we are NOT sending FormData
      if (!(options.body instanceof FormData) && !headers['Content-Type']) {
        headers['Content-Type'] = 'application/json';
      }

      const resp = await fetch(url, {
        ...options,
        headers
      });
      if (!resp.ok) {
        const errData = await resp.json().catch(() => ({}));
        throw new Error(errData.error || `HTTP Error: ${resp.status}`);
      }
      return await resp.json();
    } catch (err) {
      console.error(`API Error (${url}):`, err);
      if (typeof Toast !== 'undefined') Toast.show(err.message || 'Connection error', 'error');
      return null;
    }
  }

  return {
    fetchJSON: fetchJSON,
    getStats:     (range = '30d') => fetchJSON(`../api/admin/get_stats.php?range=${range}`),
    getProducts:  (query = '', cat = '', shopId = 'all') => fetchJSON(`../api/admin/products/list.php?search=${encodeURIComponent(query)}&category=${cat}&shop_id=${shopId}`),
    saveProduct: async (data) => {
      const isFormData = data instanceof FormData;
      return fetchJSON('../api/admin/products/save.php', {
        method: 'POST',
        body: isFormData ? data : JSON.stringify(data)
      });
    },
    
    deleteProduct:(id) => fetchJSON('../api/admin/products/delete.php', { method: 'POST', body: JSON.stringify({ id }) }),
    
    getCategories:(search = '') => fetchJSON(`../api/admin/categories/list.php?search=${encodeURIComponent(search)}`),
    saveCategory: async (data) => {
    const isFormData = data instanceof FormData;
    const options = {
      method: 'POST',
      body: isFormData ? data : JSON.stringify(data)
    };
    if (!isFormData) {
      options.headers = { 'Content-Type': 'application/json' };
    }
    const resp = await fetch('../api/admin/categories/save.php', options);
    return resp.json();
  },
    deleteCategory:(id) => fetchJSON('../api/admin/categories/delete.php', { method: 'POST', body: JSON.stringify({ id }) }),
    toggleCategoryStatus:(id) => fetchJSON('../api/admin/categories/toggle_status.php', { method: 'POST', body: JSON.stringify({ id }) }),
    
    getOrders:    (status = 'all', search = '', date = '', paymentType = 'all') => fetchJSON(`../api/admin/orders/list.php?status=${status}&search=${encodeURIComponent(search)}&date=${date}&payment_type=${paymentType}&_t=${Date.now()}`),
    getOrderDetails:(id) => fetchJSON(`../api/admin/orders/details.php?id=${id}&_t=${Date.now()}`),
    updateOrderStatus:(id, status) => fetchJSON('../api/admin/orders/update_status.php', { method: 'POST', body: JSON.stringify({ id, status }) }),
    assignDelivery: (order_id, delivery_boy_id) => fetchJSON('../api/admin/orders/assign_delivery.php', { method: 'POST', body: JSON.stringify({ order_id, delivery_boy_id }) }),

    getPayments:  (search = '') => fetchJSON(`../api/admin/payments/list.php?search=${encodeURIComponent(search)}&_t=${Date.now()}`),
    
    getUsers:     (role = 'customer', search = '', filter = 'all') => fetchJSON(`../api/admin/users/list.php?role=${role}&search=${encodeURIComponent(search)}&filter=${filter}&_t=${Date.now()}`),
    saveUser:     (data) => fetchJSON('../api/admin/users/save.php', { method: 'POST', body: JSON.stringify(data) }),
    deleteUser:   (id) => fetchJSON('../api/admin/users/delete.php', { method: 'POST', body: JSON.stringify({ id }) }),
    toggleUserStatus: (id) => fetchJSON('../api/admin/users/toggle_status.php', { method: 'POST', body: JSON.stringify({ id }) }),
    getUserDetails:   (id) => fetchJSON(`../api/admin/users/details.php?id=${id}&_t=${Date.now()}`),

    getSettings:  () => fetchJSON(`../api/admin/settings/get.php?_t=${Date.now()}`),
    saveSettings: (data) => fetchJSON('../api/admin/settings/save.php', { method: 'POST', body: JSON.stringify(data) }),
    checkNewOrders: () => fetchJSON(`../api/admin/orders/check_new.php?_t=${Date.now()}`),
    getWithdrawals: (status = 'all', search = '') => fetchJSON(`../api/admin/withdrawals/list.php?status=${status}&search=${encodeURIComponent(search)}&_t=${Date.now()}`),
    updateWithdrawalStatus: (data) => fetchJSON('../api/admin/withdrawals/update_status.php', { method: 'POST', body: JSON.stringify(data) }),
    
    saveShop: async (data) => {
      const isFormData = data instanceof FormData;
      return fetchJSON('../api/admin/shops/save.php', {
        method: 'POST',
        body: isFormData ? data : JSON.stringify(data)
      });
    },
    deleteShop:(id) => fetchJSON('../api/admin/shops/delete.php', { method: 'POST', body: JSON.stringify({ id }) }),
    fetchJSON // Export fetchJSON for external use
  };
})();

// ======= ADMIN TAB SYSTEM =======
const AdminTabs = (() => {
  const tabTitles = {
    dashTab:      'Dashboard',
    ordersTab:    'Order Management',
    productsTab:  'Product Management',
    categoriesTab: 'Category Management',
    customersTab: 'Customer Management',
    deliveryTab:  'Delivery Boys',
    paymentsTab:  'Payment Tracking',
    settingsTab:  'Settings',
  };

  function refreshTabData(tabId) {
    // Each page calls this with its respective tabId on load
    switch (tabId) {
      case 'dashTab': DashboardAdmin.init(); break;
      case 'ordersTab': OrderAdmin.init(); break;
      case 'productsTab': 
        CategoryAdmin.init(); 
        ProductAdmin.init(); 
        break;
      case 'categoriesTab': CategoryAdmin.init(); break;
      case 'customersTab': CustomerAdmin.init(); break;
      case 'deliveryTab': DeliveryAdmin.init(); break;
      case 'paymentsTab': CustomerPaymentAdmin.init(); break;
      case 'settingsTab': SettingsAdmin.init(); break;
    }
  }

  function init() {
    console.log("AdminTabs initialized");
  }

  return { init, refreshTabData };
})();

// ======= NOTIFICATION ENGINE =======
const NotificationEngine = (() => {
  let lastOrderId = null;
  let lastRapidId = null;
  let lastUpdateTimestamp = null;
  let pollInterval = null;

  async function check() {
    const data = await AdminAPI.checkNewOrders();
    if (!data || data.status !== 'success') return;

    // Detect New Regular Order
    if (lastOrderId !== null && data.latest_id > lastOrderId) {
      onNewOrder(data, 'regular');
    }
    lastOrderId = data.latest_id;

    // Detect New Rapid Order
    if (lastRapidId !== null && data.latest_rapid_id > lastRapidId) {
      onNewOrder(data, 'rapid');
    }
    lastRapidId = data.latest_rapid_id;

    // Detect Status Update
    if (lastUpdateTimestamp !== null && data.latest_update > lastUpdateTimestamp) {
        // Only notify if it's NOT a new order (new orders already notified)
        if (data.latest_id <= lastOrderId) {
            onStatusUpdate(data);
        }
    }
    lastUpdateTimestamp = data.latest_update;

    // Always update badges
    updateBadges(data.pending_count, data.pending_rapid, data.pending_withdrawals, data.pending_refunds);
  }

  function updateBadges(foodCount, rapidCount, withdrawalCount, refundCount = 0) {
    const orderBadge = document.getElementById('sidebarOrderBadge');
    const refundBadge = document.getElementById('sidebarRefundBadge');
    if (orderBadge) {
        orderBadge.textContent = foodCount;
        orderBadge.style.display = foodCount > 0 ? 'inline-block' : 'none';
        
        // Pulse effect for badge if count increased
        const lastTotal = parseInt(orderBadge.dataset.lastCount || 0);
        if (lastTotal < foodCount) {
            orderBadge.style.animation = 'none';
            setTimeout(() => orderBadge.style.animation = 'pulse-premium 0.5s ease-in-out', 10);
        }
        orderBadge.dataset.lastCount = foodCount;
    }

    if (refundBadge) {
        refundBadge.textContent = refundCount > 0 ? '!' : '';
        refundBadge.style.display = refundCount > 0 ? 'inline-block' : 'none';
        if (refundCount > 0) {
            refundBadge.style.animation = 'pulse-premium 1s infinite alternate';
        }
    }

    const rapidBadge = document.getElementById('sidebarRapidBadge');
    if (rapidBadge) {
        rapidBadge.textContent = rapidCount;
        rapidBadge.style.display = rapidCount > 0 ? 'inline-block' : 'none';
    }

    const withdrawBadge = document.getElementById('sidebarWithdrawalBadge');
    if (withdrawBadge) {
        withdrawBadge.textContent = withdrawalCount;
        withdrawBadge.style.display = withdrawalCount > 0 ? 'inline-flex' : 'none';
    }
  }

  function onStatusUpdate(data) {
    const title = `🔔 Order ${data.latest_upd_num}: ${data.latest_upd_status}`;
    if (typeof Toast !== 'undefined') {
        Toast.show(title, 'success');
    }
    
    // Auto-refresh tables
    if (document.getElementById('adminOrdersTable2') || document.getElementById('vendorOrdersList')) {
        if (typeof OrderAdmin !== 'undefined') OrderAdmin.init();
    }
    
    // Sound effect
    try {
        const audio = new Audio(`../assets/sounds/notification_status.mp3`);
        audio.play().catch(e => console.log("Sound blocked by browser"));
    } catch(e) {}
  }

  function onNewOrder(data, type) {
    const title = type === 'rapid' ? '🚀 New Rapid Pickup!' : `🍕 New Order! (#${data.latest_num})`;
    if (typeof Toast !== 'undefined') {
        Toast.show(title, 'success');
    }
    
    // Auto-refresh tables if relevant page is open
    if (document.getElementById('adminOrdersTable2')) {
        OrderAdmin.init();
    } else if (document.getElementById('statsOrders')) {
        DashboardAdmin.init();
    }
    
    // Sound effect (Optional chime)
    try {
        const sound = type === 'rapid' ? 'notification_rapid.mp3' : 'notification.mp3';
        const audio = new Audio(`../assets/sounds/${sound}`);
        audio.play().catch(e => console.log("Sound blocked by browser"));
    } catch(e) {}
  }

  function init() {
    // Initial check
    check();
    // Start polling every 20 seconds
    if (pollInterval) clearInterval(pollInterval);
    pollInterval = setInterval(check, 20000);
  }

  return { init, check };
})();

// ======= DASHBOARD ADMIN =======
const DashboardAdmin = (() => {
  async function init(range = '30d') {
    const data = await AdminAPI.getStats(range);
    if (!data) return;

    // Range Button Event Listeners
    const rangeBtns = document.querySelectorAll('.range-btn');
    rangeBtns.forEach(btn => {
        // Only attach once
        if(!btn.dataset.init) {
            btn.dataset.init = "true";
            btn.addEventListener('click', () => {
                document.querySelectorAll('.range-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                DashboardAdmin.init(btn.dataset.range);
            });
        }
    });

    // 1. Summary Cards
    const elRevenue = document.getElementById('statsRevenue');
    const elPlatformProfit = document.getElementById('statsPlatformProfit');
    const elOrders = document.getElementById('statsOrders');
    const elAOV = document.getElementById('statsAOV');
    const elCustomers = document.getElementById('statsCustomers');
    const elGrowth = document.getElementById('growthRevenue');

    if (elRevenue) elRevenue.textContent = `₹${data.stats.revenue}`;
    if (elPlatformProfit) elPlatformProfit.textContent = `₹${data.stats.platform_profit}`;
    if (elOrders) elOrders.textContent = data.stats.orders;
    if (elAOV) elAOV.textContent = `₹${data.stats.aov}`;
    if (elCustomers) elCustomers.textContent = data.stats.customers;

    const elRefundPending = document.getElementById('statsRefundPending');
    const refundCard = document.getElementById('refundCard');
    if (elRefundPending) {
        elRefundPending.textContent = data.stats.refund_pending;
        if (refundCard) {
            refundCard.style.display = data.stats.refund_pending > 0 ? 'block' : 'none';
            refundCard.style.cursor = 'pointer';
            refundCard.onclick = () => {
                // If we are in SPA mode (tabs), switch to orders tab
                const ordersTabBtn = document.querySelector('.admin-nav-item i[data-lucide="package"]')?.closest('.admin-nav-item');
                if (ordersTabBtn) {
                   // This is a bit tricky since it's PHP-based mostly, 
                   // but I'll assume we can redirect if needed or just simulate click if it's SPA
                   window.location.href = 'orders?status=Refund+Pending';
                }
            };
        }
    }
    
    if (elGrowth) {
      const growth = parseFloat(data.stats.growth) || 0;
      const isUp = growth >= 0;
      const icon = isUp ? 'arrow-up-right' : 'arrow-down-right';
      
      elGrowth.className = `analytics-trend ${isUp ? 'trend-up' : 'trend-down'}`;
      elGrowth.innerHTML = `<i data-lucide="${icon}" style="width:14px;height:14px"></i> ${Math.abs(growth)}%`;
      
      // Add a small label if needed (optional, keeping it clean)
      // elGrowth.title = "vs last month";
    }

    // Update Sidebar Badges
    NotificationEngine.check(); // Sync with notification system

    // 2. Revenue Chart
    renderChart(data.weekly_revenue);

    // 3. Top Categories
    renderTopCategories(data.category_stats);

    // 4. Top Products
    renderTopProducts(data.top_products);

    // 4b. Rapid Order Summary (Insert before recent orders)
    renderRapidSummary(data.stats);

    // 5. Recent Orders
    OrderAdmin.renderTable(data.recent_orders, 'adminOrdersTable');

    if (window.lucide) lucide.createIcons();
  }

  let revenueChartInstance = null;

  function renderChart(weeklyData) {
    const chartEl = document.getElementById('revenueChartApex');
    if (!chartEl) return;

    const amounts = weeklyData.map(d => parseFloat(d.amount));
    const ordersCounts = weeklyData.map(d => parseInt(d.orders));
    const categories = weeklyData.map(d => d.day);
    const fullDates = weeklyData.map(d => d.date);

    const options = {
      series: [
        {
          name: 'Revenue',
          data: amounts
        },
        {
          name: 'Orders',
          data: ordersCounts
        }
      ],
      chart: {
        type: 'area', // Revenue will use area, Orders will use line (set via stroke)
        height: 350,
        toolbar: { show: false },
        animations: {
          enabled: true,
          easing: 'easeinout',
          speed: 800,
          animateGradually: { enabled: true, delay: 150 },
          dynamicAnimation: { enabled: true, speed: 350 }
        }
      },
      dataLabels: {
        enabled: false
      },
      colors: ['#10b981', '#3b82f6'], // Emerald Green, Soft Blue
      stroke: {
        curve: 'smooth',
        width: [3, 3],
        dashArray: [0, 0]
      },
      fill: {
        type: ['gradient', 'solid'],
        gradient: {
          shadeIntensity: 1,
          opacityFrom: 0.4,
          opacityTo: 0.05,
          stops: [0, 90, 100]
        }
      },
      markers: {
        size: 0, // Hidden by default, shown on hover
        hover: { size: 6 }
      },
      grid: {
        borderColor: 'rgba(0,0,0,0.03)',
        strokeDashArray: 4,
        xaxis: { lines: { show: false } },
        yaxis: { lines: { show: true } },
        padding: { top: 0, right: 20, bottom: 0, left: 10 }
      },
      xaxis: {
        categories: categories,
        tickAmount: categories.length > 10 ? 10 : undefined,
        axisBorder: { show: false },
        axisTicks: { show: false },
        labels: {
          rotate: -45,
          rotateAlways: false,
          hideOverlappingLabels: true,
          style: {
            colors: '#64748b',
            fontSize: '11px',
            fontWeight: 600
          }
        }
      },
      yaxis: {
        labels: {
          style: {
            colors: '#64748b',
            fontSize: '12px',
            fontWeight: 600
          },
          formatter: (val) => val >= 1000 ? `₹${(val/1000).toFixed(1)}k` : `₹${Math.round(val)}`
        }
      },
      tooltip: {
        custom: function({ series, seriesIndex, dataPointIndex, w }) {
          const rev = series[0][dataPointIndex];
          const ord = series[1][dataPointIndex];
          const date = fullDates[dataPointIndex];
          const day = categories[dataPointIndex];
          
          return `
            <div class="chart-tooltip-premium">
              <div class="tt-header">${day}, ${date}</div>
              <div class="tt-row">
                <span class="tt-dot" style="background:#10b981"></span>
                <span class="tt-label">Revenue:</span>
                <span class="tt-val">₹${parseFloat(rev).toLocaleString()}</span>
              </div>
              <div class="tt-row">
                <span class="tt-dot" style="background:#3b82f6"></span>
                <span class="tt-label">Orders:</span>
                <span class="tt-val">${ord}</span>
              </div>
            </div>
          `;
        }
      },
      legend: { show: false }
    };

    if (revenueChartInstance) {
      revenueChartInstance.updateOptions(options);
    } else {
      revenueChartInstance = new ApexCharts(chartEl, options);
      revenueChartInstance.render();
    }
  }

  // Helpers for tooltips (No longer used with custom tooltip config in ApexCharts)
  function showTooltip() {}
  function hideTooltip() {}

  function renderTopCategories(cats) {
    const list = document.getElementById('topCategoriesList');
    if (!list) return;

    if (!cats || cats.length === 0) {
      list.innerHTML = '<div style="padding:40px; text-align:center; color:var(--text-muted)">No category data yet</div>';
      return;
    }

    const totalRev = cats.reduce((sum, c) => sum + parseFloat(c.revenue), 0);
    const colors = ['#f0fdf4', '#eff6ff', '#fff7ed', '#fef2f2', '#f5f3ff', '#fdf2f8'];
    const textColors = ['#15803d', '#1d4ed8', '#b45309', '#b91c1c', '#6d28d9', '#be185d'];

    list.innerHTML = cats.map((c, i) => {
      const revenue = parseFloat(c.revenue);
      const percent = totalRev > 0 ? Math.round((revenue / totalRev) * 100) : 0;
      const colIdx = i % colors.length;
      return `
        <div class="admin-mini-stat">
          <div class="mini-stat-icon" style="background:${colors[colIdx]}">
            <i data-lucide="${c.icon_name || 'package'}" style="width:18px;height:18px;color:${textColors[colIdx]}"></i>
          </div>
          <div class="mini-stat-info">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px">
               <span class="mini-stat-name">${Utils.escapeHTML(c.name)}</span>
               <span class="mini-stat-val">₹${revenue.toLocaleString()}</span>
            </div>
            <div class="mini-stat-bar"><div class="mini-stat-bar-fill" style="width:${percent}%; background:${textColors[colIdx]}"></div></div>
            <div style="display:flex; justify-content:space-between; align-items:center; margin-top:4px">
               <span class="mini-stat-sub">${c.order_count} orders</span>
               <span class="mini-stat-sub" style="font-weight:700">${percent}%</span>
            </div>
          </div>
        </div>
      `;
    }).join('');
  }

  function renderTopProducts(products) {
    const container = document.getElementById('topProductsTable');
    if (!container) return;

    if (!products || products.length === 0) {
      container.innerHTML = '<div style="padding:40px; text-align:center; color:var(--text-muted)">No sales data yet</div>';
      return;
    }

    container.innerHTML = products.map(p => `
      <div class="admin-mini-stat" style="padding: 12px 0">
        <div class="table-product-cell">
          <img src="${AdminPanel.fixPath(p.image_url)}" alt="${Utils.escapeHTML(p.name)}" style="width:40px;height:40px;border-radius:8px;object-fit:cover">
          <div>
            <div class="mini-stat-name">${Utils.escapeHTML(p.name)}</div>
            <div class="mini-stat-sub">${parseInt(p.total_sold)} Sold</div>
          </div>
        </div>
        <div style="margin-left:auto; text-align:right">
          <div class="mini-stat-name">₹${parseFloat(p.total_revenue).toLocaleString()}</div>
          <div class="mini-stat-sub">Revenue</div>
        </div>
      </div>
    `).join('');
  }

  function renderRapidSummary(stats) {
    const container = document.getElementById('rapidStatsContainer');
    if (!container) return;

    container.innerHTML = `
      <div class="rapid-stat-item">
        <div class="rapid-stat-label">Pending Pickups</div>
        <div class="rapid-stat-value">${stats.pending_rapid}</div>
      </div>
      <div class="rapid-stat-item">
        <div class="rapid-stat-label">Completed Pickups</div>
        <div class="rapid-stat-value">${stats.completed_rapid}</div>
      </div>
      <div class="rapid-stat-item">
        <div class="rapid-stat-label">Rapid Revenue</div>
        <div class="rapid-stat-value">₹${stats.rapid_revenue}</div>
      </div>
    `;
  }

  return { init, showTooltip, hideTooltip };
})();

// ======= PRODUCT MANAGEMENT =======
const ProductAdmin = (() => {
  let products = [];

  async function init() {
    const query = document.getElementById('prodSearch')?.value || '';
    const category = document.getElementById('prodCatFilter')?.value || 'all';
    const shopId = document.getElementById('prodShopFilter')?.value || 'all';
    
    // Fetch products
    const data = await AdminAPI.getProducts(query, category, shopId);
    products = data || [];
    
    // Fetch shops for both filters and modals
    try {
        const shopsData = await AdminAPI.fetchJSON('../api/shops/list.php');
        if (shopsData && shopsData.status === 'success') {
            // Populate Shop Filter (if not already done)
            const shopFilter = document.getElementById('prodShopFilter');
            if (shopFilter && shopFilter.options.length <= 1) {
                const options = shopsData.data.map(s => `<option value="${s.id}">${s.shop_name}</option>`);
                shopFilter.innerHTML = '<option value="all">All Shops</option>' + options.join('');
                shopFilter.value = shopId;
            }

            // Populate Shop Dropdown in Add/Edit Modal
            const shopSelect = document.getElementById('prodShop');
            if (shopSelect) {
                shopSelect.innerHTML = shopsData.data.map(s => `<option value="${s.id}">${s.shop_name}</option>`).join('');
            }
        }
    } catch(e) { console.error("Failed to load shops", e); }

    setupProductPreview();
    render();
  }

  function setupProductPreview() {
    const fileInput = document.getElementById('prodImageFile');
    const preview = document.getElementById('prodImagePreview');
    const previewImg = preview?.querySelector('img');
    
    if (!fileInput || !previewImg) return;
    
    fileInput.addEventListener('change', function() {
      const file = this.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
          previewImg.src = e.target.result;
          preview.style.display = 'block';
        }
        reader.readAsDataURL(file);
      }
    });
  }

  function render(prods = products) {
    const tbody = document.getElementById('adminProductTable');
    if (!tbody) return;
    tbody.innerHTML = prods.map(p => `
      <tr>
        <td>
          <div class="table-product-cell">
            <div style="width:36px;height:36px;border-radius:8px;overflow:hidden;background:var(--bg-light);display:flex;align-items:center;justify-content:center;">
              ${p.image_url 
                ? `<img src="${AdminPanel.fixPath(p.image_url)}" style="width:100%;height:100%;object-fit:cover;">`
                : `<i data-lucide="package" style="width:18px;height:18px"></i>`
              }
            </div>
            <div>
              <div style="font-weight:700">${p.name}</div>
              <div class="fs-12 text-muted">${p.unit}</div>
            </div>
          </div>
        </td>
        <td>${p.category_name || 'Uncategorized'}</td>
        <td><strong>₹${p.price}</strong></td>
        <td><span class="status-pill sp-delivered">${p.stock} In Stock</span></td>
        <td><span class="status-pill sp-delivered">Active</span></td>
        <td>⭐ ${p.rating || '4.5'}</td>
        <td>
          <div style="display:flex; gap:8px;">
            <button class="admin-btn-icon edit" onclick="ProductAdmin.edit(${p.id})" title="Edit Product">
              <i data-lucide="edit-3"></i>
            </button>
            <button class="admin-btn-icon delete" onclick="ProductAdmin.deleteProd(${p.id})" title="Delete Product">
              <i data-lucide="trash-2"></i>
            </button>
          </div>
        </td>
      </tr>`).join('');
    if (window.lucide) lucide.createIcons();
  }

  function edit(id) {
    const p = products.find(prod => prod.id == id);
    if (!p) return;
    
    document.getElementById('prodModalTitle').innerHTML = '<i data-lucide="edit"></i> Edit Product';
    document.getElementById('prodName').value = p.name;
    document.getElementById('prodPrice').value = p.price;
    document.getElementById('prodUnit').value = p.unit;
    document.getElementById('prodStock').value = p.stock;
    document.getElementById('prodOldPrice').value = p.old_price || '';
    document.getElementById('prodRating').value = p.rating || '4.5';
    document.getElementById('prodCat').value = p.category_id;
    
    if (document.getElementById('prodIsBestseller')) {
        document.getElementById('prodIsBestseller').value = p.is_bestseller || 0;
    }
    
    if (document.getElementById('prodShop') && p.shop_id) {
        document.getElementById('prodShop').value = p.shop_id;
    }
    
    document.getElementById('prodImageFile').value = '';

    const preview = document.getElementById('prodImagePreview');
    const previewImg = preview?.querySelector('img');
    if (p.image_url && previewImg) {
      previewImg.src = AdminPanel.fixPath(p.image_url);
      preview.style.display = 'block';
    } else if (preview) {
      preview.style.display = 'none';
      if (previewImg) previewImg.src = '';
    }

    const modal = document.getElementById('addProductModal');
    modal.dataset.editId = id;
    Modal.open('addProductModal');
  }

  async function save() {
    console.log('ProductAdmin.save() called');
    const modal = document.getElementById('addProductModal');
    const id = modal.dataset.editId;
    
    const nameStr = document.getElementById('prodName').value.trim();
    const priceStr = document.getElementById('prodPrice').value;

    if (!nameStr || !priceStr || isNaN(priceStr)) {
      Toast.show('Valid Name and Price are required', 'error');
      return;
    }

    const formData = new FormData();
    if (id) formData.append('id', id);
    formData.append('name', nameStr);
    formData.append('price', priceStr);
    formData.append('unit', document.getElementById('prodUnit').value.trim());
    formData.append('stock', document.getElementById('prodStock').value);
    formData.append('old_price', document.getElementById('prodOldPrice').value);
    formData.append('rating', document.getElementById('prodRating').value || '4.5');
    formData.append('category_id', parseInt(document.getElementById('prodCat').value));
    
    const isBestsellerEl = document.getElementById('prodIsBestseller');
    if (isBestsellerEl) {
        formData.append('is_bestseller', isBestsellerEl.value);
    }

    const shopVal = document.getElementById('prodShop')?.value;
    if (shopVal) {
        formData.append('shop_id', parseInt(shopVal));
    }

    const fileInput = document.getElementById('prodImageFile');
    if (fileInput.files.length > 0) {
      formData.append('image', fileInput.files[0]);
    }

    try {
      const resp = await AdminAPI.saveProduct(formData);
      console.log('API Response:', resp);
      
      if (resp?.success) {
        Modal.close('addProductModal');
        Toast.show(resp.message, 'success');
        resetModal();
        init();
      } else {
        Toast.show(resp?.error || 'Save failed', 'error');
      }
    } catch (err) {
      console.error('Save failed:', err);
      Toast.show('Error: ' + err.message, 'error');
    }
  }

  async function deleteProd(id) {
    if (confirm('Are you sure you want to delete this product?')) {
      const resp = await AdminAPI.deleteProduct(id);
      if (resp?.success) {
        Toast.show('Product deleted successfully', 'success');
        init();
      }
    }
  }

  function resetModal() {
    const modal = document.getElementById('addProductModal');
    delete modal.dataset.editId;
    document.getElementById('prodModalTitle').innerHTML = '<i data-lucide="plus-circle"></i> Add New Product';
    document.getElementById('prodName').value = '';
    document.getElementById('prodPrice').value = '';
    document.getElementById('prodUnit').value = '';
    document.getElementById('prodStock').value = '';
    document.getElementById('prodOldPrice').value = '';
    document.getElementById('prodRating').value = '';
    if (document.getElementById('prodIsBestseller')) {
        document.getElementById('prodIsBestseller').value = '0';
    }
    
    document.getElementById('prodImageFile').value = '';
    const preview = document.getElementById('prodImagePreview');
    if (preview) {
      preview.style.display = 'none';
      const img = preview.querySelector('img');
      if (img) img.src = '';
    }
  }

  async function filter(query, category) {
    const data = await AdminAPI.getProducts(query, category);
    products = data || [];
    render();
  }

  return { init, render, filter, deleteProd, edit, save, resetModal };
})();

// ======= ORDER MANAGEMENT =======
const OrderAdmin = (() => {
  async function init() {
    const status = document.getElementById('orderStatusFilter')?.value || 'all';
    const search = document.getElementById('orderSearch')?.value || '';
    const date = document.getElementById('orderDate')?.value || '';
    const paymentType = document.getElementById('paymentTypeFilter')?.value || 'all';
    
    const orders = await AdminAPI.getOrders(status, search, date, paymentType);
    renderTable(orders, 'adminOrdersTable');
    renderTable(orders, 'adminOrdersTable2');
  }

  function renderTable(list, containerId) {
    const tbody = document.getElementById(containerId);
    if (!tbody) return;
    if (!list || list.length === 0) {
      tbody.innerHTML = '<tr><td colspan="12" style="text-align:center;padding:40px;color:var(--text-muted)">No orders found</td></tr>';
      return;
    }
    tbody.innerHTML = list.map(o => {
      if (containerId === 'adminOrdersTable') {
        // Mini table for dashboard (4 columns: ID, Customer, Amount, Status)
        return `
          <tr>
            <td><strong>${Utils.escapeHTML(o.order_number || String(o.id))}</strong></td>
            <td>${Utils.escapeHTML(o.customer_name || 'Guest')}</td>
            <td><strong>₹${o.grand_total}</strong></td>
            <td><span class="status-pill sp-${o.status.toLowerCase().replace(/ /g, '-')}">${Utils.escapeHTML(o.status)}</span></td>
          </tr>`;
      }
      
      // Full table for Order Management page
      let durationHtml = '—';
      if (o.picked_up_at && o.delivered_at) {
          const diff = new Date(o.delivered_at) - new Date(o.picked_up_at);
          const mins = Math.floor(diff / 60000);
          const secs = Math.floor((diff % 60000) / 1000);
          durationHtml = `<span style="color:var(--primary); font-weight:700">${mins}m ${secs}s</span>`;
      } else if (o.picked_up_at && o.status === 'On the Way') {
          durationHtml = `<span class="live-admin-timer" data-start="${o.picked_up_at}" style="color:var(--primary); font-weight:700">...</span>`;
      }

      return `
      <tr>
        <td><strong>${Utils.escapeHTML(o.order_number || String(o.id))}</strong></td>
        <td>${Utils.escapeHTML(o.customer_name || 'Guest')}</td>
        <td style="white-space: nowrap;"><strong style="color:var(--primary);">${Utils.escapeHTML(o.shop_name || 'Vendor')}</strong></td>
        <td class="fs-12 text-muted">${o.items_count || '—'} items</td>
        <td><strong>₹${o.grand_total}</strong></td>
        <!-- <td><strong style="color:#10b981;">₹${parseFloat(o.platform_profit || 0).toFixed(2)}</strong></td> -->
        <td style="white-space: nowrap;">
            ${o.payment_type === 'cod' 
                ? '<span class="payment-badge cod">Cash on Delivery</span>' 
                : '<span class="payment-badge paid">Paid Online</span>'}
        </td>
        <td style="white-space: nowrap;">
            ${o.payment_type === 'cod' ? (
                o.payment_status === 'Paid' 
                    ? '<span class="status-pill sp-delivered" style="font-size:10px; padding:2px 6px">Collected</span>' 
                    : '<span class="status-pill sp-cancelled" style="font-size:10px; padding:2px 6px">Not Collected</span>'
            ) : (
                o.payment_status === 'Refund Pending'
                    ? '<span class="status-pill sp-cancelled" style="font-size:10px; padding:2px 6px; background:#fca5a5; color:#7f1d1d">Refund Pending</span>'
                    : (o.payment_status === 'Refunded' 
                        ? '<span class="status-pill sp-delivered" style="font-size:10px; padding:2px 6px; background:#dcfce7; color:#14532d">Refunded</span>'
                        : '<span class="status-pill sp-delivered" style="font-size:10px; padding:2px 6px">Verified</span>')
            )}
        </td>
        <td>${Utils.escapeHTML(o.delivery_boy_name || '—')}</td>
        <td><span class="status-pill sp-${o.status.toLowerCase().replace(/ /g, '-')}">${Utils.escapeHTML(o.status)}</span></td>
        <td>${durationHtml}</td>
        <td class="fs-12 text-muted">${new Date(o.created_at).toLocaleDateString()}</td>
        <td>
          <button class="tbl-btn tbl-btn-view" onclick="OrderAdmin.viewDetails(${o.id})"><i data-lucide="clipboard-list"></i> Details</button>
        </td>
      </tr>`;
    }).join('');
    if (window.lucide) lucide.createIcons();
  }

  async function viewDetails(id) {
    const data = await AdminAPI.getOrderDetails(id);
    if (!data || !data.success) return;

    const o = data.order;
    const modal = document.getElementById('orderDetailsModal');
    if (!modal) return;

    modal.querySelector('#modalOrderNum').textContent = `${o.order_number || o.id}`;
    modal.querySelector('#modalStatus').textContent = o.status;
    let phone = o.customer_phone;
    if (!phone || phone === '—') {
        const phoneMatch = o.address?.match(/Ph:\s*(\d{10,15})/);
        if (phoneMatch) phone = phoneMatch[1];
    }

    modal.querySelector('#modalCustName').textContent = o.customer_name;
    const phoneLink = modal.querySelector('#modalCustPhone');
    if (phoneLink) {
        if (phone && phone !== '—') {
            phoneLink.innerHTML = `<a href="tel:${phone}" style="color:var(--text); text-decoration:none; display:inline-flex; align-items:center; gap:6px;">
                <i data-lucide="phone" style="width:12px; height:12px"></i> ${phone}
            </a>`;
        } else {
            phoneLink.textContent = '—';
        }
    }
    
    const emailLink = modal.querySelector('#modalCustEmail');
    if (emailLink) {
        if (o.customer_email && o.customer_email !== '—') {
            emailLink.innerHTML = `<a href="mailto:${o.customer_email}" style="color:var(--text-muted); text-decoration:none; display:inline-flex; align-items:center; gap:6px;">
                <i data-lucide="mail" style="width:12px; height:12px"></i> ${o.customer_email}
            </a>`;
        } else {
            emailLink.textContent = '—';
        }
    }
    const cleanedAddress = o.address ? o.address.replace(/,\s*Ph:\s*\d+$/, '') : '—';
    modal.querySelector('#modalAddress').textContent = cleanedAddress;

    const formatTime = (ts) => {
        if (!ts) return '—';
        return new Date(ts).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', hour12: true });
    };

    if (modal.querySelector('#modalAcceptedAt')) {
        modal.querySelector('#modalAcceptedAt').textContent = formatTime(o.vendor_accepted_at);
    }
    if (modal.querySelector('#modalReadyAt')) {
        modal.querySelector('#modalReadyAt').textContent = formatTime(o.ready_at);
    }
    
    if (modal.querySelector('#modalShopName')) {
        modal.querySelector('#modalShopName').textContent = o.shop_name || 'Shop Info';
        modal.querySelector('#modalShopAddress').textContent = o.shop_address || '—';
        
        const phoneContainer = modal.querySelector('#modalShopPhone');
        if (phoneContainer) {
            if (o.shop_phone && o.shop_phone !== '—') {
                phoneContainer.innerHTML = `
                    <a href="tel:${o.shop_phone}" style="color:var(--primary); text-decoration:none; display:inline-flex; align-items:center; gap:4px; font-weight:700">
                        <i data-lucide="phone" style="width:12px; height:12px"></i> ${o.shop_phone}
                    </a>`;
            } else {
                phoneContainer.textContent = '—';
            }
        }
        
        const locContainer = modal.querySelector('#modalShopLocation');
        if (locContainer) {
            if (o.latitude && o.longitude) {
                locContainer.innerHTML = `
                    <a href="https://www.google.com/maps/search/?api=1&query=${o.latitude},${o.longitude}" target="_blank" class="tbl-btn" style="display:inline-flex; align-items:center; gap:8px; background:#f0f9ff; color:#0369a1; border:1px solid #bae6fd; padding:6px 12px; font-size:12px; text-decoration:none; border-radius:10px; font-weight:700; transition:all 0.2s;" onmouseover="this.style.background='#e0f2fe'" onmouseout="this.style.background='#f0f9ff'">
                        <i data-lucide="map-pin" style="width:14px; height:14px;"></i> View on Google Maps
                    </a>`;
            } else {
                locContainer.innerHTML = '';
            }
        }
    }

    modal.querySelector('#modalPayment').textContent = o.payment_method + (o.payment_status === 'Paid' ? ' (Paid)' : '');
    
    // Fill bill summary
    const productTotal = parseFloat(o.total_amount || 0);
    const commission = parseFloat(o.commission_amount || (productTotal * 0.20));
    const vendorNet = parseFloat(o.vendor_earning || (productTotal - commission));
    const commissionRate = parseFloat(o.commission_rate || 20);

    if (modal.querySelector('#modalItemsTotal')) modal.querySelector('#modalItemsTotal').textContent = `₹${productTotal.toFixed(2)}`;
    if (modal.querySelector('#modalCommission')) modal.querySelector('#modalCommission').textContent = `-₹${commission.toFixed(2)}`;
    if (modal.querySelector('#modalCommissionLabel')) modal.querySelector('#modalCommissionLabel').textContent = `Commission (${commissionRate}%):`;
    if (modal.querySelector('#modalVendorEarning')) modal.querySelector('#modalVendorEarning').textContent = `₹${vendorNet.toFixed(2)}`;
    
    if (modal.querySelector('#modalDeliveryFee')) modal.querySelector('#modalDeliveryFee').textContent = `₹${parseFloat(o.delivery_charge).toFixed(2)}`;
    if (modal.querySelector('#modalPlatformFee')) modal.querySelector('#modalPlatformFee').textContent = `₹${parseFloat(o.platform_fee || 0).toFixed(2)}`;
    if (modal.querySelector('#modalHandlingFee')) modal.querySelector('#modalHandlingFee').textContent = `₹${parseFloat(o.handling_fee || 0).toFixed(2)}`;
    if (modal.querySelector('#modalTotal')) modal.querySelector('#modalTotal').textContent = `₹${parseFloat(o.grand_total).toFixed(2)}`;

    // Sync status dropdown
    const statusSelect = modal.querySelector('#modalStatusSelect');
    if (statusSelect) {
        // Restricted Admin Status Flow: Admin can only manage up to 'Ready for Pickup'
        // Delivery statuses (Picked up, On the way, Delivered) should be for the Delivery Boy
        const adminFlowStatuses = ['Placed', 'Confirmed', 'Preparing', 'Ready for Pickup', 'Cancelled'];
        const deliveryFlowStatuses = ['Picked Up', 'On the Way', 'Delivered'];
        
        let optionsHtml = '';
        let list = [...adminFlowStatuses];
        
        // If current status is a delivery status, add it to the list as a disabled option
        if (deliveryFlowStatuses.includes(o.status)) {
            optionsHtml = `
                <option value="${o.status}" selected disabled>${o.status} (Managed by Delivery)</option>
                <option value="Cancelled">Cancelled (Admin Override)</option>
            `;
            statusSelect.disabled = false; // Admin can still cancel
        } else {
            // Standard Admin Flow
            if (!list.includes(o.status)) {
                list.unshift(o.status);
            }
            optionsHtml = list.map(s => `
                <option value="${s}" ${o.status === s ? 'selected' : ''}>${s}</option>
            `).join('');
            statusSelect.disabled = false;
        }
        
        statusSelect.innerHTML = optionsHtml;
    }

    const itemsTbody = modal.querySelector('#modalItemsTable');
    itemsTbody.innerHTML = data.items.map(i => `
      <tr>
        <td>${Utils.escapeHTML(i.product_name || 'Deleted Product')} x ${i.quantity}</td>
        <td>₹${i.price}</td>
        <td>₹${(i.price * i.quantity).toFixed(2)}</td>
      </tr>
    `).join('');

    // Setup assignment dropdown
    // Setup assignment dropdown - Only show verified partners
    const deliveryBoys = await AdminAPI.getUsers('delivery', '', 'verified');
    const assignSelect = modal.querySelector('#modalAssignSelect');
    assignSelect.innerHTML = '<option value="">Select Partner</option>' + (deliveryBoys || []).map(d => `
        <option value="${d.id}" ${o.delivery_boy_id == d.id ? 'selected' : ''}>
            ${Utils.escapeHTML(d.name)} ${d.is_online == 1 ? '🟢 (Online)' : '⚪ (Offline)'}
        </option>
    `).join('');

    modal.dataset.orderId = id;
    
    // Toggle Refund Action Group visibility
    const refundGroup = document.getElementById('refundActionGroup');
    if (refundGroup) {
        if (o.payment_status === 'Refund Pending') {
            refundGroup.style.display = 'block';
        } else {
            refundGroup.style.display = 'none';
        }
    }

    Modal.open('orderDetailsModal');
    if (window.lucide) lucide.createIcons();
  }

  async function updateStatus(id, status) {
    const res = await AdminAPI.updateOrderStatus(id, status);
    if (res?.success) {
      Toast.show(res.message, 'success');
      
      // Update badge and dropdown in modal if still open
      const modal = document.getElementById('orderDetailsModal');
      if (modal && modal.dataset.orderId == id) {
          const badge = modal.querySelector('#modalStatus');
          if (badge) {
              badge.textContent = status;
              badge.className = `status-pill sp-${status.toLowerCase().replace(' ', '-')}`;
          }
          const select = modal.querySelector('#modalStatusSelect');
          if (select) {
              select.value = status;
          }
      }

      await init();
      // Also refresh tables in and out of modal
      if (typeof DashboardAdmin !== 'undefined') DashboardAdmin.init();
    }
  }

  async function assignDelivery() {
    const modal = document.getElementById('orderDetailsModal');
    const orderId = modal.dataset.orderId;
    const deliveryBoyId = modal.querySelector('#modalAssignSelect').value;

    if (!deliveryBoyId) {
        Toast.show('Please select a delivery partner', 'warning');
        return;
    }

    const res = await AdminAPI.assignDelivery(orderId, deliveryBoyId);
    if (res?.success) {
        Toast.show(res.message, 'success');
        Modal.close('orderDetailsModal');
        init();
    }
  }

  async function processRefund() {
    const modal = document.getElementById('orderDetailsModal');
    const orderId = modal.dataset.orderId;
    if (!orderId) return;

    if (!confirm('Are you sure you want to process this refund? This will return the money to the customer via Razorpay.')) {
        return;
    }

    const btn = document.getElementById('btnProcessRefund');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="spin" data-lucide="loader-2"></i> Processing Refund...';
    if (window.lucide) lucide.createIcons();

    try {
        const res = await AdminAPI.fetchJSON('../api/admin/orders/process_refund.php', {
            method: 'POST',
            body: JSON.stringify({ order_id: orderId })
        });

        if (res && res.status === 'success') {
            Toast.show(res.message, 'success');
            Modal.close('orderDetailsModal');
            init();
            if (typeof DashboardAdmin !== 'undefined') DashboardAdmin.init();
        } else {
            Toast.show(res?.message || 'Refund failed', 'error');
        }
    } catch (err) {
        Toast.show(err.message, 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalText;
        if (window.lucide) lucide.createIcons();
    }
  }

  async function filter(status) {
    // This is now handled by init() reading from DOM
    await init();
  }

  async function exportCSV() {
    const orders = await AdminAPI.getOrders();
    if (!orders?.length) return Toast.show('No orders to export', 'warning');

    const headers = ['Order ID', 'Customer', 'Amount', /*'Profit',*/ 'Method', 'Collection', 'Status', 'Date'];
    const data = orders.map(o => {
      let method = o.payment_type === 'cod' ? 'Cash on Delivery' : 'Paid Online';
      let collection = '';
      if (o.payment_type === 'cod') {
          collection = o.payment_status === 'Paid' ? 'Collected' : 'Not Collected';
      } else {
          collection = 'Verified';
      }
      
      return [
        o.order_number || o.id,
        o.customer_name || 'Guest',
        o.grand_total,
        // o.platform_profit || 0,
        method,
        collection,
        o.status,
        o.created_at
      ];
    });

    AdminPanel.downloadCSV(headers, data, `orders_export_${new Date().toISOString().split('T')[0]}.csv`);
    Toast.show('Orders exported successfully!', 'success');
  }

  return { init, renderTable, viewDetails, updateStatus, assignDelivery, processRefund, filter, exportCSV };
})();

// ======= CATEGORY MANAGEMENT =======
const CategoryAdmin = (() => {
  let categories = [];
  async function init() {
    const search = document.getElementById('catSearch')?.value || '';
    categories = await AdminAPI.getCategories(search) || [];
    
    // Update Stats
    const totalCatsEl = document.getElementById('totalCatsCount');
    const totalProdsEl = document.getElementById('totalProdsCount');
    if (totalCatsEl) totalCatsEl.textContent = categories.length;
    if (totalProdsEl) {
        const totalProds = categories.reduce((sum, c) => sum + parseInt(c.product_count || 0), 0);
        totalProdsEl.textContent = totalProds;
    }

    setupPreview();
    render();
    populateSelects();
  }

  function populateSelects() {
    // Populate filter dropdown on products page
    const filterSelect = document.getElementById('prodCatFilter');
    if (filterSelect) {
      filterSelect.innerHTML = '<option value="all">All Categories</option>' + categories.map(c => `
        <option value="${c.slug}">${Utils.escapeHTML(c.name)}</option>
      `).join('');
    }

    // Populate category select in Add/Edit Product modal
    const prodCatSelect = document.getElementById('prodCat');
    if (prodCatSelect) {
      prodCatSelect.innerHTML = '<option value="">Select Category</option>' + categories.map(c => `
        <option value="${c.id}">${Utils.escapeHTML(c.name)}</option>
      `).join('');
    }
  }

  function setupPreview() {
    const fileInput = document.getElementById('catImageFile');
    const preview = document.getElementById('catImagePreview');
    const previewImg = preview?.querySelector('img');
    
    if (!fileInput || !previewImg) return;
    
    fileInput.addEventListener('change', function() {
      const file = this.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
          previewImg.src = e.target.result;
          preview.style.display = 'block';
        }
        reader.readAsDataURL(file);
      }
    });
  }

  function render() {
    const tbody = document.querySelector('#categoriesTab tbody');
    if (!tbody) return;
    if (categories.length === 0) {
      tbody.innerHTML = '<tr><td colspan="4" style="text-align:center; padding: 24px; color:#6b7280;">No categories found.</td></tr>';
      return;
    }
    tbody.innerHTML = categories.map(c => `
      <tr>
        <td>
          <div style="display:flex;align-items:center;gap:12px">
            <div style="width:42px;height:42px;border-radius:12px;overflow:hidden;background:#f3f4f6;display:flex;align-items:center;justify-content:center;border:1px solid #e5e7eb;">
              ${c.image_url 
                ? `<img src="${AdminPanel.fixPath(c.image_url)}" style="width:100%;height:100%;object-fit:cover;">`
                : `<i data-lucide="folder" style="width:20px;height:20px;color:#9ca3af;"></i>`
              }
            </div>
            <div>
                <div style="font-weight:700; color:#111827;">${Utils.escapeHTML(c.name)}</div>
                <div style="font-size:11px; color:#6b7280;">/${Utils.escapeHTML(c.slug)}</div>
            </div>
          </div>
        </td>
        <td>
            <div style="display:flex; align-items:center; gap:6px;">
                <span style="font-weight:700; color:#111827;">${c.product_count}</span>
                <span style="font-size:12px; color:#6b7280;">Products</span>
            </div>
        </td>
        <td>
            <div style="display:flex; align-items:center; cursor:pointer;" onclick="CategoryAdmin.toggleStatus(${c.id})" title="Click to toggle status">
                <span class="badge-status ${parseInt(c.status) === 1 ? 'st-active' : 'st-inactive'}">
                    ${parseInt(c.status) === 1 ? 'Active' : 'Hidden'}
                </span>
            </div>
        </td>
        <td>
            <div style="display:flex; gap:8px;">
                <button class="admin-btn" style="background:var(--primary-pale); color:var(--primary); padding:8px; border-radius:10px; width:36px; height:36px; justify-content:center;" onclick="CategoryAdmin.edit(${c.id})" title="Edit Category">
                    <i data-lucide="edit-3" style="width:18px; height:18px;"></i>
                </button>
                <button class="admin-btn" style="background:#fee2e2; color:#991b1b; padding:8px; border-radius:10px; width:36px; height:36px; justify-content:center;" onclick="CategoryAdmin.deleteCat(${c.id})" title="Delete Category">
                    <i data-lucide="trash-2" style="width:18px; height:18px;"></i>
                </button>
            </div>
        </td>
      </tr>`).join('');
    if (window.lucide) lucide.createIcons();
  }

  function resetModal() {
    const modal = document.getElementById('addCategoryModal');
    if (!modal) return;
    delete modal.dataset.editId;
    document.getElementById('catModalTitle').innerHTML = '<i data-lucide="plus-circle"></i> Add New Category';
    document.getElementById('catName').value = '';
    document.getElementById('catSlug').value = '';
    document.getElementById('catImageFile').value = '';
    const preview = document.getElementById('catImagePreview');
    preview.style.display = 'none';
    preview.querySelector('img').src = '';
  }

  function edit(id) {
    const cat = categories.find(c => c.id == id);
    if (!cat) return;
    
    const modal = document.getElementById('addCategoryModal');
    modal.dataset.editId = id;
    document.getElementById('catModalTitle').innerHTML = '<i data-lucide="edit"></i> Edit Category';
    document.getElementById('catName').value = cat.name;
    document.getElementById('catSlug').value = cat.slug;
    
    const preview = document.getElementById('catImagePreview');
    if (cat.image_url) {
      preview.style.display = 'block';
      preview.querySelector('img').src = AdminPanel.fixPath(cat.image_url);
    } else {
      preview.style.display = 'none';
    }
    
    Modal.open('addCategoryModal');
  }

  async function save() {
    const modal = document.getElementById('addCategoryModal');
    const id = modal.dataset.editId;
    
    const formData = new FormData();
    if (id) formData.append('id', id);
    formData.append('name', document.getElementById('catName').value.trim());
    formData.append('slug', document.getElementById('catSlug').value.trim());
    
    const fileInput = document.getElementById('catImageFile');
    if (fileInput.files[0]) {
      formData.append('image', fileInput.files[0]);
    }

    if (!formData.get('name')) {
      Toast.show('Category Name is required', 'error');
      return;
    }

    try {
      const resp = await AdminAPI.saveCategory(formData);
      Toast.show(resp.message || 'Saved successfully', 'success');
      Modal.close('addCategoryModal');
      init(); // Reload all
    } catch (err) {
      console.error(err);
      Toast.show('Failed to save category', 'error');
    }
  }

  async function deleteCat(id) {
    if (confirm('Delete this category? This will fail if there are products in it.')) {
      try {
        const resp = await AdminAPI.deleteCategory(id);
        if (resp?.success) {
          Toast.show(resp.message, 'success');
          init();
        } else {
          Toast.show(resp?.error || 'Delete failed', 'error');
        }
      } catch (err) {
        Toast.show('Error: ' + err.message, 'error');
      }
    }
  }

  async function toggleStatus(id) {
    try {
        const resp = await AdminAPI.toggleCategoryStatus(id);
        if (resp?.success) {
            Toast.show(resp.message, 'success');
            init(); // Refresh to update counts and badges
        } else {
            Toast.show(resp?.error || 'Failed to toggle status', 'error');
        }
    } catch (err) {
        Toast.show('Error: ' + err.message, 'error');
    }
  }

  return { init, render, edit, deleteCat, save, resetModal, toggleStatus };
})();

// ======= CUSTOMER/DELIVERY MANAGEMENT =======
const CustomerAdmin = (() => {
  let users = [];
  let currentFilter = 'all';
  let searchTerm = '';

  async function init() {
    AdminPanel.showLoading?.();
    const data = await AdminAPI.getUsers('customer', searchTerm, currentFilter);
    users = data || [];
    render();
    AdminPanel.hideLoading?.();
  }

  function render() {
    const tbody = document.getElementById('adminCustomerTable');
    if (!tbody) return;

    if (users.length === 0) {
      tbody.innerHTML = `<tr><td colspan="8" style="text-align:center;padding:60px;color:var(--text-muted)">
        <i data-lucide="users" style="width:48px;height:48px;opacity:0.2;margin-bottom:12px"></i>
        <p>No customers found matching your criteria</p>
      </td></tr>`;
      if (window.lucide) lucide.createIcons();
      return;
    }

    tbody.innerHTML = users.map(c => `
      <tr>
        <td class="fs-12">#CUST-${c.id}</td>
        <td>
          <div class="table-user-cell">
            <div class="table-avatar" style="background:${c.avatar_color || '#10b981'}">
              ${Utils.escapeHTML(c.name.charAt(0).toUpperCase())}
            </div>
            <div class="table-user-name">${Utils.escapeHTML(c.name)}</div>
          </div>
        </td>
        <td>
          <div class="fs-13"><strong>${c.phone ? `<a href="tel:${c.phone}" style="color:inherit;text-decoration:none">${c.phone}</a>` : '—'}</strong></div>
          <div class="fs-11 text-muted">${c.email ? `<a href="mailto:${c.email}" style="color:inherit;text-decoration:none">${c.email}</a>` : '—'}</div>
        </td>
        <td><strong>${c.total_orders || 0}</strong></td>
        <td><strong>₹${parseFloat(c.total_spent || 0).toLocaleString()}</strong></td>
        <td class="fs-12 text-muted">${new Date(c.joined_at).toLocaleDateString()}</td>
        <td>
          <span class="status-pill ${c.status === 'Active' ? 'sp-delivered' : 'sp-cancelled'}">
            ${c.status || 'Active'}
          </span>
        </td>
        <td style="text-align:right">
          <div style="display:flex; gap:6px; justify-content:flex-end">
            <button class="tbl-btn tbl-btn-edit" onclick="CustomerAdmin.viewProfile(${c.id})" title="View Profile">
              <i data-lucide="eye"></i> View
            </button>
            <button class="tbl-btn ${c.status === 'Active' ? 'tbl-btn-warning' : 'tbl-btn-edit'}" 
                    onclick="CustomerAdmin.toggleStatus(${c.id})" title="${c.status === 'Active' ? 'Block' : 'Unblock'}">
              <i data-lucide="${c.status === 'Active' ? 'ban' : 'check-circle'}"></i> 
              ${c.status === 'Active' ? 'Block' : 'Unblock'}
            </button>
            <button class="tbl-btn tbl-btn-delete" onclick="CustomerAdmin.deleteUser(${c.id})" title="Delete">
              <i data-lucide="trash-2"></i>
            </button>
          </div>
        </td>
      </tr>
    `).join('');
    if (window.lucide) lucide.createIcons();
  }

  function handleSearch(val) {
    searchTerm = val.trim();
    clearTimeout(window.searchTimer);
    window.searchTimer = setTimeout(() => init(), 500);
  }

  function setFilter(filter, btn) {
    currentFilter = filter;
    document.querySelectorAll('.filter-tabs .range-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    init();
  }

  async function viewProfile(id) {
    const details = await AdminAPI.getUserDetails(id);
    if (!details) return;

    document.getElementById('customerListView').style.display = 'none';
    const profileView = document.getElementById('customerProfileView');
    profileView.style.display = 'block';

    const content = document.getElementById('profileContent');
    content.innerHTML = `
      <div class="profile-layout">
        <div class="profile-sidebar">
          <div class="profile-card">
            <div class="profile-header-main">
              <div class="profile-lg-avatar" style="background:${details.user.avatar_color || '#10b981'}">
                ${Utils.escapeHTML(details.user.name.charAt(0).toUpperCase())}
              </div>
              <h3>${Utils.escapeHTML(details.user.name)}</h3>
              <p class="text-muted">Customer since ${new Date(details.user.joined_at).toLocaleDateString()}</p>
              <div class="status-pill ${details.user.status === 'Active' ? 'sp-delivered' : 'sp-cancelled'}" style="margin-top:8px">
                ${Utils.escapeHTML(details.user.status)}
              </div>
            </div>
            <div class="profile-stats-grid">
              <div class="p-stat"><strong>${details.user.total_orders}</strong><label>Total Orders</label></div>
              <div class="p-stat"><strong>₹${parseFloat(details.user.total_spent).toLocaleString()}</strong><label>Total Spent</label></div>
            </div>
            <div class="profile-info-list">
              <div class="p-info-item"><i data-lucide="mail"></i> <span>${details.user.email ? `<a href="mailto:${encodeURIComponent(details.user.email)}" style="color:var(--primary);text-decoration:none">${Utils.escapeHTML(details.user.email)}</a>` : 'No email'}</span></div>
              <div class="p-info-item"><i data-lucide="phone"></i> <span>${details.user.phone ? `<a href="tel:${encodeURIComponent(details.user.phone)}" style="color:var(--primary);text-decoration:none">${Utils.escapeHTML(details.user.phone)}</a>` : 'No phone'}</span></div>
              <div class="p-info-item"><i data-lucide="calendar"></i> <span>Last Order: ${details.last_order_date ? new Date(details.last_order_date).toLocaleDateString() : 'Never'}</span></div>
            </div>
          </div>

          <div class="profile-card" style="margin-top:20px">
            <h4 style="margin-bottom:12px; display:flex; align-items:center; gap:8px"><i data-lucide="map-pin"></i> Saved Addresses</h4>
            <div class="profile-addresses">
              ${details.addresses.length ? details.addresses.map(a => `
                <div class="address-item">
                  <strong>${Utils.escapeHTML(a.type || 'Home')}</strong>
                  <p>${Utils.escapeHTML(a.address_line1)}, ${Utils.escapeHTML(a.city)}</p>
                </div>
              `).join('') : '<p class="text-muted fs-12">No saved addresses</p>'}
            </div>
          </div>
        </div>

        <div class="profile-main">
          <div class="profile-card">
            <h4 style="margin-bottom:16px"><i data-lucide="shopping-bag"></i> Order History</h4>
            <div class="admin-table-wrapper">
              <table class="admin-table">
                <thead>
                  <tr>
                    <th>Order #</th>
                    <th>Date</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  ${details.orders.length ? details.orders.map(o => `
                    <tr>
                      <td><strong>${Utils.escapeHTML(o.order_number || String(o.id))}</strong></td>
                      <td class="fs-12">${new Date(o.created_at).toLocaleDateString()}</td>
                      <td><strong>₹${o.grand_total}</strong></td>
                      <td><span class="status-pill sp-${o.status.toLowerCase()}">${Utils.escapeHTML(o.status)}</span></td>
                      <td><button class="tbl-btn" onclick="OrderAdmin.viewDetails(${o.id})">Details</button></td>
                    </tr>
                  `).join('') : '<tr><td colspan="5" style="text-align:center;padding:30px">No orders found</td></tr>'}
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    `;
    if (window.lucide) lucide.createIcons();
  }

  function closeProfile() {
    document.getElementById('customerProfileView').style.display = 'none';
    document.getElementById('customerListView').style.display = 'block';
  }

  async function toggleStatus(id) {
    const res = await AdminAPI.toggleUserStatus(id);
    if (res?.success) {
      Toast.show(res.message, 'success');
      init();
    }
  }

  async function deleteUser(id) {
    if (confirm('Are you sure you want to delete this customer? This will be a soft delete.')) {
        const res = await AdminAPI.deleteUser(id);
        if (res?.success) { 
          Toast.show(res.message, 'success'); 
          init(); 
        }
    }
  }

  async function exportCSV() {
    const data = await AdminAPI.getUsers('customer', searchTerm, currentFilter);
    if (!data?.length) return Toast.show('No customers to export', 'warning');

    const headers = ['ID', 'Name', 'Phone', 'Email', 'Orders', 'Spending', 'Joined Date', 'Status'];
    const rows = data.map(u => [u.id, u.name, u.phone, u.email, u.total_orders, u.total_spent, u.joined_at, u.status]);

    AdminPanel.downloadCSV(headers, rows, `customers_export.csv`);
    Toast.show('Customers exported!', 'success');
  }

  return { init, handleSearch, setFilter, viewProfile, closeProfile, toggleStatus, deleteUser, exportCSV };
})();

const DeliveryAdmin = (() => {
  let users = [];
  let currentFilter = 'all';

  async function init() {
    AdminPanel.showLoading?.();
    const allData = await AdminAPI.getUsers('delivery', '', 'all') || [];
    
    // Update Stats
    const totalEl = document.getElementById('totalPartnersCount');
    const pendingEl = document.getElementById('pendingPartnersCount');
    const activeEl = document.getElementById('activePartnersCount');

    if (totalEl) totalEl.textContent = allData.length;
    if (pendingEl) pendingEl.textContent = allData.filter(u => u.verification_status === 'Verification Pending').length;
    if (activeEl) activeEl.textContent = allData.filter(u => u.verification_status === 'Verified').length;

    if (currentFilter === 'all') {
        users = allData;
    } else if (currentFilter === 'pending') {
        users = allData.filter(u => u.verification_status === 'Verification Pending');
    } else if (currentFilter === 'verified') {
        users = allData.filter(u => u.verification_status === 'Verified');
    } else {
        users = allData;
    }

    render();
    AdminPanel.hideLoading?.();
  }

  function render() {
    const tbody = document.getElementById('adminDeliveryTable');
    if (!tbody) return;

    if (users.length === 0) {
      tbody.innerHTML = `<tr><td colspan="6" style="text-align:center;padding:60px;color:var(--text-muted)">
        <i data-lucide="bike" style="width:48px;height:48px;opacity:0.2;margin-bottom:12px"></i>
        <p>No delivery partners found</p>
      </td></tr>`;
      if (window.lucide) lucide.createIcons();
      return;
    }

    tbody.innerHTML = users.map(c => `
      <tr>
        <td>
          <div class="table-user-cell">
            <div class="table-avatar" style="background:var(--primary); border-radius:10px; width:40px; height:40px;">
              ${c.image 
                ? `<img src="${AdminPanel.fixPath(c.image)}" style="width:100%;height:100%;object-fit:cover;border-radius:10px;">`
                : Utils.escapeHTML(c.name.charAt(0).toUpperCase())
              }
            </div>
            <div class="table-user-name" style="font-weight:700">${Utils.escapeHTML(c.name)}</div>
          </div>
        </td>
        <td>
          <div class="fs-13"><strong>${c.phone ? `<a href="tel:${c.phone}" style="color:inherit;text-decoration:none">${c.phone}</a>` : '—'}</strong></div>
          <div class="fs-11 text-muted">${c.email ? `<a href="mailto:${c.email}" style="color:inherit;text-decoration:none">${c.email}</a>` : '—'}</div>
        </td>
        <td>
          <span class="status-pill sp-${(c.verification_status || 'Pending').toLowerCase().replace(' ', '-')}">
            ${c.verification_status || 'Pending'}
          </span>
        </td>
        <td>
          <div style="display:flex; align-items:center; gap:6px;">
            <div style="width:10px; height:10px; border-radius:50%; background:${c.is_online == 1 ? '#10b981' : '#9ca3af'}"></div>
            <span style="font-size:12px; font-weight:600; color:${c.is_online == 1 ? '#065f46' : '#374151'}">${c.is_online == 1 ? 'Online' : 'Offline'}</span>
          </div>
        </td>
        <td>
          <div class="fs-13"><strong>${c.total_deliveries || 0}</strong></div>
          <div class="fs-11 text-muted">₹${c.total_earned || 0} earned</div>
        </td>
        <td class="fs-13 text-muted">${c.joined_at ? new Date(c.joined_at).toLocaleDateString() : '—'}</td>
        <td style="text-align: right;">
            <div style="display:flex; gap:6px; justify-content:flex-end">
                <button class="tbl-btn tbl-btn-edit" onclick="DeliveryAdmin.edit(${c.id})" title="Review & View Details" style="background:var(--primary-pale); color:var(--primary); border:none;">
                    <i data-lucide="eye" style="width:18px;height:18px"></i>
                </button>
                <button class="tbl-btn tbl-btn-delete" onclick="DeliveryAdmin.deleteUser(${c.id})" title="Delete">
                    <i data-lucide="trash-2" style="width:18px;height:18px"></i>
                </button>
            </div>
        </td>
      </tr>
    `).join('');
    if (window.lucide) lucide.createIcons();
  }

  function setFilter(filter, btn) {
    currentFilter = filter;
    document.querySelectorAll('.filter-tabs .range-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    init();
  }

  function resetModal() {
    const modal = document.getElementById('deliveryBoyModal');
    delete modal.dataset.editId;
    modal.dataset.role = 'delivery';
    modal.querySelector('.modal-title').textContent = 'Add Delivery Partner';
    document.getElementById('dbName').value = '';
    document.getElementById('dbPhone').value = '';
    document.getElementById('dbEmail').value = '';
    document.getElementById('dbPassword').value = '';
    document.getElementById('dbPasswordHelp').style.display = 'none';
    
    // Reset extended details
    const extended = document.getElementById('dbExtendedDetails');
    if (extended) {
        extended.style.display = 'none';
        document.getElementById('dbBankInfo').textContent = 'Loading...';
        document.getElementById('dbVehicleInfo').textContent = '—';
        document.getElementById('dbVerifyStatus').textContent = '—';
    }
  }

  async function edit(id) {
    const u = users.find(x => x.id == id);
    if (!u) return;
    const modal = document.getElementById('deliveryBoyModal');
    modal.dataset.userId = id; // New ID for verification
    modal.dataset.editId = id;
    modal.dataset.role = 'delivery';
    modal.querySelector('.modal-title').textContent = 'Edit Delivery Partner';
    document.getElementById('dbName').value = u.name;
    document.getElementById('dbPhone').value = u.phone;
    document.getElementById('dbEmail').value = u.email;
    document.getElementById('dbPassword').value = '';

    // Update Header
    document.getElementById('dbHeaderName').textContent = u.name;
    document.getElementById('dbHeaderEmail').textContent = u.email;
    
    const profileImg = document.getElementById('dbProfileImg');
    const avatarInitial = document.getElementById('dbAvatarInitial');
    if (u.image) {
        profileImg.src = AdminPanel.fixPath(u.image);
        profileImg.style.display = 'block';
        avatarInitial.style.display = 'none';
    } else {
        profileImg.style.display = 'none';
        avatarInitial.style.display = 'block';
        avatarInitial.textContent = u.name.charAt(0).toUpperCase();
    }

    // Fetch and show extended details
    const extended = document.getElementById('dbExtendedDetails');
    if (extended) {
        extended.style.display = 'block';
        const res = await fetch(`../api/admin/users/get_delivery_details.php?user_id=${id}`).then(r => r.json());
        if (res.success && res.details) {
            const d = res.details;
            
            // Re-check image from details if list didn't have it
            if (d.image && !u.image) {
                profileImg.src = AdminPanel.fixPath(d.image);
                profileImg.style.display = 'block';
                avatarInitial.style.display = 'none';
            }

            document.getElementById('dbVehicleInfo').textContent = `${d.vehicle_type || '—'} · ${d.vehicle_number || 'No Plate'}`;
            document.getElementById('dbLicenseInfo').textContent = d.license_number || '—';
            document.getElementById('dbVerifyStatus').textContent = d.verification_status || 'Pending';
            document.getElementById('dbVerifyStatus').className = `badge-${(d.verification_status || 'Pending').toLowerCase().replace(' ', '-')}`;
            
            document.getElementById('dbBankInfo').innerHTML = `
                <div>
                    <div style="color:var(--text-muted); font-size:10px; margin-bottom:2px">Bank Name</div>
                    <div style="font-weight:700; font-size:13px">${Utils.escapeHTML(d.bank_name || '—')}</div>
                </div>
                <div>
                    <div style="color:var(--text-muted); font-size:10px; margin-bottom:2px">Account Holder</div>
                    <div style="font-weight:700; font-size:13px">${Utils.escapeHTML(d.acc_holder_name || '—')}</div>
                </div>
                <div>
                    <div style="color:var(--text-muted); font-size:10px; margin-bottom:2px">Account Number</div>
                    <div style="font-weight:700; font-size:13px">${Utils.escapeHTML(d.acc_number || '—')}</div>
                </div>
                <div>
                    <div style="color:var(--text-muted); font-size:10px; margin-bottom:2px">IFSC Code</div>
                    <div style="font-weight:700; font-size:13px">${Utils.escapeHTML(d.ifsc_code || '—')}</div>
                </div>
                <div style="grid-column: span 2">
                    <div style="color:var(--text-muted); font-size:10px; margin-bottom:2px">UPI ID</div>
                    <div style="font-weight:700; font-size:13px; color:var(--primary)">${Utils.escapeHTML(d.upi_id || '—')}</div>
                </div>
            `;

            // Document Buttons
            const setupDoc = (btnId, path) => {
                const btn = document.getElementById(btnId);
                if (!btn) return;
                if (path) {
                    btn.style.display = 'inline-flex';
                    btn.onclick = () => window.open('../' + path, '_blank');
                    btn.classList.add('active');
                } else {
                    btn.style.display = 'none';
                }
            };
            setupDoc('btnViewLicense', d.license_doc);
            setupDoc('btnViewAadhaar', d.aadhaar_doc);
            setupDoc('btnViewRC', d.rc_doc);
            
            if (window.lucide) lucide.createIcons();
        }
    }

    Modal.open('deliveryBoyModal');
  }

  async function save() {
    const modal = document.getElementById('deliveryBoyModal');
    const data = {
        id: modal.dataset.editId,
        name: document.getElementById('dbName').value,
        phone: document.getElementById('dbPhone').value,
        email: document.getElementById('dbEmail').value,
        password: document.getElementById('dbPassword').value,
        role: modal.dataset.role || 'delivery'
    };

    if (!data.name || !data.phone || !data.email) { Toast.show('Name, phone and email required', 'warning'); return; }

    if (!Utils.isValidEmail(data.email)) {
        Toast.show('Please enter a valid partner email', 'error');
        return;
    }

    const res = await AdminAPI.saveUser(data);
    if (res?.success) {
        Toast.show(res.message, 'success');
        Modal.close('deliveryBoyModal');
        if (data.role === 'customer') CustomerAdmin.init();
        else DeliveryAdmin.init();
    }
  }

  async function deleteUser(id) {
    if (confirm('Delete this delivery partner?')) {
        const res = await AdminAPI.deleteUser(id);
        if (res?.success) { Toast.show(res.message, 'success'); init(); }
    }
  }

  async function verify(status) {
      const modal = document.getElementById('deliveryBoyModal');
      const userId = modal.dataset.userId;
      if (!userId) return;

      // UX Improvement: Immediate Feedback
      const actions = document.getElementById('verifyActions');
      const btns = actions.querySelectorAll('button');
      const targetBtn = status === 'Verified' ? btns[0] : btns[1];
      const originalHtml = targetBtn.innerHTML;

      // Disable buttons to prevent double-clicks
      btns.forEach(b => b.disabled = true);
      targetBtn.innerHTML = `<span class="loading-spinner"></span> Processing...`;
      
      // Optimistic Status Update in Modal
      const badge = document.getElementById('dbVerifyStatus');
      if (badge) {
          badge.textContent = status;
          badge.className = `badge-${status.toLowerCase().replace(' ', '-')}`;
      }

      try {
          const res = await fetch('../api/admin/users/verify_partner.php', {
              method: 'POST',
              headers: { 'Content-Type': 'application/json' },
              body: JSON.stringify({ user_id: userId, status })
          }).then(r => r.json());

          if (res.success) {
              Toast.show(res.message, 'success');
              init(); // Refresh list background
          } else {
              Toast.show(res.error, 'error');
              edit(userId); // Rollback modal if failed
          }
      } catch (err) {
          Toast.show('Network error', 'error');
          edit(userId); // Rollback
      } finally {
          btns.forEach(b => b.disabled = false);
          targetBtn.innerHTML = originalHtml;
      }
  }

  return { init, resetModal, edit, save, deleteUser, verify };
})();

// ======= PAYMENT TRACKING =======
const CustomerPaymentAdmin = (() => {
  async function init() {
    const data = await AdminAPI.getPayments();
    if (data?.success) {
        render(data.payments);
    }
  }

  function render(list) {
    const tbody = document.querySelector('#paymentsTab tbody');
    if (!tbody) return;

    if (!list || list.length === 0) {
      tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;padding:40px;color:var(--text-muted)">No transactions found</td></tr>';
      return;
    }

    tbody.innerHTML = list.map(p => `
      <tr>
        <td><strong>#TR-${p.id}</strong></td>
        <td>${Utils.escapeHTML(p.customer_name)}</td>
        <td><strong>₹${p.grand_total}</strong></td>
        <td><span class="payment-badge ${p.payment_status === 'Paid' ? 'paid' : 'cod'}">${Utils.escapeHTML(p.payment_method)}</span></td>
        <td class="fs-12 text-muted">${Utils.escapeHTML(p.razorpay_payment_id || '—')}</td>
        <td class="fs-12 text-muted">${new Date(p.created_at).toLocaleString()}</td>
      </tr>
    `).join('');
  }

  async function exportCSV() {
    const data = await AdminAPI.getPayments();
    if (!data?.success || !data.payments?.length) return Toast.show('No transactions to export', 'warning');

    const headers = ['Trans ID', 'Customer', 'Amount', 'Method', 'Razorpay ID', 'Date'];
    const rows = data.payments.map(p => [
        p.id, p.customer_name, p.grand_total, p.payment_method, p.razorpay_payment_id || 'N/A', p.created_at
    ]);

    AdminPanel.downloadCSV(headers, rows, `payments_export.csv`);
    Toast.show('Payments exported!', 'success');
  }

  return { init, exportCSV };
})();

// ======= STORE SETTINGS =======
const SettingsAdmin = (() => {
  async function init() {
    AdminPanel.showLoading?.();
    const data = await AdminAPI.getSettings();
    if (data?.success) {
        Object.keys(data.settings).forEach(key => {
            const el = document.getElementById(key);
            if (!el) return;
            
            if (el.type === 'checkbox') {
                el.checked = data.settings[key] == '1' || data.settings[key] == 'true';
            } else {
                el.value = data.settings[key];
            }
        });
    }
    AdminPanel.hideLoading?.();
  }

  function switchTab(btn, tabId) {
    // Buttons
    document.querySelectorAll('.settings-tab-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');

    // Content
    document.querySelectorAll('.settings-tab-content').forEach(c => c.style.display = 'none');
    document.getElementById('settings-' + tabId).style.display = 'block';
  }

  async function save() {
    const fields = [
        'base_delivery_fee', 'handling_fee', 'platform_fee', 'vendor_commission_percentage', 'enable_cod', 'shop_status'
    ];

    const data = {};
    fields.forEach(f => {
        const el = document.getElementById(f);
        if (el) {
            if (el.type === 'checkbox') {
                data[f] = el.checked ? '1' : '0';
            } else {
                data[f] = el.value.trim();
            }
        }
    });

    if (data.store_email && !Utils.isValidEmail(data.store_email)) {
        Toast.show('Please enter a valid store email', 'error');
        return;
    }

    try {
        AdminPanel.showLoading?.();
        const res = await AdminAPI.saveSettings(data);
        if (res?.success) {
            Toast.show(res.message, 'success');
        } else {
            Toast.show(res?.error || 'Failed to save settings', 'error');
        }
    } catch (err) {
        Toast.show('Error: ' + err.message, 'error');
    } finally {
        AdminPanel.hideLoading?.();
    }
  }

  return { init, save, switchTab };
})();


// ======= NOTIFICATIONS =======
const Notifications = (() => {
  let orders = [];

  async function init() {
    check();
    setInterval(check, 30000); // Check every 30s
    
    // Close dropdown on outside click
    document.addEventListener('click', (e) => {
      const dropdown = document.getElementById('notifDropdown');
      const btn = document.querySelector('.admin-notif-btn');
      if (dropdown && dropdown.classList.contains('open') && !dropdown.contains(e.target) && !btn.contains(e.target)) {
        dropdown.classList.remove('open');
      }
    });
  }

  async function check() {
    const data = await AdminAPI.getStats();
    if (!data) return;
    
    const count = parseInt(data.stats.pending_orders) || 0;
    const dot = document.getElementById('notifDot');
    const badge = document.getElementById('notifCountBadge');
    
    if (dot) dot.style.display = count > 0 ? 'block' : 'none';
    if (badge) badge.textContent = count;
    
    orders = data.recent_orders.filter(o => o.status === 'Pending');
  }

  function toggle() {
    const dropdown = document.getElementById('notifDropdown');
    if (!dropdown) return;
    
    const isOpen = dropdown.classList.toggle('open');
    if (isOpen) render();
  }

  function render() {
    const list = document.getElementById('notifList');
    if (!list) return;

    if (orders.length === 0) {
      list.innerHTML = '<div class="notif-empty">No new notifications</div>';
      return;
    }

    list.innerHTML = orders.map(o => `
      <div class="notif-item" onclick="OrderAdmin.viewDetails(${o.id}); Notifications.toggle()">
        <div class="notif-item-icon"><i data-lucide="shopping-bag"></i></div>
        <div class="notif-item-content">
          <div class="notif-item-title">New Order ${Utils.escapeHTML(o.order_number || String(o.id))}</div>
          <div class="notif-item-desc">${Utils.escapeHTML(o.customer_name)} placed an order for ₹${o.grand_total}</div>
          <div class="notif-item-time">${new Date(o.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</div>
        </div>
      </div>
    `).join('');
    
    if (window.lucide) lucide.createIcons();
  }

  return { init, toggle, check };
})();

// ======= SHOP ADMIN =======
const ShopAdmin = (() => {
  let shops = [];
  let map = null;
  let marker = null;
  const defaultLoc = [11.1271, 78.6569]; // Tamil Nadu center

  function init(data) {
    shops = data || [];
    setupShopPreview();
    initMap();
  }

  function setupShopPreview() {
    const fileInput = document.getElementById('shopImageFile');
    const preview = document.getElementById('shopImagePreview');
    const previewImg = preview?.querySelector('img');
    
    if (!fileInput || !previewImg) return;
    
    // Remove if previously attached to avoid multiple listeners
    const oldInput = fileInput.cloneNode(true);
    fileInput.parentNode.replaceChild(oldInput, fileInput);
    
    oldInput.addEventListener('change', function() {
      const file = this.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
          previewImg.src = e.target.result;
          preview.style.display = 'block';
        }
        reader.readAsDataURL(file);
      }
    });
  }

  function setMarker(lat, lng, updateInputs = true) {
    if (!map) return;
    lat = parseFloat(lat);
    lng = parseFloat(lng);
    if (isNaN(lat) || isNaN(lng)) return;

    if (marker) {
      marker.setLatLng([lat, lng]);
    } else {
      marker = L.marker([lat, lng]).addTo(map);
    }
    
    if (updateInputs) {
        document.getElementById('shopLat').value = lat.toFixed(8);
        document.getElementById('shopLng').value = lng.toFixed(8);
    }
    map.panTo([lat, lng]);
  }

  function initMap() {
    if (map) return;
    map = L.map('shopMap').setView(defaultLoc, 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 19,
      attribution: '© OpenStreetMap'
    }).addTo(map);

    // Add Geocoder (Search)
    const geocoder = L.Control.geocoder({
      defaultMarkGeocode: false,
      placeholder: "Search for address...",
      text: "Search"
    })
    .on('markgeocode', function(e) {
      const { center } = e.geocode;
      setMarker(center.lat, center.lng);
      map.setView(center, 16);
    })
    .addTo(map);

    // Add Locate Me Button
    const LocateControl = L.Control.extend({
        options: { position: 'topleft' },
        onAdd: function (map) {
            const container = L.DomUtil.create('div', 'leaflet-bar leaflet-control leaflet-control-custom');
            container.style.backgroundColor = 'white';
            container.style.width = '34px';
            container.style.height = '34px';
            container.style.cursor = 'pointer';
            container.style.display = 'flex';
            container.style.alignItems = 'center';
            container.style.justifyContent = 'center';
            container.title = "Locate Me";
            container.innerHTML = '<i data-lucide="map-pin" style="width:16px; height:16px; color:#333;"></i>';
            
            container.onclick = function(e) {
                e.stopPropagation();
                map.locate({setView: true, maxZoom: 16});
            };
            return container;
        }
    });
    map.addControl(new LocateControl());

    map.on('locationfound', (e) => {
        setMarker(e.latitude, e.longitude);
    });

    map.on('locationerror', (e) => {
        Toast.show("Location access denied or not found", "error");
    });

    map.on('click', (e) => {
      const { lat, lng } = e.latlng;
      setMarker(lat, lng);
    });

    // Handle Manual Input Changes
    const updateFromInputs = () => {
        const lat = document.getElementById('shopLat').value;
        const lng = document.getElementById('shopLng').value;
        if (lat && lng) setMarker(lat, lng, false);
    };
    document.getElementById('shopLat').addEventListener('input', updateFromInputs);
    document.getElementById('shopLng').addEventListener('input', updateFromInputs);

    // Handle map resize when modal opens
    const modal = document.getElementById('addShopModal');
    const observer = new MutationObserver(() => {
        if (modal.classList.contains('open')) {
            setTimeout(() => {
                map.invalidateSize();
                if (window.lucide) lucide.createIcons();
            }, 500);
        }
    });
    observer.observe(modal, { attributes: true, attributeFilter: ['class'] });
  }

  function resetModal() {
    const modal = document.getElementById('addShopModal');
    if(modal) {
      delete modal.dataset.editId;
      delete modal.dataset.existingUserId;
      delete modal.dataset.existingVendorPhone;
      document.getElementById('shopModalTitle').innerHTML = '<i data-lucide="store"></i> Add New Shop';
      document.getElementById('shopName').value = '';
      document.getElementById('shopOwner').value = '';
      document.getElementById('shopEmail').value = '';
      document.getElementById('shopAddress').value = '';
      document.getElementById('shopStatus').value = 'active';
      document.getElementById('shopImageFile').value = '';
      
      const checkboxes = document.querySelectorAll('.cat-checkbox');
      checkboxes.forEach(cb => cb.checked = false);

      const preview = document.getElementById('shopImagePreview');
      if (preview) {
        preview.style.display = 'none';
        const img = preview.querySelector('img');
        if (img) img.src = '';
      }

      // Map Reset
      initMap();
      if (marker) {
        map.removeLayer(marker);
        marker = null;
      }
      document.getElementById('shopLat').value = '';
      document.getElementById('shopLng').value = '';
      map.setView(defaultLoc, 13);
      
      // Reset Vendor Fields
      document.getElementById('shopVendorPhone').value = '';
      document.getElementById('shopVendorPassword').value = '';
    }
  }


  function edit(id) {
    resetModal();
    const shop = shops.find(s => s.id == id);
    if (!shop) {
        Toast.show('Shop data not found', 'error');
        return;
    }

    const modal = document.getElementById('addShopModal');
    modal.dataset.editId = shop.id;
    modal.dataset.existingUserId = shop.user_id || '';
    document.getElementById('shopModalTitle').innerHTML = '<i data-lucide="edit"></i> Edit Shop';
    
    document.getElementById('shopName').value = shop.shop_name;
    document.getElementById('shopOwner').value = shop.owner_name;
    document.getElementById('shopEmail').value = shop.email || '';
    document.getElementById('shopVendorPhone').value = shop.phone || '';
    document.getElementById('shopAddress').value = shop.address;
    document.getElementById('shopStatus').value = shop.status;
    modal.dataset.existingVendorPhone = shop.phone || '';

    if(shop.categories) {
        const catIds = shop.categories.map(c => c.id.toString());
        const checkboxes = document.querySelectorAll('.cat-checkbox');
        checkboxes.forEach(cb => {
            if(catIds.includes(cb.value)) cb.checked = true;
        });
    }

    const preview = document.getElementById('shopImagePreview');
    if (shop.shop_image && preview) {
      const img = preview.querySelector('img');
      img.src = AdminPanel.fixPath(shop.shop_image);
      preview.style.display = 'block';
    }

    // Map Coordinates
    initMap();
    if (shop.latitude && shop.longitude) {
      setMarker(parseFloat(shop.latitude), parseFloat(shop.longitude));
    }

    Modal.open('addShopModal');
  }

  async function save() {
    const modal = document.getElementById('addShopModal');
    const id = modal.dataset.editId;
    
    const nameStr = document.getElementById('shopName').value.trim();
    if (!nameStr) return Toast.show('Shop Name is required', 'error');

    const selectedCategories = Array.from(document.querySelectorAll('.cat-checkbox:checked')).map(cb => cb.value);
    
    const formData = new FormData();
    if (id) formData.append('id', id);
    formData.append('shop_name', nameStr);
    formData.append('owner_name', document.getElementById('shopOwner').value.trim());
    // Phone and Email are appended later in the vendor account section
    formData.append('address', document.getElementById('shopAddress').value.trim());
    formData.append('status', document.getElementById('shopStatus').value);
    formData.append('categories', JSON.stringify(selectedCategories));
    
    // Add Coordinates
    const lat = document.getElementById('shopLat').value;
    const lng = document.getElementById('shopLng').value;
    if (lat && lng) {
        formData.append('latitude', lat);
        formData.append('longitude', lng);
    }

    const fileInput = document.getElementById('shopImageFile');
    if (fileInput.files.length > 0) {
      formData.append('image', fileInput.files[0]);
    }

    const shopEmail = document.getElementById('shopEmail').value.trim();
    const vendorPhone = document.getElementById('shopVendorPhone').value.trim();
    const vendorPassword = document.getElementById('shopVendorPassword').value;
    const existingPhone = modal.dataset.existingVendorPhone || '';
    
    formData.append('email', shopEmail);
    formData.append('phone', vendorPhone);

    // Only attempt to create vendor if phone is new or changed
    if (vendorPhone && vendorPhone !== existingPhone) {
        formData.append('create_vendor_account', '1');
        if (vendorPassword) formData.append('vendor_password', vendorPassword);
    } else if (modal.dataset.existingUserId) {
        // If editing and no new vendor is created, keep existing user_id
        formData.append('user_id', modal.dataset.existingUserId);
        if (vendorPassword) formData.append('vendor_password', vendorPassword);
    }

    try {
      AdminPanel.showLoading?.();
      const resp = await AdminAPI.saveShop(formData);
      if (resp?.success) {
        Modal.close('addShopModal');
        Toast.show(resp.message, 'success');
        if(typeof loadShops === 'function') loadShops();
      } else {
        Toast.show(resp?.error || 'Save failed', 'error');
      }
    } catch (err) {
      Toast.show('Error: ' + err.message, 'error');
    } finally {
      AdminPanel.hideLoading?.();
    }
  }

  async function del(id) {
    if (confirm('Are you sure you want to delete this shop?')) {
      const resp = await AdminAPI.deleteShop(id);
      if (resp?.success) {
        Toast.show('Shop deleted', 'success');
        if(typeof loadShops === 'function') loadShops();
      }
    }
  }

  return { init, resetModal, edit, save, delete: del };
})();

/**
 * WITHDRAWAL MANAGEMENT
 */
const PaymentAdmin = (() => {
    let requests = [];
    let currentFilter = 'Pending';
    let searchQuery = '';

    async function init() {
        await loadRequests();
    }

    async function loadRequests() {
        const table = document.getElementById('adminWithdrawalTable');
        if (!table) return;

        try {
            const resp = await AdminAPI.getWithdrawals(currentFilter, searchQuery);
            if (resp && resp.success) {
                requests = resp.requests;
                renderTable();
                updateBadge();
            }
        } catch (err) { console.error('Failed to load withdrawals', err); }
    }

    function renderTable() {
        const table = document.getElementById('adminWithdrawalTable');
        const filtered = requests.filter(r => {
            const matchesStatus = currentFilter === 'all' || r.status === currentFilter;
            const matchesSearch = r.partner_name.toLowerCase().includes(searchQuery.toLowerCase());
            return matchesStatus && matchesSearch;
        });

        if (filtered.length === 0) {
            table.innerHTML = `<tr><td colspan="6" style="text-align:center; padding:40px; color:var(--text-muted)">No ${currentFilter} requests found.</td></tr>`;
            return;
        }

        table.innerHTML = filtered.map(r => `
            <tr>
                <td><span class="order-id-pills">#WR-${r.id}</span></td>
                <td>
                    <div style="font-weight:700">${Utils.escapeHTML(r.partner_name)}</div>
                    <div style="font-size:11px; color:var(--text-muted)">${Utils.escapeHTML(r.partner_phone)}</div>
                </td>
                <td style="font-weight:800; color:var(--primary)">₹${r.amount}</td>
                <td>${new Date(r.created_at).toLocaleDateString()}</td>
                <td><span class="status-pill sp-${r.status.toLowerCase()}">${Utils.escapeHTML(r.status)}</span></td>
                <td style="text-align:right">
                    <button class="filter-btn-export" onclick="PaymentAdmin.viewDetails(${r.id})" style="padding:6px 12px; font-size:12px">View Details</button>
                </td>
            </tr>
        `).join('');
        
        if (window.lucide) lucide.createIcons();
    }

    function viewDetails(id) {
        const r = requests.find(x => x.id == id);
        if (!r) return;

        const content = document.getElementById('bankDetailsContent');
        const buttons = document.getElementById('withdrawalActionButtons');
        const noteArea = document.getElementById('adminNote');
        
        noteArea.value = r.admin_note || '';
        
        let bank;
        try { bank = JSON.parse(r.bank_details); } catch(e) { bank = null; }

        if (bank && typeof bank === 'object') {
            content.innerHTML = `
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px">
                    <div>
                        <div style="font-size:10px; color:var(--text-muted); text-transform:uppercase; font-weight:800">Bank Name</div>
                        <div style="font-weight:700">${Utils.escapeHTML(bank.bank_name || 'N/A')}</div>
                    </div>
                    <div>
                        <div style="font-size:10px; color:var(--text-muted); text-transform:uppercase; font-weight:800">Account Holder</div>
                        <div style="font-weight:700">${Utils.escapeHTML(bank.holder_name || 'N/A')}</div>
                    </div>
                    <div>
                        <div style="font-size:10px; color:var(--text-muted); text-transform:uppercase; font-weight:800">Account Number</div>
                        <div style="font-weight:700; color:var(--primary)">${Utils.escapeHTML(bank.account_number || 'N/A')}</div>
                    </div>
                    <div>
                        <div style="font-size:10px; color:var(--text-muted); text-transform:uppercase; font-weight:800">IFSC Code</div>
                        <div style="font-weight:700">${Utils.escapeHTML(bank.ifsc_code || 'N/A')}</div>
                    </div>
                    <div style="grid-column: span 2; padding-top: 8px; border-top: 1px solid var(--border)">
                        <div style="font-size:10px; color:var(--text-muted); text-transform:uppercase; font-weight:800">UPI ID</div>
                        <div style="font-weight:700; color:#4f46e5">${Utils.escapeHTML(bank.upi_id || 'N/A')}</div>
                    </div>
                </div>
            `;
        } else {
            content.innerHTML = `<div style="color:var(--text-muted)">${r.bank_details || 'No bank details provided.'}</div>`;
        }

        if (r.status === 'Pending') {
            buttons.innerHTML = `
                <button class="filter-btn-export" onclick="PaymentAdmin.updateStatus(${r.id}, 'Approved')" style="flex:1; background:var(--primary); color:white; border:none">Approve & Payout</button>
                <button class="filter-btn-export" onclick="PaymentAdmin.updateStatus(${r.id}, 'Rejected')" style="flex:1; background:#ef4444; color:white; border:none">Reject</button>
            `;
            noteArea.disabled = false;
        } else {
            buttons.innerHTML = `<div style="width:100%; text-align:center; font-weight:700; color:var(--text-muted)">Request already ${r.status}</div>`;
            noteArea.disabled = true;
        }

        Modal.open('bankDetailsModal');
    }

    async function updateStatus(id, status) {
        if (!confirm(`Are you sure you want to mark this request as ${status}?`)) return;
        
        const note = document.getElementById('adminNote').value;
        try {
            const resp = await AdminAPI.updateWithdrawalStatus({ id, status, admin_note: note });
            if (resp && resp.success) {
                Toast.show(resp.message, 'success');
                Modal.close('bankDetailsModal');
                loadRequests();
            } else {
                Toast.show(resp.error || 'Update failed', 'error');
            }
        } catch (err) { Toast.show('Error: ' + err.message, 'error'); }
    }

    function setFilter(f, btn) {
        currentFilter = f;
        document.querySelectorAll('.filter-tabs .range-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        renderTable();
    }

    function handleSearch(q) {
        searchQuery = q;
        renderTable();
    }

    function updateBadge() {
        const pendingCount = requests.filter(r => r.status === 'Pending').length;
        const badge = document.getElementById('sidebarWithdrawalBadge');
        if (badge) {
            if (pendingCount > 0) {
                badge.textContent = pendingCount;
                badge.style.display = 'inline-flex';
            } else {
                badge.style.display = 'none';
            }
        }
    }

    return { init, setFilter, handleSearch, viewDetails, updateStatus };
})();

// ======= INIT =======
function updateAdminTimers() {
    const timers = document.querySelectorAll('.live-admin-timer');
    timers.forEach(el => {
        const startStr = el.getAttribute('data-start');
        if (!startStr) return;
        const start = new Date(startStr).getTime();
        const now = new Date().getTime();
        const diff = Math.floor((now - start) / 1000);
        if (diff < 0) return;
        const m = Math.floor(diff / 60);
        const s = diff % 60;
        el.textContent = `${m}m ${s}s`;
    });
}
setInterval(updateAdminTimers, 1000);

document.addEventListener('DOMContentLoaded', () => {
  AdminTabs.init();
  if (typeof NotificationEngine !== 'undefined') {
    NotificationEngine.init();
  }
});
