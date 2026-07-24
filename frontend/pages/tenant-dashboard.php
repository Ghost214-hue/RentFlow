<?php
// Force no-cache BEFORE any output
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

require_once __DIR__ . '/../includes/base-path-fix.php';
$basePath = getBasePath();

require_once __DIR__ . '/../includes/auth.php';
if (($userRole ?? 'tenant') !== 'tenant') {
    header('Location: ' . $basePath . '/signin');
    exit;
}

$hasConsent = !empty($user['data_protection_consent_at']);
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

<!-- Data Protection Consent Modal -->
<?php if (!$hasConsent): ?>
<div id="consentModal" class="fixed inset-0 z-[9999] bg-black/70 flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-lg p-8">
        <div class="text-center mb-6">
            <div class="w-16 h-16 rounded-full bg-gradient-to-br from-blue-500 to-blue-600 text-white flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-shield-alt text-2xl"></i>
            </div>
            <h2 class="text-2xl font-bold text-slate-900 mb-2">Data Protection Consent</h2>
            <p class="text-sm text-slate-500">We value your privacy and are committed to protecting your personal data</p>
        </div>
        
        <div class="space-y-3 mb-6">
            <h3 class="text-sm font-semibold text-slate-700 uppercase tracking-wide">Why We Collect Your Data</h3>
            <p class="text-sm text-slate-600 leading-relaxed">To provide you with the best rental experience, we collect and process the following personal information:</p>
            <ul class="space-y-2 text-sm text-slate-600 ml-4">
                <li class="flex items-start gap-2">
                    <i class="fas fa-check-circle text-blue-500 mt-0.5"></i>
                    <span><strong>Identity & Contact:</strong> Name, phone number, email, and ID details for account verification and communication</span>
                </li>
                <li class="flex items-start gap-2">
                    <i class="fas fa-check-circle text-blue-500 mt-0.5"></i>
                    <span><strong>Payment Processing:</strong> Payment history, balances, and billing information for rent and service charges</span>
                </li>
                <li class="flex items-start gap-2">
                    <i class="fas fa-check-circle text-blue-500 mt-0.5"></i>
                    <span><strong>Property Management:</strong> Unit assignments, lease terms, maintenance requests, and service improvements to manage your tenancy</span>
                </li>
                <li class="flex items-start gap-2">
                    <i class="fas fa-check-circle text-blue-500 mt-0.5"></i>
                    <span><strong>Documents:</strong> Signed lease agreements, ID copies, proof of income, references, and supporting paperwork to verify eligibility, comply with legal requirements, prevent fraud, and improve service delivery based on your tenancy history</span>
                </li>
                <li class="flex items-start gap-2">
                    <i class="fas fa-check-circle text-blue-500 mt-0.5"></i>
                    <span><strong>Communication:</strong> Sending important notices, reminders, and updates about your tenancy</span>
                </li>
                <li class="flex items-start gap-2">
                    <i class="fas fa-check-circle text-blue-500 mt-0.5"></i>
                    <span><strong>Service Improvement:</strong> Analyzing trends and feedback to enhance property management services, response times, and overall tenant satisfaction</span>
                </li>
            </ul>
        </div>

        <div class="bg-blue-50 border border-blue-100 rounded-xl p-4 mb-6">
            <p class="text-xs text-slate-600 leading-relaxed">
                <i class="fas fa-lock text-blue-600 mr-1"></i>
                <strong>Your Data is Safe:</strong> We use industry-standard security measures to protect your information. Your data will never be shared with third parties without your explicit consent, except where required by law.
            </p>
        </div>

                <div class="flex gap-3">
            <button onclick="declineConsent()" class="flex-1 py-3 rounded-xl border-2 border-slate-200 text-slate-600 font-semibold hover:bg-slate-50 transition-all">
                Cancel
            </button>
            <button onclick="acceptConsent()" class="flex-1 py-3 rounded-xl bg-gradient-to-r from-blue-500 to-blue-600 text-white font-semibold hover:from-blue-600 hover:to-blue-700 transition-all shadow-lg">
                I Agree
            </button>
        </div>
    </div>
