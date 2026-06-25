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
    <title>Complaints - RentFlow</title>
    <link rel="stylesheet" href="/css/output.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body class="bg-gradient-to-br from-blue-50 via-white to-blue-50/30 min-h-screen font-sans text-slate-800 flex overflow-hidden">
    <?php include __DIR__ . '/../public/components/sidebar.php'; ?>
    <div class="flex-1 flex flex-col min-h-screen">
        <?php include __DIR__ . '/../public/components/header.php'; ?>
        <main class="flex-1 overflow-y-auto p-4 lg:p-8">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                <div><h1 class="text-2xl font-bold text-slate-900">Complaints</h1><p class="text-slate-500 mt-1">Track maintenance issues</p></div>
                <button onclick="openModal()" class="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold rounded-xl shadow-lg shadow-blue-500/30 hover:shadow-blue-500/40 transition-all inline-flex items-center gap-2"><i class="fas fa-plus"></i>New Complaint</button>
            </div>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4" id="complaintsGrid">
                <div class="col-span-full py-12 text-center text-slate-400">Loading...</div>
            </div>
        </main>
    </div>
    <div id="modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm" onclick="if(event.target===this)closeModal()">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto p-6" onclick="event.stopPropagation()">
            <div class="flex items-center justify-between mb-4"><h3 class="text-lg font-bold text-slate-900">New Complaint</h3><button onclick="closeModal()" class="text-slate-400 hover:text-slate-600"><i class="fas fa-times text-xl"></i></button></div>
            <form id="complaintForm" class="space-y-4">
                <div><label class="block text-sm font-medium text-slate-700 mb-1">Title</label><input type="text" id="compTitle" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all" placeholder="Brief description" required></div>
                <div class="grid grid-cols-2 gap-4">
                    <div><label class="block text-sm font-medium text-slate-700 mb-1">Category</label><select id="compCategory" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all"><option>Plumbing</option><option>Electrical</option><option>Security</option><option>Other</option></select></div>
                    <div><label class="block text-sm font-medium text-slate-700 mb-1">Priority</label><select id="compPriority" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all"><option>Low</option><option>Medium</option><option>High</option></select></div>
                </div>
                <div><label class="block text-sm font-medium text-slate-700 mb-1">Description</label><textarea id="compDesc" rows="3" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all" placeholder="Describe the issue..."></textarea></div>
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
                <!-- Description -->
                <div class="bg-slate-50 rounded-xl p-4">
                    <h4 class="text-sm font-semibold text-slate-700 mb-2">Description</h4>
                    <p id="detailDescription" class="text-sm text-slate-600"></p>
                </div>
                
                <!-- Timeline & Comments -->
                <div>
                    <h4 class="text-sm font-semibold text-slate-700 mb-3">Updates & Replies</h4>
                    <div id="detailTimeline" class="space-y-3">
                        <!-- Timeline items will be loaded here -->
                    </div>
                </div>
                
                <!-- Reply Form (for owners/caretakers) -->
                <div id="replySection" class="hidden border-t border-slate-200 pt-4">
                    <h4 class="text-sm font-semibold text-slate-700 mb-2">Add Reply</h4>
                    <form id="detailReplyForm" class="space-y-3">
                        <textarea id="detailReplyText" rows="3" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all" placeholder="Type your reply..." required></textarea>
                        <div class="flex justify-end">
                            <button type="submit" class="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold rounded-xl shadow-lg shadow-blue-500/30 transition-all">Send Reply</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- REPLY MODAL -->
    <div id="replyModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm" onclick="if(event.target===this)closeReplyModal()">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto p-6" onclick="event.stopPropagation()">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-lg font-bold text-slate-900">Reply to Complaint</h3>
                    <p id="replyComplaintTitle" class="text-sm text-slate-500 mt-1"></p>
                </div>
                <button onclick="closeReplyModal()" class="text-slate-400 hover:text-slate-600"><i class="fas fa-times text-xl"></i></button>
            </div>
            <form id="replyForm" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Your Reply</label>
                    <textarea id="replyText" rows="4" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all" placeholder="Type your response or update here..." required></textarea>
                </div>
                <div class="flex justify-end gap-3 pt-4">
                    <button type="button" onclick="closeReplyModal()" class="px-5 py-2.5 bg-white text-blue-700 border border-blue-200 rounded-xl font-medium hover:bg-blue-50 transition-all">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold rounded-xl shadow-lg shadow-blue-500/30 transition-all">Send Reply</button>
                </div>
            </form>
        </div>
    </div>
    
    <div id="toast" class="fixed bottom-6 right-6 z-50 hidden px-5 py-3 rounded-xl shadow-xl text-white font-medium flex items-center gap-2"></div>
    <script>
    const API = '/api';
    const token = localStorage.getItem('rf_token') || '<?php echo $token; ?>';
    const headers = token ? {'Authorization':'Bearer '+token, 'Content-Type':'application/json'} : {'Content-Type':'application/json'};
    
    function escapeHtml(value) {
        const div = document.createElement('div');
        div.textContent = value;
        return div.innerHTML;
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

    async function loadComplaints() {
        try {
            const res = await fetch(`${API}/complaints`, { headers });
            const data = await res.json();
            const grid = document.getElementById('complaintsGrid');
            const userRole = '<?php echo $role; ?>';
            const canReply = userRole === 'owner' || userRole === 'caretaker';
            
            if (data.complaints && data.complaints.length) {
                grid.innerHTML = data.complaints.map(c => `
                    <div class="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-5 hover:shadow-md transition-shadow cursor-pointer" onclick="viewComplaintDetail(${c.id})">
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-50 to-amber-100 text-amber-600 flex items-center justify-center"><i class="fas fa-wrench"></i></div>
                                <div><h3 class="font-medium text-slate-900">${c.title}</h3><p class="text-xs text-slate-500">${c.category||'Other'} • ${c.property_name||''} ${c.unit||''}</p></div>
                            </div>
                            <span class="px-2 py-1 rounded-full text-xs font-medium ${c.status==='open'?'bg-red-100 text-red-700':c.status==='in-progress'?'bg-blue-100 text-blue-700':'bg-emerald-100 text-emerald-700'}">${c.status}</span>
                        </div>
                        <p class="text-sm text-slate-600 mb-4 line-clamp-2">${c.description||''}</p>
                        <div class="flex items-center justify-between pt-3 border-t border-blue-50">
                            <div class="flex items-center gap-2"><div class="w-6 h-6 rounded-full bg-gradient-to-br from-blue-50 to-blue-100 text-blue-600 flex items-center justify-center text-xs font-bold">${(c.tenant_name||'U').split(' ').map(s=>s[0]).join('').substring(0,2).toUpperCase()}</div><span class="text-xs text-slate-500">${c.tenant_name||'N/A'}</span></div>
                            <div class="flex items-center gap-2">
                                ${canReply ? `<button onclick="event.stopPropagation(); openReplyModal(${c.id}, '${c.title.replace(/'/g, "\\'")}')" class="text-xs text-blue-600 hover:text-blue-700 font-medium"><i class="fas fa-reply mr-1"></i>Reply</button>` : ''}
                                <span class="text-xs text-slate-400"><i class="far fa-clock mr-1"></i>${new Date(c.date).toLocaleDateString('en-GB',{day:'numeric',month:'short',year:'numeric'})}</span>
                            </div>
                        </div>
                    </div>
                `).join('');
            } else {
                grid.innerHTML = '<div class="col-span-full py-12 text-center text-slate-400">No complaints found</div>';
            }
        } catch(e) { console.error(e); if (e.message.includes('401')) window.location.href = '/signin'; }
    }
    
    async function viewComplaintDetail(complaintId) {
        try {
            console.log('Loading complaint:', complaintId);
            const url = `${API}/complaints/${complaintId}`;
            console.log('Fetching:', url);
            
            const res = await fetch(url, { headers });
            console.log('Status:', res.status, res.statusText);
            
            if (!res.ok) {
                const text = await res.text();
                console.error('Error response:', text);
                throw new Error(`HTTP ${res.status}: ${res.statusText}`);
            }
            
            const data = await res.json();
            console.log('Data received:', data);
            
            if (!data.complaint) {
                throw new Error('Invalid response format');
            }
            
            const c = data.complaint;
            const userRole = '<?php echo $role; ?>';
            const canReply = userRole === 'owner' || userRole === 'caretaker';
            
            // Populate modal
            document.getElementById('detailTitle').textContent = c.title || 'Untitled';
            document.getElementById('detailMeta').textContent = `${c.category||'Other'} • ${c.property_name||''} ${c.unit||''} • ${c.date ? new Date(c.date).toLocaleDateString('en-GB') : ''}`;
            document.getElementById('detailDescription').textContent = c.description || 'No description';
            
            // Only show reply section for owners and caretakers
            document.getElementById('replySection').classList.toggle('hidden', !canReply);
            
            // Build timeline - show for ALL users including tenants
            let html = '<div class="space-y-3">';
            
            // Show original complaint
            html += '<div class="bg-slate-50 rounded-lg p-3 border-l-4 border-blue-500">';
            html += '<div class="flex items-center justify-between mb-1">';
            html += '<span class="text-xs font-semibold text-slate-700">Original Complaint</span>';
            html += '<span class="text-xs text-slate-400">' + (c.date ? new Date(c.date).toLocaleDateString('en-GB') : '') + '</span>';
            html += '</div>';
            html += '<p class="text-sm text-slate-600">' + (c.description || 'No description provided') + '</p>';
            html += '</div>';
            
            // Show all comments/replies
            html += '<div class="border-t border-slate-200 pt-3 mt-3">';
            html += '<h5 class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">All Replies</h5>';
            
            // Parse comments - backend already decodes JSON, so handle both array and string
            let comments = [];
            try {
                const commentsData = c.comments || [];
                console.log('Comments data:', commentsData);
                console.log('Comments type:', typeof commentsData);
                
                // If it's already an array (decoded by backend), use it directly
                if (Array.isArray(commentsData)) {
                    comments = commentsData;
                } 
                // If it's a string, parse it
                else if (typeof commentsData === 'string') {
                    const parsed = JSON.parse(commentsData);
                    if (Array.isArray(parsed)) {
                        comments = parsed;
                    }
                }
                
                console.log('Final comments array:', comments);
            } catch(e) {
                console.error('Error processing comments:', e);
                html += '<p class="text-sm text-red-400">Error loading replies</p>';
            }
            
            if (comments.length === 0) {
                html += '<p class="text-sm text-slate-400 italic">No replies yet</p>';
            } else {
                comments.forEach(function(comment) {
                    const isOwner = comment.role === 'owner';
                    const bgColor = isOwner ? 'bg-blue-50' : 'bg-amber-50';
                    const icon = isOwner ? 'fa-user' : 'fa-user-tie';
                    const userName = escapeHtml(comment.user || 'Unknown');
                    const commentText = escapeHtml(comment.text || '');
                    const commentDate = comment.date || '';
                    
                    html += '<div class="' + bgColor + ' rounded-lg p-3 mt-2">';
                    html += '<div class="flex items-center justify-between mb-1">';
                    html += '<span class="text-xs font-semibold text-slate-700">' + userName + ' <span class="text-slate-400 font-normal">(' + comment.role + ')</span></span>';
                    html += '<span class="text-xs text-slate-400">' + commentDate + '</span>';
                    html += '</div>';
                    html += '<p class="text-sm text-slate-600">' + commentText + '</p>';
                    html += '</div>';
                });
            }
            
            html += '</div></div>';
            
            document.getElementById('detailTimeline').innerHTML = html;
            document.getElementById('complaintDetailModal').classList.remove('hidden');
            window.currentDetailComplaintId = complaintId;
            
        } catch(e) {
            console.error('Error:', e);
            toast('Error: ' + e.message, 'error');
        }
    }
    
    function closeComplaintDetailModal() { 
        document.getElementById('complaintDetailModal').classList.add('hidden');
        window.currentDetailComplaintId = null;
    }

    function openModal() { document.getElementById('modal').classList.remove('hidden'); }
    function closeModal() { document.getElementById('modal').classList.add('hidden'); }
    
    let currentComplaintId = null;
    
    function openReplyModal(complaintId, complaintTitle) {
        currentComplaintId = complaintId;
        document.getElementById('replyComplaintTitle').textContent = 'Re: ' + complaintTitle;
        document.getElementById('replyText').value = '';
        document.getElementById('replyModal').classList.remove('hidden');
    }
    
    function closeReplyModal() { 
        document.getElementById('replyModal').classList.add('hidden');
        currentComplaintId = null;
    }

    document.getElementById('complaintForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const data = {
            title: document.getElementById('compTitle').value,
            category: document.getElementById('compCategory').value,
            priority: document.getElementById('compPriority').value,
            description: document.getElementById('compDesc').value,
        };
        try {
            const res = await fetch(`${API}/complaints`, { method:'POST', headers, body:JSON.stringify(data) });
            const result = await res.json();
            if(!res.ok) throw new Error(result.error || 'Failed');
            toast('Complaint submitted!');
            closeModal();
            loadComplaints();
        } catch(err) { toast(err.message, 'error'); }
    });
    
    document.getElementById('replyForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        if (!currentComplaintId) return;
        
        const commentText = document.getElementById('replyText').value.trim();
        if (!commentText) { toast('Please enter a reply', 'error'); return; }
        
        try {
            const res = await fetch(`${API}/complaints/${currentComplaintId}`, {
                method: 'PUT',
                headers,
                body: JSON.stringify({
                    status: 'in-progress',
                    comments: commentText,
                    comment_user: '<?php echo htmlspecialchars($user['name'] ?? 'User'); ?>'
                })
            });
            const result = await res.json();
            if(!res.ok) throw new Error(result.error || 'Failed');
            toast('Reply sent!');
            closeReplyModal();
            loadComplaints();
        } catch(err) { toast(err.message, 'error'); }
    });
    
    document.getElementById('detailReplyForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        if (!window.currentDetailComplaintId) return;
        
        const commentText = document.getElementById('detailReplyText').value.trim();
        if (!commentText) { toast('Please enter a reply', 'error'); return; }
        
        try {
            const res = await fetch(`${API}/complaints/${window.currentDetailComplaintId}`, {
                method: 'PUT',
                headers,
                body: JSON.stringify({
                    status: 'in-progress',
                    comments: commentText,
                    comment_user: '<?php echo htmlspecialchars($user['name'] ?? 'User'); ?>'
                })
            });
            const result = await res.json();
            if(!res.ok) throw new Error(result.error || 'Failed');
            toast('Reply sent!');
            closeComplaintDetailModal();
            loadComplaints();
        } catch(err) { toast(err.message, 'error'); }
    });

    loadComplaints();
    </script>
</body>
</html>