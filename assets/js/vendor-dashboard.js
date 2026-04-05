const VendorDashboard = (() => {
    let orders = [];
    let stats = {};
    let lastOrderCount = -1;

    async function init() {
        await refreshData();
        // Start polling every 5 seconds for new orders
        setInterval(refreshData, 5000);
        
        // --- Wake Lock Manager ---
        handleWakeLock();
        document.addEventListener('visibilitychange', async () => {
            if (document.visibilityState === 'visible') await handleWakeLock();
        });
    }

    let wakeLock = null;
    async function handleWakeLock() {
        if (!('wakeLock' in navigator)) return;
        try {
            if (wakeLock === null) {
                wakeLock = await navigator.wakeLock.request('screen');
                wakeLock.addEventListener('release', () => { wakeLock = null; });
            }
        } catch (err) { console.error('Wake Lock error:', err); }
    }
    // -------------------------

    async function refreshData() {
        try {
            const [statsData, ordersData, shopData] = await Promise.all([
                fetch('../api/vendor/orders/get_dashboard_stats.php').then(r => r.json()),
                fetch('../api/vendor/orders/list.php?limit=5').then(r => r.json()),
                fetch('../api/vendor/shop/get_status.php').then(r => r.json())
            ]);
            
            if (shopData.success) {
                renderShopStatus(shopData.status);
            }

            if (statsData.success) {
                stats = statsData.stats;
                renderStats();
            }

            if (ordersData.success) {
                orders = ordersData.orders;
                renderOrders();
                checkNewOrders(orders.length);
                checkRingingStatus();
            }
        } catch (err) {
            console.error('Failed to refresh vendor data:', err);
        }
    }

    let notificationTimer = null;
    let audioCtx = null;

    let activeAudio = null;

    function playNotification() {
        if (activeAudio && !activeAudio.paused) return;

        try {
            activeAudio = new Audio('../assets/audio/zomato_1.mp3?v=' + Date.now());
            activeAudio.volume = 0.8;
            
            activeAudio.play().catch(e => {
                // Fallback to "Ding-Dong" Melodic Chime
                if (!audioCtx) audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                if (audioCtx.state === 'suspended') audioCtx.resume();
                const now = audioCtx.currentTime;
                const playNote = (freq, startTime, duration, volume) => {
                    const osc = audioCtx.createOscillator();
                    const gain = audioCtx.createGain();
                    osc.type = 'sine';
                    osc.connect(gain);
                    gain.connect(audioCtx.destination);
                    osc.frequency.setValueAtTime(freq, startTime);
                    gain.gain.setValueAtTime(0, startTime);
                    gain.gain.linearRampToValueAtTime(volume, startTime + 0.05);
                    gain.gain.exponentialRampToValueAtTime(0.01, startTime + duration);
                    osc.start(startTime);
                    osc.stop(startTime + duration);
                };
                playNote(783.99, now, 0.8, 0.4); 
                playNote(659.25, now + 0.5, 1.2, 0.3); 
            });
        } catch(e) { console.error('Audio block', e); }
    }

    function checkRingingStatus() {
        const needsRinging = orders.some(o => o.status === 'Placed' || o.status === 'Pending');
        if (needsRinging) {
            if (!notificationTimer) {
                playNotification(); // Play immediately
                notificationTimer = setInterval(playNotification, 4000); // Loop every 4s
            }
        } else {
            if (notificationTimer) {
                clearInterval(notificationTimer);
                notificationTimer = null;
            }
            if (activeAudio) {
                activeAudio.pause();
                activeAudio.currentTime = 0;
                activeAudio = null;
            }
        }
    }

    function checkNewOrders(currentCount) {
        if (lastOrderCount !== -1 && currentCount > lastOrderCount) {
            // Update sidebar badge if new order comes
            const badge = document.getElementById('vendorNewOrdersBadge');
            if (badge) {
                badge.textContent = currentCount;
                badge.style.display = 'inline-block';
            }
        }
        lastOrderCount = currentCount;
    }

    function renderStats() {
        document.getElementById('statTodayOrders').textContent = stats.todayOrders;
        document.getElementById('statTodayRevenue').textContent = '₹' + stats.todayRevenue.toLocaleString();
        document.getElementById('statAvgPrep').textContent = stats.avgPrep;
    }

    function renderOrders() {
        const tbody = document.getElementById('vendorDashboardOrders');
        if (!tbody) return;

        if (orders.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" style="text-align:center; padding:40px; color:var(--text-muted)">No active orders</td></tr>';
            return;
        }

        tbody.innerHTML = orders.map(o => {
            const statusClass = `sp-${o.status.toLowerCase().replace(/ /g, '-')}`;
            
            // Action button logic based on current status
            let actionBtn = '';
            if (o.status === 'Placed' || o.status === 'Pending') {
                actionBtn = `<button class="admin-btn" style="background:#10b981" onclick="VendorDashboard.updateStatus(${o.id}, 'Confirmed')">Accept Order</button>`;
            } else if (o.status === 'Confirmed') {
                actionBtn = `<button class="admin-btn" style="background:#3b82f6" onclick="VendorDashboard.updateStatus(${o.id}, 'Preparing')">Start Cooking</button>`;
            } else if (o.status === 'Preparing') {
                actionBtn = `<button class="admin-btn" style="background:#f97316" onclick="VendorDashboard.updateStatus(${o.id}, 'Ready for Pickup')">Ready for Pickup</button>`;
            } else if (o.status === 'Ready for Pickup') {
                actionBtn = `<span style="font-size:12px; color:#666; font-weight:600"><i data-lucide="truck" style="width:14px;height:14px;vertical-align:middle"></i> Waiting...</span>`;
            } else if (o.status === 'Delivered') {
                actionBtn = `<span style="font-size:12px; color:#10b981; font-weight:600"><i data-lucide="check-circle" style="width:14px;height:14px;vertical-align:middle"></i> Completed</span>`;
            } else if (o.status === 'Cancelled') {
                actionBtn = `<span style="font-size:12px; color:#ef4444; font-weight:600">No Actions</span>`;
            }

            return `
                <tr>
                    <td><strong>${o.order_number || o.id}</strong></td>
                    <td>
                        <div style="font-weight:600">${o.customer_name}</div>
                        <div style="font-size:11px; color:#666">${o.customer_phone}</div>
                    </td>
                    <td>
                        <div style="font-weight:700">₹${o.vendor_earning}</div>
                        <div style="font-size:10px; color:#999">Net Profit</div>
                    </td>
                    <td><span class="status-pill ${statusClass}">${o.status}</span></td>
                    <td>${actionBtn}</td>
                </tr>
            `;
        }).join('');
        
        if (window.lucide) lucide.createIcons();
    }

    async function updateStatus(orderId, newStatus) {
        try {
            const resp = await fetch('../api/vendor/orders/update_status.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ orderId, status: newStatus })
            }).then(r => r.json());

            if (resp.success) {
                if (window.Toast) Toast.show(resp.message || 'Status updated!', 'success');
                refreshData();
            } else {
                if (window.Toast) Toast.show(resp.error || 'Failed to update status', 'error');
            }
        } catch (err) {
            if (window.Toast) Toast.show('Error updating status', 'error');
        }
    }

    async function toggleShopStatus() {
        const btn = document.getElementById('shopStatusToggle');
        if (!btn) return;

        const originalHtml = btn.innerHTML;
        btn.style.opacity = '0.7';
        btn.style.pointerEvents = 'none';
        btn.innerHTML = '<i data-lucide="loader" class="spinner-icon" style="width:18px; height:18px"></i> UPDATING...';
        if (window.lucide) lucide.createIcons();

        try {
            const resp = await fetch('../api/vendor/shop/toggle_status.php', { method: 'POST' }).then(r => r.json());
            if (resp.success) {
                if (window.Toast) Toast.show(resp.message, 'success');
                renderShopStatus(resp.status);
            }
        } catch (err) {
            btn.innerHTML = originalHtml;
            if (window.Toast) Toast.show('Connection error', 'error');
        } finally {
            btn.style.opacity = '1';
            btn.style.pointerEvents = 'auto';
        }
    }

    function renderShopStatus(status) {
        const btn = document.getElementById('shopStatusToggle');
        if (!btn) return;

        if (status === 'active') {
            btn.style.background = 'var(--primary-pale)';
            btn.style.color = 'var(--primary)';
            btn.innerHTML = '<i data-lucide="store" style="width:18px; height:18px"></i> SHOP OPEN';
        } else {
            btn.style.background = '#fee2e2';
            btn.style.color = '#ef4444';
            btn.innerHTML = '<i data-lucide="store" style="width:18px; height:18px"></i> SHOP CLOSED';
        }
        if (window.lucide) lucide.createIcons();
    }

    return { init, updateStatus, toggleShopStatus };
})();

// Export to global scope for HTML onclick handlers
window.VendorDashboard = VendorDashboard;

document.addEventListener('DOMContentLoaded', VendorDashboard.init);
