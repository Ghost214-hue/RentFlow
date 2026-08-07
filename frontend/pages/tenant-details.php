<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/base-path-fix.php';
require_once __DIR__ . '/../../backend/app/Core/IdEncoder.php';
$basePath = getBasePath();

// Debug logging
error_log('DEBUG: tenant-details.php loaded. $_GET[id] = ' . ($_GET['id'] ?? 'NOT SET'));

$tenantRaw = $_GET['id'] ?? null;
$tenantId = null;
if ($tenantRaw !== null) {
    // Try decode first (expected in production). If decoding fails and the value
    // is a plain numeric id (e.g. ?id=50) fall back to using the integer id.
    $tenantId = \App\Core\IdEncoder::decode((string) $tenantRaw);
    if ($tenantId === null && ctype_digit((string) $tenantRaw)) {
        $tenantId = (int) $tenantRaw;
    }
}

// Debug logging
error_log('DEBUG: After decoding/fallback, $tenantId = ' . ($tenantId ?? 'NULL'));

if (!$tenantId) {
    error_log('DEBUG: tenantId is null, redirecting to ' . $basePath . '/tenants');
    header('Location: ' . $basePath . '/tenants');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tenant Details - RentaFlow</title>
    <base href="<?php echo htmlspecialchars($basePath); ?>/">
    <link rel="stylesheet" href="<?php echo htmlspecialchars($basePath); ?>/css/output.css">
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
                    <button onclick="history.back()" class="p-2 text-slate-500 hover:bg-blue-50 rounded-xl transition-colors"><i class="fas fa-arrow-left"></i></button>
                    <h1 class="text-2xl font-bold text-slate-900">Tenant Details</h1>
                </div>

                <div id="loadingState" class="hidden">
                    <div class="flex flex-col items-center gap-3 py-12">
                        <i class="fas fa-spinner fa-spin text-3xl text-blue-400"></i>
                        <span class="text-slate-500">Loading tenant details...</span>
                    </div>
                </div>

                <div id="errorState" class="hidden">
                    <div class="bg-red-50 border border-red-100 rounded-2xl p-6 text-center">
                        <i class="fas fa-exclamation-circle text-3xl text-red-400 mb-3"></i>
                        <p class="text-slate-700 font-medium">Tenant not found or access denied.</p>
                        <button onclick="history.back()" class="mt-4 px-5 py-2.5 bg-blue-600 text-white rounded-xl font-medium hover:bg-blue-700 transition-all">Go Back</button>
                    </div>
                </div>

                <div id="tenantContent" class="space-y-6 hidden">
                    <!-- Profile Overview -->
                    <div class="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-6">
                        <div class="flex items-center gap-4 mb-6">
                            <div class="w-20 h-20 rounded-full bg-gradient-to-br from-blue-500 to-blue-700 text-white flex items-center justify-center text-2xl font-bold overflow-hidden" id="detailInitials">??</div>
                            <div>
                                <p class="font-medium text-slate-900 text-xl" id="detailName">-</p>
                                <p class="text-sm text-slate-500" id="detailEmail">-</p>
                                <p class="text-xs text-slate-400 mt-0.5" id="detailPhone">-</p>
                            </div>
                            <span class="ml-auto px-3 py-1 rounded-full text-xs font-medium bg-blue-50 text-blue-700" id="detailStatus">Active</span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="bg-slate-50/50 rounded-xl p-4">
                                <p class="text-xs font-medium text-slate-500 mb-1">Property</p>
                                <p class="text-sm font-medium text-slate-900" id="detailProperty">-</p>
                            </div>
                            <div class="bg-slate-50/50 rounded-xl p-4">
                                <p class="text-xs font-medium text-slate-500 mb-1">Unit</p>
                                <p class="text-sm font-medium text-slate-900" id="detailUnit">-</p>
                            </div>
                            <div class="bg-slate-50/50 rounded-xl p-4">
                                <p class="text-xs font-medium text-slate-500 mb-1">Lease Period</p>
                                <p class="text-sm font-medium text-slate-900" id="detailLease">-</p>
                            </div>
                            <div class="bg-slate-50/50 rounded-xl p-4">
                                <p class="text-xs font-medium text-slate-500 mb-1">Monthly Rent</p>
                                <p class="text-sm font-medium text-slate-900" id="detailRent">KES 0</p>
                            </div>
                        </div>
                    </div>

                    <!-- Identification & Documents -->
                    <div class="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-6">
                        <h3 class="font-semibold text-slate-900 mb-4"><i class="fas fa-id-card mr-2 text-blue-600"></i>Identification & Documents</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <p class="text-xs font-medium text-slate-500 mb-1">ID Type</p>
                                <p class="text-sm text-slate-900" id="detailIdType">-</p>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-slate-500 mb-1">ID Number</p>
                                <p class="text-sm text-slate-900" id="detailIdNumber">-</p>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p class="text-xs font-medium text-slate-500 mb-1">ID Document</p>
                                <div id="detailIdDoc" class="text-sm text-slate-900">-</div>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-slate-500 mb-1">KRA Pin Document</p>
                                <div id="detailKraDoc" class="text-sm text-slate-900">-</div>
                            </div>
                        </div>
                    </div>

                    <!-- Next of Kin -->
                    <div class="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-6">
                        <h3 class="font-semibold text-slate-900 mb-4"><i class="fas fa-users mr-2 text-emerald-600"></i>Next of Kin</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p class="text-xs font-medium text-slate-500 mb-1">Full Name</p>
                                <p class="text-sm text-slate-900" id="detailKinName">-</p>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-slate-500 mb-1">Phone</p>
                                <p class="text-sm text-slate-900" id="detailKinPhone">-</p>
                            </div>
                            <div class="md:col-span-2">
                                <p class="text-xs font-medium text-slate-500 mb-1">Email</p>
                                <p class="text-sm text-slate-900" id="detailKinEmail">-</p>
                            </div>
                        </div>
                    </div>

                    <!-- Financial Summary -->
                    <div class="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-6">
                        <h3 class="font-semibold text-slate-900 mb-4"><i class="fas fa-money-bill-wave mr-2 text-purple-600"></i>Financial Details</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="bg-slate-50/50 rounded-xl p-4">
                                <p class="text-xs font-medium text-slate-500 mb-1">Security Deposit</p>
                                <p class="text-sm font-medium text-slate-900" id="detailDeposit">KES 0</p>
                            </div>
                            <div class="bg-slate-50/50 rounded-xl p-4">
                                <p class="text-xs font-medium text-slate-500 mb-1">Opening Balance</p>
                                <p class="text-sm font-medium text-slate-900" id="detailBalance">KES 0</p>
                            </div>
                            <div class="bg-slate-50/50 rounded-xl p-4">
                                <p class="text-xs font-medium text-slate-500 mb-1">Current Balance</p>
                                <p class="text-sm font-medium text-slate-900" id="detailCurrentBalance">KES 0</p>
                            </div>
                        </div>
                    </div>

                    <!-- Payment History -->
                    <div class="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-6">
                        <h3 class="font-semibold text-slate-900 mb-4"><i class="fas fa-receipt mr-2 text-blue-600"></i>Payment History</h3>
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead><tr class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wider border-b border-blue-50">
                                    <th class="pb-3 pr-4">Date</th><th class="pb-3 pr-4">Amount</th><th class="pb-3 pr-4">Method</th><th class="pb-3 pr-4">Receipt</th><th class="pb-3">Status</th>
                                </tr></thead>
                                <tbody class="divide-y divide-blue-50" id="paymentsTable">
                                    <tr><td colspan="5" class="py-8 text-center text-slate-400">Loading payments...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Complaints -->
                    <div class="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-6">
                        <h3 class="font-semibold text-slate-900 mb-4"><i class="fas fa-exclamation-triangle mr-2 text-amber-600"></i>Complaints</h3>
                        <div class="space-y-3" id="complaintsList">
                            <div class="py-4 text-center text-slate-400">Loading complaints...</div>
                        </div>
                    </div>

                    <!-- Maintenance & Damages -->
                    <div class="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-6">
                        <h3 class="font-semibold text-slate-900 mb-4"><i class="fas fa-tools mr-2 text-amber-600"></i>Maintenance & Damages <span id="damageTotalBadge" class="hidden ml-2 px-2 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-700"></span></h3>
                        <div class="space-y-3" id="maintenanceList">
                            <div class="py-4 text-center text-slate-400">Loading maintenance records...</div>
                        </div>
                    </div>

                    <!-- Documents -->
                    <div class="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-6">
                        <h3 class="font-semibold text-slate-900 mb-4"><i class="fas fa-folder-open mr-2 text-emerald-600"></i>Documents</h3>
                        <div class="space-y-2" id="documentsList">
                            <div class="py-4 text-center text-slate-400">Loading documents...</div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <div id="toast" class="fixed bottom-6 right-6 z-50 hidden px-5 py-3 rounded-xl shadow-xl text-white font-medium flex items-center gap-2"></div>

    <script>
    const API = '<?php echo $basePath; ?>/api';
    const BASE_PATH = '<?php echo $basePath; ?>';
    const tenantId = <?php echo $tenantId; ?>;

    function normalizeUrl(url) {
        if (!url) return '#';
        if (url.startsWith('http://') || url.startsWith('https://')) return url;
        const base = String(BASE_PATH || '').trim();
        const path = String(url).trim();
        
        // If path already starts with base path, don't duplicate it
        if (base && path.startsWith(base + '/')) {
            return path;
        }
        
        // Otherwise prepend base path
        if (path.startsWith('/')) return base + path;
        return base + '/' + path;
    }
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
            if (res.status === 401) {
                localStorage.removeItem('rf_token');
                window.location.href = '<?php echo $basePath; ?>/signin';
            }
            throw new Error(data.error || 'Request failed');
        }
        return data;
    }

    function initials(name) {
        const text = String(name || '').trim();
        if (!text) return '??';
        return text.split(/\s+/).map(s => s[0]).join('').substring(0, 2).toUpperCase();
    }

    function formatMoney(value) {
        return 'KES ' + (Number(value) || 0).toLocaleString();
    }

    function formatDate(value) {
        return value ? new Date(value).toLocaleDateString('en-GB', {day:'numeric', month:'short', year:'numeric'}) : 'N/A';
    }

    function statusColor(status) {
        const colors = {'paid':'bg-emerald-100 text-emerald-700','pending':'bg-slate-100 text-slate-600','partial':'bg-amber-100 text-amber-700','overdue':'bg-red-100 text-red-700'};
        return colors[status] || 'bg-slate-100 text-slate-600';
    }

    async function loadTenantDetails() {
        try {
            const data = await apiRequest(`${API}/tenants/${tenantId}`);
            const tenant = data.tenant;
            if (!tenant) {
                document.getElementById('loadingState').classList.add('hidden');
                document.getElementById('errorState').classList.remove('hidden');
                return;
            }

            // Populate overview
            const initialsEl = document.getElementById('detailInitials');
            if (tenant.profile_picture) {
                initialsEl.innerHTML = `<img src="${normalizeUrl(tenant.profile_picture)}" class="w-full h-full object-cover" alt="Profile">`;
            } else {
                initialsEl.textContent = initials(tenant.name);
            }
            document.getElementById('detailName').textContent = tenant.name || '-';
            document.getElementById('detailEmail').textContent = tenant.email || '-';
            document.getElementById('detailPhone').textContent = tenant.phone || '-';
            document.getElementById('detailStatus').textContent = (tenant.status || 'active').replace(/_/g, ' ');

            document.getElementById('detailProperty').textContent = tenant.property_name || '-';
            document.getElementById('detailUnit').textContent = tenant.house_unit || '-';
            document.getElementById('detailLease').textContent = `${formatDate(tenant.lease_start)} - ${formatDate(tenant.lease_end)}`;
            document.getElementById('detailRent').textContent = formatMoney(tenant.rent);
            document.getElementById('detailDeposit').textContent = formatMoney(tenant.deposit);
            document.getElementById('detailBalance').textContent = formatMoney(tenant.balance);

            // ID info
            document.getElementById('detailIdType').textContent = tenant.id_type || '-';
            document.getElementById('detailIdNumber').textContent = tenant.id_number || '-';

            // Documents
            const docs = tenant.documents || [];
            const idDoc = docs.find(d => d.name && d.name.toLowerCase().includes('id'));
            const kraDoc = docs.find(d => d.name && d.name.toLowerCase().includes('kra'));
            
            const idDocEl = document.getElementById('detailIdDoc');
            if (idDoc) {
                const idDocUrl = normalizeUrl(idDoc.url);
                idDocEl.innerHTML = `<a href="${idDocUrl}" target="_blank" class="text-blue-600 hover:underline"><i class="fas fa-external-link-alt mr-1"></i>${escapeHtml(idDoc.name)}</a>`;
            } else {
                idDocEl.textContent = 'No ID document uploaded';
            }

            const kraDocEl = document.getElementById('detailKraDoc');
            if (kraDoc) {
                const kraDocUrl = normalizeUrl(kraDoc.url);
                kraDocEl.innerHTML = `<a href="${kraDocUrl}" target="_blank" class="text-blue-600 hover:underline"><i class="fas fa-external-link-alt mr-1"></i>${escapeHtml(kraDoc.name)}</a>`;
            } else {
                kraDocEl.textContent = 'No KRA document uploaded';
            }

            // Next of Kin
            document.getElementById('detailKinName').textContent = tenant.next_of_kin_name || '-';
            document.getElementById('detailKinPhone').textContent = tenant.next_of_kin_phone || '-';
            document.getElementById('detailKinEmail').textContent = tenant.next_of_kin_email || '-';

            // Payments
            const payments = tenant.payments || [];
            const paymentsTable = document.getElementById('paymentsTable');
            if (payments.length) {
                paymentsTable.innerHTML = payments.map(p => `
                    <tr class="hover:bg-blue-50/30 transition-colors">
                        <td class="py-3 pr-4 text-sm text-slate-500">${formatDate(p.date || p.created_at)}</td>
                        <td class="py-3 pr-4 text-sm font-medium text-slate-900">${formatMoney(p.amount)}</td>
                        <td class="py-3 pr-4 text-sm text-slate-600">${escapeHtml(p.method || 'N/A')}</td>
                        <td class="py-3 pr-4 text-sm text-slate-600">${escapeHtml(p.receipt || '-')}</td>
                        <td class="py-3"><span class="px-2 py-0.5 rounded-full text-xs font-medium ${statusColor(p.status)}">${escapeHtml(p.status || 'pending')}</span></td>
                    </tr>
                `).join('');
            } else {
                paymentsTable.innerHTML = '<tr><td colspan="5" class="py-8 text-center text-slate-400">No payments recorded</td></tr>';
            }

            // Complaints
            const complaints = tenant.complaints || [];
            const complaintsList = document.getElementById('complaintsList');
            if (complaints.length) {
                complaintsList.innerHTML = complaints.slice(0, 10).map(c => `
                    <div class="flex items-start gap-3 p-3 rounded-xl bg-blue-50/50 border border-blue-100/50">
                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-amber-50 to-amber-100 text-amber-600 flex items-center justify-center flex-shrink-0"><i class="fas fa-exclamation text-xs"></i></div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between mb-0.5">
                                <p class="text-sm font-medium text-slate-900 truncate">${escapeHtml(c.title || 'Complaint')}</p>
                                <span class="text-xs text-slate-400">${formatDate(c.date || c.created_at)}</span>
                            </div>
                            <p class="text-xs text-slate-500 mb-1">${escapeHtml(c.description || '')}</p>
                            <span class="px-2 py-0.5 rounded text-xs font-medium ${c.status === 'resolved' ? 'bg-emerald-100 text-emerald-700' : c.status === 'in-progress' ? 'bg-blue-100 text-blue-700' : 'bg-red-100 text-red-700'}">${escapeHtml(c.status || 'open')}</span>
                        </div>
                    </div>
                `).join('');
            } else {
                complaintsList.innerHTML = '<div class="py-4 text-center text-slate-400">No complaints recorded</div>';
            }

            // Maintenance & Damages
            const maintenance = tenant.maintenance || [];
            const maintenanceList = document.getElementById('maintenanceList');
            const damageTotal = tenant.damage_total || 0;
            if (maintenance.length) {
                maintenanceList.innerHTML = maintenance.map(m => {
                    const statusClass = m.status==='completed'?'bg-emerald-100 text-emerald-700':m.status==='in-progress'?'bg-blue-100 text-blue-700':'bg-amber-100 text-amber-700';
                    const costDisplay = parseFloat(m.cost||0) > 0 ? 'KES ' + parseFloat(m.cost).toLocaleString('en-KE',{minimumFractionDigits:2}) : '<span class="text-slate-400">No cost</span>';
                    return `
                    <div class="flex items-start gap-3 p-3 rounded-xl bg-amber-50/50 border border-amber-100/50">
                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-amber-50 to-amber-100 text-amber-600 flex items-center justify-center flex-shrink-0"><i class="fas fa-tools text-xs"></i></div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between mb-0.5">
                                <p class="text-sm font-medium text-slate-900 truncate">${escapeHtml(m.title || 'Maintenance')}</p>
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
                maintenanceList.innerHTML = '<div class="py-4 text-center text-slate-400">No maintenance records recorded</div>';
            }
            
            // Show damage total badge if applicable
            if (damageTotal > 0) {
                const badge = document.getElementById('damageTotalBadge');
                badge.textContent = 'KES ' + damageTotal.toLocaleString('en-KE', {minimumFractionDigits: 2});
                badge.classList.remove('hidden');
            }

            // Documents
            const tenantDocs = tenant.documents || [];
            const docsList = document.getElementById('documentsList');
            if (tenantDocs.length) {
                docsList.innerHTML = tenantDocs.map(d => {
                    const docUrl = normalizeUrl(d.url);
                    return `
                    <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-100">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center"><i class="fas fa-file-alt"></i></div>
                            <div>
                                <p class="text-sm font-medium text-slate-900">${escapeHtml(d.name || 'Document')}</p>
                                <p class="text-xs text-slate-400">${escapeHtml(d.type || 'File')}</p>
                            </div>
                        </div>
                        <a href="${docUrl}" target="_blank" class="px-4 py-2 text-xs font-medium text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 transition-all">Open</a>
                    </div>
                    `;
                }).join('');
            } else {
                docsList.innerHTML = '<div class="py-4 text-center text-slate-400">No documents uploaded</div>';
            }

            document.getElementById('loadingState').classList.add('hidden');
            document.getElementById('tenantContent').classList.remove('hidden');
        } catch (e) {
            console.error('Failed to load tenant details:', e);
            document.getElementById('loadingState').classList.add('hidden');
            document.getElementById('errorState').classList.remove('hidden');
        }
    }

    function escapeHtml(value) {
        return String(value ?? '').replace(/[&<>"']/g, char => ({
            '&': '&', '<': '<', '>': '>', '"': '"', "'": '&#039;'
        })[char]);
    }

    loadTenantDetails();
    </script>
</body>
</html>