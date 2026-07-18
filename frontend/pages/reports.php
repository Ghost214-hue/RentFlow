<?php
session_start();
$token = $_COOKIE['rf_token'] ?? $_SESSION['rf_token'] ?? null;
if (!$token) { header('Location: ../public/signin.php'); exit; }
require_once __DIR__ . '/../../backend/app/Core/Env.php';
\App\Core\Env::load();
require_once __DIR__ . '/../../backend/app/Core/JWT.php';
$jwt = new \App\Core\JWT();
$user = $jwt->decode($token);
if (!$user) { header('Location: ../public/signin.php'); exit; }
$_SESSION['rf_user'] = $user;
$role = $user['role'] ?? 'owner';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - RentaFlow</title>
    <link rel="stylesheet" href="<?php echo $basePath; ?>/css/output.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gradient-to-br from-blue-50 via-white to-blue-50/30 min-h-screen font-sans text-slate-800 flex flex-col lg:flex-row">
    <?php include __DIR__ . '/../public/components/sidebar.php'; ?>
    <div class="flex-1 flex flex-col min-h-screen">
        <?php include __DIR__ . '/../public/components/header.php'; ?>
        <main class="flex-1 overflow-y-auto p-4 lg:p-8">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                <div><h1 class="text-2xl font-bold text-slate-900">Reports</h1><p class="text-slate-500 mt-1">Analytics and financial reports</p></div>
                <button onclick="toast('Report exported','success')" class="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold rounded-xl shadow-lg shadow-blue-500/30 hover:shadow-blue-500/40 transition-all inline-flex items-center gap-2"><i class="fas fa-download"></i>Export Report</button>
            </div>
            
            <!-- Summary Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6" id="summaryCards">
                <div class="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-5">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-50 to-blue-100 flex items-center justify-center"><i class="fas fa-dollar-sign text-blue-600"></i></div>
                    </div>
                    <p class="text-2xl font-bold text-slate-900" id="totalRevenue">KES 0</p>
                    <p class="text-sm text-slate-500">Total Revenue</p>
                </div>
                <div class="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-5">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-50 to-emerald-100 flex items-center justify-center"><i class="fas fa-check-circle text-emerald-600"></i></div>
                    </div>
                    <p class="text-2xl font-bold text-slate-900" id="totalCollected">KES 0</p>
                    <p class="text-sm text-slate-500">Amount Collected</p>
                </div>
                <div class="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-5">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-50 to-amber-100 flex items-center justify-center"><i class="fas fa-exclamation-circle text-amber-600"></i></div>
                    </div>
                    <p class="text-2xl font-bold text-slate-900" id="totalOutstanding">KES 0</p>
                    <p class="text-sm text-slate-500">Outstanding</p>
                </div>
                <div class="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-5">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-purple-50 to-purple-100 flex items-center justify-center"><i class="fas fa-percentage text-purple-600"></i></div>
                    </div>
                    <p class="text-2xl font-bold text-slate-900" id="collectionRate">0%</p>
                    <p class="text-sm text-slate-500">Collection Rate</p>
                </div>
            </div>

            <!-- Charts -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <div class="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-5">
                    <h3 class="font-semibold text-slate-900 mb-4">Revenue Trend</h3>
                    <div class="relative h-72"><canvas id="revenueTrendChart"></canvas></div>
                </div>
                <div class="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-5">
                    <h3 class="font-semibold text-slate-900 mb-4">Payment Status Distribution</h3>
                    <div class="relative h-72"><canvas id="paymentDistChart"></canvas></div>
                </div>
            </div>

            <!-- Recent Transactions -->
            <div class="bg-white rounded-2xl shadow-sm border border-blue-100/50 overflow-hidden">
                <div class="px-6 py-4 border-b border-blue-50">
                    <h3 class="font-semibold text-slate-900">Recent Transactions</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead><tr class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wider border-b border-blue-50 bg-blue-50/50">
                            <th class="px-6 py-4">Date</th><th class="px-6 py-4">Tenant</th><th class="px-6 py-4">Type</th><th class="px-6 py-4">Amount</th><th class="px-6 py-4">Status</th>
                        </tr></thead>
                        <tbody class="divide-y divide-blue-50" id="transactionsTable">
                            <tr><td colspan="5" class="px-6 py-12 text-center text-slate-400"><div class="flex flex-col items-center gap-3"><i class="fas fa-spinner fa-spin text-3xl text-blue-400"></i><span>Loading transactions...</span></div></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
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

    async function apiRequest(url, options = {}) {
        const res = await fetch(url, { ...options, headers });
        const text = await res.text();
        let data;
        try { data = JSON.parse(text); } catch(e) { throw new Error('Server error'); }
        
        if (!res.ok) {
            if (res.status === 401) {
                localStorage.removeItem('rf_token');
                window.location.href = 'signin';
            }
            throw new Error(data.error || 'Request failed');
        }
        return data;
    }

    function fmtCurrency(n) { return 'KES ' + Number(n).toLocaleString(); }
    function toast(msg, type='success') {
        const el = document.getElementById('toast');
        const colors = { success:'bg-gradient-to-r from-emerald-500 to-emerald-600', error:'bg-gradient-to-r from-red-500 to-red-600', info:'bg-gradient-to-r from-blue-500 to-blue-600' };
        const icons = { success:'fa-check-circle', error:'fa-exclamation-circle', info:'fa-info-circle' };
        el.className = `fixed bottom-6 right-6 z-50 px-5 py-3 rounded-xl shadow-xl text-white font-medium flex items-center gap-2 ${colors[type]||colors.success}`;
        el.innerHTML = `<i class="fas ${icons[type]||icons.success}"></i>${msg}`;
        el.classList.remove('hidden');
        setTimeout(() => el.classList.add('hidden'), 3000);
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
            const normalized = (p.status || '').toLowerCase();
            if (!['completed', 'paid', 'confirmed'].includes(normalized)) return;
            totals[month] += Number(p.amount || 0);
        });
        return { labels: months.map(formatMonthLabel), values: months.map(month => totals[month] || 0) };
    }

    function buildStatusDistribution(payments) {
        const counts = { paid: 0, partial: 0, pending: 0, other: 0 };
        payments.forEach(p => {
            const status = (p.status || '').toLowerCase();
            if (status === 'paid' || status === 'completed' || status === 'confirmed') counts.paid += 1;
            else if (status === 'partial') counts.partial += 1;
            else if (status === 'pending') counts.pending += 1;
            else counts.other += 1;
        });
        return [counts.paid, counts.partial, counts.pending, counts.other];
    }

    async function loadReports() {
        try {
            const currentMonth = new Date().toISOString().slice(0, 7);
            const [paymentsData, billsData] = await Promise.all([
                apiRequest(`${API}/payments`),
                apiRequest(`${API}/bills?month=${currentMonth}`)
            ]);
            const payments = paymentsData.payments || [];
            const bills = billsData.bills || [];

            const totalRevenue = payments.reduce((sum, p) => sum + Number(p.amount || 0), 0);
            const collected = payments.filter(p => ['completed','paid','confirmed'].includes((p.status || '').toLowerCase())).reduce((sum, p) => sum + Number(p.amount || 0), 0);
            const outstanding = bills.reduce((sum, b) => sum + Number(b.balance || 0), 0);
            const billTotal = bills.reduce((sum, b) => sum + Number(b.total || 0), 0);
            const collectionRate = billTotal ? Math.round((collected / billTotal) * 100) : 0;

            document.getElementById('totalRevenue').textContent = fmtCurrency(totalRevenue);
            document.getElementById('totalCollected').textContent = fmtCurrency(collected);
            document.getElementById('totalOutstanding').textContent = fmtCurrency(outstanding);
            document.getElementById('collectionRate').textContent = collectionRate + '%';

            const tbody = document.getElementById('transactionsTable');
            if (payments.length) {
                tbody.innerHTML = payments.slice(0, 10).map(p => `
                    <tr class="hover:bg-blue-50/30 transition-colors">
                        <td class="px-6 py-4 text-sm text-slate-500">${p.date ? new Date(p.date).toLocaleDateString('en-GB',{day:'numeric',month:'short',year:'numeric'}) : 'N/A'}</td>
                        <td class="px-6 py-4"><div class="flex items-center gap-2"><div class="w-7 h-7 rounded-full bg-gradient-to-br from-blue-50 to-blue-100 text-blue-600 flex items-center justify-center text-xs font-bold">${(p.tenant_name||'??').split(' ').map(s => s[0]).join('').substring(0,2).toUpperCase()}</div><span class="text-sm font-medium text-slate-900">${p.tenant_name||'N/A'}</span></div></td>
                        <td class="px-6 py-4 text-sm text-slate-500">${p.type||'Rent'}</td>
                        <td class="px-6 py-4 text-sm font-medium text-slate-900">${fmtCurrency(p.amount)}</td>
                        <td class="px-6 py-4"><span class="px-2 py-0.5 rounded-full text-xs font-medium ${['completed','paid','confirmed'].includes((p.status||'').toLowerCase()) ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700'}">${p.status || 'pending'}</span></td>
                    </tr>
                `).join('');
            } else {
                tbody.innerHTML = '<tr><td colspan="5" class="px-6 py-12 text-center text-slate-400">No transactions yet</td></tr>';
            }

            initCharts(payments);
        } catch(e) {
            console.error(e);
            toast('Unable to load reports. Please try again.', 'error');
            document.getElementById('transactionsTable').innerHTML = '<tr><td colspan="5" class="px-6 py-12 text-center text-slate-400">Unable to load transactions.</td></tr>';
        }
    }

    function initCharts(payments) {
        const trend = buildMonthlyTrend(payments);
        const revCtx = document.getElementById('revenueTrendChart');
        if (revCtx) {
            new Chart(revCtx, {
                type: 'bar',
                data: {
                    labels: trend.labels,
                    datasets: [{
                        label: 'Revenue',
                        data: trend.values,
                        backgroundColor: 'rgba(59,130,246,0.7)',
                        borderColor: '#3b82f6',
                        borderWidth: 1,
                        borderRadius: 6,
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

        const distCtx = document.getElementById('paymentDistChart');
        if (distCtx) {
            new Chart(distCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Collected', 'Partial', 'Pending', 'Other'],
                    datasets: [{
                        data: buildStatusDistribution(payments),
                        backgroundColor: ['#3b82f6', '#f59e0b', '#fbbf24', '#e2e8f0'],
                        borderWidth: 0,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '65%',
                    plugins: { legend: { position: 'bottom' } }
                }
            });
        }
    }

    loadReports();
    </script>
</body>
</html>