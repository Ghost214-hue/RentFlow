<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/base-path-fix.php';
require_once __DIR__ . '/../../backend/app/Core/IdEncoder.php';
$basePath = rtrim((string)($basePath ?? getBasePath() ?? ''), '/');

// Debug logging
error_log('DEBUG: house-details.php loaded. $_GET[id] = ' . ($_GET['id'] ?? 'NOT SET'));

$houseRaw = $_GET['id'] ?? null;
$houseId = null;
if ($houseRaw !== null) {
    // Try decode first (expected token). If that fails and a numeric id was
    // provided (e.g. ?id=50) fall back to the integer id for compatibility.
    $houseId = \App\Core\IdEncoder::decode((string) $houseRaw);
    if ($houseId === null && ctype_digit((string) $houseRaw)) {
        $houseId = (int) $houseRaw;
    }
}

// Debug logging
error_log('DEBUG: After decoding/fallback, $houseId = ' . ($houseId ?? 'NULL'));

if (!$houseId) {
    error_log('DEBUG: houseId is null, redirecting to ' . $basePath . '/houses');
    header('Location: ' . $basePath . '/houses');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>House Details - RentaFlow</title>
    <link rel="stylesheet" href="<?= htmlspecialchars($basePath); ?>/css/output.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body class="bg-gradient-to-br from-blue-50 via-white to-blue-50/30 min-h-screen font-sans text-slate-800 flex flex-col lg:flex-row">
    <?php include __DIR__ . '/../public/components/sidebar.php'; ?>
    <div class="flex-1 flex flex-col min-h-screen">
        <?php include __DIR__ . '/../public/components/header.php'; ?>
        <main class="flex-1 overflow-y-auto p-4 lg:p-8">
            <div class="max-w-5xl mx-auto">
                <div class="flex items-center gap-2 mb-6">
                    <a href="<?= $basePath; ?>/houses" class="p-2 text-slate-500 hover:bg-blue-50 rounded-xl transition-colors"><i class="fas fa-arrow-left"></i></a>
                    <h1 class="text-2xl font-bold text-slate-900">House Details</h1>
                </div>

                <div id="loadingState" class="flex flex-col items-center gap-3 py-12">
                    <i class="fas fa-spinner fa-spin text-3xl text-blue-400"></i>
                    <span class="text-slate-500">Loading house details...</span>
                </div>

                <div id="errorState" class="hidden">
                    <div class="bg-red-50 border border-red-100 rounded-2xl p-6 text-center">
                        <i class="fas fa-exclamation-circle text-3xl text-red-400 mb-3"></i>
                        <p class="text-slate-700 font-medium">House not found or access denied.</p>
                        <a href="<?= $basePath; ?>/houses" class="mt-4 inline-block px-5 py-2.5 bg-blue-600 text-white rounded-xl font-medium hover:bg-blue-700 transition-all">Go Back</a>
                    </div>
                </div>

                <div id="houseContent" class="space-y-6 hidden">
                    <!-- House Overview -->
                    <div class="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-6">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-blue-500 to-blue-700 text-white flex items-center justify-center text-xl font-bold"><?php echo substr(preg_replace('/[^A-Za-z0-9]/', '', $_GET['id'] ?? ''), 0, 2) ?: '#'; ?></div>
                            <div>
                                <p class="font-medium text-slate-900 text-xl" id="houseUnit">-</p>
                                <p class="text-sm text-slate-500" id="houseProperty">-</p>
                            </div>
                            <span class="ml-auto px-3 py-1 rounded-full text-xs font-medium" id="houseStatusBadge">-</span>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="bg-slate-50/50 rounded-xl p-4">
                                <p class="text-xs font-medium text-slate-500 mb-1">Type</p>
                                <p class="text-sm font-medium text-slate-900" id="houseType">-</p>
                            </div>
                            <div class="bg-slate-50/50 rounded-xl p-4">
                                <p class="text-xs font-medium text-slate-500 mb-1">Rent</p>
                                <p class="text-sm font-medium text-slate-900" id="houseRent">KES 0</p>
                            </div>
                            <div class="bg-slate-50/50 rounded-xl p-4">
                                <p class="text-xs font-medium text-slate-500 mb-1">Total Revenue</p>
                                <p class="text-sm font-medium text-emerald-600" id="totalRevenue">KES 0</p>
                            </div>
                        </div>
                    </div>

                    <!-- Meters -->
                    <div class="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="font-semibold text-slate-900"><i class="fas fa-tachometer-alt mr-2 text-blue-600"></i>Meters</h3>
                            <?php if ($role === 'owner'): ?>
                            <button onclick="openMeterModal()" class="px-3 py-1.5 text-xs font-semibold bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"><i class="fas fa-edit mr-1"></i>Edit Meters</button>
                            <?php endif; ?>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="bg-slate-50/50 rounded-xl p-4 flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-sky-100 to-sky-200 text-sky-600 flex items-center justify-center"><i class="fas fa-faucet-drip"></i></div>
                                <div>
                                    <p class="text-xs font-medium text-slate-500 mb-1">Water Meter No.</p>
                                    <p class="text-sm font-medium text-slate-900" id="houseWaterMeter">-</p>
                                </div>
                            </div>
                            <div class="bg-slate-50/50 rounded-xl p-4 flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-amber-100 to-amber-200 text-amber-600 flex items-center justify-center"><i class="fas fa-bolt"></i></div>
                                <div>
                                    <p class="text-xs font-medium text-slate-500 mb-1">Electric Meter No.</p>
                                    <p class="text-sm font-medium text-slate-900" id="houseElecMeter">-</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Current Tenant -->
                    <div class="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-6">
                        <h3 class="font-semibold text-slate-900 mb-4"><i class="fas fa-user mr-2 text-blue-600"></i>Current Tenant</h3>
                        <div id="currentTenantSection">
                            <div class="py-4 text-center text-slate-400">No current tenant</div>
                        </div>
                    </div>

                    <!-- Past Tenants -->
                    <div class="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-6">
                        <h3 class="font-semibold text-slate-900 mb-4"><i class="fas fa-history mr-2 text-slate-500"></i>Past Tenants</h3>
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead><tr class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wider border-b border-blue-50">
                                    <th class="pb-3 pr-4">Name</th><th class="pb-3 pr-4">Email</th><th class="pb-3 pr-4">Lease Period</th><th class="pb-3">Status</th>
                                </tr></thead>
                                <tbody class="divide-y divide-blue-50" id="pastTenantsTable">
                                    <tr><td colspan="4" class="py-8 text-center text-slate-400">Loading...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Maintenance & Damages -->
                    <div class="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-6">
                        <h3 class="font-semibold text-slate-900 mb-4"><i class="fas fa-tools mr-2 text-amber-600"></i>Maintenance & Damages <span id="houseDamageCostBadge" class="hidden ml-2 px-2 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-700"></span></h3>
                        <div class="space-y-3" id="houseMaintenanceList">
                            <div class="py-4 text-center text-slate-400">Loading maintenance records...</div>
                        </div>
                    </div>

                    <!-- Revenue / Payment History -->
                    <div class="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-6">
                        <h3 class="font-semibold text-slate-900 mb-4"><i class="fas fa-money-bill-wave mr-2 text-emerald-600"></i>Payment History & Revenue</h3>
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead><tr class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wider border-b border-blue-50">
                                    <th class="pb-3 pr-4">Date</th><th class="pb-3 pr-4">Method</th><th class="pb-3 pr-4">Amount</th><th class="pb-3">Status</th>
                                </tr></thead>
                                <tbody class="divide-y divide-blue-50" id="paymentsTable">
                                    <tr><td colspan="4" class="py-8 text-center text-slate-400">Loading...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Meters Edit Modal -->
    <div id="meterModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm" onclick="if(event.target===this)closeMeterModal()">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6" onclick="event.stopPropagation()">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-slate-900">Edit Meters</h3>
                <button onclick="closeMeterModal()" class="text-slate-400 hover:text-slate-600"><i class="fas fa-times text-xl"></i></button>
            </div>
            <form id="meterForm" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Water Meter No.</label>
                    <input type="text" id="meterWaterMeter" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all" placeholder="e.g. WM-1042">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Electric Meter No.</label>
                    <input type="text" id="meterElecMeter" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all" placeholder="e.g. KPLC-88211">
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="closeMeterModal()" class="px-5 py-2.5 bg-white text-blue-700 border border-blue-200 rounded-xl font-medium hover:bg-blue-50 transition-all">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold rounded-xl shadow-lg shadow-blue-500/30 transition-all">Save</button>
                </div>
            </form>
        </div>
    </div>

    <div id="toast" class="fixed bottom-6 right-6 z-50 hidden px-5 py-3 rounded-xl shadow-xl text-white font-medium flex items-center gap-2"></div>

    <script>
    const BASE = '<?= $basePath; ?>';
    const API = BASE + '/api';
    const houseId = <?= $houseId; ?>;
    // Get token from cookie (primary auth method) or localStorage (fallback)
    const cookies = document.cookie.split(';');
    let cookieToken = '';
    for (let c of cookies) {
        const [k, v] = c.trim().split('=');
        if (k === 'rf_token') { cookieToken = decodeURIComponent(v); break; }
    }
    const token = cookieToken || localStorage.getItem('rf_token') || '<?php echo $token ?? ''; ?>';
    const headers = token ? {'Authorization':'Bearer '+token, 'Content-Type':'application/json'} : {'Content-Type':'application/json'};

    async function apiRequest(url, options = {}) {
        const res = await fetch(url, { ...options, headers });
        const text = await res.text();
        let data;
        try { data = JSON.parse(text); } catch(e) { data = {}; }
        if (!res.ok) {
            if (res.status === 401) { localStorage.removeItem('rf_token'); window.location.href = BASE + '/signin'; }
            throw new Error(data.error || 'Request failed');
        }
        return data;
    }

    function escapeHtml(val) { return String(val ?? '').replace(/[&<>"']/g, c => ({'&':'&','<':'<','>':'>','"':'"',"'":'&#039;'})[c]); }
    function formatMoney(v) { return 'KES ' + (Number(v) || 0).toLocaleString(); }
    function formatDate(v) { return v ? new Date(v).toLocaleDateString('en-GB', {day:'numeric', month:'short', year:'numeric'}) : 'N/A'; }

    function toast(msg, type='success') {
        const el = document.getElementById('toast');
        const colors = { success:'bg-gradient-to-r from-emerald-500 to-emerald-600', error:'bg-gradient-to-r from-red-500 to-red-600', info:'bg-gradient-to-r from-blue-500 to-blue-600' };
        const icons = { success:'fa-check-circle', error:'fa-exclamation-circle', info:'fa-info-circle' };
        el.className = `fixed bottom-6 right-6 z-50 px-5 py-3 rounded-xl shadow-xl text-white font-medium flex items-center gap-2 ${colors[type]||colors.success}`;
        el.innerHTML = `<i class="fas ${icons[type]||icons.success}"></i>${escapeHtml(msg)}`;
        el.classList.remove('hidden');
        setTimeout(() => el.classList.add('hidden'), 3000);
    }

    async function loadHouseDetails() {
        try {
            const data = await apiRequest(`${API}/houses/${houseId}`);
            const house = data.house;
            if (!house) {
                document.getElementById('loadingState').classList.add('hidden');
                document.getElementById('errorState').classList.remove('hidden');
                return;
            }

            // House overview
            document.getElementById('houseUnit').textContent = house.unit || '-';
            document.getElementById('houseProperty').textContent = (house.property_name || 'Property') + ' #' + houseId;
            
            const statusBadge = document.getElementById('houseStatusBadge');
            statusBadge.textContent = (house.status || 'vacant').charAt(0).toUpperCase() + (house.status || 'vacant').slice(1);
            statusBadge.className = `ml-auto px-3 py-1 rounded-full text-xs font-medium ${house.status === 'occupied' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500'}`;

            document.getElementById('houseType').textContent = house.type || '-';
            document.getElementById('houseRent').textContent = formatMoney(house.rent);

            // Meters
            document.getElementById('houseWaterMeter').textContent = house.water_meter || '-';
            document.getElementById('houseElecMeter').textContent = house.elec_meter || '-';

            // Current tenant
            const currentTenant = data.current_tenant;
            const currentTenantSection = document.getElementById('currentTenantSection');
            if (currentTenant && currentTenant.name) {
                const initials = currentTenant.name.split(' ').map(s => s[0]).join('').substring(0, 2).toUpperCase();
                currentTenantSection.innerHTML = `
                    <a href="${BASE}/tenant-details?id=${encodeURIComponent(currentTenant.encoded_id || '')}" class="flex items-center gap-4 p-4 rounded-xl bg-blue-50/50 border border-blue-100/50 hover:bg-blue-100/50 transition-colors">
                        <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-500 to-blue-700 text-white flex items-center justify-center text-lg font-bold">${escapeHtml(initials)}</div>
                        <div class="flex-1">
                            <p class="font-medium text-slate-900">${escapeHtml(currentTenant.name)}</p>
                            <p class="text-xs text-slate-500">${escapeHtml(currentTenant.email || currentTenant.phone || '')}</p>
                            <p class="text-xs text-slate-400 mt-0.5">${formatDate(currentTenant.lease_start)} - ${formatDate(currentTenant.lease_end)}</p>
                        </div>
                        <i class="fas fa-chevron-right text-slate-400"></i>
                    </a>
                `;
            } else {
                currentTenantSection.innerHTML = '<div class="py-4 text-center text-slate-400"><i class="fas fa-user-slash mr-2"></i>No current tenant</div>';
            }

            // Past tenants
            const pastTenants = data.past_tenants || [];
            const pastTable = document.getElementById('pastTenantsTable');
            if (pastTenants.length) {
                pastTable.innerHTML = pastTenants.map(t => `
                    <tr class="hover:bg-blue-50/30 transition-colors">
                        <td class="py-3 pr-4 text-sm font-medium text-slate-900">${escapeHtml(t.name || 'Unknown')}</td>
                        <td class="py-3 pr-4 text-sm text-slate-500">${escapeHtml(t.email || '-')}</td>
                        <td class="py-3 pr-4 text-sm text-slate-500">${formatDate(t.lease_start)} - ${formatDate(t.lease_end)}</td>
                        <td class="py-3"><span class="px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600">${escapeHtml(t.status || 'unknown')}</span></td>
                    </tr>
                `).join('');
            } else {
                pastTable.innerHTML = '<tr><td colspan="4" class="py-8 text-center text-slate-400">No past tenants recorded</td></tr>';
            }

            // Payments / Revenue
            const payments = data.payments || [];
            const paymentsTable = document.getElementById('paymentsTable');
            let totalRevenue = 0;
            if (payments.length) {
                paymentsTable.innerHTML = payments.map(p => {
                    totalRevenue += Number(p.amount) || 0;
                    return `
                    <tr class="hover:bg-blue-50/30 transition-colors">
                        <td class="py-3 pr-4 text-sm text-slate-500">${formatDate(p.date)}</td>
                        <td class="py-3 pr-4 text-sm text-slate-600">${escapeHtml(p.method || 'N/A')}</td>
                        <td class="py-3 pr-4 text-sm font-medium text-slate-900">${formatMoney(p.amount)}</td>
                        <td class="py-3"><span class="px-2 py-0.5 rounded-full text-xs font-medium ${p.status === 'paid' || p.status === 'confirmed' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600'}">${escapeHtml(p.status || 'pending')}</span></td>
                    </tr>
                    `;
                }).join('');
            } else {
                paymentsTable.innerHTML = '<tr><td colspan="4" class="py-8 text-center text-slate-400">No payments recorded</td></tr>';
            }
            document.getElementById('totalRevenue').textContent = formatMoney(totalRevenue);

            // Maintenance & Damages
            const maintenance = data.maintenance || [];
            const damageCost = data.damage_cost || 0;
            const maintenanceList = document.getElementById('houseMaintenanceList');
            if (maintenance.length) {
                maintenanceList.innerHTML = maintenance.map(m => {
                    const statusClass = m.status==='completed'?'bg-emerald-100 text-emerald-700':m.status==='in-progress'?'bg-blue-100 text-blue-700':'bg-amber-100 text-amber-700';
                    const costDisplay = parseFloat(m.cost||0) > 0 ? 'KES ' + parseFloat(m.cost).toLocaleString('en-KE',{minimumFractionDigits:2}) : '<span class="text-slate-400">No cost</span>';
                    return `
                    <div class="flex items-start gap-3 p-3 rounded-xl bg-amber-50/50 border border-amber-100/50">
                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-amber-50 to-amber-100 text-amber-600 flex items-center justify-center flex-shrink-0"><i class="fas fa-tools text-xs"></i></div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between mb-0.5">
                                <div class="flex items-center gap-2">
                                    <p class="text-sm font-medium text-slate-900 truncate">${escapeHtml(m.title || 'Maintenance')}</p>
                                    ${m.tenant_name ? '<span class="text-xs text-slate-400">(' + escapeHtml(m.tenant_name) + ')</span>' : ''}
                                </div>
                                <span class="text-xs text-slate-400">${formatDate(m.created_at)}</span>
                            </div>
                            <p class="text-xs text-slate-500 mb-1">${escapeHtml(m.description || '')} ${m.category ? '• ' + escapeHtml(m.category) : ''}</p>
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 rounded text-xs font-medium ${statusClass}">${escapeHtml(m.status || 'pending')}</span>
                                <span class="text-xs font-semibold text-slate-700">${costDisplay}</span>
                                ${m.vendor_name ? '<span class="text-xs text-slate-400"><i class="fas fa-user-cog mr-1"></i>' + escapeHtml(m.vendor_name) + '</span>' : ''}
                            </div>
                        </div>
                    </div>`;
                }).join('');
            } else {
                maintenanceList.innerHTML = '<div class="py-4 text-center text-slate-400">No maintenance records for this house</div>';
            }
            
            // Show damage cost badge
            if (damageCost > 0) {
                const badge = document.getElementById('houseDamageCostBadge');
                badge.textContent = 'KES ' + damageCost.toLocaleString('en-KE', {minimumFractionDigits: 2});
                badge.classList.remove('hidden');
            }

            document.getElementById('loadingState').classList.add('hidden');
            document.getElementById('houseContent').classList.remove('hidden');
        } catch (e) {
            console.error('Failed to load house details:', e);
            document.getElementById('loadingState').classList.add('hidden');
            document.getElementById('errorState').classList.remove('hidden');
        }
    }

    function openMeterModal() {
        document.getElementById('meterWaterMeter').value = (document.getElementById('houseWaterMeter').textContent === '-') ? '' : document.getElementById('houseWaterMeter').textContent;
        document.getElementById('meterElecMeter').value = (document.getElementById('houseElecMeter').textContent === '-') ? '' : document.getElementById('houseElecMeter').textContent;
        document.getElementById('meterModal').classList.remove('hidden');
    }

    function closeMeterModal() {
        document.getElementById('meterModal').classList.add('hidden');
    }

    document.getElementById('meterForm').addEventListener('submit', async function (e) {
        e.preventDefault();
        var data = {
            water_meter: document.getElementById('meterWaterMeter').value.trim(),
            elec_meter: document.getElementById('meterElecMeter').value.trim()
        };
        try {
            await apiRequest(API + '/houses/' + houseId, { method: 'PUT', body: JSON.stringify(data) });
            document.getElementById('houseWaterMeter').textContent = data.water_meter || '-';
            document.getElementById('houseElecMeter').textContent = data.elec_meter || '-';
            closeMeterModal();
            toast('Meters updated!');
        } catch (err) {
            toast(err.message, 'error');
        }
    });

    loadHouseDetails();
    </script>
</body>
</html>