<?php
$activePage = 'rapid-orders';
$pageTitle = 'Rapid Pickup Monitor';
include 'layouts/header.php';
?>

<style>
/* Premium UI Overrides for Rapido Monitor */
:root {
    --primary-soft: rgba(var(--primary-rgb), 0.1);
    --bg-glass: rgba(255, 255, 255, 0.8);
    --border-soft: #f1f5f9;
}

.admin-card {
    border: none;
    box-shadow: 0 4px 20px rgba(0,0,0,0.05);
    background: white;
}

.admin-table {
    border-collapse: separate;
    border-spacing: 0 4px;
}

.admin-table thead th {
    background: transparent;
    color: #94a3b8;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    font-weight: 800;
    padding: 16px 20px;
    border: none;
}

.admin-table tbody tr {
    background: #fff;
    box-shadow: 0 2px 4px rgba(0,0,0,0.02);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.admin-table tbody tr:hover {
    transform: translateY(-2px) scale(1.005);
    box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05);
}

.admin-table td {
    padding: 10px 20px;
    border: none;
    background: #fff;
}

.admin-table td:first-child { border-radius: 12px 0 0 12px; }
.admin-table td:last-child { border-radius: 0 12px 12px 0; }

.status-pill {
    padding: 6px 12px;
    border-radius: 8px;
    font-weight: 800;
    font-size: 10px;
    letter-spacing: 0.02em;
    text-transform: uppercase;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.sp-pending { background: #fff7ed; color: #f97316; border: 1px solid #ffd8a8; }
.sp-assigned { background: #fefce8; color: #ca8a04; border: 1px solid #fef08a; }
.sp-accepted { background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; }
.sp-picked { background: #eff6ff; color: #2563eb; border: 1px solid #bfdbfe; }
/* Global Styles for Rapid Dashboard */
.status-pill {
    padding: 6px 12px;
    border-radius: 8px;
    font-weight: 800;
    font-size: 10px;
    letter-spacing: 0.02em;
    text-transform: uppercase;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    white-space: nowrap;
}

.sp-pending { background: #fff7ed; color: #f97316; border: 1px solid #ffd8a8; }
.sp-assigned { background: #fefce8; color: #ca8a04; border: 1px solid #fef08a; }
.sp-accepted { background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; }
.sp-picked { background: #eff6ff; color: #2563eb; border: 1px solid #bfdbfe; }
.sp-completed { background: #f8fafc; color: #64748b; border: 1px solid #e2e8f0; }
.sp-rejected { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }

/* Custom Rapid Modal UI - Namespaced to avoid conflicts */
.rapid-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background: rgba(15, 23, 42, 0.7);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    z-index: 999999;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s ease;
    padding: 16px;
    box-sizing: border-box;
}

.rapid-overlay.active {
    display: flex !important;
    opacity: 1;
}

.rapid-modal-box {
    background: white;
    width: 100%;
    max-width: 650px;
    border-radius: 28px;
    box-shadow: 0 50px 100px -20px rgba(0,0,0,0.3);
    /* Removed overflow:hidden to allow dropdowns to pop out */
    transform: scale(0.9);
    transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    max-height: calc(100vh - 32px);
    display: flex;
    flex-direction: column;
}

.rapid-overlay.active .rapid-modal-box {
    transform: scale(1);
}

.rapid-modal-header {
    background: #f8fafc;
    padding: 24px 30px;
    border-bottom: 1px solid #f1f5f9;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-radius: 28px 28px 0 0;
    flex-shrink: 0;
}

.rapid-modal-body {
    padding: 30px 30px 40px 30px;
    overflow-y: auto;
    scrollbar-width: none; /* Firefox */
    -ms-overflow-style: none; /* IE and Edge */
}

.rapid-modal-body::-webkit-scrollbar {
    display: none; /* Chrome, Safari and Opera */
}

.rapid-info-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    background: #f8fafc;
    border: 1px solid #f1f5f9;
    border-radius: 20px;
    padding: 24px;
}

.rapid-label {
    font-size: 10px;
    font-weight: 800;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 6px;
}

.rapid-value {
    font-size: 14px;
    color: #1e293b;
    font-weight: 700;
    line-height: 1.5;
    word-break: break-word;
}

.rapid-close-btn {
    width: 34px;
    height: 34px;
    border-radius: 10px;
    background: white;
    border: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 18px;
    color: #64748b;
    transition: all 0.2s;
    flex-shrink: 0;
}

.rapid-close-btn:hover {
    background: #fef2f2;
    color: #dc2626;
    border-color: #fecaca;
    transform: rotate(90deg);
}

.btn-premium {
    background: var(--primary);
    color: white;
    padding: 12px 24px;
    border-radius: 14px;
    border: none;
    font-weight: 800;
    font-size: 13px;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(var(--primary-rgb), 0.3);
    transition: all 0.3s;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.btn-premium:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 15px rgba(var(--primary-rgb), 0.4);
}

.rapid-assign-group {
    display: flex;
    gap: 10px;
}

.rapid-modal-actions {
    margin-top: 1px;
    padding-top: 24px;
    border-top: 1px solid #f1f5f9;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 10px;
}

/* Responsive Overrides */
.rapid-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
    gap: 16px;
    flex-wrap: wrap;
}

@media (max-width: 640px) {
    .rapid-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
    }
    
    .rapid-header h2 {
        font-size: 1.2rem;
    }

    .rapid-modal-header {
        padding: 20px;
    }

    .rapid-modal-body {
        padding: 20px;
    }

    .rapid-info-grid {
        grid-template-columns: 1fr;
        padding: 16px;
        gap: 16px;
    }

    .rapid-assign-group {
        flex-direction: column;
    }

    .rapid-assign-group select,
    .rapid-assign-group button {
        width: 100%;
    }

    .rapid-modal-actions {
        flex-direction: column;
        gap: 12px;
    }

    .rapid-modal-actions button {
        width: 100%;
        justify-content: center;
    }
}
</style>

<div class="rapid-header" style="margin: 0 0 24px 0; padding: 0 4px;">
    <h2 style="font-weight:800; color:var(--text); margin:0;">Rapid Pickup & Drop Requests</h2>
    <button class="admin-header-btn" onclick="RapidMonitor.init()"><i data-lucide="refresh-cw"></i> Refresh</button>
</div>

<div class="admin-card">
    <div class="admin-table-wrapper">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Customer</th>
                    <th>Pickup Address</th>
                    <th>Drop Address</th>
                    <th>Price</th>
                    <th>Delivery Boy</th>
                    <th>Status</th>
                    <th>Time</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="rapidOrdersTable">
                <tr><td colspan="9" style="text-align:center;padding:40px">Loading requests...</td></tr>
            </tbody>
        </table>
    </div>
</div>

<div id="rapidMgmtModal" class="rapid-overlay">
    <div class="rapid-modal-box">
        <div class="rapid-modal-header">
            <div style="display: flex; align-items: center; gap: 16px;">
                <div style="background: var(--primary-pale); color: var(--primary); width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                    <i data-lucide="package" style="width: 20px; height: 20px;"></i>
                </div>
                <div>
                    <h3 style="margin:0; font-size: 16px; font-weight: 800; color: #1e293b;">Order Management</h3>
                    <span id="modalOrderId" style="font-size: 12px; color: var(--primary); font-weight: 700;">#R--</span>
                </div>
            </div>
            <button class="rapid-close-btn" onclick="RapidMonitor.closeModal()">&times;</button>
        </div>
        
        <div class="rapid-modal-body">
            <!-- Section 1: Order Details -->
            <div style="margin-bottom: 24px;">
                <h4 class="rapid-label" style="margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                    <i data-lucide="info" style="width:14px; height:14px;"></i> Order Information
                </h4>
                <div class="rapid-info-grid">
                    <div>
                        <div class="rapid-label">Pickup Location</div>
                        <div id="modalPickup" class="rapid-value">-</div>
                    </div>
                    <div>
                        <div class="rapid-label">Drop Location</div>
                        <div id="modalDrop" class="rapid-value">-</div>
                    </div>
                    <div>
                        <div class="rapid-label">Customer</div>
                        <div id="modalCustomerName" class="rapid-value">-</div>
                    </div>
                    <div>
                        <div class="rapid-label">Phone Number</div>
                        <div id="modalCustomerPhone" class="rapid-value">-</div>
                    </div>
                    <div>
                        <div class="rapid-label">Price</div>
                        <div id="modalPrice" class="rapid-value" style="font-size: 18px; color: var(--primary);">₹0</div>
                    </div>
                    <div>
                        <div class="rapid-label">Status</div>
                        <div><span id="modalStatus" class="status-pill">-</span></div>
                    </div>
                </div>
            </div>

            <!-- Section 2: Assignment Control -->
            <div id="assignmentControl">
                <h4 class="rapid-label" style="margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                    <i data-lucide="truck" style="width:14px; height:14px;"></i> Delivery Assignment
                </h4>
                
                <div id="unassignedState" style="display: block;">
                    <div class="rapid-assign-group">
                        <select id="partnerSelect" class="filter-select" style="flex: 1; height: 46px; border-radius: 12px; border: 1.5px solid #e2e8f0; padding: 0 12px; font-weight: 600; font-size: 13px;">
                            <option value="">Select Partner...</option>
                        </select>
                        <button class="btn-premium" onclick="RapidMonitor.assignPartner()" style="height: 46px; padding: 0 20px;">Assign</button>
                    </div>
                </div>

                <div id="assignedState" style="display: none; align-items: center; justify-content: space-between; background: #f0fdf4; padding: 16px; border-radius: 16px; border: 1.5px solid #bbf7d0; flex-wrap: wrap; gap: 12px;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div style="background: white; color: #16a34a; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 10px rgba(0,0,0,0.05); flex-shrink: 0;">
                            <i data-lucide="user-check" style="width: 18px; height: 18px;"></i>
                        </div>
                        <div>
                            <div id="modalPartnerName" style="font-size: 14px; color: #064e3b; font-weight: 800;">-</div>
                            <div id="modalPartnerPhone" style="font-size: 11px; color: #065f46; opacity: 0.7;">Delivery Partner</div>
                        </div>
                    </div>
                    <button id="btnReassign" class="btn-premium" style="background: white; color: #16a34a; border: 1.5px solid #86efac; box-shadow: none; padding: 6px 14px; font-size:11px;" onclick="RapidMonitor.toggleReassign()">Change</button>
                </div>
            </div>

            <!-- Section 3: Modal Actions -->
            <div class="rapid-modal-actions">
                <button id="btnCancelOrder" class="btn-premium" style="background: transparent; color: #ef4444; border: 1.5px solid #fee2e2; box-shadow: none; font-size: 12px; padding: 10px 16px;" onclick="RapidMonitor.confirmAction('rejected')">
                    <i data-lucide="x-circle" style="width: 14px; height: 14px; vertical-align: middle; margin-right: 6px;"></i> Cancel Order
                </button>
                <button id="btnCompleteOrder" class="btn-premium" onclick="RapidMonitor.confirmAction('completed')">
                    <i data-lucide="check-circle" style="width: 14px; height: 14px; vertical-align: middle; margin-right: 6px;"></i> Mark Completed
                </button>
            </div>
        </div>
    </div>
</div>

<script>
const RapidMonitor = {
    availablePartners: [],
    currentOrder: null,
    statusInterval: null,
    
    statusLabels: {
        'pending': { label: 'Pending Assignment', class: 'sp-pending' },
        'requested': { label: 'Pending Assignment', class: 'sp-pending' },
        'assigned': { label: 'Assigned (Waiting)', class: 'sp-assigned' },
        'accepted': { label: 'Accepted', class: 'sp-accepted' },
        'picked': { label: 'Picked Up', class: 'sp-picked' },
        'completed': { label: 'Completed', class: 'sp-completed' },
        'rejected': { label: 'Rejected', class: 'sp-rejected' }
    },

    currentOrders: [],
    init: async function() {
        const tbody = document.getElementById('rapidOrdersTable');
        try {
            // Fetch partners and orders in parallel
            const [pResp, oResp] = await Promise.all([
                fetch('../api/admin/users/list_available_partners.php'),
                fetch('../api/admin/orders/list_rapid.php')
            ]);
            
            const [pData, oData] = await Promise.all([pResp.json(), oResp.json()]);
            this.availablePartners = pData.partners || [];
            this.currentOrders = oData || [];
            
            if (this.currentOrders.length === 0) {
                tbody.innerHTML = '<tr><td colspan="9" style="text-align:center;padding:40px;color:var(--text-muted)">No rapid requests found</td></tr>';
                return;
            }

            tbody.innerHTML = this.currentOrders.map(o => {
                const status = (o.status || 'pending').toLowerCase();
                const labelCfg = this.statusLabels[status] || { label: o.status, class: 'sp-pending' };
                // Priority: Sender Phone (from order) -> Customer Phone (from user profile) -> Fallback
                const customerPhone = (o.sender_phone && o.sender_phone !== 'null' && o.sender_phone !== '') ? o.sender_phone : 
                                      (o.customer_phone && o.customer_phone !== 'null' && o.customer_phone !== '') ? o.customer_phone : 
                                      'No Phone';
                const priceFormatted = parseFloat(o.price).toLocaleString('en-IN', { minimumFractionDigits: 0, maximumFractionDigits: 2 });

                return `
                    <tr>
                        <td style="white-space: nowrap;"><span style="color: #94a3b8; font-family: monospace; font-weight: 700;">#R-${o.id}</span></td>
                        <td>
                            <div style="font-weight:800; color:#0f172a; font-size: 13px;">${o.customer_name || 'Anonymous'}</div>
                            <div style="font-size:11px; color:${customerPhone === 'No Phone' ? '#ef4444' : '#64748b'}; margin-top:2px; font-weight: 600;">
                                <i data-lucide="phone" style="width:11px; height:11px; vertical-align: middle;"></i> 
                                ${customerPhone === 'No Phone' ? 
                                    customerPhone : 
                                    `<a href="tel:${customerPhone}" style="color: inherit; text-decoration: none; border-bottom: 1px dashed #cbd5e1;">${customerPhone}</a>`
                                }
                            </div>
                        </td>
                        <td class="fs-12" style="max-width:180px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; color: #475569;" title="${o.pickup_address}">
                            <i data-lucide="map-pin" style="width:12px; height:12px; color: var(--primary); opacity: 0.6;"></i> ${o.pickup_address}
                        </td>
                        <td class="fs-12" style="max-width:180px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; color: #475569;" title="${o.drop_address}">
                            <i data-lucide="navigation" style="width:12px; height:12px; color: #6366f1; opacity: 0.6;"></i> ${o.drop_address}
                        </td>
                        <td><strong style="color:#0f172a; font-size:15px; font-weight: 800;">₹${priceFormatted}</strong></td>
                        <td>
                            ${o.delivery_boy_name ? 
                                `<div style="display:flex; align-items:center; gap:8px;">
                                    <div style="width:28px; height:28px; background:var(--primary-pale); color:var(--primary); border-radius:50%; display:flex; align-items:center; justify-content:center; flex-shrink: 0; font-size:10px; font-weight:800;">
                                        ${o.delivery_boy_name.charAt(0)}
                                    </div>
                                    <div style="font-weight:700; color:#1e293b; font-size:13px; white-space:nowrap;">${o.delivery_boy_name}</div>
                                </div>` : 
                                `<span style="color:#f59e0b; font-weight:700; font-size:11px; display:flex; align-items:center; gap:4px; background:#fff7ed; padding:4px 10px; border-radius:20px; width:fit-content;">
                                    <i data-lucide="clock" style="width:12px; height:12px;"></i> Unassigned
                                </span>`
                            }
                        </td>
                        <td>
                            <span class="status-pill ${labelCfg.class}">
                                ${labelCfg.label}
                            </span>
                        </td>
                        <td style="white-space: nowrap; font-size: 11px; color:#94a3b8; font-weight:500;">
                            ${new Date(o.created_at).toLocaleDateString()} ${new Date(o.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}
                        </td>
                        <td>
                            <button class="btn-premium" onclick="RapidMonitor.openModal(${o.id})">
                                Manage
                            </button>
                        </td>
                    </tr>
                `;
            }).join('');
            
            if (window.lucide) lucide.createIcons();
        } catch (e) {
            console.error(e);
            tbody.innerHTML = '<tr><td colspan="9" style="text-align:center;padding:40px;color:var(--danger)">Failed to load data</td></tr>';
        }
    },

    openModal: function(orderId) {
        const order = this.currentOrders.find(o => o.id == orderId);
        if (!order) return;
        
        const modal = document.getElementById('rapidMgmtModal');
        // Pure body append to escape ANY layout constraints
        document.body.appendChild(modal);

        this.currentOrder = order;
        const status = (order.status || 'pending').toLowerCase();
        
        // Basic Info
        document.getElementById('modalOrderId').innerText = `#R-${order.id}`;
        document.getElementById('modalPickup').innerText = order.pickup_address;
        document.getElementById('modalDrop').innerText = order.drop_address;
        document.getElementById('modalPrice').innerText = `₹${parseFloat(order.price).toFixed(2)}`;

        // Customer Details
        const customerPhone = (order.sender_phone && order.sender_phone !== 'null' && order.sender_phone !== '') ? order.sender_phone : 
                              (order.customer_phone && order.customer_phone !== 'null' && order.customer_phone !== '') ? order.customer_phone : 
                              'No Phone';
        
        document.getElementById('modalCustomerName').innerText = order.customer_name || 'Anonymous';
        if (customerPhone !== 'No Phone') {
            document.getElementById('modalCustomerPhone').innerHTML = `<a href="tel:${customerPhone}" style="color: var(--primary); text-decoration: none; border-bottom: 1px dashed var(--primary); font-weight:800;"><i data-lucide="phone" style="width:12px; height:12px; vertical-align: middle;"></i> ${customerPhone}</a>`;
        } else {
            document.getElementById('modalCustomerPhone').innerText = customerPhone;
        }
        
        const labelCfg = this.statusLabels[status] || { label: order.status, class: 'sp-pending' };
        const statusEl = document.getElementById('modalStatus');
        statusEl.innerText = labelCfg.label;
        statusEl.className = `status-pill ${labelCfg.class}`;

        // Sections
        const unassignedState = document.getElementById('unassignedState');
        const assignedState = document.getElementById('assignedState');
        const btnCancel = document.getElementById('btnCancelOrder');
        const btnComplete = document.getElementById('btnCompleteOrder');

        // Assignment logic
        if (order.delivery_boy_id && status !== 'rejected') {
            unassignedState.style.display = 'none';
            assignedState.style.display = 'flex';
            document.getElementById('modalPartnerName').innerText = order.delivery_boy_name;
        } else {
            unassignedState.style.display = 'block';
            assignedState.style.display = 'none';
            this.populateSelect();
        }

        // Action buttons visibility
        btnCancel.style.display = (status === 'pending' || status === 'assigned' || status === 'rejected') ? 'block' : 'none';
        btnComplete.style.display = (status === 'picked') ? 'block' : 'none';

        modal.classList.add('active');
        if (window.lucide) lucide.createIcons();
    },

    closeModal: function() {
        document.getElementById('rapidMgmtModal').classList.remove('active');
    },

    populateSelect: function() {
        const select = document.getElementById('partnerSelect');
        
        // Sort: Online & Ready (0 orders) first, then Online & Busy, then Offline
        const sortedPartners = (this.availablePartners || []).sort((a, b) => {
            if (a.is_online != b.is_online) return b.is_online - a.is_online;
            const aActive = (parseInt(a.active_orders) || 0) + (parseInt(a.active_rapid_orders) || 0);
            const bActive = (parseInt(b.active_orders) || 0) + (parseInt(b.active_rapid_orders) || 0);
            return aActive - bActive;
        });

        select.innerHTML = '<option value="">Select Partner...</option>' + 
            sortedPartners.map(p => {
                const activeCount = (parseInt(p.active_orders) || 0) + (parseInt(p.active_rapid_orders) || 0);
                let statusText = '';
                if (p.is_online == 1) {
                    statusText = activeCount > 0 ? `Busy (${activeCount} order${activeCount > 1 ? 's' : ''})` : 'Ready';
                } else {
                    statusText = 'Inactive';
                }
                const activeTotal = (parseInt(p.active_orders) || 0) + (parseInt(p.active_rapid_orders) || 0);
                let statusIcon = '⚪';
                if (p.is_online == 1) {
                    statusIcon = activeTotal > 0 ? '🟡' : '🟢';
                }
                return `<option value="${p.id}">${Utils.escapeHTML(p.name)}${p.city ? ' (' + Utils.escapeHTML(p.city) + ')' : ''} ${statusIcon} - ${statusText}</option>`;


            }).join('');
    },


    toggleReassign: function() {
        document.getElementById('assignedState').style.display = 'none';
        document.getElementById('unassignedState').style.display = 'block';
        this.populateSelect();
    },

    assignPartner: async function() {
        const partnerId = document.getElementById('partnerSelect').value;
        if (!partnerId) {
            Toast.show('Please choose a partner first', 'error');
            return;
        }

        try {
            const resp = await fetch('../api/admin/orders/assign_rapid_order.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ order_id: this.currentOrder.id, delivery_boy_id: partnerId })
            });
            const data = await resp.json();
            if (data.success) {
                Toast.show('Assigned successfully!', 'success');
                this.closeModal();
                this.init();
            } else {
                Toast.show(data.error || 'Assignment failed', 'error');
            }
        } catch (e) {
            Toast.show('Network error', 'error');
        }
    },

    confirmAction: async function(status) {
        if (!confirm(`Are you sure you want to mark this as ${status}?`)) return;
        
        try {
            const resp = await fetch('../api/admin/orders/update_rapid_status.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: this.currentOrder.id, status: status })
            });
            const data = await resp.json();
            if (data.success) {
                Toast.show('Action successful!', 'success');
                this.closeModal();
                this.init();
            } else {
                Toast.show(data.error || 'Update failed', 'error');
            }
        } catch (e) {
            Toast.show('Network error', 'error');
        }
    }
};

window.addEventListener('load', () => {
    RapidMonitor.init();
    setInterval(() => RapidMonitor.init(), 30000);
});
</script>

<?php include 'layouts/footer.php'; ?>
