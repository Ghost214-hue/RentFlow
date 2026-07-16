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
$basePath = '/RentFlow';
$_SESSION['rf_user'] = $user;
$role = $user['role'] ?? 'caretaker';
if ($role !== 'caretaker') { header('Location: ../public/signin.php'); exit; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Caretaker Dashboard - RentFlow</title>
    <link rel="stylesheet" href="<?php echo $basePath; ?>/css/output.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body class="bg-gradient-to-br from-blue-50 via-white to-blue-50/30 min-h-screen font-sans text-slate-800 flex flex-col lg:flex-row">
    <?php include __DIR__ . '/../public/components/sidebar.php'; ?>
    <div class="flex-1 flex flex-col min-h-screen">
        <?php include __DIR__ . '/../public/components/header.php'; ?>
        <main class="flex-1 overflow-y-auto p-4 lg:p-8">
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-slate-900">Caretaker Dashboard</h1>
                <p class="text-slate-500 mt-1">Welcome, <?php echo htmlspecialchars($user['name']); ?></p>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div class="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-5">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-50 to-blue-100 flex items-center justify-center"><i class="fas fa-building text-blue-600"></i></div>
                    </div>
                    <p class="text-2xl font-bold text-slate-900" id="propCount">0</p>
                    <p class="text-sm text-slate-500">Properties</p>
                </div>
                <div class="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-5">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-50 to-emerald-100 flex items-center justify-center"><i class="fas fa-home text-emerald-600"></i></div>
                    </div>
                    <p class="text-2xl font-bold text-slate-900" id="houseCount">0</p>
                    <p class="text-sm text-slate-500">Total Units</p>
                </div>
                <div class="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-5">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-purple-50 to-purple-100 flex items-center justify-center"><i class="fas fa-users text-purple-600"></i></div>
                    </div>
                    <p class="text-2xl font-bold text-slate-900" id="tenantCount">0</p>
                    <p class="text-sm text-slate-500">Tenants</p>
                </div>
                <div class="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-5">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-50 to-amber-100 flex items-center justify-center"><i class="fas fa-exclamation-triangle text-amber-600"></i></div>
                    </div>
                    <p class="text-2xl font-bold text-slate-900" id="complaintCount">0</p>
                    <p class="text-sm text-slate-500">Open Complaints</p>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-5">
                    <h3 class="font-semibold text-slate-900 mb-4">Recent Complaints</h3>
                    <div class="space-y-3" id="recentComplaints">
                        <div class="py-8 text-center text-slate-400"><div class="flex flex-col items-center gap-3"><i class="fas fa-spinner fa-spin text-3xl text-blue-400"></i><span>Loading...</span></div></div>
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
                                <tr><td colspan="4" class="py-8 text-center text-slate-400"><div class="flex flex-col items-center gap-3"><i class="fas fa-spinner fa-spin text-3xl text-blue-400"></i><span>Loading...</span></div></td></tr>
                            </tbody>
                        </table>
                    </div>
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
                window.location.href = '../public/signin.php';
            }
            throw new Error(data.error || 'Request failed');
        }
        return data;
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

    async function loadCaretakerDashboard() {
        try {
            // Load each section independently so one failure doesn't break everything
            const results = await Promise.allSettled([
                apiRequest(`${API}/properties`),
                apiRequest(`${API}/tenants`),
                apiRequest(`${API}/complaints`),
                apiRequest(`${API}/payments`),
            ]);

            const [propsResult, tenantsResult, complaintsResult, paymentsResult] = results;
            
            // Properties count
            const propCount = propsResult.status === 'fulfilled' && propsResult.value.properties ? propsResult.value.properties.length : 0;
            document.getElementById('propCount').textContent = propCount;
            document.getElementById('houseCount').textContent = '0';
            
            // Tenants count
            const tenantCount = tenantsResult.status === 'fulfilled' && tenantsResult.value.tenants ? tenantsResult.value.tenants.length : 0;
            document.getElementById('tenantCount').textContent = tenantCount;
            
            // Complaints count
            let complaintCount = 0;
            if (complaintsResult.status === 'fulfilled' && complaintsResult.value.complaints) {
                complaintCount = complaintsResult.value.complaints.filter(c => c.status === 'open' || c.status === 'in-progress').length;
            }
            document.getElementById('complaintCount').textContent = complaintCount;

            // Recent complaints
            const compDiv = document.getElementById('recentComplaints');
            if (complaintsResult.status === 'fulfilled' && complaintsResult.value.complaints && complaintsResult.value.complaints.length) {
                compDiv.innerHTML = complaintsResult.value.complaints.slice(0,5).map(c => `
                    <div class="flex items-start gap-3 p-3 rounded-xl bg-blue-50/50 border border-blue-100/50">
                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-amber-50 to-amber-100 text-amber-600 flex items-center justify-center flex-shrink-0"><i class="fas fa-exclamation text-xs"></i></div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between mb-0.5"><p class="text-sm font-medium text-slate-900 truncate">${c.title}</p><span class="px-2 py-0.5 rounded text-xs font-medium ${c.status==='open'?'bg-red-100 text-red-700':c.status==='in-progress'?'bg-blue-100 text-blue-700':'bg-emerald-100 text-emerald-700'}">${c.status}</span></div>
                            <p class="text-xs text-slate-500">${c.tenant_name||'N/A'} • ${c.unit||''}</p>
                        </div>
                    </div>
                `).join('');
            } else {
                compDiv.innerHTML = '<div class="py-8 text-center text-slate-400">No complaints</div>';
            }

            // Recent payments
            const payTbody = document.getElementById('recentPayments');
            if (paymentsResult.status === 'fulfilled' && paymentsResult.value.payments && paymentsResult.value.payments.length) {
                payTbody.innerHTML = paymentsResult.value.payments.slice(0,5).map(p => `
                    <tr class="hover:bg-blue-50/30 transition-colors">
                        <td class="py-3 pr-4 text-sm font-medium text-slate-900">${p.tenant_name||'N/A'}</td>
                        <td class="py-3 pr-4 text-sm font-medium text-slate-900">KES ${(p.amount||0).toLocaleString()}</td>
                        <td class="py-3 pr-4 text-sm text-slate-500">${new Date(p.date).toLocaleDateString('en-GB',{day:'numeric',month:'short',year:'numeric'})}</td>
                        <td class="py-3"><span class="px-2 py-0.5 rounded-full text-xs font-medium ${p.status==='paid'?'bg-emerald-100 text-emerald-700':'bg-amber-100 text-amber-700'}">${p.status}</span></td>
                    </tr>
                `).join('');
            } else {
                payTbody.innerHTML = '<tr><td colspan="4" class="py-8 text-center text-slate-400">No payments</td></tr>';
            }
        } catch(e) {
            console.error('Dashboard load error:', e);
        }
    }

    loadCaretakerDashboard();
    </script>
</body>
</html>