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
$basePath = '/RentaFlow';
$_SESSION['rf_user'] = $user;
$role = $user['role'] ?? 'tenant';
if ($role !== 'tenant') { header('Location: ../public/signin.php'); exit; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dashboard - RentaFlow</title>
    <link rel="stylesheet" href="<?php echo $basePath; ?>/css/output.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="<?php echo $basePath; ?>/js/base-path.js?v=2"></script>
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

            <!-- Tenancy Termination Section -->
            <div class="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-5 mb-6" id="vacateSection" style="display:none;">
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-red-50 to-red-100 flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-door-open text-red-600"></i>
                    </div>
                    <div class="flex-1">
                        <h3 class="font-semibold text-slate-900">Vacate / Terminate Tenancy</h3>
                        <p class="text-sm text-slate-500 mt-1">If you are planning to move out, you can submit a termination request. The property owner will be notified and will process your termination.</p>
                        <button onclick="showTerminationModal()" class="mt-3 px-4 py-2 rounded-lg bg-gradient-to-r from-red-500 to-red-600 text-white font-medium hover:from-red-600 hover:to-red-700 transition-all shadow-sm">
                            <i class="fas fa-sign-out-alt mr-2"></i>Request Termination
                        </button>
                    </div>
                </div>
            </div>

            <!-- Termination Request Modal -->
            <div id="terminationModal" class="fixed inset-0 z-50 hidden bg-black/50 flex items-center justify-center p-4" onclick="if(event.target===this)hideTerminationModal()">
                <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6" onclick="event.stopPropagation()">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-bold text-slate-900">Termination Request</h3>
                        <button onclick="hideTerminationModal()" class="text-slate-400 hover:text-slate-600"><i class="fas fa-times"></i></button>
                    </div>
                    <form id="terminationForm" onsubmit="submitTermination(event)">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Reason for Termination</label>
                            <textarea id="terminationReason" rows="3" class="w-full px-3 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all text-sm" placeholder="Optional: Tell us why you're moving out"></textarea>
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Proposed Termination Date</label>
                            <input type="date" id="terminationDate" class="w-full px-3 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all text-sm">
                        </div>
                        <p class="text-xs text-slate-500 mb-4">Your request will be sent to the property owner for approval. You will continue to be responsible for rent until the termination date.</p>
                        <div class="flex gap-3">
                            <button type="button" onclick="hideTerminationModal()" class="flex-1 py-2 rounded-xl border border-slate-200 text-slate-600 font-medium hover:bg-slate-50 transition-all text-sm">Cancel</button>
                            <button type="submit" class="flex-1 py-2 rounded-xl bg-gradient-to-r from-red-500 to-red-600 text-white font-medium hover:from-red-600 hover:to-red-700 transition-all text-sm">Submit Request</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Management Notices Section -->
            <div class="mb-6" id="noticesSection">
                <div class="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-5">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-semibold text-slate-900">Notices from Management</h3>
                        <span class="text-xs text-slate-500">Property communications</span>
                    </div>
                    <div id="noticesList" class="space-y-3">
                        <div class="py-8 text-center text-slate-400">Loading notices...</div>
                    </div>
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
                                <tr><td colspan="4" class="py-8 text-center text-slate-400"><div class="flex flex-col items-center gap-3"><i class="fas fa-spinner fa-spin text-3xl text-blue-400"></i><span>Loading payments...</span></div></td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-5">
                    <h3 class="font-semibold text-slate-900 mb-4">My Complaints</h3>
                    <div class="space-y-3" id="myComplaints">
                        <div class="py-8 text-center text-slate-400"><div class="flex flex-col items-center gap-3"><i class="fas fa-spinner fa-spin text-3xl text-blue-400"></i><span>Loading complaints...</span></div></div>
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

    function escapeHtml(value) { return String(value ?? '').replace(/[&<>"']/g, c => ({'&':'&','<':'<','>':'>','"':'"',"'":'&#039;'}[c])); }

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
        // Get DOM elements early (outside try block so they're accessible in the catch block)
        const noticesList = document.getElementById('noticesList');
        const compDiv = document.getElementById('myComplaints');
        const payTbody = document.getElementById('myPayments');
        try {
            // Load tenant profile
            const tenantData = await apiRequest(`${API}/tenants`);
            let tenantId = null;
            if (tenantData.tenants && tenantData.tenants.length > 0) {
                const me = tenantData.tenants[0];
                tenantId = me.id;
                document.getElementById('myUnit').textContent = me.house_unit || '-';
                document.getElementById('myBalance').textContent = 'KES ' + (me.balance || 0).toLocaleString();
                
                const vacateSection = document.getElementById('vacateSection');
                if (me.house_id && me.house_unit) {
                    vacateSection.style.display = 'block';
                } else {
                    vacateSection.style.display = 'none';
                }
            }

            // Load payments for this tenant
            const payData = await apiRequest(`${API}/payments?tenant_id=${tenantId}`);
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

            // Load all complaints/notices and filter client-side
            const allData = await apiRequest(`${API}/complaints`);
            const list = allData.complaints || [];
            const notices = list.filter(c => (c.sender_role || 'tenant') !== 'tenant');
            const myComplaints = list.filter(c => (c.sender_role || 'tenant') === 'tenant');

            if (notices.length) {
                noticesList.innerHTML = notices.slice(0,5).map(c => `
                    <div class="flex items-start gap-3 p-4 rounded-xl ${c.is_unread ? 'bg-blue-50/70 border border-blue-100' : 'bg-slate-50'} cursor-pointer hover:shadow-sm transition-all" onclick="viewNotice(${c.id})">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-50 to-blue-100 text-blue-600 flex items-center justify-center flex-shrink-0"><i class="fas fa-bullhorn text-sm"></i></div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1">
                                <p class="text-sm font-semibold text-slate-900 truncate">${escapeHtml(c.title)}</p>
                                ${c.is_unread ? '<span class="px-2 py-0.5 rounded text-xs font-bold bg-blue-500 text-white">NEW</span>' : ''}
                            </div>
                            <p class="text-xs text-slate-500 mb-1">${c.property_name || ''} ${c.unit || ''}</p>
                            <p class="text-xs text-slate-600 line-clamp-2">${escapeHtml(c.description || '')}</p>
                        </div>
                        <span class="text-xs text-slate-400 whitespace-nowrap">${new Date(c.date).toLocaleDateString('en-GB',{day:'numeric',month:'short'})}</span>
                    </div>
                `).join('');
            } else {
                noticesList.innerHTML = '<div class="py-6 text-center text-slate-400">No notices from management</div>';
            }

            if (myComplaints.length) {
                compDiv.innerHTML = myComplaints.slice(0,4).map(c => `
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
            noticesList.innerHTML = '<div class="py-6 text-center text-red-400">Failed to load notices. Please try again later.</div>';
            compDiv.innerHTML = '<div class="py-8 text-center text-red-400">Failed to load complaints. Please try again later.</div>';
        }
    }

    async function viewNotice(complaintId) {
        try {
            const data = await apiRequest(`${API}/complaints/${complaintId}`);
            const c = data.complaint;
            const html = `
                <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
                    <h2 style="color: #1f2937; font-size: 18px; font-weight: 700; margin-bottom: 8px;">${escapeHtml(c.title)}</h2>
                    ${c.category ? `<span style="display:inline-block; padding:2px 8px; border-radius:10px; background:#e0e7ff; color:#3730a3; font-size:12px; font-weight:600;">${escapeHtml(c.category)}</span>` : ''}
                    ${c.priority ? `<span style="display:inline-block; padding:2px 8px; border-radius:10px; background:${c.priority==='high'?'#fee2e2':c.priority==='medium'?'#fef3c7':'#e0f2fe'}; color:${c.priority==='high'?'#b91c1c':c.priority==='medium'?'#92400e':'#075985'}; font-size:12px; font-weight:600; margin-left:6px;">${c.priority.toUpperCase()}</span>` : ''}
                    <p style="color: #6b7280; font-size: 13px; margin-top: 6px;">${escapeHtml(c.property_name || '')} ${escapeHtml(c.unit || '')} • ${new Date(c.date).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' })}</p>
                    <div style="margin-top: 18px; background: #f8fafc; border-radius: 8px; padding: 14px; border-left: 4px solid #2563eb;">
                        <p style="margin: 0; white-space: pre-wrap; color: #111827; line-height: 1.6;">${escapeHtml(c.description || 'No additional details.')}</p>
                    </div>
                </div>
            `;
            const w = window.open('', '_blank');
            if (w) {
                const cssPath = BASE + '/css/output.css';
            w.document.write(`<!DOCTYPE html><html><head><title>${escapeHtml(c.title)}</title><link rel="stylesheet" href="${cssPath}"></head><body class="bg-slate-50 p-4 sm:p-8">${html}</body></html>`);
                w.document.close();
            } else {
                toast('Please allow popups to view notice details', 'info');
            }
        } catch(e) { toast('Error: ' + e.message, 'error'); }
    }

    // Termination Modal Functions
    function showTerminationModal() {
        document.getElementById('terminationModal').classList.remove('hidden');
        // Set default date to 30 days from now
        const d = new Date();
        d.setDate(d.getDate() + 30);
        document.getElementById('terminationDate').value = d.toISOString().split('T')[0];
    }

    function hideTerminationModal() {
        document.getElementById('terminationModal').classList.add('hidden');
    }

    async function submitTermination(event) {
        event.preventDefault();
        const reason = document.getElementById('terminationReason').value.trim();
        const effectiveDate = document.getElementById('terminationDate').value;
        
        if (!effectiveDate) {
            toast('Please select a termination date', 'error');
            return;
        }
        
        const btn = event.target.querySelector('button[type="submit"]');
        btn.disabled = true;
        btn.textContent = 'Submitting...';
        
        try {
            const result = await apiRequest(`${API}/tenants/request-termination`, {
                method: 'POST',
                body: JSON.stringify({
                    reason: reason,
                    effective_date: effectiveDate
                })
            });
            
            toast(result.message || 'Termination request submitted', 'success');
            hideTerminationModal();
            setTimeout(() => location.reload(), 2000);
        } catch (e) {
            console.error(e);
            toast('An error occurred. Please try again.', 'error');
            btn.disabled = false;
            btn.textContent = 'Submit Request';
        }
    }

    loadTenantDashboard();
    </script>
</body>
</html>
