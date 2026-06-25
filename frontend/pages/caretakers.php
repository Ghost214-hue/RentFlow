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
    <title>Caretakers - RentFlow</title>
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
                <div><h1 class="text-2xl font-bold text-slate-900">Caretakers</h1><p class="text-slate-500 mt-1">Manage caretakers and assign them to your properties.</p></div>
                <button onclick="openCaretakerModal()" class="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold rounded-xl shadow-lg shadow-blue-500/30 hover:shadow-blue-500/40 transition-all inline-flex items-center gap-2"><i class="fas fa-user-plus"></i>Add Caretaker</button>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border border-blue-100/50 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead><tr class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wider border-b border-blue-50 bg-blue-50/50">
                            <th class="px-6 py-4">Name</th>
                            <th class="px-6 py-4">Email</th>
                            <th class="px-6 py-4">Phone</th>
                            <th class="px-6 py-4">Assigned Properties</th>
                            <th class="px-6 py-4">Actions</th>
                        </tr></thead>
                        <tbody class="divide-y divide-blue-50" id="caretakersTable">
                            <tr><td colspan="5" class="px-6 py-12 text-center text-slate-400">Loading caretakers...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <div id="caretakerModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm" onclick="if(event.target===this)closeCaretakerModal()">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto p-6" onclick="event.stopPropagation()">
            <div class="flex items-center justify-between mb-4">
                <div><h3 class="text-lg font-bold text-slate-900">Add Caretaker</h3><p class="text-sm text-slate-500 mt-1">Add a caretaker and assign properties they can manage.</p></div>
                <button onclick="closeCaretakerModal()" class="text-slate-400 hover:text-slate-600 transition-colors"><i class="fas fa-times text-xl"></i></button>
            </div>
            <form id="caretakerForm" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div><label class="block text-sm font-medium text-slate-700 mb-1">Full Name</label><input type="text" id="caretakerName" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all" placeholder="e.g. James Kariuki" required></div>
                    <div><label class="block text-sm font-medium text-slate-700 mb-1">Email Address</label><input type="email" id="caretakerEmail" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all" placeholder="caretaker@example.com" required></div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div><label class="block text-sm font-medium text-slate-700 mb-1">Phone Number</label><input type="tel" id="caretakerPhone" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all" placeholder="+254 712 345 678"></div>
                    <div><label class="block text-sm font-medium text-slate-700 mb-1">National ID</label><input type="text" id="caretakerId" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all" placeholder="ID number" required></div>
                </div>
                <div><label class="block text-sm font-medium text-slate-700 mb-1">Password <span class="text-slate-400 text-xs">optional</span></label><input type="password" id="caretakerPassword" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all" placeholder="Leave blank to use ID number"></div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Assign Properties</label>
                    <select id="caretakerProperties" multiple class="w-full h-40 px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all">
                    </select>
                    <p class="text-xs text-slate-400 mt-2">Hold Ctrl/Cmd to select multiple properties.</p>
                </div>
                <div class="flex justify-end gap-3 pt-4">
                    <button type="button" onclick="closeCaretakerModal()" class="px-5 py-2.5 bg-white text-blue-700 border border-blue-200 rounded-xl font-medium hover:bg-blue-50 transition-all">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold rounded-xl shadow-lg shadow-blue-500/30 transition-all">Save Caretaker</button>
                </div>
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

    async function loadCaretakers() {
        try {
            const res = await fetch(`${API}/caretakers`, { headers });
            const data = await res.json();
            const tbody = document.getElementById('caretakersTable');
            if (!res.ok) throw new Error(data.error || 'Failed to load caretakers');
            if (data.caretakers && data.caretakers.length) {
                tbody.innerHTML = data.caretakers.map(c => `
                    <tr class="hover:bg-blue-50/30 transition-colors">
                        <td class="px-6 py-4"><div class="flex items-center gap-3"><div class="w-9 h-9 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-semibold">${(c.name||'CT').split(' ').map(s=>s[0]).join('').substring(0,2).toUpperCase()}</div><div><p class="text-sm font-medium text-slate-900">${c.name}</p></div></div></td>
                        <td class="px-6 py-4 text-sm text-slate-600">${c.email || 'N/A'}</td>
                        <td class="px-6 py-4 text-sm text-slate-600">${c.phone || 'N/A'}</td>
                        <td class="px-6 py-4 text-sm text-slate-900">${c.property_count || 0}</td>
                        <td class="px-6 py-4"><button onclick="deleteCaretaker(${c.id})" class="px-3 py-2 text-sm font-medium text-red-600 bg-red-50 rounded-xl hover:bg-red-100 transition-colors">Remove</button></td>
                    </tr>
                `).join('');
            } else {
                tbody.innerHTML = '<tr><td colspan="5" class="px-6 py-12 text-center text-slate-400">No caretakers found. Add a caretaker to assign them properties.</td></tr>';
            }
        } catch(e) {
            console.error('loadCaretakers error:', e);
            document.getElementById('caretakersTable').innerHTML = `<tr><td colspan="5" class="px-6 py-12 text-center text-red-400">Error: ${e.message}</td></tr>`;
            if (e.message.includes('401')) window.location.href = '/signin';
        }
    }

    async function loadProperties() {
        try {
            const res = await fetch(`${API}/properties`, { headers });
            const data = await res.json();
            const select = document.getElementById('caretakerProperties');
            if (!res.ok) throw new Error(data.error || 'Failed to load properties');
            if (data.properties && data.properties.length) {
                select.innerHTML = data.properties.map(p => `<option value="${p.id}">${p.name}</option>`).join('');
            } else {
                select.innerHTML = '<option value="">No properties available</option>';
            }
        } catch(e) {
            console.error('loadProperties error:', e);
            toast('Could not load properties: ' + e.message, 'error');
        }
    }

    function openCaretakerModal() {
        document.getElementById('caretakerModal').classList.remove('hidden');
        document.getElementById('caretakerForm').reset();
        loadProperties();
    }

    function closeCaretakerModal() {
        document.getElementById('caretakerModal').classList.add('hidden');
    }

    document.getElementById('caretakerForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const selectedOptions = Array.from(document.getElementById('caretakerProperties').selectedOptions).map(opt => opt.value);
        const data = {
            name: document.getElementById('caretakerName').value.trim(),
            email: document.getElementById('caretakerEmail').value.trim(),
            phone: document.getElementById('caretakerPhone').value.trim() || null,
            id_number: document.getElementById('caretakerId').value.trim(),
            password: document.getElementById('caretakerPassword').value || null,
            assigned_properties: selectedOptions.join(','),
        };

        try {
            const res = await fetch(`${API}/caretakers`, { method: 'POST', headers, body: JSON.stringify(data) });
            const result = await res.json();
            if (!res.ok) throw new Error(result.error || 'Failed to save caretaker');
            toast('Caretaker added successfully!');
            closeCaretakerModal();
            loadCaretakers();
        } catch(err) {
            console.error('create caretaker error:', err);
            toast(err.message, 'error');
        }
    });

    async function deleteCaretaker(id) {
        if (!confirm('Remove this caretaker?')) return;
        try {
            const res = await fetch(`${API}/caretakers/${id}`, { method: 'DELETE', headers });
            const result = await res.json();
            if (!res.ok) throw new Error(result.error || 'Failed to remove caretaker');
            toast('Caretaker removed');
            loadCaretakers();
        } catch(err) {
            console.error('delete caretaker error:', err);
            toast(err.message, 'error');
        }
    }

    loadProperties();
    loadCaretakers();
    </script>
</body>
</html>
