<?php
require_once __DIR__ . '/../../backend/app/Core/Env.php';
\App\Core\Env::load();
require_once __DIR__ . '/../includes/base-path-fix.php';
$basePath = getBasePath();
session_start();
$token = $_COOKIE['rf_token'] ?? $_SESSION['rf_token'] ?? null;
if (!$token) { header('Location: ../public/signin.php'); exit; }
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
    <title>Maintenance - RentaFlow</title>
    <link rel="stylesheet" href="<?php echo htmlspecialchars($basePath); ?>/css/output.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        .cost-input { max-width: 180px; }
        @media (max-width: 640px) { .cost-input { max-width: 100%; } }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-50 via-white to-blue-50/30 min-h-screen font-sans text-slate-800 flex flex-col lg:flex-row">
    <?php include __DIR__ . '/../public/components/sidebar.php'; ?>
    <div class="flex-1 flex flex-col min-h-screen">
        <?php include __DIR__ . '/../public/components/header.php'; ?>
        <main class="flex-1 overflow-y-auto p-4 lg:p-8">
            <!-- Stats Cards -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-6" id="statsGrid">
                <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-4 text-center"><p class="text-xs text-slate-500 uppercase tracking-wider mb-1">Total</p><p class="text-2xl font-bold text-slate-900" id="statTotal">-</p></div>
                <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-4 text-center"><p class="text-xs text-slate-500 uppercase tracking-wider mb-1">Pending</p><p class="text-2xl font-bold text-amber-600" id="statPending">-</p></div>
                <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-4 text-center"><p class="text-xs text-slate-500 uppercase tracking-wider mb-1">This Month</p><p class="text-2xl font-bold text-emerald-600" id="statMonthlyCost">-</p></div>
                <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-4 text-center"><p class="text-xs text-slate-500 uppercase tracking-wider mb-1">Total Cost</p><p class="text-2xl font-bold text-blue-600" id="statTotalCost">-</p></div>
            </div>

            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                <div><h1 class="text-2xl font-bold text-slate-900">Maintenance Records</h1><p class="text-slate-500 mt-1">Track maintenance work and costs per tenant or property</p></div>
                <button onclick="openModal()" class="px-5 py-2.5 bg-gradient-to-r from-emerald-500 to-emerald-600 text-white font-semibold rounded-xl shadow-lg shadow-emerald-500/30 hover:shadow-emerald-500/40 transition-all inline-flex items-center gap-2"><i class="fas fa-tools"></i>New Record</button>
            </div>

            <!-- Filters -->
            <div class="flex flex-wrap gap-3 mb-4">
                <select id="filterStatus" onchange="loadMaintenance()" class="px-3 py-2 rounded-xl border border-slate-200 bg-white text-sm focus:ring-2 focus:ring-emerald-500/30">
                    <option value="">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="in-progress">In Progress</option>
                    <option value="completed">Completed</option>
                    <option value="cancelled">Cancelled</option>
                </select>
                <select id="filterProperty" onchange="loadMaintenance()" class="px-3 py-2 rounded-xl border border-slate-200 bg-white text-sm focus:ring-2 focus:ring-emerald-500/30">
                    <option value="">All Properties</option>
                </select>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4" id="maintenanceGrid">
                <div class="col-span-full py-12 text-center text-slate-400"><div class="flex flex-col items-center gap-3"><i class="fas fa-spinner fa-spin text-3xl text-emerald-400"></i><span>Loading maintenance records...</span></div></div>
            </div>
        </main>
    </div>

    <!-- NEW MAINTENANCE MODAL -->
    <div id="modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm" onclick="if(event.target===this)closeModal()">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto p-6" onclick="event.stopPropagation()">
            <div class="flex items-center justify-between mb-4"><h3 class="text-lg font-bold text-slate-900" id="modalTitle">New Maintenance Record</h3><button onclick="closeModal()" class="text-slate-400 hover:text-slate-600"><i class="fas fa-times text-xl"></i></button></div>
            <form id="maintenanceForm" class="space-y-4">
                <div><label class="block text-sm font-medium text-slate-700 mb-1">Title <span class="text-red-400">*</span></label><input type="text" id="mTitle" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 outline-none transition-all" placeholder="e.g. Plumbing repair" required></div>
                <div><label class="block text-sm font-medium text-slate-700 mb-1">Description</label><textarea id="mDesc" rows="2" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 outline-none transition-all" placeholder="Describe the issue..."></textarea></div>
                <div class="grid grid-cols-2 gap-4">
                    <div><label class="block text-sm font-medium text-slate-700 mb-1">Category</label><select id="mCategory" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 outline-none transition-all"><option>Plumbing</option><option>Electrical</option><option>Structural</option><option>Painting</option><option>Cleaning</option><option>Gardening</option><option>Security</option><option>General</option><option>Other</option></select></div>
                    <div><label class="block text-sm font-medium text-slate-700 mb-1">Priority</label><select id="mPriority" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 outline-none transition-all"><option value="low">Low</option><option value="medium">Medium</option><option value="high">High</option><option value="urgent">Urgent</option></select></div>
                </div>

                <!-- Recipient selection for owner/caretaker -->
                <div id="recipientSection" class="<?php echo $role === 'tenant' ? 'hidden' : '' ?>">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Scope</label>
                    <select id="mScope" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 outline-none transition-all mb-2" onchange="toggleScopeFields()">
                        <option value="individual">Individual Tenant</option>
                        <option value="property">All Tenants in Property</option>
                        <option value="all">All Managed Tenants</option>
                    </select>
                    <div id="tenantSelectDiv"><label class="block text-sm font-medium text-slate-700 mb-1">Tenant</label><select id="mTenant" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 outline-none transition-all"><option value="">Select tenant...</option></select></div>
                    <div id="propertySelectDiv" class="hidden"><label class="block text-sm font-medium text-slate-700 mb-1">Property</label><select id="mProperty" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 outline-none transition-all"><option value="">Select property...</option></select></div>
                </div>

                <div class="border-t border-slate-100 pt-4">
                    <h4 class="text-sm font-semibold text-slate-700 mb-3">Cost & Vendor Details</h4>
                    <div class="grid grid-cols-2 gap-4">
                        <div><label class="block text-sm font-medium text-slate-700 mb-1">Cost (KES)</label><input type="number" id="mCost" step="0.01" min="0" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 outline-none transition-all" placeholder="0.00"></div>
                        <div><label class="block text-sm font-medium text-slate-700 mb-1">Scheduled Date</label><input type="date" id="mDate" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 outline-none transition-all"></div>
                    </div>
                    <div class="grid grid-cols-2 gap-4 mt-3">
                        <div><label class="block text-sm font-medium text-slate-700 mb-1">Vendor Name</label><input type="text" id="mVendor" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 outline-none transition-all" placeholder="e.g. John's Plumbing"></div>
                        <div><label class="block text-sm font-medium text-slate-700 mb-1">Vendor Phone</label><input type="text" id="mVendorPhone" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 outline-none transition-all" placeholder="e.g. 0712345678"></div>
                    </div>
                    <div class="mt-3"><label class="block text-sm font-medium text-slate-700 mb-1">Cost Notes</label><input type="text" id="mCostNotes" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 outline-none transition-all" placeholder="e.g. Includes parts and labor"></div>
                    <div class="mt-3"><label class="block text-sm font-medium text-slate-700 mb-1">Assigned To</label><input type="text" id="mAssignedTo" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 outline-none transition-all" placeholder="e.g. Staff name or company"></div>
                </div>

                <div class="flex justify-end gap-3 pt-4"><button type="button" onclick="closeModal()" class="px-5 py-2.5 bg-white text-emerald-700 border border-emerald-200 rounded-xl font-medium hover:bg-emerald-50 transition-all">Cancel</button><button type="submit" class="px-5 py-2.5 bg-gradient-to-r from-emerald-500 to-emerald-600 text-white font-semibold rounded-xl shadow-lg shadow-emerald-500/30 transition-all">Create Record</button></div>
            </form>
        </div>
    </div>

    <!-- UPDATE STATUS MODAL -->
    <div id="updateModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm" onclick="if(event.target===this)closeUpdateModal()">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md max-h-[90vh] overflow-y-auto p-6" onclick="event.stopPropagation()">
            <div class="flex items-center justify-between mb-4"><h3 class="text-lg font-bold text-slate-900">Update Maintenance</h3><button onclick="closeUpdateModal()" class="text-slate-400 hover:text-slate-600"><i class="fas fa-times text-xl"></i></button></div>
            <form id="updateForm" class="space-y-4">
                <div><label class="block text-sm font-medium text-slate-700 mb-1">Status</label><select id="uStatus" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 outline-none transition-all"><option value="pending">Pending</option><option value="in-progress">In Progress</option><option value="completed">Completed</option><option value="cancelled">Cancelled</option></select></div>
                <div><label class="block text-sm font-medium text-slate-700 mb-1">Cost (KES)</label><input type="number" id="uCost" step="0.01" min="0" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 outline-none transition-all"></div>
                <div class="grid grid-cols-2 gap-4">
                    <div><label class="block text-sm font-medium text-slate-700 mb-1">Vendor Name</label><input type="text" id="uVendor" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 outline-none transition-all"></div>
                    <div><label class="block text-sm font-medium text-slate-700 mb-1">Vendor Phone</label><input type="text" id="uVendorPhone" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 outline-none transition-all"></div>
                </div>
                <div><label class="block text-sm font-medium text-slate-700 mb-1">Notes</label><textarea id="uNotes" rows="2" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 outline-none transition-all"></textarea></div>
                <div class="flex justify-end gap-3 pt-4"><button type="button" onclick="closeUpdateModal()" class="px-5 py-2.5 bg-white text-emerald-700 border border-emerald-200 rounded-xl font-medium hover:bg-emerald-50 transition-all">Cancel</button><button type="submit" class="px-5 py-2.5 bg-gradient-to-r from-emerald-500 to-emerald-600 text-white font-semibold rounded-xl shadow-lg shadow-emerald-500/30 transition-all">Update</button></div>
            </form>
        </div>
    </div>

    <div id="toast" class="fixed bottom-6 right-6 z-50 hidden px-5 py-3 rounded-xl shadow-xl text-white font-medium flex items-center gap-2"></div>
    <script>
    let BASE = window.location.pathname;
    const frontendPagesIndex = BASE.indexOf('/frontend/pages/');
    if (frontendPagesIndex !== -1) {
        BASE = BASE.substring(0, frontendPagesIndex);
    } else {
        BASE = BASE.replace(/\/[^\/]*$/, '');
    }
    const API = BASE + '/api';
    const token = localStorage.getItem('rf_token') || '<?php echo $token; ?>';
    const headers = token ? {'Authorization':'Bearer '+token, 'Content-Type':'application/json'} : {'Content-Type':'application/json'};
    const userRole = '<?php echo $role; ?>';

    async function apiRequest(url, options = {}) {
        const res = await fetch(url, { ...options, headers });
        const text = await res.text();
        let data;
        try { data = JSON.parse(text); } catch(e) { throw new Error('Server error'); }
        if (!res.ok) {
            if (res.status === 401) { localStorage.removeItem('rf_token'); window.location.href = 'signin'; }
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

    async function loadStats() {
        try {
            const data = await apiRequest(`${API}/maintenance/stats`);
            if (data.stats) {
                document.getElementById('statTotal').textContent = data.stats.total || 0;
                document.getElementById('statPending').textContent = data.stats.pending || 0;
                document.getElementById('statTotalCost').textContent = 'KES ' + ((data.stats.total_cost || 0)).toLocaleString('en-KE', {minimumFractionDigits:2});
                document.getElementById('statMonthlyCost').textContent = 'KES ' + ((data.stats.monthly_cost || 0)).toLocaleString('en-KE', {minimumFractionDigits:2});
            }
        } catch(e) { console.error(e); }
    }

    async function loadMaintenance() {
        try {
            const status = document.getElementById('filterStatus').value;
            const propertyId = document.getElementById('filterProperty').value;
            let url = `${API}/maintenance`;
            const params = [];
            if (status) params.push('status=' + encodeURIComponent(status));
            if (propertyId) params.push('property_id=' + encodeURIComponent(propertyId));
            if (params.length) url += '?' + params.join('&');

            const data = await apiRequest(url);
            const grid = document.getElementById('maintenanceGrid');
            if (data.maintenance && data.maintenance.length) {
                grid.innerHTML = data.maintenance.map(m => {
                    const statusClass = m.status==='completed'?'bg-emerald-100 text-emerald-700':m.status==='in-progress'?'bg-blue-100 text-blue-700':m.status==='cancelled'?'bg-red-100 text-red-700':'bg-amber-100 text-amber-700';
                    const priorityIcon = m.priority==='urgent'?'fa-exclamation-triangle text-red-500':m.priority==='high'?'fa-arrow-up text-orange-500':m.priority==='medium'?'fa-minus text-yellow-500':'fa-arrow-down text-slate-400';
                    const costDisplay = parseFloat(m.cost||0) > 0 ? '<span class="font-medium text-slate-700">KES ' + parseFloat(m.cost).toLocaleString('en-KE',{minimumFractionDigits:2}) + '</span>' : '<span class="text-slate-400 text-xs">No cost</span>';
                    return `<div class="bg-white rounded-2xl shadow-sm border border-emerald-100/50 p-5 hover:shadow-md transition-shadow">
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-50 to-emerald-100 text-emerald-600 flex items-center justify-center"><i class="fas fa-tools"></i></div>
                                <div><h3 class="font-medium text-slate-900">${escapeHtml(m.title)}</h3><p class="text-xs text-slate-500">${escapeHtml(m.category||'General')} • ${escapeHtml(m.property_name||'')} ${escapeHtml(m.unit||'')}</p></div>
                            </div>
                            <span class="px-2 py-1 rounded-full text-xs font-medium ${statusClass}">${m.status}</span>
                        </div>
                        <p class="text-sm text-slate-600 mb-3 line-clamp-2">${escapeHtml(m.description || '')}</p>
                        <div class="flex items-center justify-between pt-3 border-t border-emerald-50">
                            <div class="flex items-center gap-3 text-xs text-slate-500">
                                <span><i class="fas ${priorityIcon} mr-1"></i>${m.priority}</span>
                                <span>${costDisplay}</span>
                                ${m.vendor_name ? '<span><i class="fas fa-user-cog mr-1"></i>' + escapeHtml(m.vendor_name) + '</span>' : ''}
                            </div>
                            <div class="flex gap-2">
                                <button onclick="openUpdateModal(${m.id}, '${m.status}', ${m.cost||0}, '${escapeHtml(m.vendor_name||'')}', '${escapeHtml(m.vendor_phone||'')}', '${escapeHtml(m.notes||'')}')" class="px-3 py-1.5 bg-gradient-to-r from-emerald-500 to-emerald-600 text-white text-xs font-medium rounded-lg hover:shadow-md transition-all"><i class="fas fa-edit mr-1"></i>Update</button>
                                ${userRole !== 'tenant' ? '<button onclick="deleteMaintenance(' + m.id + ')" class="px-3 py-1.5 bg-white text-red-500 border border-red-200 text-xs font-medium rounded-lg hover:bg-red-50 transition-all"><i class="fas fa-trash"></i></button>' : ''}
                            </div>
                        </div>
                    </div>`;
                }).join('');
            } else {
                grid.innerHTML = '<div class="col-span-full py-12 text-center text-slate-400">No maintenance records found</div>';
            }
            loadStats();
        } catch(e) { console.error(e); }
    }

    let currentUpdateId = null;

    function openUpdateModal(id, status, cost, vendor, vendorPhone, notes) {
        currentUpdateId = id;
        document.getElementById('uStatus').value = status;
        document.getElementById('uCost').value = cost;
        document.getElementById('uVendor').value = vendor;
        document.getElementById('uVendorPhone').value = vendorPhone;
        document.getElementById('uNotes').value = notes;
        document.getElementById('updateModal').classList.remove('hidden');
    }

    function closeUpdateModal() { document.getElementById('updateModal').classList.add('hidden'); currentUpdateId = null; }

    async function deleteMaintenance(id) {
        if (!confirm('Delete this maintenance record?')) return;
        try {
            await apiRequest(`${API}/maintenance/${id}`, { method:'DELETE' });
            toast('Record deleted');
            loadMaintenance();
        } catch(err) { toast(err.message, 'error'); }
    }

    function openModal() { document.getElementById('modal').classList.remove('hidden'); }
    function closeModal() { document.getElementById('modal').classList.add('hidden'); }

    function toggleScopeFields() {
        const scope = document.getElementById('mScope').value;
        document.getElementById('tenantSelectDiv').classList.toggle('hidden', scope !== 'individual');
        document.getElementById('propertySelectDiv').classList.toggle('hidden', scope !== 'property');
    }

    async function loadSelectOptions() {
        if (userRole === 'tenant') return;
        try {
            const tData = await apiRequest(`${API}/tenants`);
            const tSelect = document.getElementById('mTenant');
            if (tSelect && tData.tenants) {
                tSelect.innerHTML = '<option value="">Select tenant...</option>' + tData.tenants.map(t => `<option value="${t.id}">${escapeHtml(t.name)} (${t.house_unit || 'No unit'})</option>`).join('');
            }
            const pData = await apiRequest(`${API}/properties`);
            const pSelect = document.getElementById('mProperty');
            if (pSelect && pData.properties) {
                pSelect.innerHTML = '<option value="">Select property...</option>' + pData.properties.map(p => `<option value="${p.id}">${escapeHtml(p.name)}</option>`).join('');
                // Also populate filter
                const filterSelect = document.getElementById('filterProperty');
                filterSelect.innerHTML = '<option value="">All Properties</option>' + pData.properties.map(p => `<option value="${p.id}">${escapeHtml(p.name)}</option>`).join('');
            }
        } catch(e) { console.error('Failed to load select options', e); }
    }

    document.getElementById('maintenanceForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const btn = e.target.querySelector('button[type="submit"]');
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
        const data = {
            title: document.getElementById('mTitle').value,
            description: document.getElementById('mDesc').value,
            category: document.getElementById('mCategory').value,
            priority: document.getElementById('mPriority').value,
            cost: document.getElementById('mCost').value || 0,
            scheduled_date: document.getElementById('mDate').value || null,
            vendor_name: document.getElementById('mVendor').value,
            vendor_phone: document.getElementById('mVendorPhone').value,
            cost_notes: document.getElementById('mCostNotes').value,
            assigned_to: document.getElementById('mAssignedTo').value,
        };
        if (userRole !== 'tenant') {
            const scope = document.getElementById('mScope').value;
            if (scope === 'individual') {
                data.tenant_id = document.getElementById('mTenant').value;
                data.recipient_type = 'individual';
            } else if (scope === 'property') {
                data.property_id = document.getElementById('mProperty').value;
                data.recipient_type = 'property';
            } else {
                data.recipient_type = 'all';
            }
        }
        try {
            const result = await apiRequest(`${API}/maintenance`, { method:'POST', body:JSON.stringify(data) });
            toast('Maintenance record created!');
            closeModal();
            document.getElementById('maintenanceForm').reset();
            loadMaintenance();
        } catch(err) { toast(err.message, 'error'); }
        finally { btn.disabled = false; btn.innerHTML = originalText; }
    });

    document.getElementById('updateForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        if (!currentUpdateId) return;
        const btn = e.target.querySelector('button[type="submit"]');
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
        const data = {
            status: document.getElementById('uStatus').value,
            cost: document.getElementById('uCost').value || 0,
            vendor_name: document.getElementById('uVendor').value,
            vendor_phone: document.getElementById('uVendorPhone').value,
            notes: document.getElementById('uNotes').value,
        };
        try {
            await apiRequest(`${API}/maintenance/${currentUpdateId}`, { method:'PUT', body:JSON.stringify(data) });
            toast('Record updated!');
            closeUpdateModal();
            loadMaintenance();
        } catch(err) { toast(err.message, 'error'); }
        finally { btn.disabled = false; btn.innerHTML = originalText; }
    });

    loadMaintenance();
    loadSelectOptions();
    setTimeout(() => { if (typeof refreshSidebarCounts === 'function') refreshSidebarCounts(); }, 300);
    </script>
</body>
</html>