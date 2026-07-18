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
    <title>Bills - RentaFlow</title>
    <link rel="stylesheet" href="<?php echo $basePath; ?>/css/output.css">
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
                            <tr id="loadingRow"><td colspan="8" class="px-6 py-12 text-center text-slate-400"><div class="flex flex-col items-center gap-3"><i class="fas fa-spinner fa-spin text-3xl text-blue-400"></i><span>Loading bills...</span></div></td></tr>
                        </tbody>
                    </table>
                </div>
                <div id="billsPager" class="p-4"></div>
            </div>
            <?php include __DIR__ . '/../public/components/pagination.php'; ?>
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
    const BILLS_PER_PAGE_KEY = 'rf_bills_per_page';
    const BILLS_PER_PAGE_DEFAULT = 25;

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

    function toast(msg, type='success') {
        const el = document.getElementById('toast');
        const colors = { success:'bg-gradient-to-r from-emerald-500 to-emerald-600', error:'bg-gradient-to-r from-red-500 to-red-600', info:'bg-gradient-to-r from-blue-500 to-blue-600' };
        const icons = { success:'fa-check-circle', error:'fa-exclamation-circle', info:'fa-info-circle' };
        el.className = `fixed bottom-6 right-6 z-50 px-5 py-3 rounded-xl shadow-xl text-white font-medium flex items-center gap-2 ${colors[type]||colors.success}`;
        el.innerHTML = `<i class="fas ${icons[type]||icons.success}"></i>${msg}`;
        el.classList.remove('hidden');
        setTimeout(() => el.classList.add('hidden'), 3000);
    }

    async function loadBills(page = 1, perPage = window.getSavedPerPage(BILLS_PER_PAGE_KEY, BILLS_PER_PAGE_DEFAULT)) {
        try {
            const url = new URL(`${API}/bills`, window.location.origin);
            const propertySelect = document.getElementById('propertyFilter');
            if (propertySelect && propertySelect.value) url.searchParams.set('property_id', propertySelect.value);
            const monthInput = document.getElementById('monthFilter');
            if (monthInput && monthInput.value) url.searchParams.set('month', monthInput.value);
            else url.searchParams.set('month', new Date().toISOString().substring(0,7));
            url.searchParams.set('page', page);
            url.searchParams.set('per_page', perPage);

            const data = await apiRequest(url.pathname + url.search);
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
                        <td class="px-6 py-4">
                            <button onclick="downloadInvoice(${b.id}, '${(b.tenant_name||'').replace(/'/g, "\\'")}', '${(b.month||'').replace(/'/g, "\\'")}')" class="text-blue-600 hover:text-blue-800 text-sm font-medium inline-flex items-center gap-1">
                                <i class="fas fa-download"></i> Invoice
                            </button>
                        </td>
                    </tr>
                `).join('');
            } else {
                tbody.innerHTML = '<tr><td colspan="7" class="px-6 py-12 text-center text-slate-400">No bills found</td></tr>';
            }
            // Render pagination if meta available
            if (data.meta) {
                renderPagination('billsPager', data.meta, (p) => loadBills(p, perPage), {
                    perPageKey: BILLS_PER_PAGE_KEY,
                    defaultPerPage: BILLS_PER_PAGE_DEFAULT,
                    onPerPageChange: (newPerPage) => loadBills(1, newPerPage),
                });
            }
        } catch(e) { console.error(e); }
    }

    async function generateBills() {
        const month = document.getElementById('monthFilter')?.value || new Date().toISOString().substring(0,7);
        const propertyId = document.getElementById('propertyFilter')?.value || null;
        const body = propertyId ? JSON.stringify({month, property_id: Number(propertyId)}) : JSON.stringify({month});
        try {
            const result = await apiRequest(`${API}/bills/generate`, { method:'POST', body });
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

        const html = `<!DOCTYPE html><html><head><title>${title}</title><link rel="stylesheet" href="../css/output.css"></head><body class="bg-slate-50 p-6">
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
            <div class="px-6 py-4 border-t border-slate-100 text-xs text-slate-500">Generated by RentaFlow on ${new Date().toLocaleString()}</div>
          </div>
        </body></html>`;

        const w = window.open('', '_blank', 'width=1000,height=800');
        if (!w) { alert(summary.map(s => `${s.tenant_name}: Paid=${s.paid}, Arrears=${s.arrears}`).join('\n')); return; }
        w.document.write(html);
        w.document.close();
    }

    function downloadInvoice(billId, tenantName, month) {
        const url = `${API}/bills/${billId}/invoice?token=${encodeURIComponent(token)}`;
        const w = window.open(url, '_blank', 'width=900,height=700');
        if (!w) { toast('Popup blocked. Please allow popups for PDF export.', 'error'); }
    }

    function exportBillPdf(id, tenantName, month) {
        const url = `${API}/bills/${id}/pdf?token=${encodeURIComponent(token)}`;
        const w = window.open(url, '_blank', 'width=900,height=700');
        if (!w) { toast('Popup blocked. Please allow popups for PDF export.', 'error'); }
    }

    async function loadProperties() {
        try {
            const data = await apiRequest(`${API}/properties`);
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