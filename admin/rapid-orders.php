<?php
$activePage = 'rapid-orders';
$pageTitle = 'Rapid Pickup Monitor';
include 'layouts/header.php';
?>

<div class="admin-card">
    <div class="admin-card-header">
        <div class="admin-card-title">
            <i data-lucide="bike" class="header-icon"></i> 
            Rapid Pickup & Drop Requests
        </div>
        <div class="header-right">
            <button class="nav-btn nav-btn-primary" onclick="RapidMonitor.init()">
                <i data-lucide="refresh-cw"></i> Refresh
            </button>
        </div>
    </div>

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

<script>
const RapidMonitor = {
    init: async function() {
        const tbody = document.getElementById('rapidOrdersTable');
        try {
            const resp = await fetch('../api/admin/orders/list_rapid.php');
            const data = await resp.json();
            
            if (!data || data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="9" style="text-align:center;padding:40px;color:var(--text-muted)">No rapid requests found</td></tr>';
                return;
            }

            tbody.innerHTML = data.map(o => `
                <tr>
                    <td><strong>#R-${o.id}</strong></td>
                    <td>
                        <div style="font-weight:700;">${o.customer_name}</div>
                        ${o.customer_phone ? `<a href="tel:${o.customer_phone}" style="font-size:11px; color:var(--text-muted); text-decoration:none; display:flex; align-items:center; gap:4px; margin-top:2px;">
                            <i data-lucide="phone" style="width:10px; height:10px;"></i> ${o.customer_phone}
                        </a>` : ''}
                    </td>
                    <td class="fs-12">${o.pickup_address}</td>
                    <td class="fs-12">${o.drop_address}</td>
                    <td><strong>₹${o.price}</strong></td>
                    <td>${o.delivery_boy_name || '<span class="text-muted">Not Assigned</span>'}</td>
                    <td><span class="status-pill sp-${o.status.toLowerCase()}">${o.status}</span></td>
                    <td class="fs-11 text-muted">${new Date(o.created_at).toLocaleString()}</td>
                    <td>
                        <select class="filter-select" onchange="RapidMonitor.updateStatus(${o.id}, this.value)" style="padding:4px 8px; font-size:12px; height:auto; border-radius:4px;">
                            <option value="">Update...</option>
                            <option value="Completed">Complete</option>
                            <option value="Cancelled">Cancel</option>
                        </select>
                    </td>
                </tr>
            `).join('');
            
            if (window.lucide) lucide.createIcons();
        } catch (e) {
            tbody.innerHTML = '<tr><td colspan="9" style="text-align:center;padding:40px;color:var(--danger)">Failed to load data</td></tr>';
        }
    },
    updateStatus: async function(id, status) {
        if (!status) return;
        if (!confirm('Mark rapid order as ' + status + '?')) return;
        
        try {
            const resp = await fetch('../api/admin/orders/update_rapid_status.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id, status })
            });
            const data = await resp.json();
            if (data.success) {
                Toast.show('Status updated successfully', 'success');
                RapidMonitor.init();
                if(typeof NotificationEngine !== 'undefined') NotificationEngine.check();
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
    // Auto refresh every 30s
    setInterval(RapidMonitor.init, 30000);
});
</script>

<?php include 'layouts/footer.php'; ?>