</div>
<?php endif; ?>

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

            <!-- Emergency Contacts Section -->
            <div class="mb-6">
                <div class="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-5">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-rose-50 to-rose-100 flex items-center justify-center"><i class="fas fa-phone-alt text-rose-600"></i></div>
                        <div>
                            <h3 class="font-semibold text-slate-900">Emergency Contacts</h3>
                            <p class="text-xs text-slate-500">Property management contacts for your unit</p>
                        </div>
                    </div>
                    <div id="contactsContainer" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="py-4 text-center text-slate-400 col-span-full"><i class="fas fa-spinner fa-spin mr-2"></i>Loading contacts...</div>
                    </div>
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
    // Get token from cookie (same as auth.php)
    const cookies = document.cookie.split(';');
    let token = '';
    for (let c of cookies) {
        const [k, v] = c.trim().split('=');
        if (k === 'rf_token') { token = decodeURIComponent(v); break; }
    }
    const headers = token ? {'Authorization':'Bearer '+token, 'Content-Type':'application/json'} : {'Content-Type':'application/json'};

    async function apiRequest(url, options = {}) {
        const res = await fetch(url, { ...options, headers });
        const text = await res.text();
        let data;
        try { data = JSON.parse(text); } catch(e) { throw new Error('Server error'); }
        
        if (!res.ok) {
            if (res.status === 401) {
                // Clear cookie
                document.cookie = 'rf_token=; path=/; max-age=0';
                window.location.href = '<?php echo $basePath; ?>/signin';
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
            if (e.message.includes('401')) window.location.href = 'signin';
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

    async function loadEmergencyContacts() {
        const container = document.getElementById('contactsContainer');
        try {
            const data = await apiRequest(`${API}/tenants/property-contact`);
            const contacts = data.contacts;
            
            if (!contacts || (!contacts.owner && !contacts.caretaker)) {
                container.innerHTML = '<div class="py-8 text-center text-slate-400 col-span-full"><i class="fas fa-info-circle mr-2"></i>Contact information not available for your property yet.</div>';
                return;
            }
            
            let cards = '';
            
            if (contacts.owner) {
                cards += `
                    <div class="bg-gradient-to-br from-blue-50 to-blue-100/50 rounded-xl p-4 border border-blue-100/80">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-blue-600 text-white flex items-center justify-center text-sm font-bold">${escapeHtml(initials(contacts.owner.name))}</div>
                            <div>
                                <p class="text-sm font-semibold text-slate-900">Property Owner</p>
                                <p class="text-xs text-slate-500">${escapeHtml(contacts.owner.name)}</p>
                            </div>
                        </div>
                        <div class="space-y-1.5 text-xs">
                            ${contacts.owner.phone ? `<p class="flex items-center gap-2 text-slate-600"><i class="fas fa-phone w-4 text-blue-500"></i><a href="tel:${escapeHtml(contacts.owner.phone)}" class="hover:text-blue-600">${escapeHtml(contacts.owner.phone)}</a></p>` : ''}
                            ${contacts.owner.email ? `<p class="flex items-center gap-2 text-slate-600"><i class="fas fa-envelope w-4 text-blue-500"></i><a href="mailto:${escapeHtml(contacts.owner.email)}" class="hover:text-blue-600 truncate">${escapeHtml(contacts.owner.email)}</a></p>` : ''}
                        </div>
                    </div>
                `;
            }
            
            if (contacts.caretaker) {
                cards += `
                    <div class="bg-gradient-to-br from-emerald-50 to-emerald-100/50 rounded-xl p-4 border border-emerald-100/80">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-emerald-500 to-emerald-600 text-white flex items-center justify-center text-sm font-bold">${escapeHtml(initials(contacts.caretaker.name))}</div>
                            <div>
                                <p class="text-sm font-semibold text-slate-900">Caretaker</p>
                                <p class="text-xs text-slate-500">${escapeHtml(contacts.caretaker.name)}</p>
                            </div>
                        </div>
                        <div class="space-y-1.5 text-xs">
                            ${contacts.caretaker.phone ? `<p class="flex items-center gap-2 text-slate-600"><i class="fas fa-phone w-4 text-emerald-500"></i><a href="tel:${escapeHtml(contacts.caretaker.phone)}" class="hover:text-emerald-600">${escapeHtml(contacts.caretaker.phone)}</a></p>` : ''}
                            ${contacts.caretaker.email ? `<p class="flex items-center gap-2 text-slate-600"><i class="fas fa-envelope w-4 text-emerald-500"></i><a href="mailto:${escapeHtml(contacts.caretaker.email)}" class="hover:text-emerald-600 truncate">${escapeHtml(contacts.caretaker.email)}</a></p>` : ''}
                        </div>
                    </div>
                `;
            }
            
            container.innerHTML = cards || '<div class="py-8 text-center text-slate-400 col-span-full">No contacts available</div>';
        } catch(e) {
            console.error('loadEmergencyContacts error:', e);
            container.innerHTML = '<div class="py-8 text-center text-red-400 col-span-full">Failed to load contacts</div>';
        }
    }

    function initials(name, fallback = '??') {
        const text = String(name || '').trim();
        if (!text) return fallback;
        return text.split(/\s+/).map(s => s[0]).join('').substring(0, 2).toUpperCase();
    }

    async function acceptConsent() {
        try {
            await apiRequest(`${API}/tenants/consent-data-protection`, {
                method: 'POST'
            });
            
            // Persist consent locally so it never reappears this session
            try { localStorage.setItem('rf_consent', '1'); } catch (e) {}
            
            toast('Thank you for accepting. Welcome to your dashboard!', 'success');
            setTimeout(() => location.reload(), 600);
        } catch (e) {
            console.error(e);
            toast('Failed to record consent. Please try again.', 'error');
        }
    }

    function declineConsent() {
        // Clear auth cookie and redirect to login
        document.cookie = 'rf_token=; path=/; max-age=0';
        window.location.href = '<?php echo $basePath; ?>/signin';
    }

    loadTenantDashboard();
    loadEmergencyContacts();

    // If consent was previously accepted in this browser, hide the modal immediately
    try {
        if (localStorage.getItem('rf_consent') === '1') {
            const modal = document.getElementById('consentModal');
            if (modal) modal.style.display = 'none';
        }
    } catch (e) {}
    </script>
</body>
</html>
