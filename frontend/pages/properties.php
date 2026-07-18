<?php
require_once __DIR__ . '/../includes/auth.php';
$currentRole = $role ?? 'owner';
$token = $token ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Properties | RentaFlow Kenya</title>
    <link rel="stylesheet" href="<?php echo $basePath; ?>/css/output.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
      .pay-group { display: none; }
      .pay-group.active { display: block; }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-50 via-white to-blue-50/30 min-h-screen font-sans text-slate-800 flex flex-col lg:flex-row">
    <?php include __DIR__ . '/../public/components/sidebar.php'; ?>
    <div class="flex-1 flex flex-col min-h-screen">
        <?php include __DIR__ . '/../public/components/header.php'; ?>
        <main class="flex-1 overflow-y-auto p-4 lg:p-8">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                <div><h1 class="text-2xl font-bold text-slate-900">Properties</h1><p class="text-slate-500 mt-1">Manage your rental properties</p></div>
                <?php if ($currentRole === 'owner'): ?>
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
            <div class="flex items-center justify-between mb-4"><h3 class="text-lg font-bold text-slate-900">Add Property</h3><button onclick="closeModal()" class="text-slate-400 hover:text-slate-600"><i class="fas fa-times text-xl"></i></button></div>
            <form id="propertyForm" class="space-y-4">
                <div><label class="block text-sm font-medium text-slate-700 mb-1">Name</label><input type="text" id="propName" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all" placeholder="e.g. Sunrise Apartments" required></div>
                <div><label class="block text-sm font-medium text-slate-700 mb-1">Address</label><input type="text" id="propAddress" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all" placeholder="Full address" required></div>
                <div class="grid grid-cols-2 gap-4">
                    <div><label class="block text-sm font-medium text-slate-700 mb-1">Type</label><select id="propType" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all"><option>Apartment Block</option><option>Townhouses</option><option>Studio</option><option>Villa</option></select></div>
                    <div><label class="block text-sm font-medium text-slate-700 mb-1">Units</label><input type="number" id="propUnits" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all" placeholder="0" required></div>
                </div>
                <div class="border-t border-slate-100 pt-4">
                  <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">Payment details</p>
                  <div class="grid grid-cols-2 gap-4">
                    <div><label class="block text-sm font-medium text-slate-700 mb-1">Payment Method</label>
                      <select id="payMethod" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all" onchange="togglePayFields()">
                        <option value="">None</option>
                        <option value="paybill">Paybill</option>
                        <option value="till">Till Number</option>
                        <option value="bank">Bank Account</option>
                        <option value="mobile_money">M-Pesa / Mobile Money</option>
                      </select>
                    </div>
                    <div class="pay-group" data-pay="paybill"><label class="block text-sm font-medium text-slate-700 mb-1">Paybill Number</label><input type="text" id="paybillNumber" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all" placeholder="e.g. 123456"></div>
                    <div class="pay-group" data-pay="paybill"><label class="block text-sm font-medium text-slate-700 mb-1">Paybill Account</label><input type="text" id="paybillAccount" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all" placeholder="e.g. 00123456789"></div>
                    <div class="pay-group" data-pay="till"><label class="block text-sm font-medium text-slate-700 mb-1">Till Number</label><input type="text" id="tillNumber" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all" placeholder="e.g. 987654"></div>
                    <div class="pay-group col-span-2" data-pay="bank"><label class="block text-sm font-medium text-slate-700 mb-1">Bank Name</label><input type="text" id="bankName" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all" placeholder="e.g. NCBA Bank"></div>
                    <div class="pay-group" data-pay="bank"><label class="block text-sm font-medium text-slate-700 mb-1">Account Number</label><input type="text" id="bankAccount" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all" placeholder="e.g. 1234567890"></div>
                    <div class="pay-group" data-pay="bank"><label class="block text-sm font-medium text-slate-700 mb-1">Branch</label><input type="text" id="bankBranch" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all" placeholder="e.g. Westlands"></div>
                    <div class="pay-group col-span-2" data-pay="mobile_money"><label class="block text-sm font-medium text-slate-700 mb-1">Mobile Money Number</label><input type="text" id="mobileMoney" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all" placeholder="e.g. 0712345678"></div>
                  </div>
                </div>
                <div class="flex justify-end gap-3 pt-4"><button type="button" onclick="closeModal()" class="px-5 py-2.5 bg-white text-blue-700 border border-blue-200 rounded-xl font-medium hover:bg-blue-50 transition-all">Cancel</button><button type="submit" class="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold rounded-xl shadow-lg shadow-blue-500/30 transition-all">Save</button></div>
            </form>
        </div>
    </div>

    <div id="editModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm" onclick="if(event.target===this)closeEditModal()">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto p-6" onclick="event.stopPropagation()">
            <div class="flex items-center justify-between mb-4"><h3 class="text-lg font-bold text-slate-900">Edit Property</h3><button onclick="closeEditModal()" class="text-slate-400 hover:text-slate-600"><i class="fas fa-times text-xl"></i></button></div>
            <form id="editPropertyForm" class="space-y-4">
                <input type="hidden" id="editPropertyId">
                <div><label class="block text-sm font-medium text-slate-700 mb-1">Name</label><input type="text" id="editPropName" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all" required></div>
                <div><label class="block text-sm font-medium text-slate-700 mb-1">Address</label><input type="text" id="editPropAddress" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all" required></div>
                <div class="grid grid-cols-2 gap-4">
                    <div><label class="block text-sm font-medium text-slate-700 mb-1">Type</label><select id="editPropType" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all"><option>Apartment Block</option><option>Townhouses</option><option>Studio</option><option>Villa</option></select></div>
                    <div><label class="block text-sm font-medium text-slate-700 mb-1">Units</label><input type="number" id="editPropUnits" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all"></div>
                </div>
                <div class="border-t border-slate-100 pt-4">
                  <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">Payment details</p>
                  <div class="grid grid-cols-2 gap-4">
                    <div><label class="block text-sm font-medium text-slate-700 mb-1">Payment Method</label>
                      <select id="editPayMethod" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all" onchange="togglePayFields()">
                        <option value="">None</option>
                        <option value="paybill">Paybill</option>
                        <option value="till">Till Number</option>
                        <option value="bank">Bank Account</option>
                        <option value="mobile_money">M-Pesa / Mobile Money</option>
                      </select>
                    </div>
                    <div class="pay-group" data-pay="paybill"><label class="block text-sm font-medium text-slate-700 mb-1">Paybill Number</label><input type="text" id="editPaybillNumber" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all"></div>
                    <div class="pay-group" data-pay="paybill"><label class="block text-sm font-medium text-slate-700 mb-1">Paybill Account</label><input type="text" id="editPaybillAccount" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all"></div>
                    <div class="pay-group" data-pay="till"><label class="block text-sm font-medium text-slate-700 mb-1">Till Number</label><input type="text" id="editTillNumber" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all"></div>
                    <div class="pay-group col-span-2" data-pay="bank"><label class="block text-sm font-medium text-slate-700 mb-1">Bank Name</label><input type="text" id="editBankName" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all"></div>
                    <div class="pay-group" data-pay="bank"><label class="block text-sm font-medium text-slate-700 mb-1">Account Number</label><input type="text" id="editBankAccount" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all"></div>
                    <div class="pay-group" data-pay="bank"><label class="block text-sm font-medium text-slate-700 mb-1">Branch</label><input type="text" id="editBankBranch" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all"></div>
                    <div class="pay-group col-span-2" data-pay="mobile_money"><label class="block text-sm font-medium text-slate-700 mb-1">Mobile Money Number</label><input type="text" id="editMobileMoney" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all"></div>
                  </div>
                </div>
                <div class="flex justify-end gap-3 pt-4"><button type="button" onclick="closeEditModal()" class="px-5 py-2.5 bg-white text-blue-700 border border-blue-200 rounded-xl font-medium hover:bg-blue-50 transition-all">Cancel</button><button type="submit" class="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold rounded-xl shadow-lg shadow-blue-500/30 transition-all">Update</button></div>
            </form>
        </div>
    </div>

    <div id="toast" class="fixed bottom-6 right-6 z-50 hidden px-5 py-3 rounded-xl shadow-xl text-white font-medium flex items-center gap-2"></div>
    <script>
    const API = '<?php echo $basePath; ?>/api';
    let token = localStorage.getItem('rf_token') || '';
    if (!token) {
        const m = document.cookie.match(/(?:^|; )rf_token=([^;]+)/);
        token = m ? decodeURIComponent(m[1]) : '';
    }
    const headers = token ? {'Authorization':'Bearer '+token, 'Content-Type':'application/json'} : {'Content-Type':'application/json'};

    function initials(n) { if(!n) return 'PR'; return String(n).trim().split(/\s+/).map(s=>s[0]).join('').substring(0,2).toUpperCase() || 'PR'; }
    function toast(msg, type='success') {
        const el = document.getElementById('toast');
        const colors = { success:'bg-gradient-to-r from-emerald-500 to-emerald-600', error:'bg-gradient-to-r from-red-500 to-red-600', info:'bg-gradient-to-r from-blue-500 to-blue-600' };
        const icons = { success:'fa-check-circle', error:'fa-exclamation-circle', info:'fa-info-circle' };
        el.className = `fixed bottom-6 right-6 z-50 px-5 py-3 rounded-xl shadow-xl text-white font-medium flex items-center gap-2 ${colors[type]||colors.success}`;
        el.innerHTML = `<i class="fas ${icons[type]||icons.success}"></i>${msg}`;
        el.classList.remove('hidden');
        setTimeout(() => el.classList.add('hidden'), 3000);
    }

    function updatePayGroups(containerId, selected) {
      document.querySelectorAll('#' + containerId + ' .pay-group').forEach(el => {
        const show = el.dataset.pay === selected;
        el.classList.toggle('active', show);
      });
    }

    function togglePayFields() {
      const val = document.getElementById('payMethod').value;
      updatePayGroups('modal', val);
      const val2 = document.getElementById('editPayMethod').value;
      updatePayGroups('editModal', val2);
    }

    function setText(el, txt) {
        if (el) el.textContent = txt ?? '';
    }

    async function loadProperties() {
        const grid = document.getElementById('propertiesGrid');
        grid.innerHTML = '<div class="col-span-full py-12 text-center text-slate-400">Loading properties...</div>';
        try {
            const res = await fetch(`${API}/properties`, { headers });
            const text = await res.text();
            let data;
            try { data = JSON.parse(text); } catch(e) { console.error('Invalid JSON:', text); throw new Error('Invalid server response'); }
            if (!res.ok) {
                if (res.status === 401) { window.location.href = '/signin.php'; return; }
                throw new Error(data.error || `Request failed (${res.status})`);
            }
            if (!data.properties || !data.properties.length) {
                grid.innerHTML = '<div class="col-span-full py-12 text-center text-slate-400">No properties found. Add your first property!</div>';
                return;
            }
            grid.innerHTML = '';
            data.properties.forEach(p => {
                const card = document.createElement('div');
                card.className = 'bg-white rounded-2xl shadow-sm border border-blue-100/50 overflow-hidden group hover:shadow-md transition-all duration-300';
                const name = String(p.name ?? '');
                const address = String(p.address ?? '');
                const typeLabel = String(p.type ?? 'N/A');
                const units = Number(p.unit_count || p.units || 0);
                const occupied = Number(p.occupied_count || p.occupied || 0);
                const rent = Number(p.rent || 0).toLocaleString();
                const initial = initials(name) || 'PR';
                const pct = Math.round(((occupied)/(units||1))*100);

                const header = document.createElement('div');
                header.className = 'relative h-48 overflow-hidden bg-gradient-to-br from-blue-100 to-blue-200';
                header.innerHTML = '<div class="w-full h-full flex items-center justify-center"><div class="text-6xl font-bold text-white/80 drop-shadow-lg"></div></div><div class="absolute inset-0 bg-gradient-to-t from-black/40 to-transparent"></div><div class="absolute bottom-4 left-4 right-4"><h3 class="text-lg font-bold text-white"></h3><p class="text-sm text-white/80"><i class="fas fa-map-marker-alt mr-1"></i></p></div><div class="absolute top-4 right-4"><span class="px-2 py-1 bg-white/90 backdrop-blur rounded-lg text-xs font-medium text-slate-700"></span></div>';
                header.querySelector('.text-6xl').textContent = initial;
                header.querySelector('h3').textContent = name;
                header.querySelector('p.text-white\\/80').textContent = address;
                header.querySelector('span.px-2').textContent = typeLabel;

                const body = document.createElement('div');
                body.className = 'p-5';
                body.innerHTML = '<div class="grid grid-cols-3 gap-4 mb-4"><div class="text-center"><p class="text-lg font-bold text-slate-900">'+units+'</p><p class="text-xs text-slate-500">Units</p></div><div class="text-center"><p class="text-lg font-bold text-emerald-600">'+occupied+'</p><p class="text-xs text-slate-500">Occupied</p></div><div class="text-center"><p class="text-lg font-bold text-slate-900">KES '+rent+'</p><p class="text-xs text-slate-500">Avg Rent</p></div></div><div class="flex items-center gap-2 mb-4"><div class="flex-1 h-2 bg-slate-100 rounded-full overflow-hidden"><div class="h-full bg-gradient-to-r from-blue-500 to-blue-600 rounded-full" style="width:'+pct+'%"></div></div><span class="text-xs text-slate-500">'+pct+'%</span></div><div class="flex gap-2"><a href="/houses?property_id='+p.id+'" class="flex-1 py-2 text-sm font-medium text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-xl transition-colors text-center">Manage</a><button data-action="edit" data-id="'+p.id+'" data-name="'+name.replace(/"/g,'"')+'" data-address="'+address.replace(/"/g,'"')+'" data-type="'+typeLabel.replace(/"/g,'"')+'" data-units="'+units+'" class="px-3 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 rounded-xl transition-colors"><i class="fas fa-pen"></i></button><button data-action="delete" data-id="'+p.id+'" class="px-3 py-2 text-sm font-medium text-red-600 bg-red-50 hover:bg-red-100 rounded-xl transition-colors"><i class="fas fa-trash"></i></button></div>';

                card.appendChild(header);
                card.appendChild(body);
                grid.appendChild(card);
            });
            grid.querySelectorAll('button[data-action="edit"]').forEach(btn => {
                btn.addEventListener('click', () => {
                    const name = btn.dataset.name || '';
                    const address = btn.dataset.address || '';
                    const type = btn.dataset.type || '';
                    const units = parseInt(btn.dataset.units || '0', 10);
                    editProperty(btn.dataset.id, name, address, type, units);
                });
            });
            grid.querySelectorAll('button[data-action="delete"]').forEach(btn => {
                btn.addEventListener('click', () => deleteProperty(btn.dataset.id));
            });
        } catch(e) {
            console.error('Failed to load properties:', e);
            grid.innerHTML = '<div class="col-span-full py-12 text-center text-red-400">Error: ' + e.message + '</div>';
        }
    }

    function openModal() { document.getElementById('modal').classList.remove('hidden'); }
    function closeModal() { document.getElementById('modal').classList.add('hidden'); }
    function openEditModal() { document.getElementById('editModal').classList.remove('hidden'); }
    function closeEditModal() { document.getElementById('editModal').classList.add('hidden'); }

    togglePayFields();

    document.getElementById('propertyForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const data = {
            name: document.getElementById('propName').value,
            address: document.getElementById('propAddress').value,
            type: document.getElementById('propType').value,
            units: parseInt(document.getElementById('propUnits').value),
            payment_method_type: document.getElementById('payMethod').value || null,
            paybill_number: document.getElementById('paybillNumber').value || null,
            paybill_account: document.getElementById('paybillAccount').value || null,
            till_number: document.getElementById('tillNumber').value || null,
            bank_name: document.getElementById('bankName').value || null,
            bank_account: document.getElementById('bankAccount').value || null,
            bank_branch: document.getElementById('bankBranch').value || null,
            mobile_money_number: document.getElementById('mobileMoney').value || null,
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

    document.getElementById('editPropertyForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const id = document.getElementById('editPropertyId').value;
        const data = {
            name: document.getElementById('editPropName').value,
            address: document.getElementById('editPropAddress').value,
            type: document.getElementById('editPropType').value,
            units: parseInt(document.getElementById('editPropUnits').value),
            payment_method_type: document.getElementById('editPayMethod').value || null,
            paybill_number: document.getElementById('editPaybillNumber').value || null,
            paybill_account: document.getElementById('editPaybillAccount').value || null,
            till_number: document.getElementById('editTillNumber').value || null,
            bank_name: document.getElementById('editBankName').value || null,
            bank_account: document.getElementById('editBankAccount').value || null,
            bank_branch: document.getElementById('editBankBranch').value || null,
            mobile_money_number: document.getElementById('editMobileMoney').value || null,
        };
        try {
            const res = await fetch(`${API}/properties/${id}`, { method:'PUT', headers, body:JSON.stringify(data) });
            const result = await res.json();
            if(!res.ok) throw new Error(result.error || 'Failed to update');
            toast('Property updated successfully!');
            closeEditModal();
            loadProperties();
        } catch(err) { toast(err.message, 'error'); }
    });

    function editProperty(id, name, address, type, units) {
        document.getElementById('editPropertyId').value = id;
        document.getElementById('editPropName').value = name;
        document.getElementById('editPropAddress').value = address;
        document.getElementById('editPropType').value = type;
        document.getElementById('editPropUnits').value = units || 0;
        openEditModal();
    }

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

    loadProperties();
    </script>
</body>
</html>