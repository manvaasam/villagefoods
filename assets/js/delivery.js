const DeliveryConsole = (() => {
    let orders = [];
    let rapidOrders = [];
    let availableRapid = [];
    let currentFilter = 'all';
    let isOnline = true;
    let audioCtx = null;

    async function init() {
        // Fetch current online status from DB
        await fetchOnlineStatus();

        // Initial data fetch
        await fetchAll();
        
        if (window.location.pathname.includes('history.php')) {
            fetchHistory();
        }

        if (window.location.pathname.includes('wallet.php')) {
            fetchWalletActivity();
            fetchProfile();
        }

        // Dynamic polling: faster when online, slower when offline
        startPolling();

        // Start Timer Refresh
        setInterval(updateTimers, 1000);
    }

    let pollInterval = null;
    function startPolling() {
        if (pollInterval) clearInterval(pollInterval);
        // Poll every 5s if online, every 30s if offline
        const timer = isOnline ? 5000 : 30000;
        pollInterval = setInterval(fetchAll, timer);
    }

    async function fetchOnlineStatus() {
        try {
            const res = await fetch(`../api/delivery/toggle_online.php?_t=${Date.now()}`);
            const data = await res.json();
            if (data.success) {
                isOnline = !!data.is_online;
                updateOnlineUI();
                handleWakeLock();
            }
        } catch (e) { console.error("Failed to fetch online status"); }
    }

    // --- Wake Lock Manager ---
    let wakeLock = null;

    async function requestWakeLock() {
        if (!('wakeLock' in navigator)) return;
        try {
            if (wakeLock === null) {
                wakeLock = await navigator.wakeLock.request('screen');
                wakeLock.addEventListener('release', () => {
                    wakeLock = null;
                });
            }
        } catch (err) { console.error('Wake Lock error:', err); }
    }

    function releaseWakeLock() {
        if (wakeLock !== null) {
            wakeLock.release().catch(console.error);
            wakeLock = null;
        }
    }

    function handleWakeLock() {
        if (isOnline) {
            requestWakeLock();
        } else {
            releaseWakeLock();
        }
    }

    document.addEventListener('visibilitychange', async () => {
        if (document.visibilityState === 'visible' && isOnline) {
            await requestWakeLock();
        }
    });
    // -------------------------

    function updateOnlineUI() {
        const toggle = document.querySelector('.online-toggle');
        if (!toggle) return;
        const label = toggle.querySelector('.online-label');
        if (isOnline) {
            toggle.classList.remove('offline');
            if (label) label.textContent = 'Online';
        } else {
            toggle.classList.add('offline');
            if (label) label.textContent = 'Offline';
        }
    }

    async function toggleOnline() {
        isOnline = !isOnline;
        updateOnlineUI();
        startPolling(); // Adjust polling speed
        handleWakeLock(); // Adjust Screen Awake Status
        
        try {
            const res = await fetch('../api/delivery/toggle_online.php', {
                method: 'POST',
                body: JSON.stringify({ is_online: isOnline ? 1 : 0 })
            });
            const data = await res.json();
            if (data.success) {
                Toast.show(isOnline ? "You're online. Ready for orders!" : "You're offline. New orders won't be assigned.", isOnline ? "success" : "warning");
            }
        } catch (e) { 
            Toast.show("Failed to update status", "error");
            isOnline = !isOnline; // Revert
            updateOnlineUI();
        }
    }

    async function fetchAll() {
        const listContainer = document.getElementById('deliveryOrdersList');
        // Only show loader if list is currently empty
        if (listContainer && listContainer.children.length === 0) {
            listContainer.innerHTML = `<div style="text-align:center; padding:60px 20px;"><i data-lucide="loader-2" class="animate-spin" style="width:32px; height:32px; color:var(--primary); margin:0 auto 12px; display:block"></i><p style="font-size:13px; color:var(--text-dim)">Checking for orders...</p></div>`;
            if (window.lucide) lucide.createIcons();
        }

        const prevRapidCount = availableRapid.length;
        const prevOrderCount = orders.length;

        await Promise.all([
            fetchOrders(),
            fetchAssignedRapid(),
            fetchAvailableRapid()
        ]);

        if (window.location.pathname.includes('wallet.php')) {
            fetchWalletActivity();
        }

        // State-based Audio Alert: Ring continuously until picked up or accepted
        const needsRinging = availableRapid.length > 0 || 
                             rapidOrders.some(o => ['assigned'].includes((o.status || '').toLowerCase())) ||
                             orders.some(o => ['Placed', 'Confirmed', 'Preparing', 'Ready for Pickup'].includes(o.status));

        if (needsRinging) {
            startPersistentNotification();
        } else {
            stopNotification();
        }

        render();
        updateStats(); // Keep stats in sync with list
    }

    let notificationTimer = null;
    function startPersistentNotification() {
        if (notificationTimer) return; // Already alarming
        
        // Play immediately
        playNotification();
        
        // Loop every 4 seconds indefinitely
        notificationTimer = setInterval(() => {
            playNotification();
        }, 4000);
    }

    function stopNotification() {
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

    let activeAudio = null;

    function playNotification() {
        if (activeAudio && !activeAudio.paused) return; // Already playing

        try {
            // 1. Try playing custom audio file if it exists
            activeAudio = new Audio('../assets/audio/zomato_1.mp3?v=' + Date.now());
            activeAudio.volume = 0.8;
            
            activeAudio.play().catch(() => {
                // 2. Fallback to a much more pleasant "Ding-Dong" Melodic Chime
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

                // "Ding-Dong" (G5, E5)
                playNote(783.99, now, 0.8, 0.4); // G5 
                playNote(659.25, now + 0.5, 1.2, 0.3); // E5
            });
            
        } catch (e) { console.error("Audio block", e); }
    }

    async function fetchOrders() {
        try {
            const res = await fetch(`../api/delivery/get_assigned_orders.php?status=${currentFilter}&_t=${Date.now()}`);
            orders = await res.json();
        } catch (err) { console.error('Orders fetch failed', err); }
    }

    async function fetchAssignedRapid() {
        try {
            const res = await fetch(`../api/delivery/get_assigned_rapid.php?status=${currentFilter}&_t=${Date.now()}`);
            rapidOrders = await res.json();
        } catch (err) { console.error('Assigned Rapid fetch failed', err); }
    }

    async function fetchAvailableRapid() {
        try {
            const res = await fetch(`../api/delivery/get_available_rapid.php?_t=${Date.now()}`);
            availableRapid = await res.json();
        } catch (err) { console.error('Available Rapid fetch failed', err); }
    }

    function setFilter(filter, el) {
        currentFilter = filter;
        
        // Update UI
        const tabs = document.querySelectorAll('.db-filter-tab');
        tabs.forEach(tab => tab.classList.remove('active'));
        if (el) el.classList.add('active');

        // Fetch new data
        fetchAll();
    }

    function render() {
        const container = document.getElementById('deliveryOrdersList');
        const countEl = document.getElementById('activeCount');
        if (!container) return;

        let html = '';
        let totalActive = 0;

        // 1. Show Available Rapid Pickups (New Requests)
        if (currentFilter === 'all' || currentFilter === 'active') {
            if (availableRapid.length > 0) {
                html += availableRapid.map(o => renderAvailableRapidCard(o)).join('');
                totalActive += availableRapid.length;
            }
        }

        // 2. Show Assigned Regular Orders
        if (orders.length > 0) {
            html += orders.map(o => renderOrderCard(o)).join('');
            totalActive += orders.length;
        }

        // 3. Show Assigned Rapid Orders
        if (rapidOrders.length > 0) {
            html += rapidOrders.map(o => renderRapidCard(o)).join('');
            totalActive += rapidOrders.length;
        }

        if (countEl) countEl.textContent = `${totalActive} Assigned`;

        if (html === '') {
            html = `
                <div style="text-align:center; padding:60px 20px; color:var(--text-dim); background:var(--white); border-radius:28px; border:1px dashed var(--border);">
                    <i data-lucide="package-open" style="width:40px; height:40px; margin-bottom:16px; opacity:0.2; color:var(--text-dim)"></i>
                    <h3 style="font-size:15px; font-weight:800; color:var(--text)">No Orders Now</h3>
                    <p style="font-size:12px; margin-top:4px">New orders will appear here automatically.</p>
                </div>
            `;
        }

        if (container.dataset.lastRenderedHTML !== html) {
            container.innerHTML = html;
            container.dataset.lastRenderedHTML = html;
            if (window.lucide) lucide.createIcons();
        }
    }

    function renderAvailableRapidCard(o) {
        return `
            <div class="db-order-card rapid-request" style="border-left: 4px solid #f97316;">
                <div class="db-order-header">
                    <div class="db-order-id">RAPID #${o.id}</div>
                    <div class="db-order-badge badge-rapid">
                        <span class="online-dot" style="background:#f97316; box-shadow:0 0 8px #f97316;"></span>
                        NEW PICKUP
                    </div>
                </div>
                <div class="db-order-items">
                    <i data-lucide="package" style="width:14px;height:14px;display:inline;vertical-align:middle;margin-right:6px;color:#f97316"></i> 
                    ${o.item_description || 'General Package'}
                </div>
                <div class="db-order-route">
                   <div style="flex:1">
                        <div class="route-stop stop-pickup">
                            <div class="db-route-label">Pickup From</div>
                            <div class="db-route-value">${o.pickup_address}</div>
                        </div>
                        <div class="route-line"></div>
                        <div class="route-stop stop-drop">
                            <div class="db-route-label">Drop To</div>
                            <div class="db-route-value">${o.drop_address}</div>
                        </div>
                   </div>
                </div>
                <div class="db-order-actions">
                    <button class="db-action-btn db-action-primary" style="grid-column: span 3; background:#f97316" onclick="DeliveryConsole.acceptRapid(${o.id})">
                        <i data-lucide="check-circle" style="width:16px;height:16px;margin-right:8px"></i> 
                        Accept & Earn ₹${o.price}
                    </button>
                </div>
            </div>
        `;
    }

    function renderRapidCard(o) {
        const dbStatus = (o.status || '').toLowerCase();
        const isDone = ['completed', 'rejected', 'cancelled'].includes(dbStatus);
        
        let actionHtml = '';
        if (dbStatus === 'assigned') {
            actionHtml = `
                <button class="db-action-btn db-action-primary" style="grid-column: span 12; background:#f97316" onclick="DeliveryConsole.acceptRapid(${o.id})">
                    <i data-lucide="check-circle" style="width:16px;height:16px;margin-right:6px"></i> 
                    Accept Order
                </button>
            `;
        } else if (!isDone) {
            const nextAction = getRapidNextAction(dbStatus);
            if (nextAction) {
                let checkboxHtml = '';
                if (nextAction.status === 'completed') {
                    checkboxHtml = `
                        <div style="background:#fff7ed; padding:12px; border-radius:12px; border:1px dashed #f97316; margin-bottom: 12px;">
                            <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                                <input type="checkbox" id="rapid_cash_collected_${o.id}" style="width:18px; height:18px; accent-color:#f97316;">
                                <span style="font-size:12px; font-weight:800; color:#9a3412;">I have collected ₹${o.price || 0} cash</span>
                            </label>
                        </div>
                    `;
                }

                actionHtml = `
                    ${checkboxHtml}
                    <button class="db-action-btn db-action-primary" style="grid-column: span 12; width: 100%" onclick="DeliveryConsole.updateRapidStatus(${o.id}, '${nextAction.status}')">
                        <i data-lucide="${nextAction.icon}" style="width:16px;height:16px;margin-right:6px"></i> 
                        ${nextAction.label}
                    </button>
                `;
            }
        }

        return `
            <div class="db-order-card rapid-active" style="border-left: 4px solid var(--primary);">
                <div class="db-order-header">
                    <div class="db-order-id">RAPID #${o.id}</div>
                    <div class="db-order-badge badge-active" style="text-transform: capitalize;">${o.status}</div>
                </div>
                
                <div class="db-order-items" style="margin: 10px 0 6px 0; font-size: 13px; font-weight: 700; color: var(--text-main); display: flex; align-items: center; gap: 8px;">
                    <i data-lucide="package" style="width:16px;height:16px;color:#f97316"></i> 
                    ${o.item_description || 'General Package'}
                </div>
                
                <div style="background:#fff7ed; color:#ea580c; border: 1px solid #ffedd5; padding: 10px 12px; border-radius: 10px; font-size: 14px; font-weight: 900; margin-bottom: 12px; display: flex; align-items: center; justify-content: space-between;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <i data-lucide="banknote" style="width:18px;height:18px"></i>
                        Collect Cash
                    </div>
                    <span>₹${o.price || 0}</span>
                </div>

                <!-- Delivery Timer -->
                <div class="timer-display" style="margin: 10px 0; background: var(--bg-dark); padding: 8px 12px; border-radius: 12px; display: flex; align-items: center; gap: 8px;">
                    <i data-lucide="timer" style="width:14px; height:14px; color:var(--primary)"></i>
                    <span class="live-timer" data-timer-start="${o.picked_up_at || ''}" data-timer-end="${o.delivered_at || ''}">
                        ${getTimerLabel(o.picked_up_at, o.delivered_at)}
                    </span>
                </div>
                <div class="db-order-route">
                    <div style="flex:1">
                        <div class="route-stop stop-pickup">
                            <div class="db-route-label">Pickup From</div>
                            <div class="db-route-value" style="font-weight:800">${o.customer_name}</div>
                            <div style="font-size:12px; color:var(--text-muted); margin-bottom:8px;">${o.pickup_address}</div>
                            <div style="display:flex; gap:8px; margin-top:4px;">
                                <button class="mini-action-btn" onclick="DeliveryConsole.openMap('${o.pickup_address}', ${o.pickup_lat}, ${o.pickup_lng})">
                                    <i data-lucide="map-pin"></i> Map
                                </button>
                                ${!isDone ? `
                                <a href="tel:${o.customer_phone || ''}" class="mini-action-btn" style="text-decoration:none">
                                    <i data-lucide="phone"></i> Call
                                </a>
                                ` : ''}
                            </div>
                        </div>
                        <div class="route-line" style="margin: 12px 0;"></div>
                        <div class="route-stop stop-drop">
                            <div class="db-route-label">Drop To / Deliver To</div>
                            <div style="font-size:12px; color:var(--text-muted); margin-bottom:8px;">${o.drop_address}</div>
                            <div style="display:flex; gap:8px; margin-top:4px;">
                                <button class="mini-action-btn" onclick="DeliveryConsole.openMap('${o.drop_address}', ${o.drop_lat}, ${o.drop_lng})">
                                    <i data-lucide="map-pin"></i> Map
                                </button>
                                ${!isDone ? `
                                <a href="tel:${o.customer_phone || ''}" class="mini-action-btn" style="text-decoration:none">
                                    <i data-lucide="phone"></i> Call
                                </a>
                                ` : ''}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="db-order-actions" style="margin-top:16px; border-top:1px dashed var(--border); padding-top:12px;">
                    ${actionHtml}
                </div>
            </div>
        `;
    }

    function renderOrderCard(o) {
        const isDone = ['Delivered', 'Completed', 'Cancelled'].includes(o.status);
        const nextAction = getNextAction(o.status);
        const isCOD = o.payment_type === 'cod';
        const isPaid = o.payment_status === 'Paid';
        
        const paymentBadge = isCOD 
            ? `<div class="db-order-badge" style="background:#fff7ed; color:#f97316; border:1px solid #ffedd5;">
                <i data-lucide="banknote" style="width:12px;height:12px;margin-right:4px"></i> 
                COD – Collect ₹${o.grand_total}
               </div>`
            : `<div class="db-order-badge" style="background:#f0fdf4; color:#16a34a; border:1px solid #dcfce7;">
                <i data-lucide="shield-check" style="width:12px;height:12px;margin-right:4px"></i> 
                Paid Online
               </div>`;

        return `
            <div class="db-order-card order-${o.status.toLowerCase().replace(/\s/g, '-')}">
                <div class="db-order-header">
                    <div class="db-order-id">#${o.order_number || o.id}</div>
                    <div style="display:flex; flex-direction:column; align-items:flex-end; gap:6px">
                        <div class="db-order-badge badge-active">
                            <i data-lucide="${getStatusIcon(o.status)}" style="width:12px;height:12px;margin-right:4px"></i> 
                            ${o.status}
                        </div>
                        ${paymentBadge}
                    </div>
                </div>
                <!-- Delivery Timer -->
                <div class="timer-display" style="margin: 10px 0; background: var(--bg-dark); padding: 8px 12px; border-radius: 12px; display: flex; align-items: center; gap: 8px;">
                    <i data-lucide="timer" style="width:14px; height:14px; color:var(--primary)"></i>
                    <span class="live-timer" data-timer-start="${o.picked_up_at || ''}" data-timer-end="${o.delivered_at || ''}">
                        ${getTimerLabel(o.picked_up_at, o.delivered_at)}
                    </span>
                </div>
                <div class="db-order-items-detailed" style="margin: 16px 0; background: #fafafa; border-radius: 12px; border: 1px solid #eee; overflow: hidden;">
                    <div style="padding: 10px 12px; background: #f0f0f0; font-size: 11px; font-weight: 800; color: #666; display: grid; grid-template-columns: 2fr 1fr 1fr 1fr; border-bottom: 1px solid #eee;">
                        <span>PRODUCT</span>
                        <span style="text-align:center">QTY</span>
                        <span style="text-align:right">PRICE</span>
                        <span style="text-align:right">TOTAL</span>
                    </div>
                    ${o.items.map(i => `
                        <div style="padding: 10px 12px; font-size: 12px; display: grid; grid-template-columns: 2fr 1fr 1fr 1fr; border-bottom: 1px solid #f0f0f0; align-items: center;">
                            <span style="font-weight:700; color:var(--text-dark)">${i.product_name}</span>
                            <span style="text-align:center; color:var(--text-dim)">×${i.quantity}</span>
                            <span style="text-align:right; color:var(--text-dim)">₹${i.price}</span>
                            <span style="text-align:right; font-weight:800; color:var(--primary)">₹${i.subtotal}</span>
                        </div>
                    `).join('')}
                    <div style="padding: 10px 12px; background: #fff; display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 11px; font-weight: 800; color: #666;">GRAND TOTAL</span>
                        <span style="font-size: 14px; font-weight: 900; color: var(--primary);">₹${o.grand_total}</span>
                    </div>
                </div>
                <div class="db-order-route">
                    <div style="flex:1">
                        <div class="route-stop stop-pickup">
                            <div class="db-route-label">Pickup From</div>
                            <div class="db-route-value" style="color:var(--primary); font-weight:800">${o.shop_name || 'Village Foods Hub'}</div>
                            <div style="font-size:11px; color:var(--text-dim); margin-top:2px;">${o.shop_address || ''}</div>
                            <div style="display:flex; gap:8px; margin-top:8px;">
                                <button class="mini-action-btn" onclick="DeliveryConsole.openMap('${o.shop_address || 'Village Foods Hub'}', ${o.shop_lat}, ${o.shop_lng})">
                                    <i data-lucide="map-pin"></i> Map
                                </button>
                                ${!isDone ? `
                                <a href="tel:${o.shop_phone || ''}" class="mini-action-btn" style="text-decoration:none">
                                    <i data-lucide="phone"></i> Call Shop
                                </a>
                                ` : ''}
                            </div>
                        </div>
                        <div class="route-line" style="margin: 12px 0;"></div>
                        <div class="route-stop stop-drop">
                            <div class="db-route-label">Deliver To</div>
                            <div class="db-route-value" style="font-weight:800">${o.customer_name}</div>
                            <div style="font-size:12px; color:var(--text-muted); margin-bottom:8px;">${o.address}</div>
                            <div style="display:flex; gap:8px; margin-top:4px;">
                                <button class="mini-action-btn" onclick="DeliveryConsole.openMap('${o.address}', ${o.latitude}, ${o.longitude})">
                                    <i data-lucide="map-pin"></i> Map
                                </button>
                                ${!isDone ? `
                                <a href="tel:${o.customer_phone || ''}" class="mini-action-btn" style="text-decoration:none">
                                    <i data-lucide="phone"></i> Call Customer
                                </a>
                                ` : ''}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="db-order-actions" style="margin-top:16px; border-top:1px dashed var(--border); padding-top:12px; display:flex; flex-direction:column; gap:10px;">
                    ${!isDone && nextAction ? `
                        ${(nextAction.status === 'Delivered' && isCOD && !isPaid) ? `
                            <div style="background:#fff7ed; padding:12px; border-radius:12px; border:1px dashed #f97316;">
                                <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                                    <input type="checkbox" id="cash_collected_${o.id}" style="width:18px; height:18px; accent-color:#f97316;">
                                    <span style="font-size:12px; font-weight:800; color:#9a3412;">I have collected ₹${o.grand_total} cash</span>
                                </label>
                            </div>
                        ` : ''}
                        <button class="db-action-btn db-action-primary" style="width:100%" onclick="DeliveryConsole.updateStatus(${o.id}, '${nextAction.status}', this, ${isCOD ? 'true' : 'false'})">
                            <i data-lucide="${nextAction.icon}" style="width:16px;height:16px;margin-right:6px"></i> 
                            ${nextAction.label}
                        </button>
                    ` : ''}
                </div>
            </div>
        `;
    }

    function getStatusIcon(status) {
        switch(status) {
            case 'Placed': return 'shopping-bag';
            case 'Confirmed': return 'check-circle';
            case 'Preparing': return 'timer';
            case 'Picked Up': return 'package';
            case 'On the Way': return 'bike';
            case 'Delivered': return 'award';
            case 'Cancelled': return 'x-circle';
            default: return 'package';
        }
    }

    function getNextAction(status) {
        switch(status) {
            case 'Ready for Pickup':
            case 'Preparing':
            case 'Confirmed': return { label: 'Picked Up', status: 'Picked Up', icon: 'package' };
            case 'Picked Up': return { label: 'On Way', status: 'On the Way', icon: 'bike' };
            case 'On the Way': return { label: 'Delivered', status: 'Delivered', icon: 'award' };
            default: return null;
        }
    }

    function getRapidNextAction(status) {
        if (!status) return null;
        switch(status.toLowerCase()) {
            case 'accepted': return { label: 'Picked Up', status: 'picked', icon: 'package' };
            case 'picked': return { label: 'Mark Completed', status: 'completed', icon: 'check-circle' };
            default: return null;
        }
    }

    async function acceptRapid(id) {
        try {
            const res = await fetch('../api/delivery/accept_rapid_order.php', {
                method: 'POST',
                body: JSON.stringify({ id: id })
            });
            const result = await res.json();
            if (result.success) {
                Toast.show(result.message, 'success');
                stopNotification();
                fetchAll();
            } else { Toast.show(result.error, 'error'); }
        } catch (err) { Toast.show('Accept failed', 'error'); }
    }

    async function updateStatus(orderId, newStatus, btn, isCOD = false) {
        if (newStatus === 'Delivered' && isCOD) {
            const check = document.getElementById(`cash_collected_${orderId}`);
            if (check && !check.checked) {
                Toast.show("Please confirm cash collection first", "warning");
                return;
            }
        }

        if (btn) {
            btn.disabled = true;
            btn.innerHTML = `<i data-lucide="loader-2" class="animate-spin" style="width:14px;height:14px;margin-right:4px"></i>...`;
            if (window.lucide) lucide.createIcons();
        }
        try {
            const res = await fetch('../api/delivery/update_order_status.php', {
                method: 'POST',
                body: JSON.stringify({ 
                    order_id: orderId, 
                    status: newStatus,
                    cash_collected: (newStatus === 'Delivered' && isCOD) ? 1 : 0
                })
            });
            const result = await res.json();
            if (result.success) {
                Toast.show(result.message, 'success');
                fetchAll();
            } else { 
                Toast.show(result.error, 'error'); 
                if (btn) btn.disabled = false;
            }
        } catch (err) { 
            Toast.show('Status update failed', 'error'); 
            if (btn) btn.disabled = false;
        }
    }

    async function updateRapidStatus(rapidId, newStatus) {
        if (newStatus === 'completed') {
            const check = document.getElementById(`rapid_cash_collected_${rapidId}`);
            if (check && !check.checked) {
                if (typeof Toast !== 'undefined') Toast.show("Please confirm cash collection first", "warning");
                else alert("Please confirm cash collection first");
                return;
            }
        }

        try {
            const res = await fetch('../api/delivery/update_rapid_status.php', {
                method: 'POST',
                body: JSON.stringify({ id: rapidId, status: newStatus })
            });
            const result = await res.json();
            if (result.success) {
                Toast.show(result.message, 'success');
                fetchAll();
            } else { Toast.show(result.error, 'error'); }
        } catch (err) { Toast.show('Status update failed', 'error'); }
    }

    async function fetchHistory() {
        const container = document.getElementById('historyLogs');
        if (!container) return;

        try {
            const res = await fetch(`../api/delivery/get_delivery_history.php?_t=${Date.now()}`);
            const history = await res.json();
            
            if (history.error) throw new Error(history.error);

            if (history.length === 0) {
                container.innerHTML = `
                    <div style="text-align:center; padding:60px 20px; color:var(--text-dim); background: var(--glass); border-radius: 24px; border: 1px solid var(--border);">
                         <i data-lucide="calendar-days" style="width:48px; height:48px; opacity:0.1; margin-bottom:16px"></i>
                         <div style="font-size:15px; font-weight: 700; margin-bottom: 4px;">No Recent Records</div>
                         <div style="font-size:13px; opacity: 0.8;">Your delivery milestones will be tracked here.</div>
                    </div>
                `;
                return;
            }

            container.innerHTML = history.map(item => `
                <div class="db-order-card" style="display:flex; align-items:center; gap:20px; padding: 20px; margin-bottom: 16px; border-radius: 24px;">
                    <div style="width:48px; height:48px; border-radius:14px; background:rgba(16, 185, 129, 0.1); display:flex; align-items:center; justify-content:center; color:var(--primary); flex-shrink:0">
                        <i data-lucide="${item.type === 'rapid' ? 'zap' : 'package'}"></i>
                    </div>
                    <div style="flex:1">
                        <div style="font-weight:800; font-size:15px; color: var(--text-main);">${item.display_id}</div>
                        <div style="font-size:12px; color:var(--text-dim)">
                            ${new Date(item.created_at).toLocaleDateString()} · ${item.status}
                            ${item.picked_up_at && item.delivered_at ? ` · <span style="color:var(--primary)">${formatDuration(new Date(item.delivered_at) - new Date(item.picked_up_at))}</span>` : ''}
                        </div>
                    </div>
                    <div style="text-align:right">
                        <div style="font-weight:800; font-size:16px; color: var(--primary);">₹${item.earning}</div>
                        <div style="font-size:11px; color:var(--text-dim)">Earnings</div>
                    </div>
                </div>
            `).join('');

            if (window.lucide) lucide.createIcons();
        } catch (err) { console.error('History fetch failed', err); }
    }

    async function fetchWalletActivity() {
        const container = document.getElementById('transactionLogs');
        const balanceEl = document.getElementById('walletBalance');
        if (!container) return;

        try {
            const res = await fetch(`../api/delivery/get_wallet_data.php?_t=${Date.now()}`);
            const resp = await res.json();
            
            if (!resp.success) throw new Error(resp.error);
            const data = resp.data;

            if (balanceEl) balanceEl.textContent = `₹${data.available_balance.toFixed(2)}`;

            if (data.logs.length === 0) {
                container.innerHTML = `
                    <div style="text-align:center; padding:40px; color:var(--text-dim); background: var(--glass); border-radius: 20px; border: 1px solid var(--border);">
                        <i data-lucide="info" style="width:32px; height:32px; opacity:0.2; margin-bottom:12px"></i>
                        <div style="font-size:14px; font-weight: 600;">No withdrawal activity yet</div>
                    </div>
                `;
                return;
            }

            container.innerHTML = data.logs.map(item => `
                <div class="db-order-card" style="display:flex; align-items:center; gap:20px; padding: 16px; margin-bottom: 12px; border-radius: 20px;">
                    <div style="width:40px; height:40px; border-radius:12px; background:rgba(16, 185, 129, 0.1); display:flex; align-items:center; justify-content:center; color:var(--primary); flex-shrink:0">
                        <i data-lucide="arrow-up-right"></i>
                    </div>
                    <div style="flex:1">
                        <div style="font-weight:800; font-size:14px; color: var(--text-main);">Withdrawal Request</div>
                        <div style="font-size:11px; color:var(--text-dim)">
                            ${new Date(item.created_at).toLocaleDateString()} · <span style="text-transform: capitalize;">${item.status}</span>
                        </div>
                    </div>
                    <div style="text-align:right">
                        <div style="font-weight:800; font-size:15px; color: ${item.status === 'Rejected' ? '#ef4444' : 'var(--text-main)'};">₹${item.amount}</div>
                        <div style="font-size:10px; color:var(--text-dim)">Payout</div>
                    </div>
                </div>
            `).join('');

            if (window.lucide) lucide.createIcons();
        } catch (err) { console.error('Wallet activity fetch failed', err); }
    }

    async function requestWithdrawal() {
        console.log("Withdrawal button clicked!");
        const balanceEl = document.getElementById('walletBalance');
        if (!balanceEl) return;
        
        const currentBalance = parseFloat(balanceEl.textContent.replace('₹', '')) || 0;
        
        if (currentBalance <= 0) {
            if (typeof Toast !== 'undefined') Toast.show('No balance available for withdrawal', 'error');
            else alert('No balance available for withdrawal');
            return;
        }

        // Show Modal instead of prompt
        const modal = document.getElementById('withdrawalModal');
        const input = document.getElementById('withdrawalAmountInput');
        const display = document.getElementById('maxAvailableDisplay');
        
        if (modal && input && display) {
            input.value = currentBalance.toFixed(2);
            input.max = currentBalance;
            display.textContent = `₹${currentBalance.toFixed(2)}`;
            modal.classList.add('open');
            if (window.lucide) lucide.createIcons();
        }
    }

    function closeWithdrawalModal() {
        const modal = document.getElementById('withdrawalModal');
        if (modal) modal.classList.remove('open');
    }

    async function submitWithdrawal() {
        const input = document.getElementById('withdrawalAmountInput');
        const balanceEl = document.getElementById('walletBalance');
        if (!input || !balanceEl) return;

        const currentBalance = parseFloat(balanceEl.textContent.replace('₹', '')) || 0;
        const withdrawalAmt = parseFloat(input.value);

        if (isNaN(withdrawalAmt) || withdrawalAmt <= 0 || withdrawalAmt > currentBalance) {
            if (typeof Toast !== 'undefined') Toast.show('Invalid amount entered', 'error');
            return;
        }

        try {
            const res = await fetch('../api/delivery/request_withdrawal.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ amount: withdrawalAmt })
            });
            const data = await res.json();
            
            if (data.success) {
                if (typeof Toast !== 'undefined') Toast.show(data.message, 'success');
                closeWithdrawalModal();
                fetchWalletActivity();
            } else {
                if (typeof Toast !== 'undefined') Toast.show(data.error, 'error');
            }
        } catch (e) {
            console.error('Withdrawal request failed', e);
            if (typeof Toast !== 'undefined') Toast.show('Request failed', 'error');
        }
    }

    async function fetchProfile() {
        const bankMeta = document.getElementById('bankMeta');
        if (!bankMeta) return;

        try {
            const res = await fetch(`../api/delivery/get_profile.php?_t=${Date.now()}`);
            const data = await res.json();
            if (data.success && data.bank) {
                const b = data.bank;
                bankMeta.textContent = `${b.bank_name} ·· ${b.account_number.slice(-4)}`;
            } else {
                bankMeta.textContent = 'No bank details linked';
            }
        } catch (e) { console.error("Failed to fetch profile for wallet", e); }
    }

    async function updateStats() {
        try {
            const res = await fetch(`../api/delivery/get_dashboard_stats.php?_t=${Date.now()}`);
            const data = await res.json();
            if (data.error) return;

            const pendingEl = document.getElementById('pendingCount');
            const earningsEl = document.getElementById('totalEarnings');
            const summaryEl = document.getElementById('deliverySummary');
            const historyStatEl = document.getElementById('historyStatCount');
            if (pendingEl) pendingEl.textContent = data.pending_count;
            if (earningsEl) earningsEl.textContent = `₹${data.today_earnings}`;
            // walletBalanceEl update removed - handled by fetchWalletActivity for precision
            if (summaryEl) summaryEl.textContent = `${data.today_delivered_count} deliveries today · ₹${data.today_avg} avg`;
            if (historyStatEl) historyStatEl.textContent = data.delivered_count;

            // Verification Alert
            const alertEl = document.getElementById('verificationAlert');
            if (alertEl) {
                if (data.verification_status !== 'Verified') {
                    const isRejected = data.verification_status === 'Rejected';
                    alertEl.style.display = 'block';
                    alertEl.innerHTML = `
                        <div style="background:var(--white); border:1px solid var(--border); padding:20px; border-radius:24px; display:flex; gap:16px; align-items:flex-start; box-shadow:var(--shadow);">
                            <div style="width:48px; height:48px; border-radius:14px; background:${isRejected ? '#fef2f2' : '#fffbeb'}; display:flex; align-items:center; justify-content:center; flex-shrink:0">
                                <i data-lucide="${isRejected ? 'x-circle' : 'alert-circle'}" style="color:${isRejected ? '#ef4444' : '#f59e0b'}"></i>
                            </div>
                            <div>
                                <div style="font-weight:800; color:var(--text); font-size:15px; margin-bottom:4px">
                                    ${isRejected ? 'Verification Rejected' : 'Profile Under Review'}
                                </div>
                                <div style="font-size:12px; color:var(--text-muted); line-height:1.6">
                                    ${isRejected 
                                        ? 'Please update your documents in profile to get verified.' 
                                        : 'Our team is verifying your profile. You will see orders once approved.'}
                                </div>
                                <button onclick="window.location.href='profile.php'" style="margin-top:12px; background:var(--primary); color:white; border:none; padding:8px 20px; border-radius:12px; font-size:11px; font-weight:800; cursor:pointer;">
                                    View Profile
                                </button>
                            </div>
                        </div>
                    `;
                } else {
                    alertEl.style.display = 'none';
                }
                if (window.lucide) lucide.createIcons();
            }

        } catch (err) { console.error('Stats fetch failed', err); }
    }

    function formatDuration(ms) {
        const totalSeconds = Math.floor(ms / 1000);
        const minutes = Math.floor(totalSeconds / 60);
        const seconds = totalSeconds % 60;
        return `${minutes}m ${seconds}s`;
    }

    function getTimerLabel(start, end) {
        if (!start) return '<span style="color:var(--text-dim); font-size:11px">Awaiting Pickup</span>';
        if (end) {
            const duration = new Date(end) - new Date(start);
            return `<span style="color:var(--text-dim); font-size:11px">Duration:</span> <span style="font-weight:700">${formatDuration(duration)}</span>`;
        }
        return 'Calculating...';
    }

    function updateTimers() {
        const timers = document.querySelectorAll('.live-timer');
        timers.forEach(el => {
            const start = el.getAttribute('data-timer-start');
            const end = el.getAttribute('data-timer-end');
            
            if (!start) return;
            if (end) return; // Static label already set

            const startTime = new Date(start).getTime();
            const now = new Date().getTime();
            const diff = now - startTime;

            if (diff < 0) {
                el.innerHTML = '0m 0s';
                return;
            }

            el.innerHTML = `<span style="color:var(--primary); font-size:11px">Delivering:</span> <span style="font-weight:700; color:var(--primary)">${formatDuration(diff)}</span>`;
        });
    }

    function openMap(address, lat = null, lng = null) {
        if (!address && (!lat || !lng)) return;
        let dest = encodeURIComponent(address);
        if (lat && lng) dest = `${lat},${lng}`;
        window.open(`https://www.google.com/maps/dir/?api=1&destination=${dest}`, '_blank');
    }

    return { init, fetchAll, setFilter, updateStatus, updateRapidStatus, acceptRapid, openMap, toggleOnline, requestWithdrawal, fetchWalletActivity, fetchProfile, closeWithdrawalModal, submitWithdrawal };
})();

document.addEventListener('DOMContentLoaded', DeliveryConsole.init);

function toggleOnlineStatus(el) {
    DeliveryConsole.toggleOnline();
}
