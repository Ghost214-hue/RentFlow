<?php
// Force no-cache BEFORE any output
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

$basePath = '/RentFlow';

require_once __DIR__ . '/../includes/auth.php';
if (($userRole ?? 'owner') === 'tenant') {
    header('Location: /RentFlow/tenant-dashboard');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - RentFlow</title>
    <link rel="stylesheet" href="<?php echo $basePath; ?>/css/output.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="<?php echo $basePath; ?>/js/base-path.js?v=2"></script>
</head>
<body class="bg-gradient-to-br from-blue-50 via-white to-blue-50/30 min-h-screen font-sans text-slate-800 flex flex-col lg:flex-row">
    <?php include __DIR__ . '/../public/components/sidebar.php'; ?>
    <div class="flex-1 flex flex-col min-h-screen">
        <?php include __DIR__ . '/../public/components/header.php'; ?>
        <main class="flex-1 overflow-y-auto p-4 lg:p-8">
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-slate-900">Dashboard</h1>
                <p class="text-slate-500 mt-1">Welcome back, <?php echo htmlspecialchars($user['name']); ?>. Here's your overview.</p>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6" id="statsGrid"></div>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6" id="chartsRow" style="display: none;">
                <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-blue-100/50 p-5">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-semibold text-slate-900">Revenue Overview</h3>
                    </div>
                    <div class="relative h-72"><canvas id="revenueChart"></canvas></div>
                </div>
                <div class="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-5">
                    <h3 class="font-semibold text-slate-900 mb-4">Occupancy</h3>
                    <div class="relative h-48"><canvas id="occChart"></canvas></div>
                    <div class="mt-4 space-y-3">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2"><div class="w-3 h-3 rounded-full bg-blue-500"></div><span class="text-sm text-slate-600">Occupied</span></div>
                            <span class="text-sm font-medium text-slate-900" id="occLegend">0 units</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2"><div class="w-3 h-3 rounded-full bg-slate-200"></div><span class="text-sm text-slate-600">Vacant</span></div>
                            <span class="text-sm font-medium text-slate-900" id="vacLegend">0 units</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6" id="activityRow"></div>
        </main>
    </div>
    <div id="toast" class="fixed bottom-6 right-6 z-50 hidden px-5 py-3 rounded-xl shadow-xl text-white font-medium flex items-center gap-2"></div>
    <script>
    // Calculate base path - navigate up from /frontend/pages/ to project root
    let BASE = window.location.pathname;
    const frontendPagesIndex = BASE.indexOf('/frontend/pages/');
    if (frontendPagesIndex !== -1) {
        BASE = BASE.substring(0, frontendPagesIndex);
    } else {
        // Fallback: remove last path segment
        BASE = BASE.replace(/\/[^\/]*$/, '');
    }
    const API = BASE + '/api';
    const token = localStorage.getItem('rf_token') || '<?php echo $token; ?>';
    const headers = token ? {'Authorization':'Bearer '+token, 'Content-Type':'application/json'} : {'Content-Type':'application/json'};
    const userRole = '<?php echo $userRole ?? 'owner'; ?>';

    async function apiRequest(url, options = {}) {
        const res = await fetch(url, { ...options, headers });
        const text = await res.text();
        let data;
        try { data = JSON.parse(text); } catch(e) { throw new Error('Server error'); }
        
        if (!res.ok) {
            if (res.status === 401) {
                localStorage.removeItem('rf_token');
                window.location.href = '../public/signin.php';
            }
            throw new Error(data.error || 'Request failed');
        }
        return data;
    }

    function fmtCurrency(n) { return 'KES ' + Number(n).toLocaleString(); }
    function fmtDate(d) { return new Date(d).toLocaleDateString('en-GB',{day:'numeric',month:'short',year:'numeric'}); }
    function initials(n) { return n.split(' ').map(s=>s[0]).join('').substring(0,2).toUpperCase(); }
    function statusColor(s) {
        const colors = {'paid':'bg-emerald-100 text-emerald-700','completed':'bg-emerald-100 text-emerald-700','partial':'bg-amber-100 text-amber-700','pending':'bg-slate-100 text-slate-600','open':'bg-red-100 text-red-700','in-progress':'bg-blue-100 text-blue-700','resolved':'bg-emerald-100 text-emerald-700'};
        return colors[s] || 'bg-slate-100 text-slate-600';
    }

    function getLastSixMonths() {
        const months = [];
        const now = new Date();
        for (let i = 5; i >= 0; i--) {
            const dt = new Date(now.getFullYear(), now.getMonth() - i, 1);
            const year = dt.getFullYear();
            const month = String(dt.getMonth() + 1).padStart(2, '0');
            months.push(`${year}-${month}`);
        }
        return months;
    }

    function formatMonthLabel(ym) {
        const [year, month] = ym.split('-');
        return new Date(Number(year), Number(month) - 1, 1).toLocaleDateString('en-GB', { month: 'short', year: '2-digit' });
    }

    function buildMonthlyTrend(payments) {
        const months = getLastSixMonths();
        const totals = Object.fromEntries(months.map(month => [month, 0]));
        payments.forEach(p => {
            const month = p.month || (p.date ? p.date.slice(0, 7) : null);
            if (!month || !totals.hasOwnProperty(month)) return;
            const status = (p.status || '').toLowerCase();
            if (!['completed', 'paid', 'confirmed'].includes(status)) return;
            totals[month] += Number(p.amount || 0);
        });
        return { labels: months.map(formatMonthLabel), values: months.map(month => totals[month] || 0) };
    }

    function toast(msg, type='success') {
        const el = document.getElementById('toast');
        const colors = { success:'bg-gradient-to-r from-emerald-500 to-emerald-600', error:'bg-gradient-to-r from-red-500 to-red-600', info:'bg-gradient-to-r from-blue-500 to-blue-600' };
        const icons = { success:'fa-check-circle', error:'fa-exclamation-circle', info:'fa-info-circle' };
        el.className = `fixed bottom-6 right-6 z-50 px-5 py-3 rounded-xl shadow-xl text-white font-medium flex items-center gap-2 ${colors[type]||colors.success}`;
        el.innerHTML = `<i class="fas ${icons[type]||icons.success}"></i>${msg}`;
        el.classList.remove('hidden');
        setTimeout(() => el.classList.add('hidden'), 3000);
    }

    async function loadDashboard() {
        try {
            if (userRole === 'owner') await loadOwnerDashboard();
            else if (userRole === 'caretaker') await loadCaretakerDashboard();
        } catch(e) {
            console.error(e);
            document.getElementById('statsGrid').innerHTML = '<div class="col-span-full py-12 text-center text-red-400">Failed to load dashboard.</div>';
        }
    }

    async function loadOwnerDashboard() {
        const [dashData, paymentsData] = await Promise.all([
            apiRequest(`${API}/dashboard`),
            apiRequest(`${API}/payments`)
        ]);
        const payments = paymentsData.payments || [];
        const p = dashData.properties || {};
        const h = dashData.houses || {};
        const totalUnits = h.total || 0;
        const occupied = h.occupied || 0;
        const vacant = totalUnits - occupied;
        const occupancyRate = totalUnits ? Math.round((occupied / totalUnits) * 100) : 0;

        document.getElementById('statsGrid').innerHTML = `
            <div class="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-5">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-50 to-blue-100 flex items-center justify-center"><i class="fas fa-building text-blue-600"></i></div>
                    <span class="text-xs font-medium text-blue-600 bg-blue-50 px-2 py-1 rounded-full">${p.total || 0} total</span>
                </div>
                <p class="text-2xl font-bold text-slate-900">${p.total || 0}</p>
                <p class="text-sm text-slate-500">Properties</p>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-5">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-50 to-emerald-100 flex items-center justify-center"><i class="fas fa-users text-emerald-600"></i></div>
                    <span class="text-xs font-medium text-emerald-600 bg-emerald-50 px-2 py-1 rounded-full">${dashData.tenants || 0} tenants</span>
                </div>
                <p class="text-2xl font-bold text-slate-900">${dashData.tenants || 0}</p>
                <p class="text-sm text-slate-500">Active Tenants</p>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-5">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-50 to-blue-100 flex items-center justify-center"><i class="fas fa-money-bill-wave text-blue-600"></i></div>
                    <span class="text-xs font-medium text-blue-600 bg-blue-50 px-2 py-1 rounded-full">This month</span>
                </div>
                <p class="text-2xl font-bold text-slate-900">${fmtCurrency(dashData.revenue||0)}</p>
                <p class="text-sm text-slate-500">Monthly Revenue</p>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-5">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-50 to-amber-100 flex items-center justify-center"><i class="fas fa-exclamation-circle text-amber-600"></i></div>
                    <span class="text-xs font-medium text-amber-600 bg-amber-50 px-2 py-1 rounded-full">Outstanding</span>
                </div>
                <p class="text-2xl font-bold text-slate-900">${fmtCurrency(dashData.outstanding||0)}</p>
                <p class="text-sm text-slate-500">Outstanding Rent</p>
            </div>`;

        document.getElementById('chartsRow').style.display = 'grid';
        document.getElementById('activityRow').innerHTML = `
            <div class="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-5">
                <h3 class="font-semibold text-slate-900 mb-4">Recent Payments</h3>
                <div class="overflow-x-auto"><table class="w-full">
                    <thead><tr class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wider border-b border-blue-50">
                        <th class="pb-3 pr-4">Tenant</th><th class="pb-3 pr-4">Amount</th><th class="pb-3 pr-4">Date</th><th class="pb-3">Status</th>
                    </tr></thead>
                    <tbody class="divide-y divide-blue-50">${(dashData.recentPayments||[]).map(p => `
                        <tr class="hover:bg-blue-50/30 transition-colors">
                            <td class="py-3 pr-4"><div class="flex items-center gap-2"><div class="w-7 h-7 rounded-full bg-gradient-to-br from-blue-50 to-blue-100 text-blue-600 flex items-center justify-center text-xs font-bold">${initials(p.tenant_name)}</div><span class="text-sm font-medium text-slate-900">${p.tenant_name||'N/A'}</span></div></td>
                            <td class="py-3 pr-4 text-sm font-medium text-slate-900">${fmtCurrency(p.amount)}</td>
                            <td class="py-3 pr-4 text-sm text-slate-500">${fmtDate(p.date)}</td>
                            <td class="py-3"><span class="px-2 py-0.5 rounded-full text-xs font-medium ${statusColor(p.status)}">${p.status}</span></td>
                        </tr>`).join('')}</tbody>
                </table></div>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-5">
                <h3 class="font-semibold text-slate-900 mb-4">Active Complaints</h3>
                <div class="space-y-3">${(dashData.activeComplaints||[]).slice(0,4).map(c => `
                    <div class="flex items-start gap-3 p-3 rounded-xl bg-blue-50/50 border border-blue-100/50">
                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-amber-50 to-amber-100 text-amber-600 flex items-center justify-center flex-shrink-0"><i class="fas fa-exclamation text-xs"></i></div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between mb-0.5"><p class="text-sm font-medium text-slate-900 truncate">${c.title}</p><span class="text-xs text-slate-400">${fmtDate(c.date)}</span></div>
                            <p class="text-xs text-slate-500 mb-1">${c.tenant_name||'N/A'} • ${c.unit||''}</p>
                            <span class="px-2 py-0.5 rounded text-xs font-medium ${statusColor(c.status)}">${c.status}</span>
                        </div>
                    </div>`).join('')}</div>
            </div>`;

        setTimeout(() => initCharts(dashData, payments), 100);
    }

    async function loadCaretakerDashboard() {
        const [props, houses, tenants, complaints, payments] = await Promise.all([
            apiRequest(`${API}/properties`),
            apiRequest(`${API}/houses`),
            apiRequest(`${API}/tenants`),
            apiRequest(`${API}/complaints`),
            apiRequest(`${API}/payments`)
        ]);
        document.getElementById('statsGrid').innerHTML = `
            <div class="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-5"><div class="flex items-center justify-between mb-3"><div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-50 to-blue-100 flex items-center justify-center"><i class="fas fa-building text-blue-600"></i></div></div><p class="text-2xl font-bold text-slate-900">${props.properties ? props.properties.length : 0}</p><p class="text-sm text-slate-500">Properties</p></div>
            <div class="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-5"><div class="flex items-center justify-between mb-3"><div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-50 to-emerald-100 flex items-center justify-center"><i class="fas fa-home text-emerald-600"></i></div></div><p class="text-2xl font-bold text-slate-900">${houses.houses ? houses.houses.length : 0}</p><p class="text-sm text-slate-500">Total Units</p></div>
            <div class="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-5"><div class="flex items-center justify-between mb-3"><div class="w-10 h-10 rounded-xl bg-gradient-to-br from-purple-50 to-purple-100 flex items-center justify-center"><i class="fas fa-users text-purple-600"></i></div></div><p class="text-2xl font-bold text-slate-900">${tenants.tenants ? tenants.tenants.length : 0}</p><p class="text-sm text-slate-500">Tenants</p></div>
            <div class="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-5"><div class="flex items-center justify-between mb-3"><div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-50 to-amber-100 flex items-center justify-center"><i class="fas fa-exclamation-triangle text-amber-600"></i></div></div><p class="text-2xl font-bold text-slate-900">${complaints.complaints ? complaints.complaints.filter(c => c.status === 'open' || c.status === 'in-progress').length : 0}</p><p class="text-sm text-slate-500">Open Complaints</p></div>`;
        document.getElementById('chartsRow').style.display = 'none';
        document.getElementById('activityRow').innerHTML = `
            <div class="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-5">
                <h3 class="font-semibold text-slate-900 mb-4">Recent Complaints</h3>
                <div class="space-y-3">${(complaints.complaints||[]).slice(0,5).map(c => `
                    <div class="flex items-start gap-3 p-3 rounded-xl bg-blue-50/50 border border-blue-100/50">
                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-amber-50 to-amber-100 text-amber-600 flex items-center justify-center flex-shrink-0"><i class="fas fa-exclamation text-xs"></i></div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between mb-0.5"><p class="text-sm font-medium text-slate-900 truncate">${c.title}</p><span class="px-2 py-0.5 rounded text-xs font-medium ${c.status==='open'?'bg-red-100 text-red-700':c.status==='in-progress'?'bg-blue-100 text-blue-700':'bg-emerald-100 text-emerald-700'}">${c.status}</span></div>
                            <p class="text-xs text-slate-500">${c.tenant_name||'N/A'} • ${c.unit||''}</p>
                        </div>
                    </div>`).join('')}</div>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-5">
                <h3 class="font-semibold text-slate-900 mb-4">Recent Payments</h3>
                <div class="overflow-x-auto"><table class="w-full">
                    <thead><tr class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wider border-b border-blue-50">
                        <th class="pb-3 pr-4">Tenant</th><th class="pb-3 pr-4">Amount</th><th class="pb-3 pr-4">Date</th><th class="pb-3">Status</th>
                    </tr></thead>
                    <tbody class="divide-y divide-blue-50">${(payments.payments||[]).slice(0,5).map(p => `
                        <tr class="hover:bg-blue-50/30 transition-colors">
                            <td class="py-3 pr-4 text-sm font-medium text-slate-900">${p.tenant_name||'N/A'}</td>
                            <td class="py-3 pr-4 text-sm font-medium text-slate-900">${fmtCurrency(p.amount)}</td>
                            <td class="py-3 pr-4 text-sm text-slate-500">${fmtDate(p.date)}</td>
                            <td class="py-3"><span class="px-2 py-0.5 rounded-full text-xs font-medium ${p.status==='paid'?'bg-emerald-100 text-emerald-700':'bg-amber-100 text-amber-700'}">${p.status}</span></td>
                        </tr>`).join('')}</tbody>
                </table></div>
            </div>`;
    }

    function initCharts(data, payments = []) {
        const h = data.houses || {};
        const trend = buildMonthlyTrend(payments);
        const revCtx = document.getElementById('revenueChart');
        if (revCtx) {
            new Chart(revCtx, {
                type: 'line',
                data: {
                    labels: trend.labels,
                    datasets: [{
                        label: 'Revenue',
                        data: trend.values,
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59,130,246,0.12)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.35,
                        pointRadius: 4,
                        pointBackgroundColor: '#3b82f6'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' }, ticks: { color: '#64748b' } },
                        x: { grid: { display: false }, ticks: { color: '#64748b' } }
                    }
                }
            });
        }
        const occCtx = document.getElementById('occChart');
        if (occCtx) {
            new Chart(occCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Occupied', 'Vacant'],
                    datasets: [{
                        data: [h.occupied||0, (h.total||0)-(h.occupied||0)],
                        backgroundColor: ['#3b82f6', '#e2e8f0'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '70%',
                    plugins: { legend: { display: false } }
                }
            });
        }
        document.getElementById('occLegend').textContent = (h.occupied||0) + ' units';
        document.getElementById('vacLegend').textContent = ((h.total||0)-(h.occupied||0)) + ' units';
    }

    setTimeout(() => {
        const s = document.getElementById('statsGrid');
        if (s && s.innerHTML.trim() === '') s.innerHTML = '<div class="col-span-full py-12 text-center text-red-400">Failed to load data.</div>';
    }, 3000);

    loadDashboard();
    </script>
</body>
</html>