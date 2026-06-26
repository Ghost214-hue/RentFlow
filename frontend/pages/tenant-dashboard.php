<?php
session_start();
$token = $_COOKIE['rf_token'] ?? $_SESSION['rf_token'] ?? null;
if (!$token) { header('Location: /signin'); exit; }
require_once __DIR__ . '/../../backend/app/Core/Env.php';
\App\Core\Env::load();
require_once __DIR__ . '/../../backend/app/Core/JWT.php';
$jwt = new \App\Core\JWT();
$user = $jwt->decode($token);
if (!$user) { header('Location: /signin'); exit; }
$_SESSION['rf_user'] = $user;
$role = $user['role'] ?? 'tenant';
if ($role !== 'tenant') { header('Location: /signin'); exit; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dashboard - RentFlow</title>
    <link rel="stylesheet" href="/css/output.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body class="bg-gradient-to-br from-blue-50 via-white to-blue-50/30 min-h-screen font-sans text-slate-800 flex flex-col lg:flex-row">
    <?php include __DIR__ . '/../public/components/sidebar.php'; ?>
    <div class="flex-1 flex flex-col min-h-screen">
        <?php include __DIR__ . '/../public/components/header.php'; ?>
        <main class="flex-1 overflow-y-auto p-4 lg:p-8">
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-slate-900">My Dashboard</h1>
                <p class="text-slate-500 mt-1">Welcome, <?php echo htmlspecialchars($user['name']); ?></p>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
                <div class="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-5">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-50 to-blue-100 flex items-center justify-center"><i class="fas fa-home text-blue-600"></i></div>
                    </div>
                    <p class="text-2xl font-bold text-slate-900" id="myUnit">-</p>
                    <p class="text-sm text-slate-500">My Unit</p>
                </div>
                <div class="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-5">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-50 to-amber-100 flex items-center justify-center"><i class="fas fa-file-invoice text-amber-600"></i></div>
                    </div>
                    <p class="text-2xl font-bold text-slate-900" id="myBalance">KES 0</p>
                    <p class="text-sm text-slate-500">Current Balance</p>
                </div>
                <div class="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-5">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-50 to-emerald-100 flex items-center justify-center"><i class="fas fa-check-circle text-emerald-600"></i></div>
                    </div>
                    <p class="text-2xl font-bold text-slate-900" id="myPaid">KES 0</p>
                    <p class="text-sm text-slate-500">Total Paid</p>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-5">
                    <h3 class="font-semibold text-slate-900 mb-4">Recent Payments</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead><tr class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wider border-b border-blue-50">
                                <th class="pb-3 pr-4">Date</th><th class="pb-3 pr-4">Amount</th><th class="pb-3 pr-4">Method</th><th class="pb-3">Status</th>
                            </tr></thead>
                            <tbody class="divide-y divide-blue-50" id="myPayments">
                                <tr><td colspan="4" class="py-8 text-center text-slate-400">Loading...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-5">
                    <h3 class="font-semibold text-slate-900 mb-4">My Complaints</h3>
                    <div class="space-y-3" id="myComplaints">
                        <div class="py-8 text-center text-slate-400">Loading...</div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <div id="toast" class="fixed bottom-6 right-6 z-50 hidden px-5 py-3 rounded-xl shadow-xl text-white font-medium flex items-center gap-2"></div>
    <script>
    const API = '/api';
    const token = localStorage.getItem('rf_token') || '<?php echo $token; ?>';
    const headers = token ? {'Authorization':'Bearer '+token, 'Content-Type':'application/json'} : {'Content-Type':'application/json'};

    function toast(msg, type='success') {
        const el = document.getElementById('toast');
        const colors = { success:'bg-gradient-to-r from-emerald-500 to-emerald-600', error:'bg-gradient-to-r from-red-500 to-red-600', info:'bg-gradient-to-r from-blue-500 to-blue-600' };
        const icons = { success:'fa-check-circle', error:'fa-exclamation-circle', info:'fa-info-circle' };
        el.className = `fixed bottom-6 right-6 z-50 px-5 py-3 rounded-xl shadow-xl text-white font-medium flex items-center gap-2 ${colors[type]||colors.success}`;
        el.innerHTML = `<i class="fas ${icons[type]||icons.success}"></i>${msg}`;
        el.classList.remove('hidden');
        setTimeout(() => el.classList.add('hidden'), 3000);
    }

    async function loadTenantDashboard() {
        try {
            // Load tenant profile
            const tenantRes = await fetch(`${API}/tenants`, { headers });
            const tenantData = await tenantRes.json();
            if (tenantData.tenants && tenantData.tenants.length > 0) {
                const me = tenantData.tenants[0]; // First tenant is current user
                document.getElementById('myUnit').textContent = me.house_unit || '-';
                document.getElementById('myBalance').textContent = 'KES ' + (me.balance || 0).toLocaleString();
            }

            // Load payments
            const payRes = await fetch(`${API}/payments`, { headers });
            const payData = await payRes.json();
            const payTbody = document.getElementById('myPayments');
            if (payData.payments && payData.payments.length) {
                payTbody.innerHTML = payData.payments.slice(0,5).map(p => `
                    <tr class="hover:bg-blue-50/30 transition-colors">
                        <td class="py-3 pr-4 text-sm text-slate-500">${new Date(p.date).toLocaleDateString('en-GB',{day:'numeric',month:'short',year:'numeric'})}</td>
                        <td class="py-3 pr-4 text-sm font-medium text-slate-900">KES ${(p.amount||0).toLocaleString()}</td>
                        <td class="py-3 pr-4 text-sm text-slate-500">${p.method||'N/A'}</td>
                        <td class="py-3"><span class="px-2 py-0.5 rounded-full text-xs font-medium ${p.status==='paid'?'bg-emerald-100 text-emerald-700':'bg-amber-100 text-amber-700'}">${p.status}</span></td>
                    </tr>
                `).join('');
            } else {
                payTbody.innerHTML = '<tr><td colspan="4" class="py-8 text-center text-slate-400">No payments yet</td></tr>';
            }

            // Load complaints
            const compRes = await fetch(`${API}/complaints`, { headers });
            const compData = await compRes.json();
            const compDiv = document.getElementById('myComplaints');
            if (compData.complaints && compData.complaints.length) {
                compDiv.innerHTML = compData.complaints.slice(0,4).map(c => `
                    <div class="flex items-start gap-3 p-3 rounded-xl bg-blue-50/50 border border-blue-100/50">
                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-amber-50 to-amber-100 text-amber-600 flex items-center justify-center flex-shrink-0"><i class="fas fa-exclamation text-xs"></i></div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between mb-0.5"><p class="text-sm font-medium text-slate-900 truncate">${c.title}</p><span class="px-2 py-0.5 rounded text-xs font-medium ${c.status==='open'?'bg-red-100 text-red-700':c.status==='in-progress'?'bg-blue-100 text-blue-700':'bg-emerald-100 text-emerald-700'}">${c.status}</span></div>
                            <p class="text-xs text-slate-400"><i class="far fa-clock mr-1"></i>${new Date(c.date).toLocaleDateString('en-GB',{day:'numeric',month:'short',year:'numeric'})}</p>
                        </div>
                    </div>
                `).join('');
            } else {
                compDiv.innerHTML = '<div class="py-8 text-center text-slate-400">No complaints</div>';
            }
        } catch(e) {
            console.error(e);
            if (e.message.includes('401')) window.location.href = '/signin';
        }
    }

    loadTenantDashboard();
    </script>
</body>
</html>