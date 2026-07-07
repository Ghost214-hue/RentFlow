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
$role = $user['role'] ?? 'owner';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bills - RentFlow</title>
    <link rel="stylesheet" href="/css/output.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body class="bg-gradient-to-br from-blue-50 via-white to-blue-50/30 min-h-screen font-sans text-slate-800 flex flex-col lg:flex-row">
    <?php include __DIR__ . '/../public/components/sidebar.php'; ?>
    <div class="flex-1 flex flex-col min-h-screen">
        <?php include __DIR__ . '/../public/components/header.php'; ?>
        <main class="flex-1 overflow-y-auto p-4 lg:p-8">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                <div class="flex items-center gap-3">
                    <select id="monthFilter" class="px-3 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all text-sm">
                        <option value="<?php echo date('Y-m'); ?>"><?php echo date('F Y'); ?></option>
                    </select>
                    <select id="propertyFilter" class="px-3 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all text-sm">
                        <option value="">All Properties</option>
                    </select>
                </div>
                <button onclick="generateBills()" class="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold rounded-xl shadow-lg shadow-blue-500/30 hover:shadow-blue-500/40 transition-all inline-flex items-center gap-2"><i class="fas fa-file-invoice"></i>Generate Bills</button>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border border-blue-100/50 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead><tr class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wider border-b border-blue-50 bg-blue-50/50">
                            <th class="px-6 py-4">Tenant</th><th class="px-6 py-4">Unit</th><th class="px-6 py-4">Month</th><th class="px-6 py-4">Amount</th><th class="px-6 py-4">Paid</th><th class="px-6 py-4">Balance</th><th class="px-6 py-4">Status</th>
                        </tr></thead>
                        <tbody class="divide-y divide-blue-50" id="billsTable">
                            <tr><td colspan="8" class="px-6 py-12 text-center text-slate-400">Loading...</td></tr>
                        </tbody>
                    </table>
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

    async function loadBills() {
        try {
            const url = new URL(`${API}/bills`, window.location.origin);
            const propertySelect = document.getElementById('propertyFilter');
            if (propertySelect && propertySelect.value) url.searchParams.set('property_id', propertySelect.value);
            const monthInput = document.getElementById('monthFilter');
            if (monthInput && monthInput.value) url.searchParams.set('month', monthInput.value);
            else url.searchParams.set('month', new Date().toISOString().substring(0,7));

            const res = await fetch(url.pathname + url.search, { headers });
            const data = await res.json();
            const tbody = document.getElementById('billsTable');
            if (data.bills && data.bills.length) {
                tbody.innerHTML = data.bills.map(b => `
                    <tr class="hover:bg-blue-50/30 transition-colors">
                        <td class="px-6 py-4 text-sm font-medium text-slate-900">${(b.tenant_name||'N/A')}</td>
                        <td class="px-6 py-4 text-sm text-slate-600">${b.unit||'N/A'}</td>
                        <td class="px-6 py-4 text-sm text-slate-500">${b.month||'N/A'}</td>
                        <td class="px-6 py-4 text-sm font-bold text-slate-900">KES ${(b.amount||0).toLocaleString()}</td>
                        <td class="px-6 py-4 text-sm text-emerald-600 font-medium">KES ${(b.paid||0).toLocaleString()}</td>
                        <td class="px-6 py-4 text-sm font-medium ${b.balance > 0 ? 'text-amber-600' : 'text-emerald-600'}">KES ${(b.balance||0).toLocaleString()}</td>
                        <td class="px-6 py-4"><span class="px-2 py-1 rounded-full text-xs font-medium ${b.status==='paid'?'bg-emerald-100 text-emerald-700':b.status==='partial'?'bg-amber-100 text-amber-700':'bg-slate-100 text-slate-600'}">${b.status}</span></td>
                        <td class="px-6 py-4"></td>
                    </tr>
                `).join('');
            } else {
                tbody.innerHTML = '<tr><td colspan="7" class="px-6 py-12 text-center text-slate-400">No bills found</td></tr>';
            }
        } catch(e) { console.error(e); if (e.message.includes('401')) window.location.href = '/signin'; }
    }

    async function generateBills() {
        const month = document.getElementById('monthFilter')?.value || new Date().toISOString().substring(0,7);
        const propertyId = document.getElementById('propertyFilter')?.value || null;
        const body = propertyId ? JSON.stringify({month, property_id: Number(propertyId)}) : JSON.stringify({month});
        try {
            const res = await fetch(`${API}/bills/generate`, { method:'POST', headers, body });
            const result = await res.json();
            if(!res.ok) throw new Error(result.error || 'Failed');
            toast('Bills generated!');
            loadBills();
            if (result.summary && result.summary.length) showArrearsSummary(result.summary);
        } catch(err) { toast(err.message, 'error'); }
    }

    function showArrearsSummary(summary) {
        const paid = summary.filter(s => s.status === 'paid');
        const partial = summary.filter(s => s.status === 'partial');
        const pending = summary.filter(s => s.status === 'pending');

        const rows = summary.map(s => {
            let badge = '';
            if (s.status === 'paid') badge = '<span class="px-2 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">Paid</span>';
            else if (s.status === 'partial') badge = '<span class="px-2 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-700">Partial</span>';
            else badge = '<span class="px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-700">Not Paid</span>';
            return `<tr class="border-b border-slate-200">
              <td class="px-3 py-3 text-sm font-medium text-slate-900">${s.tenant_name}</td>
              <td class="px-3 py-3 text-sm text-slate-600">${s.unit}</td>
              <td class="px-3 py-3 text-sm text-right font-medium">KES ${s.expected.toLocaleString()}</td>
              <td class="px-3 py-3 text-sm text-right text-emerald-700 font-medium">KES ${s.paid.toLocaleString()}</td>
              <td class="px-3 py-3 text-sm text-right font-medium ${s.arrears > 0 ? 'text-red-700' : 'text-emerald-700'}">KES ${s.arrears.toLocaleString()}</td>
              <td class="px-3 py-3 text-sm text-center">${badge}</td>
            </tr>`;
        }).join('');

        const title = summary[0]?.property ? `Rent Follow-up Summary - ${summary[0].property}` : 'Rent Follow-up Summary';
        const subtitle = [summary[0]?.unit ? `Unit ${summary[0].unit}` : '', new Date().toLocaleDateString()].filter(Boolean).join('  ·  ');

        const html = `<!DOCTYPE html><html><head><title>${title}</title><link rel="stylesheet" href="/css/output.css"></head><body class="bg-slate-50 p-6">
          <div class="max-w-4xl mx-auto bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
              <div>
                <h1 class="text-xl font-bold text-slate-900">${title}</h1>
                <p class="text-sm text-slate-500 mt-1">${subtitle}</p>
              </div>
              <button onclick="window.print()" class="px-4 py-2 bg-blue-600 text-white rounded-xl text-sm font-semibold shadow-sm">Print / Save PDF</button>
            </div>
            <div class="overflow-x-auto">
              <table class="w-full text-left">
                <thead>
                  <tr class="bg-slate-50 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                    <th class="px-6 py-3">Tenant</th>
                    <th class="px-6 py-3">Unit</th>
                    <th class="px-6 py-3 text-right">Expected</th>
                    <th class="px-6 py-3 text-right">Paid</th>
                    <th class="px-6 py-3 text-right">Arrears</th>
                    <th class="px-6 py-3 text-center">Status</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">${rows}</tbody>
              </table>
            </div>
            <div class="px-6 py-4 border-t border-slate-100 text-xs text-slate-500">Generated by RentFlow on ${new Date().toLocaleString()}</div>
          </div>
        </body></html>`;

        const w = window.open('', '_blank', 'width=1000,height=800');
        if (!w) { alert(summary.map(s => `${s.tenant_name}: Paid=${s.paid}, Arrears=${s.arrears}`).join('\n')); return; }
        w.document.write(html);
        w.document.close();
    }

    function exportBillPdf(id, tenantName, month) {
        const url = `${API}/bills/${id}/pdf?token=${encodeURIComponent(token)}`;
        const w = window.open(url, '_blank', 'width=900,height=700');
        if (!w) { toast('Popup blocked. Please allow popups for PDF export.', 'error'); }
    }

    async function loadProperties() {
        try {
            const res = await fetch(`${API}/properties`, { headers });
            const data = await res.json();
            const select = document.getElementById('propertyFilter');
            if (select && data.properties) {
                data.properties.forEach(p => {
                    const opt = document.createElement('option');
                    opt.value = p.id;
                    opt.textContent = p.name;
                    select.appendChild(opt);
                });
            }
        } catch(e) { console.error('Failed to load properties', e); }
    }

    document.getElementById('monthFilter')?.addEventListener('change', loadBills);
    document.getElementById('propertyFilter')?.addEventListener('change', loadBills);

    loadBills();
    loadProperties();
    </script>
</body>
</html>