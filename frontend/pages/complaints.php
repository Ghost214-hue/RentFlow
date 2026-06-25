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
if ($role !== 'owner') { header('Location: /signin'); exit; }
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

    async function loadComplaints() {
        try {
            const res = await fetch(`${API}/complaints`, { headers });
            const data = await res.json();
            const grid = document.getElementById('complaintsGrid');
            if (data.complaints && data.complaints.length) {
                grid.innerHTML = data.complaints.map(c => `
                    <div class="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-5 hover:shadow-md transition-shadow cursor-pointer" onclick="toast('View complaint','info')">
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
                            <span class="text-xs text-slate-400"><i class="far fa-clock mr-1"></i>${new Date(c.date).toLocaleDateString('en-GB',{day:'numeric',month:'short',year:'numeric'})}</span>
                        </div>
                    </div>
                `).join('');
            } else {
                grid.innerHTML = '<div class="col-span-full py-12 text-center text-slate-400">No complaints found</div>';
            }
        } catch(e) { console.error(e); if (e.message.includes('401')) window.location.href = '/signin'; }
    }

    function openModal() { document.getElementById('modal').classList.remove('hidden'); }
    function closeModal() { document.getElementById('modal').classList.add('hidden'); }

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

    loadComplaints();
    </script>
</body>
</html>