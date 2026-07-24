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
    <title>Complaints - RentaFlow</title>
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
                <div><h1 class="text-2xl font-bold text-slate-900">Complaints & Notices</h1><p class="text-slate-500 mt-1">Two-way communication between tenants and management</p></div>
                <button onclick="openModal()" class="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold rounded-xl shadow-lg shadow-blue-500/30 hover:shadow-blue-500/40 transition-all inline-flex items-center gap-2"><i class="fas fa-plus"></i>New <?php echo $role === 'tenant' ? 'Complaint' : 'Notice'; ?></button>
            </div>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4" id="complaintsGrid">
                <div class="col-span-full py-12 text-center text-slate-400"><div class="flex flex-col items-center gap-3"><i class="fas fa-spinner fa-spin text-3xl text-blue-400"></i><span>Loading complaints...</span></div></div>
            </div>
        </main>
    </div>

    <!-- NEW COMPLAINT/NOTICE MODAL -->
    <div id="modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm" onclick="if(event.target===this)closeModal()">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto p-6" onclick="event.stopPropagation()">
            <div class="flex items-center justify-between mb-4"><h3 class="text-lg font-bold text-slate-900" id="modalTitle">New Complaint</h3><button onclick="closeModal()" class="text-slate-400 hover:text-slate-600"><i class="fas fa-times text-xl"></i></button></div>
            <form id="complaintForm" class="space-y-4">
                <div><label class="block text-sm font-medium text-slate-700 mb-1">Title <span class="text-red-400">*</span></label><input type="text" id="compTitle" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all" placeholder="Brief subject" required></div>
                <div class="grid grid-cols-2 gap-4">
                    <div><label class="block text-sm font-medium text-slate-700 mb-1">Category</label><select id="compCategory" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all"><option>Maintenance</option><option>Noise</option><option>Security</option><option>Notice</option><option>Rent</option><option>Other</option></select></div>
                    <div><label class="block text-sm font-medium text-slate-700 mb-1">Priority</label><select id="compPriority" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all"><option>Low</option><option>Medium</option><option>High</option></select></div>
                </div>
                <div><label class="block text-sm font-medium text-slate-700 mb-1">Description <span class="text-red-400">*</span></label><textarea id="compDesc" rows="3" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all" placeholder="Describe your concern or notice..." required></textarea></div>

                <!-- Owner/Caretaker recipient selection -->
                <div id="recipientSection" class="<?php echo $role === 'tenant' ? 'hidden' : '' ?>">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Send To</label>
                    <select id="recipientType" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all mb-2" onchange="toggleRecipientFields()">
                        <option value="individual">Individual Tenant</option>
                        <option value="property">All Tenants in a Property</option>
                        <option value="all">All Managed Tenants</option>
                    </select>
                    <div id="tenantSelectDiv"><label class="block text-sm font-medium text-slate-700 mb-1">Tenant</label><select id="compTenant" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all"><option value="">Loading...</option></select></div>
                    <div id="propertySelectDiv" class="hidden"><label class="block text-sm font-medium text-slate-700 mb-1">Property</label><select id="compProperty" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all"><option value="">Loading...</option></select></div>
                </div>

                <div class="flex justify-end gap-3 pt-4"><button type="button" onclick="closeModal()" class="px-5 py-2.5 bg-white text-blue-700 border border-blue-200 rounded-xl font-medium hover:bg-blue-50 transition-all">Cancel</button><button type="submit" class="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold rounded-xl shadow-lg shadow-blue-500/30 transition-all">Submit</button></div>
            </form>
        </div>
    </div>

    <!-- COMPLAINT DETAIL MODAL -->
    <div id="complaintDetailModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm" onclick="if(event.target===this)closeComplaintDetailModal()">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-3xl max-h-[90vh] overflow-y-auto p-6" onclick="event.stopPropagation()">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 id="detailTitle" class="text-xl font-bold text-slate-900">Complaint Title</h3>
                    <p id="detailMeta" class="text-sm text-slate-500 mt-1"></p>
                </div>
                <button onclick="closeComplaintDetailModal()" class="text-slate-400 hover:text-slate-600"><i class="fas fa-times text-xl"></i></button>
            </div>
            <div class="space-y-4">
                <div class="bg-slate-50 rounded-xl p-4">
                    <h4 class="text-sm font-semibold text-slate-700 mb-2">Description</h4>
                    <p id="detailDescription" class="text-sm text-slate-600"></p>
                </div>
                <div>
                    <h4 class="text-sm font-semibold text-slate-700 mb-3">Updates & Replies</h4>
                    <div id="detailTimeline" class="space-y-3"></div>
                </div>
                <div id="replySection" class="hidden border-t border-slate-200 pt-4">
                    <h4 class="text-sm font-semibold text-slate-700 mb-2">Add Reply</h4>
                    <form id="detailReplyForm" class="space-y-3">
                        <textarea id="detailReplyText" rows="3" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all" placeholder="Type your reply..." required></textarea>
                        <div class="flex justify-end"><button type="submit" class="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold rounded-xl shadow-lg shadow-blue-500/30 transition-all">Send Reply</button></div>
                    </form>
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
    const token = localStorage.getItem('rf_token') || '<?php echo $token; ?>';
    const headers = token ? {'Authorization':'Bearer '+token, 'Content-Type':'application/json'} : {'Content-Type':'application/json'};
    const userRole = '<?php echo $role; ?>';

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

    async function loadComplaints() {
        try {
            const data = await apiRequest(`${API}/complaints`);
            const grid = document.getElementById('complaintsGrid');
            if (data.complaints && data.complaints.length) {
                grid.innerHTML = data.complaints.map(c => {
                    const isUnread = c.is_unread ? '<span class="ml-2 px-2 py-0.5 rounded-full text-xs font-bold bg-red-500 text-white">NEW</span>' : '';
                    const senderLabel = c.sender_role === 'tenant' ? (c.tenant_name || 'Tenant') : (c.sender_role === 'caretaker' ? (c.caretaker_name || 'Manager') : (c.owner_name || 'Owner'));
                    const isCaretakerPending = c.sender_role === 'caretaker' && c.status === 'pending_approval';
                    const statusLabel = c.status === 'pending_approval' ? 'Pending Approval' : c.status === 'rejected' ? 'Rejected' : c.status;
                    const statusClass = c.status==='pending_approval'?'bg-amber-100 text-amber-700':c.status==='rejected'?'bg-red-100 text-red-700':c.status==='open'?'bg-red-100 text-red-700':c.status==='in-progress'?'bg-blue-100 text-blue-700':'bg-emerald-100 text-emerald-700';
                    return `<div class="bg-white rounded-2xl shadow-sm border ${isCaretakerPending ? 'border-amber-200' : 'border-blue-100/50'} p-5 hover:shadow-md transition-shadow cursor-pointer ${c.is_unread ? 'border-l-4 border-l-blue-500' : ''}" onclick="viewComplaintDetail(${c.id})">
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-50 to-amber-100 text-amber-600 flex items-center justify-center"><i class="fas fa-${c.sender_role === 'tenant' ? 'wrench' : 'bullhorn'}"></i></div>
                                <div><h3 class="font-medium text-slate-900">${escapeHtml(c.title)}${isUnread}</h3><p class="text-xs text-slate-500">${c.category||'Other'} • ${c.property_name||''} ${c.unit||''}</p></div>
                            </div>
                            <span class="px-2 py-1 rounded-full text-xs font-medium ${statusClass}">${statusLabel}</span>
                        </div>
                        <p class="text-sm text-slate-600 mb-4 line-clamp-2">${escapeHtml(c.description || '')}</p>
                        <div class="flex items-center justify-between pt-3 border-t border-blue-50">
                            <div class="flex items-center gap-2"><div class="w-6 h-6 rounded-full bg-gradient-to-br from-blue-50 to-blue-100 text-blue-600 flex items-center justify-center text-xs font-bold">${(c.tenant_name||'U').split(' ').map(s=>s[0]).join('').substring(0,2).toUpperCase()}</div><span class="text-xs text-slate-500">From: ${escapeHtml(senderLabel)}</span></div>
                            <span class="text-xs text-slate-400"><i class="far fa-clock mr-1"></i>${new Date(c.date).toLocaleDateString('en-GB',{day:'numeric',month:'short',year:'numeric'})}</span>
                        </div>
                    </div>`;
                }).join('');
            } else {
                grid.innerHTML = '<div class="col-span-full py-12 text-center text-slate-400">No complaints or notices found</div>';
            }
        } catch(e) { console.error(e); }
    }

    async function viewComplaintDetail(complaintId) {
        try {
            const data = await apiRequest(`${API}/complaints/${complaintId}`);
            const c = data.complaint;
            const senderLabel = c.sender_role === 'tenant' ? (c.tenant_name || 'Tenant') : (c.sender_role === 'caretaker' ? (c.caretaker_name || 'Manager') : (c.owner_name || 'Owner'));
            document.getElementById('detailTitle').textContent = c.title || 'Untitled';
            document.getElementById('detailMeta').textContent = `${c.category||'Other'} • ${c.property_name||''} ${c.unit||''} • ${c.date ? new Date(c.date).toLocaleDateString('en-GB') : ''}`;
            document.getElementById('detailDescription').textContent = c.description || 'No description';
            document.getElementById('replySection').classList.toggle('hidden', false);
            let html = '<div class="space-y-3">';
            html += '<div class="border-t border-slate-200 pt-3"><h5 class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">All Updates</h5>';
            let comments = [];
            try {
                const raw = c.comments || [];
                comments = Array.isArray(raw) ? raw : (typeof raw === 'string' ? JSON.parse(raw) : []);
            } catch(e) { html += '<p class="text-sm text-red-400">Error loading updates</p>'; }
            // Filter out any comment that is essentially the same as the description from the same sender
            comments = comments.filter(comment => {
                if (!comment.text || !c.description) return true;
                const commentText = comment.text.replace(/\s+/g, ' ').trim();
                const descriptionText = c.description.replace(/\s+/g, ' ').trim();
                const commentSender = (comment.user || '').trim();
                const descriptionSender = (senderLabel || '').trim();
                return !(commentText === descriptionText && commentSender === descriptionSender);
            });
            if (!comments.length) { html += '<p class="text-sm text-slate-400 italic">No updates yet</p>'; }
            else { comments.forEach(comment => { const isOwner = comment.role === 'owner' || comment.role === 'caretaker'; html += '<div class="' + (isOwner ? 'bg-blue-50' : 'bg-amber-50') + ' rounded-lg p-3 mt-2"><div class="flex items-center justify-between mb-1"><span class="text-xs font-semibold text-slate-700">' + escapeHtml(comment.user || 'Unknown') + ' <span class="text-slate-400 font-normal">(' + comment.role + ')</span></span><span class="text-xs text-slate-400">' + (comment.date || '') + '</span></div><p class="text-sm text-slate-600">' + escapeHtml(comment.text || '') + '</p></div>'; }); }
            html += '</div></div>';
            // Add approve/reject buttons for owner if caretaker complaint is pending
            if (userRole === 'owner' && c.sender_role === 'caretaker' && c.status === 'pending_approval') {
                html += '<div class="border-t border-slate-200 pt-4 mt-4 flex gap-3">';
                html += '<button onclick="approveComplaint(' + c.id + ')" class="px-4 py-2 bg-gradient-to-r from-emerald-500 to-emerald-600 text-white font-semibold rounded-xl shadow-lg shadow-emerald-500/30 hover:shadow-emerald-500/40 transition-all">Approve</button>';
                html += '<button onclick="rejectComplaint(' + c.id + ')" class="px-4 py-2 bg-gradient-to-r from-red-500 to-red-600 text-white font-semibold rounded-xl shadow-lg shadow-red-500/30 hover:shadow-red-500/40 transition-all">Reject</button>';
                html += '</div>';
            }
            document.getElementById('detailTimeline').innerHTML = html;
            document.getElementById('complaintDetailModal').classList.remove('hidden');
            window.currentDetailComplaintId = complaintId;
        } catch(e) { console.error(e); toast('Error: ' + e.message, 'error'); }
    }

    async function approveComplaint(complaintId) {
        if (!confirm('Approve this complaint? It will be visible to tenants.')) return;
        try {
            await apiRequest(`${API}/complaints/${complaintId}/approve`, { method:'POST' });
            toast('Complaint approved');
            viewComplaintDetail(complaintId);
            loadComplaints();
        } catch(err) { toast(err.message, 'error'); }
    }

    async function rejectComplaint(complaintId) {
        if (!confirm('Reject this complaint? It will not be sent to tenants.')) return;
        try {
            await apiRequest(`${API}/complaints/${complaintId}/reject`, { method:'POST' });
            toast('Complaint rejected');
            viewComplaintDetail(complaintId);
            loadComplaints();
        } catch(err) { toast(err.message, 'error'); }
    }

    function closeComplaintDetailModal() { document.getElementById('complaintDetailModal').classList.add('hidden'); window.currentDetailComplaintId = null; }
    function openModal() { document.getElementById('modal').classList.remove('hidden'); }
    function closeModal() { document.getElementById('modal').classList.add('hidden'); }

    function toggleRecipientFields() {
        const type = document.getElementById('recipientType').value;
        document.getElementById('tenantSelectDiv').classList.toggle('hidden', type !== 'individual');
        document.getElementById('propertySelectDiv').classList.toggle('hidden', type !== 'property');
    }

    // Load tenants/properties for owner selects
    async function loadSelectOptions() {
        if (userRole === 'tenant') return;
        try {
            const tData = await apiRequest(`${API}/tenants`);
            const tSelect = document.getElementById('compTenant');
            if (tSelect && tData.tenants) { tSelect.innerHTML = '<option value="">Select tenant...</option>' + tData.tenants.map(t => `<option value="${t.id}">${escapeHtml(t.name)} (${t.house_unit || 'No unit'})</option>`).join(''); }
            const pData = await apiRequest(`${API}/properties`);
            const pSelect = document.getElementById('compProperty');
            if (pSelect && pData.properties) { pSelect.innerHTML = '<option value="">Select property...</option>' + pData.properties.map(p => `<option value="${p.id}">${escapeHtml(p.name)}</option>`).join(''); }
        } catch(e) { console.error('Failed to load select options', e); }
    }

    document.getElementById('complaintForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const btn = e.target.querySelector('button[type="submit"]');
        const originalText = btn.textContent;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
        const data = {
            title: document.getElementById('compTitle').value,
            category: document.getElementById('compCategory').value,
            priority: document.getElementById('compPriority').value,
            description: document.getElementById('compDesc').value,
        };
        if (userRole !== 'tenant') {
            data.recipient_type = document.getElementById('recipientType').value;
            if (data.recipient_type === 'individual') data.tenant_id = document.getElementById('compTenant').value;
            if (data.recipient_type === 'property') data.property_id = document.getElementById('compProperty').value;
        }
        try {
            const result = await apiRequest(`${API}/complaints`, { method:'POST', body:JSON.stringify(data) });
            toast('Submitted successfully!');
            closeModal();
            loadComplaints();
        } catch(err) { toast(err.message, 'error'); }
        finally {
            btn.disabled = false;
            btn.textContent = originalText;
        }
    });

    document.getElementById('detailReplyForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        if (!window.currentDetailComplaintId) return;
        const commentText = document.getElementById('detailReplyText').value.trim();
        if (!commentText) { toast('Please enter a reply', 'error'); return; }
        const btn = e.target.querySelector('button[type="submit"]');
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
        try {
            const payload = { comments: commentText, comment_user: '<?php echo htmlspecialchars($user['name'] ?? 'User'); ?>' };
            if (userRole === 'tenant') {
                payload.status = 'in-progress';
            }
            const result = await apiRequest(`${API}/complaints/${window.currentDetailComplaintId}`, { method:'PUT', body: JSON.stringify(payload) });
            toast('Reply sent!');
            document.getElementById('detailReplyText').value = '';
            viewComplaintDetail(window.currentDetailComplaintId);
            loadComplaints();
        } catch(err) { toast(err.message, 'error'); }
        finally {
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    });

    loadComplaints();
    loadSelectOptions();
    // Refresh sidebar counts after page loads so unread badges clear when tenant views complaints
    setTimeout(() => { if (typeof refreshSidebarCounts === 'function') refreshSidebarCounts(); }, 300);
    </script>
</body>
</html>
