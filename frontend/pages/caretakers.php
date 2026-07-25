<?php
require_once __DIR__ . '/../includes/auth.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Caretakers - RentaFlow</title>
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
                <div id="caretakersPager" class="p-4"></div>
            </div>
            <?php include __DIR__ . '/../public/components/pagination.php'; ?>
        </main>
    </div>

    <div id="caretakerModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm" onclick="if(event.target===this)closeCaretakerModal()">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto p-6" onclick="event.stopPropagation()">
            <div class="flex items-center justify-between mb-4">
                <div><h3 id="caretakerModalTitle" class="text-lg font-bold text-slate-900">Add Caretaker</h3><p class="text-sm text-slate-500 mt-1">Add a caretaker and assign properties they can manage.</p></div>
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
                    <label class="block text-sm font-medium text-slate-700 mb-1">Assign Property</label>
                    <select id="caretakerProperties" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all">
                        <option value="">Select a property...</option>
                    </select>
                    <p class="text-xs text-slate-400 mt-2">Choose the property this caretaker will manage.</p>
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
    const BASE = window.location.pathname.replace(/\/[^\/]*$/, '');
    const API = (BASE || '') + '/api';
    const CARETAKERS_PER_PAGE_KEY = 'rf_caretakers_per_page';
    const CARETAKERS_PER_PAGE_DEFAULT = 25;
    
    // Get token from cookie (current request's auth)
    function getCookie(name) {
        const value = `; ${document.cookie}`;
        const parts = value.split(`; ${name}=`);
        if (parts.length === 2) return parts.pop().split(';').shift();
        return '';
    }
    
    const token = getCookie('rf_token');
    const headers = token ? {'Authorization':'Bearer '+token, 'Content-Type':'application/json'} : {'Content-Type':'application/json'};
    
    let caretakersCache = [];
    let currentCaretakerId = null;

    async function parseJsonResponse(res) {
        const text = await res.text();
        if (!text) return {};
        try {
            return JSON.parse(text);
        } catch (err) {
            console.error('JSON parse failed', err, text);
            return { error: text || 'Invalid server response' };
        }
    }

    function sanitizeEmail(email) {
        if (!email || typeof email !== 'string') return null;
        const trimmed = email.trim();
        if (!trimmed) return null;
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(trimmed)) {
            toast('Invalid email format', 'error');
            return null;
        }
        return trimmed.toLowerCase();
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

    async function loadCaretakers(page = 1, perPage = window.getSavedPerPage(CARETAKERS_PER_PAGE_KEY, CARETAKERS_PER_PAGE_DEFAULT)) {
        try {
            const res = await fetch(`${API}/caretakers?page=${page}&per_page=${perPage}`, { headers });
            const data = await parseJsonResponse(res);
            const tbody = document.getElementById('caretakersTable');
            
            if (!res.ok) {
                if (res.status === 401) {
                    window.location.href = BASE + '/signin';
                    return;
                }
                throw new Error(data.error || `Failed to load caretakers (${res.status})`);
            }
            
            if (data.caretakers && data.caretakers.length) {
                caretakersCache = data.caretakers;
                tbody.innerHTML = data.caretakers.map(c => `
                    <tr class="hover:bg-blue-50/30 transition-colors">
                        <td class="px-6 py-4"><div class="flex items-center gap-3"><div class="w-9 h-9 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-semibold">${(c.name||'CT').split(' ').map(s=>s[0]).join('').substring(0,2).toUpperCase()}</div><div><p class="text-sm font-medium text-slate-900">${c.name}</p></div></div></td>
                        <td class="px-6 py-4 text-sm text-slate-600">${c.email || 'N/A'}</td>
                        <td class="px-6 py-4 text-sm text-slate-600">${c.phone || 'N/A'}</td>
                        <td class="px-6 py-4 text-sm text-slate-900">${c.property_count || 0}${c.assigned_property_names && c.assigned_property_names.length ? `<div class="text-xs text-slate-500 mt-1">${c.assigned_property_names.slice(0,3).join(', ')}${c.assigned_property_names.length > 3 ? '...' : ''}</div>` : ''}</td>
                        <td class="px-6 py-4 flex items-center gap-2"><button onclick="openCaretakerModal(${c.id})" class="px-3 py-2 text-sm font-medium text-blue-600 bg-blue-50 rounded-xl hover:bg-blue-100 transition-colors">Edit</button><button onclick="deleteCaretaker(${c.id})" class="px-3 py-2 text-sm font-medium text-red-600 bg-red-50 rounded-xl hover:bg-red-100 transition-colors">Remove</button></td>
                    </tr>
                `).join('');
            } else {
                tbody.innerHTML = '<tr><td colspan="5" class="px-6 py-12 text-center text-slate-400">No caretakers found. Add a caretaker to assign them properties.</td></tr>';
            }
            if (data.meta) renderPagination('caretakersPager', data.meta, (p) => loadCaretakersPage(p, perPage), {
                perPageKey: CARETAKERS_PER_PAGE_KEY,
                defaultPerPage: CARETAKERS_PER_PAGE_DEFAULT,
                onPerPageChange: (newPerPage) => loadCaretakersPage(1, newPerPage),
            });
        } catch(e) {
            console.error('loadCaretakers error:', e);
            document.getElementById('caretakersTable').innerHTML = `<tr><td colspan="5" class="px-6 py-12 text-center text-red-400"><strong>Error:</strong> ${e.message}</td></tr>`;
        }
    }

    // wrapper to load a specific page
    async function loadCaretakersPage(page = 1, perPage = window.getSavedPerPage(CARETAKERS_PER_PAGE_KEY, CARETAKERS_PER_PAGE_DEFAULT)) {
        try {
            const res = await fetch(`${API}/caretakers?page=${page}&per_page=${perPage}`, { headers });
            const data = await parseJsonResponse(res);
            const tbody = document.getElementById('caretakersTable');
            if (data.caretakers && data.caretakers.length) {
                caretakersCache = data.caretakers;
                tbody.innerHTML = data.caretakers.map(c => `
                    <tr class="hover:bg-blue-50/30 transition-colors">
                        <td class="px-6 py-4"><div class="flex items-center gap-3"><div class="w-9 h-9 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-semibold">${(c.name||'CT').split(' ').map(s=>s[0]).join('').substring(0,2).toUpperCase()}</div><div><p class="text-sm font-medium text-slate-900">${c.name}</p></div></div></td>
                        <td class="px-6 py-4 text-sm text-slate-600">${c.email || 'N/A'}</td>
                        <td class="px-6 py-4 text-sm text-slate-600">${c.phone || 'N/A'}</td>
                        <td class="px-6 py-4 text-sm text-slate-900">${c.property_count || 0}${c.assigned_property_names && c.assigned_property_names.length ? `<div class="text-xs text-slate-500 mt-1">${c.assigned_property_names.slice(0,3).join(', ')}${c.assigned_property_names.length > 3 ? '...' : ''}</div>` : ''}</td>
                        <td class="px-6 py-4 flex items-center gap-2"><button onclick="openCaretakerModal(${c.id})" class="px-3 py-2 text-sm font-medium text-blue-600 bg-blue-50 rounded-xl hover:bg-blue-100 transition-colors">Edit</button><button onclick="deleteCaretaker(${c.id})" class="px-3 py-2 text-sm font-medium text-red-600 bg-red-50 rounded-xl hover:bg-red-100 transition-colors">Remove</button></td>
                    </tr>
                `).join('');
            } else {
                tbody.innerHTML = '<tr><td colspan="5" class="px-6 py-12 text-center text-slate-400">No caretakers found. Add a caretaker to assign them properties.</td></tr>';
            }
            if (data.meta) renderPagination('caretakersPager', data.meta, (p) => loadCaretakersPage(p, perPage), {
                perPageKey: CARETAKERS_PER_PAGE_KEY,
                defaultPerPage: CARETAKERS_PER_PAGE_DEFAULT,
                onPerPageChange: (newPerPage) => loadCaretakersPage(1, newPerPage),
            });
        } catch(e) { console.error('loadCaretakersPage error:', e); }
    }

    async function loadProperties(selectedIds = [], includeAll = false) {
        try {
            const endpoint = includeAll ? `${API}/properties` : `${API}/properties/available`;
            const res = await fetch(endpoint, { headers });
            const data = await parseJsonResponse(res);
            const select = document.getElementById('caretakerProperties');
            if (!res.ok) throw new Error(data.error || `Failed to load properties (${res.status})`);
            if (data.properties && data.properties.length) {
                select.innerHTML = data.properties.map(p => `<option value="${p.id}" ${selectedIds.includes(p.id.toString()) ? 'selected' : ''}>${p.name}</option>`).join('');
            } else {
                select.innerHTML = '<option value="">No properties available</option>';
            }
        } catch(e) {
            console.error('loadProperties error:', e);
            toast('Could not load properties: ' + e.message, 'error');
        }
    }

    async function openCaretakerModal(id = null) {
        currentCaretakerId = id;
        const title = document.getElementById('caretakerModalTitle');
        const submitButton = document.querySelector('#caretakerForm button[type="submit"]');
        const form = document.getElementById('caretakerForm');
        form.reset();
        document.getElementById('caretakerPassword').value = '';

        if (id) {
            const caretaker = caretakersCache.find(c => c.id === id);
            title.textContent = 'Edit Caretaker';
            submitButton.textContent = 'Save Changes';
            if (caretaker) {
                document.getElementById('caretakerName').value = caretaker.name || '';
                document.getElementById('caretakerEmail').value = caretaker.email || '';
                document.getElementById('caretakerPhone').value = caretaker.phone || '';
                document.getElementById('caretakerId').value = caretaker.id_number || '';
                const assignedIds = (caretaker.assigned_properties || '').split(',').filter(Boolean);
                await loadProperties(assignedIds, true); // includeAll=true so assigned property shows
            } else {
                await loadProperties([], true);
            }
        } else {
            title.textContent = 'Add Caretaker';
            submitButton.textContent = 'Save Caretaker';
            await loadProperties([], false); // only show unassigned properties
        }

        document.getElementById('caretakerModal').classList.remove('hidden');
    }

    function closeCaretakerModal() {
        currentCaretakerId = null;
        document.getElementById('caretakerModal').classList.add('hidden');
    }

    document.getElementById('caretakerForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const submitBtn = e.target.querySelector('button[type="submit"]');
        
        // Prevent double submission
        if (submitBtn.disabled) return;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> ' + (currentCaretakerId ? 'Saving...' : 'Adding...');
        
        const selectedProperty = document.getElementById('caretakerProperties').value;
        
        // Validate a property is selected
        if (!selectedProperty) {
            toast('Please assign a property to the caretaker', 'error');
            submitBtn.disabled = false;
            submitBtn.textContent = currentCaretakerId ? 'Save Changes' : 'Save Caretaker';
            return;
        }
        
        // Sanitize email
        const rawEmail = document.getElementById('caretakerEmail').value.trim();
        const sanitizedEmail = sanitizeEmail(rawEmail);
        
        if (!sanitizedEmail && rawEmail) {
            toast('Invalid email format', 'error');
            submitBtn.disabled = false;
            submitBtn.textContent = currentCaretakerId ? 'Save Changes' : 'Save Caretaker';
            return;
        }
        
        const payload = {
            name: document.getElementById('caretakerName').value.trim(),
            email: sanitizedEmail,
            phone: document.getElementById('caretakerPhone').value.trim() || null,
            id_number: document.getElementById('caretakerId').value.trim(),
            assigned_properties: selectedProperty,
        };
        const passwordValue = document.getElementById('caretakerPassword').value;
        if (!currentCaretakerId) {
            payload.password = passwordValue || payload.id_number;
        } else if (passwordValue) {
            payload.password = passwordValue;
        }

        const method = currentCaretakerId ? 'PUT' : 'POST';
        const url = currentCaretakerId ? `${API}/caretakers/${currentCaretakerId}` : `${API}/caretakers`;

        try {
            const res = await fetch(url, { method, headers, body: JSON.stringify(payload) });
            const result = await parseJsonResponse(res);
            if (!res.ok) throw new Error(result.error || `Failed to save caretaker (${res.status})`);
            toast(currentCaretakerId ? 'Caretaker updated successfully!' : 'Caretaker added successfully!');
            closeCaretakerModal();
            loadCaretakers();
        } catch(err) {
            console.error('save caretaker error:', err);
            toast(err.message, 'error');
            submitBtn.disabled = false;
            submitBtn.textContent = currentCaretakerId ? 'Save Changes' : 'Save Caretaker';
        }
    });

    async function deleteCaretaker(id) {
        if (!confirm('Remove this caretaker?')) return;
        try {
            const res = await fetch(`${API}/caretakers/${id}`, { method: 'DELETE', headers });
            const result = await parseJsonResponse(res);
            if (!res.ok) throw new Error(result.error || `Failed to remove caretaker (${res.status})`);
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
