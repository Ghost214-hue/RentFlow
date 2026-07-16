<?php
require_once __DIR__ . '/../includes/auth.php';
$propertyId = $_GET['property_id'] ?? null;
$basePath = '/RentaFlow';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Houses & Units - RentaFlow</title>
    <base href="/RentaFlow/">
    <link rel="stylesheet" href="<?php echo $basePath; ?>/css/output.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body class="bg-gradient-to-br from-blue-50 via-white to-blue-50/30 min-h-screen font-sans text-slate-800 flex flex-col lg:flex-row">
    <?php include __DIR__ . '/../public/components/sidebar.php'; ?>
    <div class="flex-1 flex flex-col min-h-screen">
        <?php include __DIR__ . '/../public/components/header.php'; ?>
        <main class="flex-1 overflow-y-auto p-4 lg:p-8">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                <div><h1 class="text-2xl font-bold text-slate-900">Houses & Units</h1><p class="text-slate-500 mt-1">Manage individual units</p></div>
                <?php if ($role === 'owner'): ?>
                <button onclick="openModal()" class="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold rounded-xl shadow-lg shadow-blue-500/30 hover:shadow-blue-500/40 transition-all inline-flex items-center gap-2"><i class="fas fa-plus"></i>Add Unit</button>
                <?php endif; ?>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border border-blue-100/50 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead><tr class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wider border-b border-blue-50 bg-blue-50/50">
                            <th class="px-6 py-4">Unit</th><th class="px-6 py-4">Property</th><th class="px-6 py-4">Type</th><th class="px-6 py-4">Tenant</th><th class="px-6 py-4">Rent</th><th class="px-6 py-4">Status</th><th class="px-6 py-4">Actions</th>
                        </tr></thead>
                        <tbody class="divide-y divide-blue-50" id="housesTable">
                            <tr><td colspan="7" class="px-6 py-12 text-center text-slate-400">Loading...</td></tr>
                        </tbody>
                    </table>
                </div>
                <div id="housesPager" class="p-4"></div>
            </div>
            <?php include __DIR__ . '/../public/components/pagination.php'; ?>
        </main>
    </div>
    <div id="modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm" onclick="if(event.target===this)closeModal()">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto p-6" onclick="event.stopPropagation()">
            <div class="flex items-center justify-between mb-4"><h3 class="text-lg font-bold text-slate-900" id="modalTitle">Add Unit</h3><button onclick="closeModal()" class="text-slate-400 hover:text-slate-600"><i class="fas fa-times text-xl"></i></button></div>
            <form id="houseForm" class="space-y-4">
                <input type="hidden" id="houseId">
                <div><label class="block text-sm font-medium text-slate-700 mb-1">Property</label><select id="houseProperty" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all"><option value="">Select property...</option></select></div>
                <div class="grid grid-cols-2 gap-4">
                    <div><label class="block text-sm font-medium text-slate-700 mb-1">Unit</label><input type="text" id="houseUnit" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all" placeholder="e.g. A1" required></div>
                    <div><label class="block text-sm font-medium text-slate-700 mb-1">Type</label><select id="houseType" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all"><option>Studio</option><option>1 Bedroom</option><option>2 Bedroom</option><option>3 Bedroom</option></select></div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div><label class="block text-sm font-medium text-slate-700 mb-1">Rent (KES)</label><input type="number" id="houseRent" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all" placeholder="45000" required></div>
                    <div><label class="block text-sm font-medium text-slate-700 mb-1">Status</label><select id="houseStatus" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all"><option>Vacant</option><option>Occupied</option></select></div>
                </div>
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
    const urlParams = new URLSearchParams(window.location.search);
    const filterPropId = urlParams.get('property_id');
    const HOUSES_PER_PAGE_KEY = 'rf_houses_per_page';
    const HOUSES_PER_PAGE_DEFAULT = 25;

    function toast(msg, type='success') {
        const el = document.getElementById('toast');
        const colors = { success:'bg-gradient-to-r from-emerald-500 to-emerald-600', error:'bg-gradient-to-r from-red-500 to-red-600', info:'bg-gradient-to-r from-blue-500 to-blue-600' };
        const icons = { success:'fa-check-circle', error:'fa-exclamation-circle', info:'fa-info-circle' };
        el.className = `fixed bottom-6 right-6 z-50 px-5 py-3 rounded-xl shadow-xl text-white font-medium flex items-center gap-2 ${colors[type]||colors.success}`;
        el.innerHTML = `<i class="fas ${icons[type]||icons.success}"></i>${msg}`;
        el.classList.remove('hidden');
        setTimeout(() => el.classList.add('hidden'), 3000);
    }

    async function loadHouses(page = 1, perPage = window.getSavedPerPage(HOUSES_PER_PAGE_KEY, HOUSES_PER_PAGE_DEFAULT)) {
        try {
            console.log('Fetching houses from:', `${API}/houses${filterPropId ? `?property_id=${filterPropId}` : ''}`);
            const qs = filterPropId ? `?property_id=${filterPropId}` : '';
            const data = await apiRequest(`${API}/houses${qs}${qs ? '&' : '?'}page=${page}&per_page=${perPage}`);
            const tbody = document.getElementById('housesTable');
            if (data.houses && data.houses.length) {
                tbody.innerHTML = data.houses.map(h => `
                    <tr class="hover:bg-blue-50/30 transition-colors">
                        <td class="px-6 py-4 font-medium text-slate-900">${h.unit}</td>
                        <td class="px-6 py-4 text-sm text-slate-600">${h.property_name||'N/A'}</td>
                        <td class="px-6 py-4 text-sm text-slate-500">${h.type}</td>
                        <td class="px-6 py-4">${h.tenant_name ? `<div class="flex items-center gap-2"><div class="w-6 h-6 rounded-full bg-gradient-to-br from-blue-50 to-blue-100 text-blue-600 flex items-center justify-center text-xs font-bold">${h.tenant_name.split(' ').map(s=>s[0]).join('').substring(0,2).toUpperCase()}</div><span class="text-sm text-slate-700">${h.tenant_name}</span></div>` : '<span class="text-sm text-slate-400">-</span>'}</td>
                        <td class="px-6 py-4 text-sm font-medium text-slate-900">KES ${(h.rent||0).toLocaleString()}</td>
                        <td class="px-6 py-4"><span class="px-2 py-1 rounded-full text-xs font-medium ${h.status==='occupied'?'bg-emerald-100 text-emerald-700':'bg-slate-100 text-slate-500'}">${h.status}</span></td>
                        <td class="px-6 py-4"><?php if ($role === 'owner'): ?><button onclick="editHouse(${h.id}, ${h.property_id}, '${h.unit}', '${h.type}', ${h.rent}, '${h.status}')" class="p-1.5 text-slate-400 hover:text-blue-600 transition-colors"><i class="fas fa-edit"></i></button><?php endif; ?></td>
                    </tr>
                `).join('');
            } else {
                tbody.innerHTML = '<tr><td colspan="7" class="px-6 py-12 text-center text-slate-400">No units found</td></tr>';
            }
            if (data.meta) renderPagination('housesPager', data.meta, (p) => loadHouses(p, perPage), {
                perPageKey: HOUSES_PER_PAGE_KEY,
                defaultPerPage: HOUSES_PER_PAGE_DEFAULT,
                onPerPageChange: (newPerPage) => loadHouses(1, newPerPage),
            });
        } catch(e) {
            console.error('loadHouses error:', e);
            document.getElementById('housesTable').innerHTML = `<tr><td colspan="7" class="px-6 py-12 text-center text-red-400">Error loading units: ${e.message}</td></tr>`;
            if (e.message.includes('401')) {
                window.location.href = '../public/signin.php';
            }
        }
    }

    async function loadProperties() {
        try {
            console.log('Fetching properties...');
            const data = await apiRequest(`${API}/properties`);
            console.log('Properties API response:', data);
            const select = document.getElementById('houseProperty');
            if (data.properties && data.properties.length) {
                select.innerHTML = '<option value="">Select property...</option>' + data.properties.map(p => `<option value="${p.id}" ${filterPropId==p.id?'selected':''}>${p.name}</option>`).join('');
            } else {
                select.innerHTML = '<option value="">No properties available - create one first</option>';
            }
        } catch(e) {
            console.error('loadProperties error:', e);
            toast('Could not load properties: ' + e.message, 'error');
        }
    }

    function openModal() { 
        document.getElementById('modalTitle').textContent = 'Add Unit';
        document.getElementById('houseId').value = '';
        document.getElementById('houseForm').reset();
        document.getElementById('modal').classList.remove('hidden'); 
    }
    
    function editHouse(id, propertyId, unit, type, rent, status) {
        document.getElementById('modalTitle').textContent = 'Edit Unit';
        document.getElementById('houseId').value = id;
        document.getElementById('houseProperty').value = propertyId;
        document.getElementById('houseUnit').value = unit;
        document.getElementById('houseType').value = type;
        document.getElementById('houseRent').value = rent;
        document.getElementById('houseStatus').value = status;
        document.getElementById('modal').classList.remove('hidden');
    }
    
    function closeModal() { document.getElementById('modal').classList.add('hidden'); }

    document.getElementById('houseForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const houseId = document.getElementById('houseId').value;
        const propertyId = parseInt(document.getElementById('houseProperty').value);
        
        if (!propertyId) {
            toast('Please select a property', 'error');
            return;
        }
        
        const data = {
            property_id: propertyId,
            unit: document.getElementById('houseUnit').value.trim(),
            type: document.getElementById('houseType').value,
            rent: parseFloat(document.getElementById('houseRent').value) || 0,
            status: document.getElementById('houseStatus').value,
        };
        
        if (!data.unit) {
            toast('Please enter a unit number', 'error');
            return;
        }
        
        try {
            const isEdit = !!houseId;
            const url = isEdit ? `${API}/houses/${houseId}` : `${API}/houses`;
            const method = isEdit ? 'PUT' : 'POST';
            console.log(`Saving house: ${method} ${url}`, data);
            const result = await apiRequest(url, {
                method,
                body: JSON.stringify(data)
            });
            toast(isEdit ? 'Unit updated!' : 'Unit added!');
            closeModal();
            loadHouses();
        } catch(err) {
            console.error('Form submit error:', err);
            toast(err.message, 'error');
        }
    });

    // Load data on page ready
    loadProperties();
    loadHouses();
    </script>
</body>
</html>