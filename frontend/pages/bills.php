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
    <title>Bills - RentaFlow</title>
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
                <div class="flex items-center gap-3">
                    <select id="monthFilter" class="px-3 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all text-sm">
                        <option value="<?php echo date('Y-m'); ?>"><?php echo date('F Y'); ?></option>
                    </select>
                    <select id="propertyFilter" class="px-3 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all text-sm">
                        <option value="">All Properties</option>
                    </select>
                </div>
                <button onclick="openBillingModal()" class="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold rounded-xl shadow-lg shadow-blue-500/30 hover:shadow-blue-500/40 transition-all inline-flex items-center gap-2"><i class="fas fa-file-invoice"></i>Generate Bills</button>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border border-blue-100/50 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead><tr class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wider border-b border-blue-50 bg-blue-50/50">
                            <th class="px-6 py-4">Tenant</th><th class="px-6 py-4">Unit</th><th class="px-6 py-4">Month</th><th class="px-6 py-4">Amount</th><th class="px-6 py-4">Paid</th><th class="px-6 py-4">Balance</th><th class="px-6 py-4">Status</th><th class="px-6 py-4">Actions</th>
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
    <!-- Billing Confirmation Modal -->
    <div id="billingModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm" onclick="if(event.target===this)closeBillingModal()">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md max-h-[90vh] overflow-y-auto p-6" onclick="event.stopPropagation()">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-slate-900">Generate Bills</h3>
                <button onclick="closeBillingModal()" class="text-slate-400 hover:text-slate-600"><i class="fas fa-times text-xl"></i></button>
            </div>
            <form id="billingForm" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Property</label>
                    <select id="billPropertySelect" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all">
                        <option value="">All Properties</option>
                    </select>
                    <p class="text-xs text-slate-400 mt-1">Leave as "All Properties" to generate bills for every tenanted unit.</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Billing Month</label>
                    <input type="month" id="billMonthInput" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all" value="<?php echo date('Y-m'); ?>" required>
                    <p class="text-xs text-slate-500 mt-1">Generates bills for all tenanted units and emails each tenant (and next of kin) with their invoice.</p>
                </div>
                <div class="bg-amber-50/70 rounded-xl p-4 border border-amber-100/70">
                    <div class="flex items-start gap-3">
                        <i class="fas fa-info-circle text-amber-500 mt-0.5"></i>
                        <div>
                            <p class="text-sm font-medium text-slate-800">What happens next?</p>
                            <ul class="text-xs text-slate-600 mt-1 space-y-1 list-disc ml-4">
                                <li>Bills will be created for all occupied units</li>
                                <li>Each tenant will receive an email with their invoice</li>
                                <li>Next of kin will also be notified</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="closeBillingModal()" class="px-5 py-2.5 bg-white text-blue-700 border border-blue-200 rounded-xl font-medium hover:bg-blue-50 transition-all text-sm">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold rounded-xl shadow-lg shadow-blue-500/30 hover:shadow-blue-500/40 transition-all inline-flex items-center gap-2 text-sm">
                        <i class="fas fa-file-invoice"></i> Generate & Send
                    </button>
                </div>
            </form>
        </div>
    </div>
