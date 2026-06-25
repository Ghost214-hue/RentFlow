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
    <title>Tenants - RentFlow</title>
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
                <div><h1 class="text-2xl font-bold text-slate-900">Tenants</h1><p class="text-slate-500 mt-1">Manage your tenants</p></div>
                <button onclick="openModal()" class="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold rounded-xl shadow-lg shadow-blue-500/30 hover:shadow-blue-500/40 transition-all inline-flex items-center gap-2"><i class="fas fa-plus"></i>Add Tenant</button>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border border-blue-100/50 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead><tr class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wider border-b border-blue-50 bg-blue-50/50">
                            <th class="px-6 py-4">Name</th><th class="px-6 py-4">Property</th><th class="px-6 py-4">Unit</th><th class="px-6 py-4">Phone</th><th class="px-6 py-4">Balance</th><th class="px-6 py-4">Lease End</th><th class="px-6 py-4">Actions</th>
                        </tr></thead>
                        <tbody class="divide-y divide-blue-50" id="tenantsTable">
                            <tr><td colspan="7" class="px-6 py-12 text-center text-slate-400">Loading...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
    <div id="modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm" onclick="if(event.target===this)closeModal()">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto p-6" onclick="event.stopPropagation()">
            <div class="flex items-center justify-between mb-4"><h3 class="text-lg font-bold text-slate-900">Add Tenant - Full Onboarding</h3><button onclick="closeModal()" class="text-slate-400 hover:text-slate-600"><i class="fas fa-times text-xl"></i></button></div>
            <form id="tenantForm" class="space-y-4">
                <!-- Personal Information -->
                <div class="bg-blue-50/50 rounded-xl p-4 border border-blue-100/50">
                    <h4 class="text-sm font-semibold text-blue-700 mb-3"><i class="fas fa-user mr-2"></i>Personal Information</h4>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-2"><label class="block text-sm font-medium text-slate-700 mb-1">Full Name *</label><input type="text" id="tenantName" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all" placeholder="Tenant full name" required></div>
                        <div><label class="block text-sm font-medium text-slate-700 mb-1">Email</label><input type="email" id="tenantEmail" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all" placeholder="email@example.com"></div>
                        <div><label class="block text-sm font-medium text-slate-700 mb-1">Phone *</label><input type="tel" id="tenantPhone" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all" placeholder="+254 7XX XXX XXX" required></div>
                    </div>
                </div>

                <!-- ID Information -->
                <div class="bg-blue-50/50 rounded-xl p-4 border border-blue-100/50">
                    <h4 class="text-sm font-semibold text-blue-700 mb-3"><i class="fas fa-id-card mr-2"></i>Identification</h4>
                    <div class="grid grid-cols-2 gap-4">
                        <div><label class="block text-sm font-medium text-slate-700 mb-1">ID Type</label><select id="tenantIdType" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all"><option>National ID</option><option>Passport</option><option>Driving License</option><option>Alien ID</option></select></div>
                        <div><label class="block text-sm font-medium text-slate-700 mb-1">ID Number *</label><input type="text" id="tenantIdNumber" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all" placeholder="ID/Passport number" required></div>
                    </div>
                    <div class="mt-3"><label class="block text-sm font-medium text-slate-700 mb-1">Emergency Contact</label><input type="text" id="tenantEmergency" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all" placeholder="Name and phone number of emergency contact"></div>
                </div>

                <!-- Lease & Assignment -->
                <div class="bg-blue-50/50 rounded-xl p-4 border border-blue-100/50">
                    <h4 class="text-sm font-semibold text-blue-700 mb-3"><i class="fas fa-file-contract mr-2"></i>Lease & Assignment</h4>
                    <div class="grid grid-cols-2 gap-4">
                        <div><label class="block text-sm font-medium text-slate-700 mb-1">Property</label><select id="tenantProperty" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all"><option value="">Select property first...</option></select></div>
                        <div><label class="block text-sm font-medium text-slate-700 mb-1">House/Unit *</label><select id="tenantHouse" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all"><option value="">Select house...</option></select></div>
                    </div>
                    <div class="grid grid-cols-2 gap-4 mt-3">
                        <div><label class="block text-sm font-medium text-slate-700 mb-1">Lease Start</label><input type="date" id="tenantLeaseStart" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all"></div>
                        <div><label class="block text-sm font-medium text-slate-700 mb-1">Lease End</label><input type="date" id="tenantLeaseEnd" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all"></div>
                    </div>
                </div>

                <!-- Financial -->
                <div class="bg-blue-50/50 rounded-xl p-4 border border-blue-100/50">
                    <h4 class="text-sm font-semibold text-blue-700 mb-3"><i class="fas fa-money-bill-wave mr-2"></i>Financial Details</h4>
                    <div class="grid grid-cols-3 gap-4">
                        <div><label class="block text-sm font-medium text-slate-700 mb-1">Monthly Rent (KES) *</label><input type="number" id="tenantRent" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all" placeholder="45000" required></div>
                        <div><label class="block text-sm font-medium text-slate-700 mb-1">Deposit (KES)</label><input type="number" id="tenantDeposit" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all" placeholder="45000"></div>
                        <div><label class="block text-sm font-medium text-slate-700 mb-1">Balance (KES)</label><input type="number" id="tenantBalance" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all" placeholder="0" value="0"></div>
                    </div>
                </div>

                <!-- Document Upload -->
                <div class="bg-blue-50/50 rounded-xl p-4 border border-blue-100/50">
                    <h4 class="text-sm font-semibold text-blue-700 mb-3"><i class="fas fa-upload mr-2"></i>Documents (Optional)</h4>
                    <div class="border-2 border-dashed border-blue-200 rounded-xl p-6 text-center hover:border-blue-400 transition-colors cursor-pointer" onclick="document.getElementById('fileInput').click()">
                        <i class="fas fa-cloud-upload-alt text-3xl text-blue-300 mb-2"></i>
                        <p class="text-sm text-slate-500">Click to upload ID, passport, or lease documents</p>
                        <p class="text-xs text-slate-400 mt-1">PDF, JPG, PNG (Max 10MB each)</p>
                        <input type="file" id="fileInput" class="hidden" accept=".pdf,.jpg,.jpeg,.png" multiple onchange="handleFileUpload(this.files)">
                    </div>
                    <div id="uploadedFiles" class="mt-3 space-y-2"></div>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-blue-100">
                    <button type="button" onclick="closeModal()" class="px-5 py-2.5 bg-white text-blue-700 border border-blue-200 rounded-xl font-medium hover:bg-blue-50 transition-all">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold rounded-xl shadow-lg shadow-blue-500/30 transition-all">Register Tenant</button>
                </div>
            </form>
        </div>
    </div>
    <div id="toast" class="fixed bottom-6 right-6 z-50 hidden px-5 py-3 rounded-xl shadow-xl text-white font-medium flex items-center gap-2"></div>
    <script>
    const API = '/api';
    const token = localStorage.getItem('rf_token') || '<?php echo $token; ?>';
    const headers = token ? {'Authorization':'Bearer '+token, 'Content-Type':'application/json'} : {'Content-Type':'application/json'};
    let uploadedDocs = [];

    function toast(msg, type='success') {
        const el = document.getElementById('toast');
        const colors = { success:'bg-gradient-to-r from-emerald-500 to-emerald-600', error:'bg-gradient-to-r from-red-500 to-red-600', info:'bg-gradient-to-r from-blue-500 to-blue-600' };
        const icons = { success:'fa-check-circle', error:'fa-exclamation-circle', info:'fa-info-circle' };
        el.className = `fixed bottom-6 right-6 z-50 px-5 py-3 rounded-xl shadow-xl text-white font-medium flex items-center gap-2 ${colors[type]||colors.success}`;
        el.innerHTML = `<i class="fas ${icons[type]||icons.success}"></i>${msg}`;
        el.classList.remove('hidden');
        setTimeout(() => el.classList.add('hidden'), 3000);
    }

    async function loadTenants() {
        try {
            const res = await fetch(`${API}/tenants`, { headers });
            const data = await res.json();
            const tbody = document.getElementById('tenantsTable');
            if (data.tenants && data.tenants.length) {
                tbody.innerHTML = data.tenants.map(t => `
                    <tr class="hover:bg-blue-50/30 transition-colors">
                        <td class="px-6 py-4"><div class="flex items-center gap-2"><div class="w-7 h-7 rounded-full bg-gradient-to-br from-blue-50 to-blue-100 text-blue-600 flex items-center justify-center text-xs font-bold">${t.name.split(' ').map(s=>s[0]).join('').substring(0,2).toUpperCase()}</div><div><p class="text-sm font-medium text-slate-900">${t.name}</p><p class="text-xs text-slate-400">${t.email||''}</p></div></div></td>
                        <td class="px-6 py-4 text-sm text-slate-600">${t.property_name||'N/A'}</td>
                        <td class="px-6 py-4 text-sm font-medium text-slate-900">${t.house_unit||'N/A'}</td>
                        <td class="px-6 py-4 text-sm text-slate-500">${t.phone||'N/A'}</td>
                        <td class="px-6 py-4 text-sm font-medium ${t.balance > 0 ? 'text-amber-600' : 'text-emerald-600'}">KES ${(t.balance||0).toLocaleString()}</td>
                        <td class="px-6 py-4 text-sm text-slate-500">${t.lease_end ? new Date(t.lease_end).toLocaleDateString('en-GB',{day:'numeric',month:'short',year:'numeric'}) : 'N/A'}</td>
                        <td class="px-6 py-4"><button onclick="toast('View tenant','info')" class="p-1.5 text-slate-400 hover:text-blue-600 transition-colors"><i class="fas fa-eye"></i></button></td>
                    </tr>
                `).join('');
            } else {
                tbody.innerHTML = '<tr><td colspan="7" class="px-6 py-12 text-center text-slate-400">No tenants found</td></tr>';
            }
        } catch(e) { console.error(e); if (e.message.includes('401')) window.location.href = '/signin'; }
    }

    async function loadProperties() {
        try {
            const res = await fetch(`${API}/properties`, { headers });
            const data = await res.json();
            const select = document.getElementById('tenantProperty');
            if (data.properties) {
                select.innerHTML = '<option value="">Select property first...</option>' + data.properties.map(p => `<option value="${p.id}">${p.name}</option>`).join('');
            }
        } catch(e) { console.error(e); }
    }

    // Load houses when property is selected
    document.getElementById('tenantProperty').addEventListener('change', async function() {
        const propId = this.value;
        const houseSelect = document.getElementById('tenantHouse');
        if (!propId) {
            houseSelect.innerHTML = '<option value="">Select property first...</option>';
            return;
        }
        try {
            const res = await fetch(`${API}/houses?property_id=${propId}&status=vacant`, { headers });
            const data = await res.json();
            if (data.houses) {
                houseSelect.innerHTML = '<option value="">Select house...</option>' + data.houses.map(h => `<option value="${h.id}" data-rent="${h.rent}">${h.unit} - ${h.type} (KES ${(h.rent||0).toLocaleString()})</option>`).join('');
            }
        } catch(e) { console.error(e); }
    });

    // Auto-fill rent when house is selected
    document.getElementById('tenantHouse').addEventListener('change', function() {
        const selected = this.options[this.selectedIndex];
        const rent = selected ? selected.dataset.rent : 0;
        if (rent) document.getElementById('tenantRent').value = rent;
    });

    async function handleFileUpload(files) {
        for (const file of files) {
            const formData = new FormData();
            formData.append('file', file);
            try {
                const res = await fetch(`${API}/upload`, {
                    method: 'POST',
                    headers: {'Authorization': 'Bearer ' + token},
                    body: formData
                });
                const data = await res.json();
                if (!res.ok) throw new Error(data.error || 'Upload failed');
                uploadedDocs.push(data.file);
                renderUploadedFiles();
                toast(`${file.name} uploaded`, 'success');
            } catch(err) {
                toast(err.message, 'error');
            }
        }
    }

    function renderUploadedFiles() {
        const container = document.getElementById('uploadedFiles');
        if (!uploadedDocs.length) {
            container.innerHTML = '';
            return;
        }
        container.innerHTML = uploadedDocs.map((f, i) => `
            <div class="flex items-center justify-between bg-white rounded-lg p-2 border border-blue-100">
                <div class="flex items-center gap-2">
                    <i class="fas ${f.type.includes('pdf') ? 'fa-file-pdf text-red-400' : 'fa-file-image text-blue-400'}"></i>
                    <span class="text-sm text-slate-600">${f.name}</span>
                </div>
                <button type="button" onclick="removeFile(${i})" class="text-red-400 hover:text-red-600"><i class="fas fa-times"></i></button>
            </div>
        `).join('');
    }

    function removeFile(index) {
        uploadedDocs.splice(index, 1);
        renderUploadedFiles();
    }

    function openModal() { document.getElementById('modal').classList.remove('hidden'); }
    function closeModal() { document.getElementById('modal').classList.add('hidden'); }

    document.getElementById('tenantForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const houseSelect = document.getElementById('tenantHouse');
        const selectedHouse = houseSelect.options[houseSelect.selectedIndex];
        
        const data = {
            name: document.getElementById('tenantName').value,
            email: document.getElementById('tenantEmail').value,
            phone: document.getElementById('tenantPhone').value,
            id_type: document.getElementById('tenantIdType').value,
            id_number: document.getElementById('tenantIdNumber').value,
            emergency_contact: document.getElementById('tenantEmergency').value,
            property_id: parseInt(document.getElementById('tenantProperty').value) || null,
            house_id: parseInt(houseSelect.value) || null,
            lease_start: document.getElementById('tenantLeaseStart').value || null,
            lease_end: document.getElementById('tenantLeaseEnd').value || null,
            rent: parseFloat(document.getElementById('tenantRent').value) || 0,
            deposit: parseFloat(document.getElementById('tenantDeposit').value) || 0,
            balance: parseFloat(document.getElementById('tenantBalance').value) || 0,
            documents: uploadedDocs.length ? JSON.stringify(uploadedDocs) : null,
        };
        
        try {
            const res = await fetch(`${API}/tenants`, { method:'POST', headers, body:JSON.stringify(data) });
            const result = await res.json();
            if(!res.ok) throw new Error(result.error || 'Failed');
            toast('Tenant registered successfully!');
            closeModal();
            loadTenants();
            // Reset form
            document.getElementById('tenantForm').reset();
            uploadedDocs = [];
            renderUploadedFiles();
        } catch(err) { toast(err.message, 'error'); }
    });

    loadTenants();
    loadProperties();
    </script>
</body>
</html>