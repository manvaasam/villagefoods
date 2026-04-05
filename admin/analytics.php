<?php
$activePage = 'analytics';
$pageTitle = 'Google Analytics Dashboard';
include 'layouts/header.php';
?>

<div style="margin-bottom: 24px;">
    <h2 style="font-size: 1.5rem; font-weight: 700; color: var(--text);">Google Analytics</h2>
    <p style="color: var(--text-muted); font-size: 0.9rem;">Real-time performance and tracking metrics.</p>
</div>

<div id="analyticsError" style="display:none; padding:16px 20px; background:#fee2e2; color:#991b1b; border-radius:12px; border: 1px solid #f87171; margin-bottom: 24px; font-weight:600; font-size: 14px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);"></div>

<div class="bento-grid" id="analyticsGrid">
  <!-- Active Users Card -->
  <div class="premium-card analytics-card ac-blue col-span-4">
    <div class="analytics-header">
      <div class="analytics-icon-box"><i data-lucide="users"></i></div>
      <div class="analytics-trend trend-up">
        30 Days
      </div>
    </div>
    <div class="analytics-body">
      <div class="analytics-value" id="gaUsers">...</div>
      <div class="analytics-label">Active Users</div>
    </div>
  </div>

  <!-- Page Views Card -->
  <div class="premium-card analytics-card ac-purple col-span-4">
    <div class="analytics-header">
      <div class="analytics-icon-box" style="background:rgba(168, 85, 247, 0.1); color:#a855f7"><i data-lucide="eye"></i></div>
      <div class="analytics-trend trend-up">
        30 Days
      </div>
    </div>
    <div class="analytics-body">
      <div class="analytics-value" id="gaViews">...</div>
      <div class="analytics-label">Total Page Views</div>
    </div>
  </div>

  <!-- Bounce Rate Card -->
  <div class="premium-card analytics-card ac-orange col-span-4">
    <div class="analytics-header">
      <div class="analytics-icon-box" style="background:rgba(249, 115, 22, 0.1); color:#f97316"><i data-lucide="mouse-pointer-click"></i></div>
      <div class="analytics-trend" style="background:var(--accent-light); color:var(--text)">
        Avg Rate
      </div>
    </div>
    <div class="analytics-body">
      <div class="analytics-value" id="gaBounce">...</div>
      <div class="analytics-label">Bounce Rate</div>
    </div>
  </div>

  <!-- Traffic Chart -->
  <div class="premium-card col-span-12" id="analyticsChartCard">
    <div class="admin-card-header" style="border-bottom: 1px dashed var(--border); padding-bottom: 16px;">
      <div class="admin-card-title" style="display: flex; align-items: center; gap: 8px;">
        <div class="analytics-icon-box" style="background: rgba(16, 185, 129, 0.1); color: #10b981; width: 32px; height: 32px; border-radius: 8px;"><i data-lucide="trending-up" style="width: 16px; height: 16px;"></i></div> 
        Daily Traffic (Last 7 Days)
      </div>
    </div>
    <div class="revenue-chart-wrapper" style="padding: 16px 8px 8px 8px;">
      <div id="trafficChart"></div>
    </div>
  </div>

  <!-- Advanced Analytics Row -->
  <div class="premium-card col-span-5" id="analyticsDevicesCard">
    <div class="admin-card-header" style="border-bottom: 1px dashed var(--border); padding-bottom: 16px;">
      <div class="admin-card-title" style="display: flex; align-items: center; gap: 8px;">
        <div class="analytics-icon-box" style="background: rgba(59, 130, 246, 0.1); color: #3b82f6; width: 32px; height: 32px; border-radius: 8px;"><i data-lucide="smartphone" style="width: 16px; height: 16px;"></i></div> 
        Users by Device
      </div>
    </div>
    <div style="padding: 16px; min-height: 250px; display: flex; align-items: center; justify-content: center;" id="devicesLoading">
      <span style="color:var(--text-muted); font-size: 13px;">Loading data...</span>
    </div>
    <div style="padding: 16px;">
      <div id="devicesChart"></div>
    </div>
  </div>

  <div class="premium-card col-span-7" id="analyticsPagesCard">
    <div class="admin-card-header" style="border-bottom: 1px dashed var(--border); padding-bottom: 16px;">
      <div class="admin-card-title" style="display: flex; align-items: center; gap: 8px;">
        <div class="analytics-icon-box" style="background: rgba(168, 85, 247, 0.1); color: #a855f7; width: 32px; height: 32px; border-radius: 8px;"><i data-lucide="layout-list" style="width: 16px; height: 16px;"></i></div> 
        Top 5 Viewed Pages
      </div>
    </div>
    <div class="admin-table-wrapper" style="margin-top: 10px; min-height: 250px;">
      <table class="admin-table">
        <thead>
          <tr>
            <th style="text-align:left">Page Path</th>
            <th style="text-align:right">Views</th>
          </tr>
        </thead>
        <tbody id="topPagesTable">
           <tr><td colspan="2" style="text-align:center; color:var(--text-muted); padding: 40px 0;">Loading pages...</td></tr>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
