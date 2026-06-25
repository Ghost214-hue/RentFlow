<?php
require_once __DIR__ . '/../includes/auth.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Properties - RentFlow</title>
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
                <div><h1 class="text-2xl font-bold text-slate-900">Properties</h1><p class="text-slate-500 mt-1">Manage your rental properties</p></div>
                <?php if ($role === 'owner'): ?>
                <button onclick="openModal('property')" class="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold rounded-xl shadow-lg shadow-blue-500/30 hover:shadow-blue-500/40 transition-all inline-flex items-center gap-2"><i class="fas fa-plus"></i>Add Property</button>
                <?php endif; ?>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6" id="propertiesGrid">
                <div class="col-span-full py-12 text-center text-slate-400">Loading properties...</div>
            </div>
        </main>
    </div>
    <div id="modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm" onclick="if(event.target===this)closeModal()">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto p-6" onclick="event.stopPropagation()">
            <div class="flex items-center justify-between mb-4"><h3 class="text-lg font-bold text-slate-900" id="modalTitle">Add Property</h3><button onclick="closeModal()" class="text-slate-400 hover:text-slate-600"><i class="fas fa-times text-xl"></i></button></div>
            <form id="propertyForm" class="space-y-4">
                <div><label class="block text-sm font-medium text-slate-700 mb-1">Name</label><input type="text" id="propName" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all" placeholder="e.g. Sunrise Apartments" required></div>
                <div><label class="block text-sm font-medium text-slate-700 mb-1">Address</label><input type="text" id="propAddress" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all" placeholder="Full address" required></div>
                <div class="grid grid-cols-2 gap-4">
                    <div><label class="block text-sm font-medium text-slate-700 mb-1">Type</label><select id="propType" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all"><option>Apartment Block</option><option>Townhouses</option><option>Studio</option><option>Villa</option></select></div>
                    <div><label class="block text-sm font-medium text-slate-700 mb-1">Units</label><input type="number" id="propUnits" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all" placeholder="0" required></div>
                </div>
                <div class="flex justify-end gap-3 pt-4"><button type="button" onclick="closeModal()" class="px-5 py-2.5 bg-white text-blue-700 border border-blue-200 rounded-xl font-medium hover:bg-blue-50 transition-all">Cancel</button><button type="submit" class="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold rounded-xl shadow-lg shadow-blue-500/30 transition-all">Save</button></div>
            </form>
        </div>
    </div>
    <div id="toast" class="fixed bottom-6 right-6 z-50 hidden px-5 py-3 rounded-xl shadow-xl text-white font-medium flex items-center gap-2"></div>
    <script>
    const API = '/api';
    const token = localStorage.getItem('rf_token') || '<?php echo $token; ?>';
    const headers = token ? {'Authorization':'Bearer '+token, 'Content-Type':'application/json'} : {'Content-Type':'application/json'};

    function initials(n) { return n.split(' ').map(s=>s[0]).join('').substring(0,2).toUpperCase(); }
    function toast(msg, type='success') {
        const el = document.getElementById('toast');
        const colors = { success:'bg-gradient-to-r from-emerald-500 to-emerald-600', error:'bg-gradient-to-r from-red-500 to-red-600', info:'bg-gradient-to-r from-blue-500 to-blue-600' };
        const icons = { success:'fa-check-circle', error:'fa-exclamation-circle', info:'fa-info-circle' };
        el.className = `fixed bottom-6 right-6 z-50 px-5 py-3 rounded-xl shadow-xl text-white font-medium flex items-center gap-2 ${colors[type]||colors.success}`;
        el.innerHTML = `<i class="fas ${icons[type]||icons.success}"></i>${msg}`;
        el.classList.remove('hidden');
        setTimeout(() => el.classList.add('hidden'), 3000);
    }

    async function loadProperties() {
        try {
            console.log('Loading properties with token:', token ? 'yes' : 'no');
            const res = await fetch(`${API}/properties`, { headers });
            console.log('Properties response:', res.status);
            const data = await res.json();
            console.log('Properties data:', data);
            const grid = document.getElementById('propertiesGrid');
            if (data.properties && data.properties.length) {
                grid.innerHTML = data.properties.map(p => `
                    <div class="bg-white rounded-2xl shadow-sm border border-blue-100/50 overflow-hidden group hover:shadow-md transition-all duration-300">
                        <div class="relative h-48 overflow-hidden bg-gradient-to-br from-blue-100 to-blue-200">
                            <div class="w-full h-full flex items-center justify-center">
                                <div class="text-6xl font-bold text-white/80 drop-shadow-lg">${initials(p.name)}</div>
                            </div>
                            <div class="absolute inset-0 bg-gradient-to-t from-black/40 to-transparent"></div>
                            <div class="absolute bottom-4 left-4 right-4"><h3 class="text-lg font-bold text-white">${p.name}</h3><p class="text-sm text-white/80"><i class="fas fa-map-marker-alt mr-1"></i>${p.address}</p></div>
                            <div class="absolute top-4 right-4"><span class="px-2 py-1 bg-white/90 backdrop-blur rounded-lg text-xs font-medium text-slate-700">${p.type||'N/A'}</span></div>
                        </div>
                        <div class="p-5">
                            <div class="grid grid-cols-3 gap-4 mb-4">
                                <div class="text-center"><p class="text-lg font-bold text-slate-900">${p.unit_count||p.units||0}</p><p class="text-xs text-slate-500">Units</p></div>
                                <div class="text-center"><p class="text-lg font-bold text-emerald-600">${p.occupied_count||p.occupied||0}</p><p class="text-xs text-slate-500">Occupied</p></div>
                                <div class="text-center"><p class="text-lg font-bold text-slate-900">KES ${(p.rent||0).toLocaleString()}</p><p class="text-xs text-slate-500">Avg Rent</p></div>
                            </div>
                            <div class="flex items-center gap-2 mb-4">
                                <div class="flex-1 h-2 bg-slate-100 rounded-full overflow-hidden"><div class="h-full bg-gradient-to-r from-blue-500 to-blue-600 rounded-full" style="width:${((p.occupied_count||p.occupied||0)/(p.unit_count||p.units||1))*100}%"></div></div>
                                <span class="text-xs text-slate-500">${Math.round(((p.occupied_count||p.occupied||0)/(p.unit_count||p.units||1))*100)}%</span>
                            </div>
                            <div class="flex gap-2">
                                <a href="/houses?property_id=${p.id}" class="flex-1 py-2 text-sm font-medium text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-xl transition-colors text-center">Manage</a>
                                <?php if ($role === 'owner'): ?>
                                <button onclick="deleteProperty(${p.id})" class="px-3 py-2 text-sm font-medium text-red-600 bg-red-50 hover:bg-red-100 rounded-xl transition-colors"><i class="fas fa-trash"></i></button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                `).join('');
            } else {
                grid.innerHTML = '<div class="col-span-full py-12 text-center text-slate-400">No properties found. Add your first property!</div>';
            }
        } catch(e) {
            console.error('Failed to load properties:', e);
            if (e.message.includes('401')) window.location.href = '/signin';
        }
    }

    function openModal() { document.getElementById('modal').classList.remove('hidden'); }
    function closeModal() { document.getElementById('modal').classList.add('hidden'); }

    document.getElementById('propertyForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const data = {
            name: document.getElementById('propName').value,
            address: document.getElementById('propAddress').value,
            type: document.getElementById('propType').value,
            units: parseInt(document.getElementById('propUnits').value),
        };
        try {
            const res = await fetch(`${API}/properties`, { method:'POST', headers, body:JSON.stringify(data) });
            const result = await res.json();
            if(!res.ok) throw new Error(result.error || 'Failed to save');
            toast('Property added successfully!');
            closeModal();
            loadProperties();
        } catch(err) { toast(err.message, 'error'); }
    });

    async function deleteProperty(id) {
        if (!confirm('Delete this property?')) return;
        try {
            const res = await fetch(`${API}/properties/${id}`, { method:'DELETE', headers });
            const result = await res.json();
            if(!res.ok) throw new Error(result.error || 'Failed to delete');
            toast('Property deleted');
            loadProperties();
        } catch(err) { toast(err.message, 'error'); }
    }

    // Timeout fallback - if properties don't load in 5s, show error
    setTimeout(() => {
        const grid = document.getElementById('propertiesGrid');
        if (grid && grid.innerHTML.includes('Loading properties...')) {
            grid.innerHTML = '<div class="col-span-full py-12 text-center text-red-400">Failed to load properties. Please refresh the page.</div>';
        }
    }, 5000);

    loadProperties();
    </script>
</body>
</html>