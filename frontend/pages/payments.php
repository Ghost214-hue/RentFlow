:
<?php
session_start();
$token = $_COOKIE['rf_token'] ?? $_SESSION['rf_token'] ?? null;
if (!$token) { header('Location: ../public/signin.php'); exit; }
require_once __DIR__ . '/../../backend/app/Core/Env.php';
// Load correct .env for localhost vs production
$isLocalhost = in_array($_SERVER['HTTP_HOST'] ?? '', ['localhost', '127.0.0.1'], true) ||
               str_starts_with($_SERVER['HTTP_HOST'] ?? '', 'localhost:');
$envPath = $isLocalhost
    ? __DIR__ . '/../../.env'
    : (file_exists(__DIR__ . '/../../.env.production') ? __DIR__ . '/../../.env.production' : __DIR__ . '/../../.env');
\App\Core\Env::load($envPath);
require_once __DIR__ . '/../../backend/app/Core/JWT.php';
$jwt = new \App\Core\JWT();
$user = $jwt->decode($token);
if (!$user) { header('Location: ../public/signin.php'); exit; }
$_SESSION['rf_user'] = $user;
$role = $user['role'] ?? 'owner';
require_once __DIR__ . '/../includes/base-path-fix.php';
$basePath = getBasePath();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payments - RentaFlow</title>
    <link rel="stylesheet" href="<?php echo htmlspecialchars($basePath); ?>/css/output.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body class="bg-gradient-to-br from-blue-50 via-white to-blue-50/30 min-h-screen font-sans text-slate-800 flex flex-col lg:flex-row">
    <?php include __DIR__ . '/../public/components/sidebar.php'; ?>
    <div class="flex-1 flex flex-col min-h-screen">
        <?php include __DIR__ . '/../public/components/header.php'; ?>
        <main class="flex-1 overflow-y-auto p-4 lg:p-8">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                <div><h1 class="text-2xl font-bold text-slate-900">Payments</h1><p class="text-slate-500 mt-1">All payment records</p></div>
                <?php if ($role === 'owner' || $role === 'caretaker'): ?>
                <button onclick="openModal()" class="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold rounded-xl shadow-lg shadow-blue-500/30 hover:shadow-blue-500/40 transition-all inline-flex items-center gap-2"><i class="fas fa-plus"></i>Record Payment</button>
                <?php endif; ?>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border border-blue-100/50 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead><tr class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wider border-b border-blue-50 bg-blue-50/50">
                            <th class="px-6 py-4">Receipt</th><th class="px-6 py-4">Tenant</th><th class="px-6 py-4">Description</th><th class="px-6 py-4">Amount</th><th class="px-6 py-4">Method</th><th class="px-6 py-4">Date</th><th class="px-6 py-4">Status</th><th class="px-6 py-4">Confirmed</th>
                        </tr></thead>
                        <tbody class="divide-y divide-blue-50" id="paymentsTable">
                            <tr id="loadingRow"><td colspan="8" class="px-6 py-12 text-center text-slate-400"><div class="flex flex-col items-center gap-3"><i class="fas fa-spinner fa-spin text-3xl text-blue-400"></i><span>Loading payments...</span></div></td></tr>
                        </tbody>
                    </table>
                </div>
                <div id="paymentsPager" class="p-4"></div>
            </div>
            <?php include __DIR__ . '/../public/components/pagination.php'; ?>
        </main>
    </div>
    <div id="modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm" onclick="if(event.target===this)closeModal()">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto p-6" onclick="event.stopPropagation()">
            <div class="flex items-center justify-between mb-4"><h3 class="text-lg font-bold text-slate-900">Record Payment</h3><button onclick="closeModal()" class="text-slate-400 hover:text-slate-600"><i class="fas fa-times text-xl"></i></button></div>
            <form id="paymentForm" class="space-y-4">
                <div><label class="block text-sm font-medium text-slate-700 mb-1">Tenant</label><select id="payTenant" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all"><option value="">Select tenant...</option></select></div>
                <!-- Tenant Financial Info (auto-fetched) -->
                <div id="tenantFinanceInfo" class="hidden bg-blue-50/50 rounded-xl p-4 border border-blue-100/70 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-slate-500">Monthly Rent</span>
                        <span class="text-sm font-semibold text-slate-900" id="infoMonthlyRent">KES 0</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-slate-500">Current Arrears</span>
                        <span class="text-sm font-semibold text-red-600" id="infoArrears">KES 0</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-slate-500">Overpaid</span>
                        <span class="text-sm font-semibold text-emerald-600" id="infoOverpaid">KES 0</span>
                    </div>
                    <div class="flex items-center justify-between pt-1 border-t border-blue-100/70">
                        <span class="text-xs font-medium text-slate-500">Suggested Payment</span>
                        <span class="text-sm font-bold text-blue-600" id="infoSuggested">KES 0</span>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div><label class="block text-sm font-medium text-slate-700 mb-1">Type</label><select id="payType" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all"><option>Rent</option><option>Water</option><option>Electricity</option></select></div>
                    <div><label class="block text-sm font-medium text-slate-700 mb-1">Amount (KES)</label><input type="number" id="payAmount" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all" placeholder="0.00" required></div>
                </div>
                <div><label class="block text-sm font-medium text-slate-700 mb-1">Method</label><select id="payMethod" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all"><option>M-Pesa</option><option>Bank Transfer</option><option>Cash</option></select></div>
                <div class="flex justify-end gap-3 pt-4"><button type="button" onclick="closeModal()" class="px-5 py-2.5 bg-white text-blue-700 border border-blue-200 rounded-xl font-medium hover:bg-blue-50 transition-all">Cancel</button><button type="submit" class="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold rounded-xl shadow-lg shadow-blue-500/30 transition-all">Save</button></div>
            </form>
        </div>
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
    // Get token from cookie (primary auth method) or localStorage (fallback)
    const cookies = document.cookie.split(';');
    let cookieToken = '';
    for (let c of cookies) {
        const [k, v] = c.trim().split('=');
        if (k === 'rf_token') { cookieToken = decodeURIComponent(v); break; }
    }
    const token = cookieToken || localStorage.getItem('rf_token') || '<?php echo $token; ?>';
    const headers = token ? {'Authorization':'Bearer '+token, 'Content-Type':'application/json'} : {'Content-Type':'application/json'};
    const userRole = '<?php echo $role; ?>';
    const PAYMENTS_PER_PAGE_KEY = 'rf_payments_per_page';
    const PAYMENTS_PER_PAGE_DEFAULT = 25;

    async function apiRequest(url, options = {}) {
        const res = await fetch(url, { ...options, headers });
        const text = await res.text();
        let data;
        try { data = JSON.parse(text); } catch(e) { throw new Error('Server error'); }
        
        if (!res.ok) {
            if (res.status === 401) {
                localStorage.removeItem('rf_token');
                window.location.href = BASE + '/signin';
            }
            throw new Error(data.error || 'Request failed');
        }
        return data;
    }

    function escapeHtml(value) { return String(value ?? '').replace(/[&<>"']/g, c => ({'&':'&','<':'<','>':'>','"':'"',"'":'&#039;'}[c])); }
    function toast(msg, type='success') {
        const el = document.getElementById('toast');
        const colors = { success:'bg-gradient-to-r from-emerald-500 to-emerald-600', error:'bg-gradient-to-r from-red-500 to-red-600', info:'bg-gradient-to-r from-blue-500 to-blue-600' };
        const icons = { success:'fa-check-circle', error:'fa-exclamation-circle', info:'fa-info-circle' };
        el.className = `fixed bottom-6 right-6 z-50 px-5 py-3 rounded-xl shadow-xl text-white font-medium flex items-center gap-2 ${colors[type]||colors.success}`;
        el.innerHTML = `<i class="fas ${icons[type]||icons.success}"></i>${escapeHtml(msg)}`;
        el.classList.remove('hidden');
        setTimeout(() => el.classList.add('hidden'), 3000);
    }

    async function loadPayments(page = 1, perPage = window.getSavedPerPage(PAYMENTS_PER_PAGE_KEY, PAYMENTS_PER_PAGE_DEFAULT)) {
        const tbody = document.getElementById('paymentsTable');
        try {
            // Show loading spinner
            tbody.innerHTML = '<tr id="loadingRow"><td colspan="8" class="px-6 py-12 text-center text-slate-400"><div class="flex flex-col items-center gap-3"><i class="fas fa-spinner fa-spin text-3xl text-blue-400"></i><span>Loading payments...</span></div></td></tr>';
            
            const data = await apiRequest(`${API}/payments?page=${page}&per_page=${perPage}`);
            if (data.payments && data.payments.length) {
                tbody.innerHTML = data.payments.map(p => {
                    const isConfirmed = p.tenant_confirmed == 1;
                    const isTenantPayment = userRole === 'tenant' && p.tenant_confirmed == 0;
                    return `<tr class="hover:bg-blue-50/30 transition-colors">
                        <td class="px-6 py-4 text-sm font-medium text-blue-600">${p.receipt||'N/A'}</td>
                        <td class="px-6 py-4"><div class="flex items-center gap-2"><div class="w-6 h-6 rounded-full bg-gradient-to-br from-blue-50 to-blue-100 text-blue-600 flex items-center justify-center text-xs font-bold">${(p.tenant_name||'U').split(' ').map(s=>s[0]).join('').substring(0,2).toUpperCase()}</div><span class="text-sm text-slate-700">${p.tenant_name||'N/A'}</span></div></td>
                        <td class="px-6 py-4 text-sm text-slate-600">${p.description||''}</td>
                        <td class="px-6 py-4 text-sm font-bold text-slate-900">KES ${(p.amount||0).toLocaleString()}</td>
                        <td class="px-6 py-4 text-sm text-slate-500">${p.method||'N/A'}</td>
                        <td class="px-6 py-4 text-sm text-slate-500">${new Date(p.date).toLocaleDateString('en-GB',{day:'numeric',month:'short',year:'numeric'})}</td>
                        <td class="px-6 py-4"><span class="px-2 py-0.5 rounded-full text-xs font-medium ${p.status==='confirmed'||p.status==='paid'?'bg-emerald-100 text-emerald-700':'bg-amber-100 text-amber-700'}">${p.status}</span></td>
                        <td class="px-6 py-4">${isConfirmed ? '<span class="text-emerald-600 text-sm"><i class="fas fa-check-circle mr-1"></i>Confirmed</span>' : (isTenantPayment ? '<button onclick="confirmPayment('+p.id+')" class="px-3 py-1.5 text-xs font-medium bg-amber-100 text-amber-700 rounded-full hover:bg-amber-200 transition-all"><i class="fas fa-check mr-1"></i>Confirm</button>' : '<span class="text-slate-400 text-sm">Awaiting confirmation</span>')}</td>
                    </tr>`;
                }).join('');
            } else {
                tbody.innerHTML = '<tr><td colspan="8" class="px-6 py-12 text-center text-slate-400">No payments found</td></tr>';
            }
            if (data.meta) renderPagination('paymentsPager', data.meta, (p) => loadPayments(p, perPage), {
                perPageKey: PAYMENTS_PER_PAGE_KEY,
                defaultPerPage: PAYMENTS_PER_PAGE_DEFAULT,
                onPerPageChange: (newPerPage) => loadPayments(1, newPerPage),
            });
        } catch(e) { 
            console.error(e); 
        }
    }

    async function confirmPayment(paymentId) {
        try {
            const data = await apiRequest(`${API}/payments/${paymentId}/confirm`, { method:'PUT' });
            toast('Payment confirmed! You have verified this payment record.', 'success');
            loadPayments();
            // Refresh sidebar counts
            if (typeof loadSidebarCounts === 'function') loadSidebarCounts();
        } catch(e) { toast(e.message, 'error'); }
    }

    async function loadTenants() {
        const select = document.getElementById('payTenant');
        try {
            // Show loading state
            select.innerHTML = '<option value="">Loading tenants...</option>';
            select.disabled = true;
            
            const data = await apiRequest(`${API}/tenants`);
            if (data.tenants && '<?php echo $role; ?>' !== 'tenant') {
                select.innerHTML = '<option value="">Select tenant...</option>' + data.tenants.map(t => `<option value="${t.id}">${escapeHtml(t.name)}</option>`).join('');
            }
        } catch(e) { 
            console.error(e); 
            select.innerHTML = '<option value="">Failed to load</option>';
        } finally {
            select.disabled = false;
        }
    }

    function openModal() { 
        document.getElementById('modal').classList.remove('hidden'); 
        document.getElementById('tenantFinanceInfo').classList.add('hidden');
        document.getElementById('payAmount').value = '';
        document.getElementById('payMethod').value = 'M-Pesa';
        document.getElementById('payType').value = 'Rent';
        document.getElementById('payTenant').value = '';
    }
    function closeModal() { document.getElementById('modal').classList.add('hidden'); }

    // When tenant changes, fetch their financial info
    document.getElementById('payTenant').addEventListener('change', async function() {
        const tenantId = this.value;
        const infoBox = document.getElementById('tenantFinanceInfo');
        if (!tenantId) {
            infoBox.classList.add('hidden');
            return;
        }
        try {
            const data = await apiRequest(`${API}/payments/tenant-finance/${tenantId}`);
            document.getElementById('infoMonthlyRent').textContent = 'KES ' + (data.monthly_rent || 0).toLocaleString();
            document.getElementById('infoArrears').textContent = 'KES ' + (data.arrears || 0).toLocaleString();
            document.getElementById('infoOverpaid').textContent = 'KES ' + (data.overpaid || 0).toLocaleString();
            document.getElementById('infoSuggested').textContent = 'KES ' + (data.suggested_payment || 0).toLocaleString();
            infoBox.classList.remove('hidden');
        } catch(e) {
            console.error('Failed to load tenant finance info:', e);
            infoBox.classList.add('hidden');
        }
    });

    document.getElementById('paymentForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const submitBtn = e.target.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
        const data = {
            type: document.getElementById('payType').value,
            amount: parseFloat(document.getElementById('payAmount').value),
            method: document.getElementById('payMethod').value,
        };
        const tenantSelect = document.getElementById('payTenant');
        if (tenantSelect && tenantSelect.value) {
            data.tenant_id = parseInt(tenantSelect.value);
        }
        try {
            const result = await apiRequest(`${API}/payments`, { method:'POST', body:JSON.stringify(data) });
            toast('Payment recorded!');
            closeModal();
            loadPayments();
        } catch(err) { toast(err.message, 'error'); }
        finally {
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        }
    });

    loadTenants();
    loadPayments();

    // Refresh sidebar counts after recording payment
    const origOpen = openModal;
    openModal = function() {
        if (typeof refreshSidebarCounts === 'function') refreshSidebarCounts();
        origOpen();
    };
    </script>
</body>
</html>