window.addEventListener('load', async () => {
    try {
        const res = await fetch('../api/admin/analytics/data.php');
        const data = await res.json();

        if (data.error) {
            document.getElementById('analyticsError').style.display = 'block';
            document.getElementById('analyticsError').innerText = data.error;
            return;
        }

        if (data.overview) {
            document.getElementById('gaUsers').innerText = data.overview.users || 0;
            document.getElementById('gaViews').innerText = data.overview.pageViews || 0;
            document.getElementById('gaBounce').innerText = data.overview.bounceRate || '0%';
        }

        if (data.chart) {
            var options = {
                chart: { type: 'area', height: 350, toolbar: { show: false }, fontFamily: 'Nunito, sans-serif' },
                series: [{ name: 'Active Users', data: data.chart.users }],
                xaxis: { 
                    categories: data.chart.dates,
                    labels: { style: { colors: '#64748b' } },
                    axisBorder: { show: false },
                    axisTicks: { show: false }
                },
                yaxis: {
                    labels: { style: { colors: '#64748b' } }
                },
                grid: {
                    borderColor: '#e2e8f0',
                    strokeDashArray: 4,
                    yaxis: { lines: { show: true } }
                },
                colors: ['#10b981'],
                fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.45, opacityTo: 0.05, stops: [50, 100] } },
                dataLabels: { enabled: false },
                stroke: { curve: 'smooth', width: 3 },
                tooltip: { theme: 'light' }
            };
            var chart = new ApexCharts(document.querySelector("#trafficChart"), options);
            chart.render();
        }

        if (data.devices && data.devices.length > 0) {
            document.getElementById('devicesLoading').style.display = 'none';
            var devLabels = data.devices.map(d => d.device);
            var devSeries = data.devices.map(d => d.users);
            
            var devOpts = {
                chart: { type: 'donut', height: 300, fontFamily: 'Nunito, sans-serif' },
                series: devSeries,
                labels: devLabels,
                colors: ['#3b82f6', '#10b981', '#f59e0b', '#8b5cf6'],
                dataLabels: { enabled: false },
                plotOptions: { pie: { donut: { size: '75%' } } },
                legend: { position: 'bottom' }
            };
            new ApexCharts(document.querySelector("#devicesChart"), devOpts).render();
        }

        if (data.topPages && data.topPages.length > 0) {
            let html = '';
            data.topPages.forEach(p => {
                html += `<tr>
                    <td style="font-weight:600; color:var(--text)">${p.path === '/' ? '/ (Home)' : p.path}</td>
                    <td style="text-align:right; font-weight:700; color:var(--primary)">${p.views}</td>
                </tr>`;
            });
            document.getElementById('topPagesTable').innerHTML = html;
        }

    } catch (e) {
        console.error(e);
        document.getElementById('analyticsError').style.display = 'block';
        document.getElementById('analyticsError').innerText = "Failed to load Analytics data. Server returned an invalid response.";
    }
});
</script>

<?php include 'layouts/footer.php'; ?>