<!-- Edit Bill Modal -->
    <div id="editBillModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm" onclick="if(event.target===this)closeEditBill()">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto p-6" onclick="event.stopPropagation()">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-lg font-bold text-slate-900" id="editBillTitle">Edit Bill</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Adjust line items to standardize the invoice. Paid amounts update automatically from recorded payments.</p>
                </div>
                <button onclick="closeEditBill()" class="text-slate-400 hover:text-slate-600"><i class="fas fa-times text-xl"></i></button>
            </div>
            <div class="space-y-4">
                <div class="flex items-center gap-6 flex-wrap text-sm pb-3 border-b border-slate-100">
                    <div><span class="text-slate-400">Tenant:</span> <span class="font-medium text-slate-800" id="editBillTenant">-</span></div>
                    <div><span class="text-slate-400">Unit:</span> <span class="font-medium text-slate-800" id="editBillUnit">-</span></div>
                    <div><span class="text-slate-400">Month:</span> <span class="font-medium text-slate-800" id="editBillMonth">-</span></div>
                </div>
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="text-sm font-semibold text-slate-700">Line Items</label>
                        <button type="button" onclick="addBillItemRow()" class="px-3 py-1.5 text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200 rounded-lg hover:bg-blue-100 transition-all inline-flex items-center gap-1"><i class="fas fa-plus"></i> Add Item</button>
                    </div>
                    <div id="billItemsContainer" class="space-y-2"></div>
                </div>
                <div class="bg-slate-50 rounded-xl p-4 flex items-center justify-between border border-slate-100">
                    <span class="text-sm font-semibold text-slate-700">Total</span>
                    <div class="text-right">
                        <div class="text-lg font-bold text-slate-900" id="editBillTotal">KES 0</div>
                        <div class="text-xs text-slate-400">Paid to date: <span class="text-emerald-600 font-medium" id="editBillPaid">KES 0</span></div>
                    </div>
                </div>
                <div class="bg-amber-50/70 rounded-xl p-3 border border-amber-100/70 flex items-start gap-2">
                    <i class="fas fa-info-circle text-amber-500 mt-0.5"></i>
                    <p class="text-xs text-slate-600">Items that already have payments allocated cannot be removed, but their amounts can be adjusted. Lowering an amount below what's been paid becomes an overpayment/credit.</p>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="closeEditBill()" class="px-5 py-2.5 bg-white text-blue-700 border border-blue-200 rounded-xl font-medium hover:bg-blue-50 transition-all text-sm">Cancel</button>
                    <button type="button" id="saveBillBtn" onclick="saveBill()" class="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold rounded-xl shadow-lg shadow-blue-500/30 hover:shadow-blue-500/40 transition-all inline-flex items-center gap-2 text-sm">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                </div>
            </div>
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
    // Populate month filter with only actual billed months, newest first.
    async function loadAvailableMonths() {
        const select = document.getElementById('monthFilter');
        if (!select) return;

        try {
            const url = new URL(`${API}/bills/months`, window.location.origin);
            const propertySelect = document.getElementById('propertyFilter');
            if (propertySelect && propertySelect.value) {
                url.searchParams.set('property_id', propertySelect.value);
            }

            const data = await apiRequest(url.pathname + url.search);
            const months = Array.isArray(data.months) ? data.months : [];
            select.innerHTML = '';

            if (!months.length) {
                const opt = document.createElement('option');
                opt.value = '';
                opt.textContent = 'No billed months';
                select.appendChild(opt);
                loadBills();
                return;
            }

            months.forEach((month) => {
                const opt = document.createElement('option');
                opt.value = month;
                opt.textContent = new Date(month + '-01').toLocaleString('default', { month: 'long', year: 'numeric' });
                select.appendChild(opt);
            });

            const latestMonth = months[0];
            if (latestMonth) {
                select.value = latestMonth;
            }

            loadBills();
        } catch (e) {
            console.error('Failed to load billed months', e);
            const opt = document.createElement('option');
            opt.value = new Date().toISOString().substring(0, 7);
            opt.textContent = new Date().toLocaleString('default', { month: 'long', year: 'numeric' });
            select.innerHTML = '';
            select.appendChild(opt);
            loadBills();
        }
    }
    // Owners and caretakers can edit bill line items to standardize invoices
    const canEditBill = ['owner','caretaker'].includes('<?php echo $role; ?>');
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
                window.location.href = BASE + '/signin';
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
                            ${canEditBill ? `
                            <button onclick="openEditBill(${b.id})" class="text-slate-600 hover:text-slate-900 text-sm font-medium inline-flex items-center gap-1 mr-3">
                                <i class="fas fa-edit"></i> Edit
                            </button>` : ''}
                            <button onclick="downloadInvoice(${b.id}, '${(b.tenant_name||'').replace(/'/g, "\\'")}', '${(b.month||'').replace(/'/g, "\\'")}')" class="text-blue-600 hover:text-blue-800 text-sm font-medium inline-flex items-center gap-1">
                                <i class="fas fa-download"></i> Invoice
                            </button>
                        </td>
                    </tr>
                `).join('');
            } else {
                tbody.innerHTML = '<tr><td colspan="8" class="px-6 py-12 text-center text-slate-400">No bills found</td></tr>';
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
        } catch(err) { toast(err.message, 'error'); }
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

let currentEditBillId = null;
    let currentEditBillPaid = 0;

    // Escape user-provided text before interpolation into HTML
    function esc(v) { return String(v ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c])); }

    function billItemRow(item) {
        const itemId = item && item.id ? item.id : '';
        const paid = item && item.paid ? item.paid : 0;
        const type = item && item.type ? item.type : 'Rent';
        const description = item && item.description ? item.description : '';
        const amount = item && item.amount != null ? item.amount : (itemId ? 0 : '');
        const locked = paid > 0;
        const removeBtn = locked
            ? '<span class="text-xs text-slate-300 px-2" title="Has payments allocated - cannot remove"><i class="fas fa-lock"></i></span>'
            : '<button type="button" onclick="removeBillItemRow(this)" class="text-red-400 hover:text-red-600"><i class="fas fa-trash-alt"></i></button>';
        const types = ['Rent','Water','Electricity','Deposit','Other'].map(t =>
            `<option value="${t}" ${t===type?'selected':''}>${t}</option>`).join('');
        return `
        <div class="grid grid-cols-12 gap-2 items-center bill-item-row" data-id="${itemId}" data-paid="${paid}">
            <select class="bill-item-type col-span-3 px-2 py-2 rounded-lg border border-slate-200 bg-white text-slate-800 text-sm focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none">${types}</select>
            <input type="text" class="bill-item-desc col-span-4 px-2 py-2 rounded-lg border border-slate-200 bg-white text-slate-800 text-sm placeholder-slate-400 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none" placeholder="Description" value="${esc(description)}">
            <input type="number" min="0" step="0.01" class="bill-item-amount col-span-3 px-2 py-2 rounded-lg border border-slate-200 bg-white text-slate-800 text-sm placeholder-slate-400 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none" placeholder="0.00" value="${amount === '' ? '' : esc(amount)}" oninput="updateBillSummary()">
            <div class="col-span-2 flex items-center justify-end">${removeBtn}</div>
        </div>`;
    }

    function updateBillSummary() {
        let total = 0;
        document.querySelectorAll('#billItemsContainer .bill-item-row').forEach(row => {
            total += parseFloat(row.querySelector('.bill-item-amount').value) || 0;
        });
        document.getElementById('editBillTotal').textContent = 'KES ' + total.toLocaleString();
        document.getElementById('editBillPaid').textContent = 'KES ' + (currentEditBillPaid||0).toLocaleString();
    }

    function addBillItemRow() {
        const container = document.getElementById('billItemsContainer');
        const wrapper = document.createElement('div');
        wrapper.innerHTML = billItemRow(null);
        container.appendChild(wrapper.firstElementChild);
        updateBillSummary();
    }

    function removeBillItemRow(btn) {
        btn.closest('.bill-item-row').remove();
        updateBillSummary();
    }

    function closeEditBill() {
        document.getElementById('editBillModal').classList.add('hidden');
        currentEditBillId = null;
        currentEditBillPaid = 0;
    }

    async function openEditBill(billId) {
        try {
            const data = await apiRequest(`${API}/bills/${billId}`);
            const bill = data.bill;
            if (!bill) { toast('Could not load bill', 'error'); return; }

            currentEditBillId = bill.id;
            currentEditBillPaid = 0;
            document.getElementById('editBillTitle').textContent = 'Edit Bill #' + bill.id;
            document.getElementById('editBillTenant').textContent = bill.tenant_name || 'N/A';
            document.getElementById('editBillUnit').textContent = bill.unit || 'N/A';
            document.getElementById('editBillMonth').textContent = bill.month || 'N/A';

            const items = (bill.items && bill.items.length) ? bill.items : [{}];
            document.getElementById('billItemsContainer').innerHTML = items.map(i => billItemRow(i)).join('');
            (bill.items||[]).forEach(i => { currentEditBillPaid += (i.paid||0); });

            updateBillSummary();
            document.getElementById('editBillModal').classList.remove('hidden');
        } catch(e) {
            toast(e.message || 'Failed to load bill', 'error');
            console.error(e);
        }
    }

    async function saveBill() {
        const rows = document.querySelectorAll('#billItemsContainer .bill-item-row');
        const items = [];
        let runningTotal = 0;
        rows.forEach(row => {
            const id = parseInt(row.dataset.id) || 0;
            const type = row.querySelector('.bill-item-type').value;
            const description = row.querySelector('.bill-item-desc').value.trim();
            const amount = parseFloat(row.querySelector('.bill-item-amount').value) || 0;
            runningTotal += amount;
            items.push({ id, type, description, amount });
        });
        if (!runningTotal) { toast('Bill must have at least one item with an amount', 'error'); return; }

        const btn = document.getElementById('saveBillBtn');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
        try {
            await apiRequest(`${API}/bills/${currentEditBillId}`, { method:'PUT', body: JSON.stringify({ items }) });
            toast('Bill updated! Paid & balance recalculated.', 'success');
            closeEditBill();
            await loadAvailableMonths();
            loadBills();
        } catch(e) {
            toast(e.message, 'error');
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-save"></i> Save Changes';
        }
    }

    function openBillingModal() {
        // Populate property dropdown
        const select = document.getElementById('billPropertySelect');
        select.innerHTML = '<option value="">All Properties</option>';
        // Copy options from filter
        const filterSelect = document.getElementById('propertyFilter');
        for (let i = 1; i < filterSelect.options.length; i++) {
            const opt = document.createElement('option');
            opt.value = filterSelect.options[i].value;
            opt.textContent = filterSelect.options[i].textContent;
            select.appendChild(opt);
        }
        document.getElementById('billMonthInput').value = '<?php echo date('Y-m'); ?>';
        document.getElementById('billingModal').classList.remove('hidden');
    }
    function closeBillingModal() { document.getElementById('billingModal').classList.add('hidden'); }

    document.getElementById('billingForm')?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const btn = e.target.querySelector('button[type="submit"]');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating...';
        
        const month = document.getElementById('billMonthInput').value;
        const propertyId = document.getElementById('billPropertySelect').value;
        const body = propertyId ? JSON.stringify({month, property_id: Number(propertyId)}) : JSON.stringify({month});
        
        try {
            const result = await apiRequest(`${API}/bills/generate`, { method:'POST', body });
            closeBillingModal();
            const msg = result.message || (result.count > 0 ? 'Bills generated successfully!' : 'No new bills were created.');
            toast(msg, result.already_existed ? 'info' : 'success');
            await loadAvailableMonths();
        } catch(err) { toast(err.message, 'error'); }
        finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-file-invoice"></i> Generate & Send';
        }
    });

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
    document.getElementById('propertyFilter')?.addEventListener('change', async () => {
        await loadAvailableMonths();
    });

    loadAvailableMonths();
    loadProperties();
    </script>
</body>
</html>