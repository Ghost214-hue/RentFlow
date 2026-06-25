<?php
session_start();
$token = $_COOKIE['rf_token'] ?? $_SESSION['rf_token'] ?? null;
if (!$token) { header('Location: /signin'); exit; }

// Verify token and get user
require_once __DIR__ . '/../../backend/app/Core/Env.php';
\App\Core\Env::load();
require_once __DIR__ . '/../../backend/app/Core/JWT.php';
$jwt = new \App\Core\JWT();
$user = $jwt->decode($token);
if (!$user) { header('Location: /signin'); exit; }

$_SESSION['rf_user'] = $user;
$role = $user['role'] ?? 'owner';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - RentFlow</title>
    <link rel="stylesheet" href="/css/output.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gradient-to-br from-blue-50 via-white to-blue-50/30 min-h-screen font-sans text-slate-800 flex overflow-hidden">
    <?php include __DIR__ . '/../public/components/sidebar.php'; ?>
    <div class="flex-1 flex flex-col min-h-screen">
        <?php include __DIR__ . '/../public/components/header.php'; ?>
        <main class="flex-1 overflow-y-auto p-4 lg:p-8">
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-slate-900">Dashboard</h1>
                <p class="text-slate-500 mt-1">Welcome back, <?php echo htmlspecialchars($user['name']); ?>. Here's your overview.</p>
            </div>

            <!-- Stats Grid - Different for each role -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6" id="statsGrid">
                <!-- Stats will be loaded by JavaScript based on role -->
            </div>

            <!-- Charts Row - Only for owner -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6" id="chartsRow" style="display: none;">
                <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-blue-100/50 p-5">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-semibold text-slate-900">Revenue Overview</h3>
                        <select class="text-sm border border-blue-100 rounded-xl px-3 py-1.5 bg-white text-slate-600 outline-none">
                            <option>Last 6 Months</option>
                        </select>
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

            <!-- Recent Activity -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6" id="activityRow">
                <!-- Activity will be loaded by JavaScript based on role -->
            </div>
        </main>
    </div>
    <div id="toast" class="fixed bottom-6 right-6 z-50 hidden px-5 py-3 rounded-xl shadow-xl text-white font-medium flex items-center gap-2"></div>
    <script>
    const API = '/api';
    const token = localStorage.getItem('rf_token') || '<?php echo $token; ?>';
    const headers = token ? {'Authorization':'Bearer '+token, 'Content-Type':'application/json'} : {'Content-Type':'application/json'};
    const userRole = '<?php echo $role; ?>';

    function fmtCurrency(n) { return 'KES ' + Number(n).toLocaleString(); }
    function fmtDate(d) { return new Date(d).toLocaleDateString('en-GB',{day:'numeric',month:'short',year:'numeric'}); }
    function initials(n) { return n.split(' ').map(s=>s[0]).join('').substring(0,2).toUpperCase(); }
    function statusColor(s) {
        const colors = {'paid':'bg-emerald-100 text-emerald-700','completed':'bg-emerald-100 text-emerald-700','partial':'bg-amber-100 text-amber-700','pending':'bg-slate-100 text-slate-600','open':'bg-red-100 text-red-700','in-progress':'bg-blue-100 text-blue-700','resolved':'bg-emerald-100 text-emerald-700'};
        return colors[s] || 'bg-slate-100 text-slate-600';
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
            console.log('Loading dashboard for role:', userRole);
            if (userRole === 'owner') {
                await loadOwnerDashboard();
            } else if (userRole === 'caretaker') {
                await loadCaretakerDashboard();
            } else if (userRole === 'tenant') {
                await loadTenantDashboard();
            }
            console.log('Dashboard loaded successfully');
        } catch(e) {
            console.error('Failed to load dashboard:', e);
            document.getElementById('statsGrid').innerHTML = '<div class="col-span-full py-12 text-center text-red-400">Failed to load dashboard. Error: ' + e.message + '</div>';
            if (e.message.includes('401')) window.location.href = '/signin';
        }
    }

    async function loadOwnerDashboard() {
        const res = await fetch(`${API}/dashboard`, { headers });
        const data = await res.json();
        const p = data.properties || {};
        const h = data.houses || {};

        // Stats
        const statsGrid = document.getElementById('statsGrid');
        statsGrid.innerHTML = `
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
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-50 to-emerald-100 flex items-center justify-center"><i class="fas fa-door-open text-emerald-600"></i></div>
                    <span class="text-xs font-medium text-emerald-600 bg-emerald-50 px-2 py-1 rounded-full">${h.total ? Math.round((h.occupied||0)/(h.total||1)*100)+'%' : '0%'}</span>
                </div>
                <p class="text-2xl font-bold text-slate-900">${h.occupied||0}/${h.total||0}</p>
                <p class="text-sm text-slate-500">Occupied Units</p>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-5">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-50 to-blue-100 flex items-center justify-center"><i class="fas fa-money-bill-wave text-blue-600"></i></div>
                    <span class="text-xs font-medium text-blue-600 bg-blue-50 px-2 py-1 rounded-full">This month</span>
                </div>
                <p class="text-2xl font-bold text-slate-900">${fmtCurrency(data.revenue||0)}</p>
                <p class="text-sm text-slate-500">Monthly Revenue</p>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-5">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-50 to-amber-100 flex items-center justify-center"><i class="fas fa-exclamation-circle text-amber-600"></i></div>
                    <span class="text-xs font-medium text-amber-600 bg-amber-50 px-2 py-1 rounded-full">Outstanding</span>
                </div>
                <p class="text-2xl font-bold text-slate-900">${fmtCurrency(data.outstanding||0)}</p>
                <p class="text-sm text-slate-500">Outstanding Rent</p>
            </div>
        `;

        // Show charts
        document.getElementById('chartsRow').style.display = 'grid';

        // Recent payments
        const activityRow = document.getElementById('activityRow');
        activityRow.innerHTML = `
            <div class="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-5">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-semibold text-slate-900">Recent Payments</h3>
                    <a href="/payments" class="text-sm text-blue-600 hover:text-blue-700 font-medium">View All</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead><tr class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wider border-b border-blue-50">
                            <th class="pb-3 pr-4">Tenant</th><th class="pb-3 pr-4">Amount</th><th class="pb-3 pr-4">Date</th><th class="pb-3">Status</th>
                        </tr></thead>
                        <tbody class="divide-y divide-blue-50" id="recentPayments">
                            ${(data.recentPayments||[]).map(p => `
                            <tr class="hover:bg-blue-50/30 transition-colors">
                                <td class="py-3 pr-4"><div class="flex items-center gap-2"><div class="w-7 h-7 rounded-full bg-gradient-to-br from-blue-50 to-blue-100 text-blue-600 flex items-center justify-center text-xs font-bold">${initials(p.tenant_name)}</div><span class="text-sm font-medium text-slate-900">${p.tenant_name||'N/A'}</span></div></td>
                                <td class="py-3 pr-4 text-sm font-medium text-slate-900">${fmtCurrency(p.amount)}</td>
                                <td class="py-3 pr-4 text-sm text-slate-500">${fmtDate(p.date)}</td>
                                <td class="py-3"><span class="px-2 py-0.5 rounded-full text-xs font-medium ${statusColor(p.status)}">${p.status}</span></td>
                            </tr>`).join('')}
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-5">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-semibold text-slate-900">Active Complaints</h3>
                    <span class="px-2 py-1 bg-red-50 text-red-600 text-xs font-medium rounded-full">${(data.activeComplaints||[]).length} Open</span>
                </div>
                <div class="space-y-3" id="activeComplaints">
                    ${(data.activeComplaints||[]).slice(0,4).map(c => `
                    <div class="flex items-start gap-3 p-3 rounded-xl bg-blue-50/50 border border-blue-100/50">
                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-amber-50 to-amber-100 text-amber-600 flex items-center justify-center flex-shrink-0"><i class="fas fa-exclamation text-xs"></i></div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between mb-0.5"><p class="text-sm font-medium text-slate-900 truncate">${c.title}</p><span class="text-xs text-slate-400">${fmtDate(c.date)}</span></div>
                            <p class="text-xs text-slate-500 mb-1">${c.tenant_name||'N/A'} • ${c.unit||''}</p>
                            <span class="px-2 py-0.5 rounded text-xs font-medium ${statusColor(c.status)}">${c.status}</span>
                        </div>
                    </div>`).join('')}
                </div>
            </div>
        `;

        // Init charts
        setTimeout(() => initCharts(data), 100);
    }

    async function loadCaretakerDashboard() {
        const [propRes, houseRes, tenantRes, compRes, payRes] = await Promise.all([
            fetch(`${API}/properties`, { headers }),
            fetch(`${API}/houses`, { headers }),
            fetch(`${API}/tenants`, { headers }),
            fetch(`${API}/complaints`, { headers }),
            fetch(`${API}/payments`, { headers }),
        ]);

        const props = await propRes.json();
        const houses = await houseRes.json();
        const tenants = await tenantRes.json();
        const complaints = await compRes.json();
        const payments = await payRes.json();

        // Stats
        const statsGrid = document.getElementById('statsGrid');
        statsGrid.innerHTML = `
            <div class="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-5">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-50 to-blue-100 flex items-center justify-center"><i class="fas fa-building text-blue-600"></i></div>
                </div>
                <p class="text-2xl font-bold text-slate-900">${props.properties ? props.properties.length : 0}</p>
                <p class="text-sm text-slate-500">Properties</p>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-5">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-50 to-emerald-100 flex items-center justify-center"><i class="fas fa-home text-emerald-600"></i></div>
                </div>
                <p class="text-2xl font-bold text-slate-900">${houses.houses ? houses.houses.length : 0}</p>
                <p class="text-sm text-slate-500">Total Units</p>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-5">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-purple-50 to-purple-100 flex items-center justify-center"><i class="fas fa-users text-purple-600"></i></div>
                </div>
                <p class="text-2xl font-bold text-slate-900">${tenants.tenants ? tenants.tenants.length : 0}</p>
                <p class="text-sm text-slate-500">Tenants</p>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-5">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-50 to-amber-100 flex items-center justify-center"><i class="fas fa-exclamation-triangle text-amber-600"></i></div>
                </div>
                <p class="text-2xl font-bold text-slate-900">${complaints.complaints ? complaints.complaints.filter(c => c.status === 'open' || c.status === 'in-progress').length : 0}</p>
                <p class="text-sm text-slate-500">Open Complaints</p>
            </div>
        `;

        // Hide charts for caretaker
        document.getElementById('chartsRow').style.display = 'none';

        // Recent activity
        const activityRow = document.getElementById('activityRow');
        activityRow.innerHTML = `
            <div class="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-5">
                <h3 class="font-semibold text-slate-900 mb-4">Recent Complaints</h3>
                <div class="space-y-3" id="recentComplaints">
                    ${(complaints.complaints||[]).slice(0,5).map(c => `
                    <div class="flex items-start gap-3 p-3 rounded-xl bg-blue-50/50 border border-blue-100/50">
                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-amber-50 to-amber-100 text-amber-600 flex items-center justify-center flex-shrink-0"><i class="fas fa-exclamation text-xs"></i></div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between mb-0.5"><p class="text-sm font-medium text-slate-900 truncate">${c.title}</p><span class="px-2 py-0.5 rounded text-xs font-medium ${c.status==='open'?'bg-red-100 text-red-700':c.status==='in-progress'?'bg-blue-100 text-blue-700':'bg-emerald-100 text-emerald-700'}">${c.status}</span></div>
                            <p class="text-xs text-slate-500">${c.tenant_name||'N/A'} • ${c.unit||''}</p>
                        </div>
                    </div>`).join('')}
                </div>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-5">
                <h3 class="font-semibold text-slate-900 mb-4">Recent Payments</h3>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead><tr class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wider border-b border-blue-50">
                            <th class="pb-3 pr-4">Tenant</th><th class="pb-3 pr-4">Amount</th><th class="pb-3 pr-4">Date</th><th class="pb-3">Status</th>
                        </tr></thead>
                        <tbody class="divide-y divide-blue-50" id="recentPayments">
                            ${(payments.payments||[]).slice(0,5).map(p => `
                            <tr class="hover:bg-blue-50/30 transition-colors">
                                <td class="py-3 pr-4 text-sm font-medium text-slate-900">${p.tenant_name||'N/A'}</td>
                                <td class="py-3 pr-4 text-sm font-medium text-slate-900">${fmtCurrency(p.amount)}</td>
                                <td class="py-3 pr-4 text-sm text-slate-500">${fmtDate(p.date)}</td>
                                <td class="py-3"><span class="px-2 py-0.5 rounded-full text-xs font-medium ${p.status==='paid'?'bg-emerald-100 text-emerald-700':'bg-amber-100 text-amber-700'}">${p.status}</span></td>
                            </tr>`).join('')}
                        </tbody>
                    </table>
                </div>
            </div>
        `;
    }

    async function loadTenantDashboard() {
        // Load tenant profile
        const tenantRes = await fetch(`${API}/tenants`, { headers });
        const tenantData = await tenantRes.json();
        
        // Load payments
        const payRes = await fetch(`${API}/payments`, { headers });
        const payData = await payRes.json();
        
        // Load complaints
        const compRes = await fetch(`${API}/complaints`, { headers });
        const compData = await compRes.json();

        const me = tenantData.tenants && tenantData.tenants[0] ? tenantData.tenants[0] : {};

        // Stats
        const statsGrid = document.getElementById('statsGrid');
        statsGrid.innerHTML = `
            <div class="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-5">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-50 to-blue-100 flex items-center justify-center"><i class="fas fa-home text-blue-600"></i></div>
                </div>
                <p class="text-2xl font-bold text-slate-900">${me.house_unit || '-'}</p>
                <p class="text-sm text-slate-500">My Unit</p>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-5">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-50 to-amber-100 flex items-center justify-center"><i class="fas fa-file-invoice text-amber-600"></i></div>
                </div>
                <p class="text-2xl font-bold text-slate-900">${fmtCurrency(me.balance || 0)}</p>
                <p class="text-sm text-slate-500">Current Balance</p>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-5">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-50 to-emerald-100 flex items-center justify-center"><i class="fas fa-check-circle text-emerald-600"></i></div>
                </div>
                <p class="text-2xl font-bold text-slate-900">${fmtCurrency(me.total_paid || 0)}</p>
                <p class="text-sm text-slate-500">Total Paid</p>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-5">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-purple-50 to-purple-100 flex items-center justify-center"><i class="fas fa-exclamation-triangle text-purple-600"></i></div>
                </div>
                <p class="text-2xl font-bold text-slate-900">${(compData.complaints||[]).length}</p>
                <p class="text-sm text-slate-500">My Complaints</p>
            </div>
        `;

        // Hide charts for tenant
        document.getElementById('chartsRow').style.display = 'none';

        // Recent activity
        const activityRow = document.getElementById('activityRow');
        activityRow.innerHTML = `
            <div class="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-5">
                <h3 class="font-semibold text-slate-900 mb-4">Recent Payments</h3>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead><tr class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wider border-b border-blue-50">
                            <th class="pb-3 pr-4">Date</th><th class="pb-3 pr-4">Amount</th><th class="pb-3 pr-4">Method</th><th class="pb-3">Status</th>
                        </tr></thead>
                        <tbody class="divide-y divide-blue-50" id="recentPayments">
                            ${(payData.payments||[]).slice(0,5).map(p => `
                            <tr class="hover:bg-blue-50/30 transition-colors">
                                <td class="py-3 pr-4 text-sm text-slate-500">${fmtDate(p.date)}</td>
                                <td class="py-3 pr-4 text-sm font-medium text-slate-900">${fmtCurrency(p.amount)}</td>
                                <td class="py-3 pr-4 text-sm text-slate-500">${p.method||'N/A'}</td>
                                <td class="py-3"><span class="px-2 py-0.5 rounded-full text-xs font-medium ${p.status==='paid'?'bg-emerald-100 text-emerald-700':'bg-amber-100 text-amber-700'}">${p.status}</span></td>
                            </tr>`).join('')}
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-5">
                <h3 class="font-semibold text-slate-900 mb-4">My Complaints</h3>
                <div class="space-y-3" id="myComplaints">
                    ${(compData.complaints||[]).slice(0,4).map(c => `
                    <div class="flex items-start gap-3 p-3 rounded-xl bg-blue-50/50 border border-blue-100/50">
                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-amber-50 to-amber-100 text-amber-600 flex items-center justify-center flex-shrink-0"><i class="fas fa-exclamation text-xs"></i></div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between mb-0.5"><p class="text-sm font-medium text-slate-900 truncate">${c.title}</p><span class="px-2 py-0.5 rounded text-xs font-medium ${c.status==='open'?'bg-red-100 text-red-700':c.status==='in-progress'?'bg-blue-100 text-blue-700':'bg-emerald-100 text-emerald-700'}">${c.status}</span></div>
                            <p class="text-xs text-slate-400"><i class="far fa-clock mr-1"></i>${fmtDate(c.date)}</p>
                        </div>
                    </div>`).join('')}
                </div>
            </div>
        `;
    }

    function initCharts(data) {
        const h = data.houses || {};
        const revCtx = document.getElementById('revenueChart');
        if (revCtx) {
            new Chart(revCtx, {
                type: 'line',
                data: {
                    labels: ['Aug','Sep','Oct','Nov','Dec','Jan'],
                    datasets: [{
                        label: 'Revenue',
                        data: [320000,335000,310000,350000,380000,395000],
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59,130,246,0.1)',
                        borderWidth: 2, fill: true, tension: 0.4,
                        pointRadius: 4, pointBackgroundColor: '#3b82f6'
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
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
                    labels: ['Occupied','Vacant'],
                    datasets: [{ data: [h.occupied||0, (h.total||0)-(h.occupied||0)], backgroundColor: ['#3b82f6', '#e2e8f0'], borderWidth: 0 }]
                },
                options: { responsive: true, maintainAspectRatio: false, cutout: '70%', plugins: { legend: { display: false } } }
            });
        }
        document.getElementById('occLegend').textContent = (h.occupied||0) + ' units';
        document.getElementById('vacLegend').textContent = ((h.total||0)-(h.occupied||0)) + ' units';
    }

    // Timeout fallback
    setTimeout(() => {
        const stats = document.getElementById('statsGrid');
        if (stats && stats.innerHTML.trim() === '') {
            stats.innerHTML = '<div class="col-span-full py-12 text-center text-red-400">Failed to load dashboard data. Please check browser console (F12) for errors and refresh.</div>';
        }
    }, 3000);

    loadDashboard();
    </script>
</body>
</html>