const VendorOrders = (() => {
    let orders = [];
    let currentFilter = 'all';

    async function init() {
        await loadOrders();
        // Setup search
        document.getElementById('orderSearch')?.addEventListener('input', (e) => {
            renderOrders(e.target.value.toLowerCase());
        });
        
        // Polling every 5 seconds
        setInterval(loadOrders, 5000);

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

    async function loadOrders() {
        try {
            const resp = await fetch(`../api/vendor/orders/list.php?status=${currentFilter}&_t=${Date.now()}`).then(r => r.json());
            if (resp.success) {
                orders = resp.orders;
                renderOrders(document.getElementById('orderSearch')?.value?.toLowerCase() || '');
            }
            
            // Fetch actively pending orders specifically for the alert sound loop
            // (Just in case the vendor is currently viewing the 'Completed' tab)
            const activeResp = await fetch(`../api/vendor/orders/list.php?status=active&_t=${Date.now()}`).then(r => r.json());
            if (activeResp.success) {
                checkRingingStatus(activeResp.orders);
            }
            
        } catch (err) {
            console.error('Failed to load orders:', err);
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

    function checkRingingStatus(activeOrdersList) {
        const needsRinging = activeOrdersList.some(o => o.status === 'Placed' || o.status === 'Pending');
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

    window.setOrderFilter = (filter) => {
        currentFilter = filter;
        // Update UI buttons
        document.querySelectorAll('.range-btn').forEach(btn => {
            btn.classList.toggle('active', btn.textContent.toLowerCase() === filter);
        });
        loadOrders();
    };

    function renderOrders(searchTerm = '') {
        const tbody = document.getElementById('vendorOrdersList');
        if (!tbody) return;

        const filtered = orders.filter(o => {
            const matchSearch = (o.order_number || '').toLowerCase().includes(searchTerm) || 
                                (o.customer_name || '').toLowerCase().includes(searchTerm);
            return matchSearch;
        });

        if (filtered.length === 0) {
            tbody.innerHTML = `<tr><td colspan="7" style="text-align:center; padding:40px; color:var(--text-muted)">No orders found</td></tr>`;
            return;
        }

        tbody.innerHTML = filtered.map(o => {
            const statusClass = `sp-${o.status.toLowerCase().replace(/ /g, '-')}`;
            return `
                <tr>
                    <td><strong>${o.order_number || o.id}</strong></td>
                    <td>
                        <div style="font-weight:600">${o.customer_name || 'Customer'}</div>
                        ${o.customer_phone && o.customer_phone !== 'null' ? `<div style="font-size:11px; color:#666">${o.customer_phone}</div>` : ''}
                    </td>
                    <td><button class="view-all-btn" style="padding:4px 8px; font-size:11px" onclick="VendorOrders.viewDetails(${o.id})">View Items</button></td>
                    <td>
                        <div style="font-weight:700">₹${o.vendor_earning}</div>
                        <div style="font-size:10px; color:#999">Net Profit</div>
                    </td>
                    <td><span class="status-pill ${statusClass}">${o.status}</span></td>
                    <td><div style="font-size:12px; color:#666">${new Date(o.created_at).toLocaleString(['en-IN'], {dateStyle:'short', timeStyle:'short'})}</div></td>
                    <td>
                        <button class="view-all-btn" onclick="VendorOrders.viewDetails(${o.id})"><i data-lucide="eye"></i></button>
                    </td>
                </tr>
            `;
        }).join('');
        
        if (window.lucide) lucide.createIcons();
    }

    async function viewDetails(orderId) {
        try {
            const resp = await fetch(`../api/vendor/orders/get_details.php?order_id=${orderId}`).then(r => r.json());
            if (resp.success) {
                const o = resp.order;
                const items = resp.items;

                document.getElementById('modalOrderNum').textContent = `#${o.order_number || o.id}`;
                document.getElementById('modalCustName').textContent = o.customer_name || 'Customer';
                const phoneEl = document.getElementById('modalCustPhone');
                if (o.customer_phone && o.customer_phone !== 'null') {
                    phoneEl.textContent = o.customer_phone;
                    phoneEl.style.display = 'block';
                } else {
                    phoneEl.style.display = 'none';
                }
                document.getElementById('modalStatus').textContent = o.status;
                document.getElementById('modalStatus').className = `status-pill sp-${o.status.toLowerCase().replace(/ /g, '-')}`;
                document.getElementById('modalAddress').textContent = o.address;
                document.getElementById('modalPayment').textContent = o.payment_method;
                
                // Fill bill summary breakdown
                const productTotal = parseFloat(o.total_amount || 0);
                const commission = parseFloat(o.commission_amount || (productTotal * 0.20));
                const vendorNet = parseFloat(o.vendor_earning || (productTotal * 0.80));

                if (document.getElementById('modalItemsTotal')) document.getElementById('modalItemsTotal').textContent = `₹${productTotal.toFixed(2)}`;
                if (document.getElementById('modalCommission')) document.getElementById('modalCommission').textContent = `-₹${commission.toFixed(2)}`;
                if (document.getElementById('modalCommissionLabel')) {
                    const rate = o.commission_rate || 20;
                    document.getElementById('modalCommissionLabel').textContent = `Commission (${rate}%):`;
                }
                if (document.getElementById('modalVendorEarning')) document.getElementById('modalVendorEarning').textContent = `₹${vendorNet.toFixed(2)}`;
                
                if (document.getElementById('modalDeliveryFee')) document.getElementById('modalDeliveryFee').textContent = `₹${parseFloat(o.delivery_charge || 0).toFixed(2)}`;
                if (document.getElementById('modalPlatformFee')) document.getElementById('modalPlatformFee').textContent = `₹${parseFloat(o.platform_fee || 0).toFixed(2)}`;
                if (document.getElementById('modalHandlingFee')) document.getElementById('modalHandlingFee').textContent = `₹${parseFloat(o.handling_fee || 0).toFixed(2)}`;
                if (document.getElementById('modalTotal')) document.getElementById('modalTotal').textContent = `₹${parseFloat(o.grand_total || 0).toFixed(2)}`;

                document.getElementById('modalItemsContainer').innerHTML = items.map(it => `
                    <div style="background:white; border:1px solid var(--border); border-radius:12px; padding:12px; display:flex; justify-content:space-between; align-items:center; box-shadow:0 2px 4px rgba(0,0,0,0.02)">
                        <div style="display:flex; align-items:center; gap:12px">
                            <div style="background:rgba(39, 174, 96, 0.1); color:#27ae60; min-width:36px; height:36px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-weight:900; font-size:15px; border:1px solid rgba(39, 174, 96, 0.2)">
                                ${it.quantity}
                            </div>
                            <div>
                                <div style="font-weight:700; color:var(--text); font-size:14px; margin-bottom:2px">${it.product_name}</div>
                                <div style="font-size:11px; color:var(--text-muted)">Unit Price: ₹${it.price}</div>
                            </div>
                        </div>
                        <div style="text-align:right">
                            <div style="font-size:10px; color:var(--text-muted); text-transform:uppercase; font-weight:800; margin-bottom:2px; letter-spacing:0.5px">Subtotal</div>
                            <div style="font-weight:800; color:var(--text); font-size:16px">₹${it.subtotal}</div>
                        </div>
                    </div>
                `).join('');

                renderActions(o);
                
                // Show modal (Global Modal helper assumed or use fallback)
                if (window.Modal) Modal.open('orderDetailsModal');
                else document.getElementById('orderDetailsModal').classList.add('open');
                
                if (window.lucide) lucide.createIcons();
            }
        } catch (err) {
            console.error('Error fetching details:', err);
        }
    }

    function renderActions(o) {
        const container = document.getElementById('modalActionContainer');
        let btns = '';

        if (o.status === 'Placed' || o.status === 'Pending') {
            btns = `<button class="admin-btn admin-btn-primary" style="flex:1" onclick="VendorOrders.updateStatus(${o.id}, 'Confirmed')"><i data-lucide="check-circle" style="width:18px;height:18px"></i> Accept Order</button>`;
        } else if (o.status === 'Confirmed') {
            btns = `<button class="admin-btn admin-btn-primary" style="flex:1" onclick="VendorOrders.updateStatus(${o.id}, 'Ready for Pickup')"><i data-lucide="bell" style="width:18px;height:18px"></i> Mark as Ready</button>`;
        } else {
            btns = `<div style="font-size:13px; color:var(--text-muted); padding:10px; border-radius:12px; background:var(--bg); border:1px dashed var(--border); width:100%; text-align:center">No further actions available for this status.</div>`;
        }

        container.innerHTML = btns;
    }

    async function updateStatus(orderId, newStatus) {
        try {
            const resp = await fetch('../api/vendor/orders/update_status.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ orderId, status: newStatus })
            }).then(r => r.json());

            if (resp.success) {
                // Close modal immediately
                if (typeof Modal !== 'undefined') Modal.close('orderDetailsModal');
                if (window.Toast) Toast.show(resp.message || 'Status updated!', 'success');
                loadOrders();
            } else {
                if (window.Toast) Toast.show(resp.error || 'Failed to update status', 'error');
            }
        } catch (err) {
            console.error('Error updating status:', err);
            if (window.Toast) Toast.show('Error updating status', 'error');
        }
    }

    return { init, viewDetails, updateStatus };
})();

// Export to global scope for HTML onclick handlers
window.VendorOrders = VendorOrders;

document.addEventListener('DOMContentLoaded', VendorOrders.init);
