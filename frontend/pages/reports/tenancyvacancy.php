<?php
/**
 * Tenancy & Vacancy Report View
 * Renders occupancy analytics, tenancy status, and vacancy summaries.
 * All data is loaded via the API controller.
 */
?>
<div class="space-y-6">
    <!-- Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4" id="tvSummaryCards">
        <div class="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-5">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-50 to-blue-100 flex items-center justify-center"><i class="fas fa-building text-blue-600"></i></div>
            </div>
            <p class="text-2xl font-bold text-slate-900" id="tvTotalUnits">0</p>
            <p class="text-sm text-slate-500">Total Units</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-5">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-50 to-emerald-100 flex items-center justify-center"><i class="fas fa-check-circle text-emerald-600"></i></div>
            </div>
            <p class="text-2xl font-bold text-slate-900" id="tvOccupiedUnits">0</p>
            <p class="text-sm text-slate-500">Occupied Units</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-5">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-50 to-amber-100 flex items-center justify-center"><i class="fas fa-door-open text-amber-600"></i></div>
            </div>
            <p class="text-2xl font-bold text-slate-900" id="tvVacantUnits">0</p>
            <p class="text-sm text-slate-500">Vacant Units</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-5">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-purple-50 to-purple-100 flex items-center justify-center"><i class="fas fa-percentage text-purple-600"></i></div>
            </div>
            <p class="text-2xl font-bold text-slate-900" id="tvOccupancyRate">0%</p>
            <p class="text-sm text-slate-500">Occupancy Rate</p>
        </div>
    </div>

    <!-- Additional Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-5">
            <h3 class="font-semibold text-slate-900 mb-3">Tenant Status</h3>
            <div class="flex items-center justify-between py-2 border-b border-blue-50">
                <span class="text-sm text-slate-600">Active Tenants</span>
                <span class="text-lg font-bold text-emerald-600" id="tvActiveTenants">0</span>
            </div>
            <div class="flex items-center justify-between py-2">
                <span class="text-sm text-slate-600">Terminated Tenants</span>
                <span class="text-lg font-bold text-red-600" id="tvTerminatedTenants">0</span>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-5">
            <h3 class="font-semibold text-slate-900 mb-3">Vacancy Rate</h3>
            <div class="flex items-center justify-between py-2 border-b border-blue-50">
                <span class="text-sm text-slate-600">Vacancy Rate</span>
                <span class="text-lg font-bold text-amber-600" id="tvVacancyRate">0%</span>
            </div>
            <div class="flex items-center justify-between py-2">
                <span class="text-sm text-slate-600">Total Properties</span>
                <span class="text-lg font-bold text-blue-600" id="tvTotalProperties">0</span>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-4 sm:p-5">
        <div class="flex flex-wrap items-center gap-3">
            <div class="flex-1 min-w-[150px]">
                <label class="block text-xs font-medium text-slate-500 mb-1">Property</label>
                <select id="tvPropertyFilter" class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-white text-slate-900 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all text-sm">
                    <option value="">All Properties</option>
                </select>
            </div>
            <div class="flex-1 min-w-[150px]">
                <label class="block text-xs font-medium text-slate-500 mb-1">Vacancy Status</label>
                <select id="tvVacancyFilter" class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-white text-slate-900 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all text-sm">
                    <option value="">All</option>
                    <option value="occupied">Occupied</option>
                    <option value="vacant">Vacant</option>
                </select>
            </div>
            <div class="flex-1 min-w-[150px]">
                <label class="block text-xs font-medium text-slate-500 mb-1">Occupancy Status</label>
                <select id="tvOccupancyFilter" class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-white text-slate-900 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all text-sm">
                    <option value="">All</option>
                    <option value="active">Active</option>
                    <option value="terminated">Terminated</option>
                </select>
            </div>
            <div class="flex-1 min-w-[150px]">
                <label class="block text-xs font-medium text-slate-500 mb-1">Property Type</label>
                <select id="tvTypeFilter" class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-white text-slate-900 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all text-sm">
                    <option value="">All Types</option>
                    <option value="Apartment Block">Apartment Block</option>
                    <option value="Bungalow">Bungalow</option>
                    <option value="Maisonette">Maisonette</option>
                    <option value="Studio">Studio</option>
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button onclick="loadTenancyVacancy()" class="px-4 py-2 bg-blue-600 text-white rounded-xl text-sm font-medium hover:bg-blue-700 transition-colors"><i class="fas fa-search mr-1"></i>Filter</button>
                <button onclick="resetTVFilters()" class="px-4 py-2 bg-slate-100 text-slate-600 rounded-xl text-sm font-medium hover:bg-slate-200 transition-colors"><i class="fas fa-undo mr-1"></i>Reset</button>
            </div>
        </div>
    </div>

    <!-- Data Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-blue-100/50 overflow-hidden">
        <div class="px-6 py-4 border-b border-blue-50 flex items-center justify-between">
            <h3 class="font-semibold text-slate-900">Unit Details</h3>
            <span class="text-xs text-slate-400" id="tvRecordCount">Loading...</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wider border-b border-blue-50 bg-blue-50/50">
                        <th class="px-6 py-4">Property</th>
                        <th class="px-6 py-4">Unit</th>
                        <th class="px-6 py-4">Tenant</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4">Lease Period</th>
                        <th class="px-6 py-4">Rent</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-blue-50" id="tvTableBody">
                    <tr><td colspan="6" class="px-6 py-12 text-center text-slate-400"><div class="flex flex-col items-center gap-3"><i class="fas fa-spinner fa-spin text-3xl text-blue-400"></i><span>Loading data...</span></div></td></tr>
                </tbody>
            </table>
        </div>
        <div id="tvPagination" class="px-6 py-4 border-t border-blue-50"></div>
    </div>
</div>

<script>
let tvCurrentPage = 1;
let tvPerPage = 25;

async function loadTenancyVacancy(page = 1) {
    tvCurrentPage = page;
    const params = new URLSearchParams({
        page: page,
        per_page: tvPerPage,
        property_id: document.getElementById('tvPropertyFilter')?.value || '',
        vacancy_status: document.getElementById('tvVacancyFilter')?.value || '',
        occupancy_status: document.getElementById('tvOccupancyFilter')?.value || '',
        property_type: document.getElementById('tvTypeFilter')?.value || '',
    });

    try {
        const res = await apiRequest(`${API}/reports/tenancy-vacancy?${params}`);
        const data = res.data || [];
        const summary = res.summary || {};
        const meta = res.meta || {};

        // Update summary cards
        document.getElementById('tvTotalUnits').textContent = summary.total_units || 0;
        document.getElementById('tvOccupiedUnits').textContent = summary.occupied_units || 0;
        document.getElementById('tvVacantUnits').textContent = summary.vacant_units || 0;
        document.getElementById('tvOccupancyRate').textContent = (summary.occupancy_rate || 0) + '%';
        document.getElementById('tvActiveTenants').textContent = summary.active_tenants || 0;
        document.getElementById('tvTerminatedTenants').textContent = summary.terminated_tenants || 0;
        document.getElementById('tvVacancyRate').textContent = (summary.vacancy_rate || 0) + '%';
        document.getElementById('tvRecordCount').textContent = `${meta.total || 0} records`;

        // Render table
        const tbody = document.getElementById('tvTableBody');
        if (data.length) {
            tbody.innerHTML = data.map(row => `
                <tr class="hover:bg-blue-50/30 transition-colors">
                    <td class="px-6 py-4 text-sm font-medium text-slate-900">${row.property_name || 'N/A'}</td>
                    <td class="px-6 py-4 text-sm text-slate-500">${row.unit || 'N/A'} <span class="text-xs text-slate-400">(${row.unit_type || ''})</span></td>
                    <td class="px-6 py-4">
                        ${row.tenant_name ? `<div class="flex items-center gap-2"><div class="w-7 h-7 rounded-full bg-gradient-to-br from-blue-50 to-blue-100 text-blue-600 flex items-center justify-center text-xs font-bold">${row.tenant_name.split(' ').map(s => s[0]).join('').substring(0,2).toUpperCase()}</div><div><span class="text-sm font-medium text-slate-900">${row.tenant_name}</span><br><span class="text-xs text-slate-400">${row.tenant_phone || ''}</span></div></div>` : '<span class="text-sm text-slate-400">Vacant</span>'}
                    </td>
                    <td class="px-6 py-4">${getStatusBadge(row.occupancy_status || row.tenant_status || 'vacant')}</td>
                    <td class="px-6 py-4 text-sm text-slate-500">${row.lease_start ? new Date(row.lease_start).toLocaleDateString('en-GB',{day:'numeric',month:'short',year:'numeric'}) : '-'} ${row.lease_end ? ' → ' + new Date(row.lease_end).toLocaleDateString('en-GB',{day:'numeric',month:'short',year:'numeric'}) : ''}</td>
                    <td class="px-6 py-4 text-sm font-medium text-slate-900">${fmtCurrency(row.rent)}</td>
                </tr>
            `).join('');
        } else {
            tbody.innerHTML = '<tr><td colspan="6" class="px-6 py-12 text-center text-slate-400">No units found matching the filters.</td></tr>';
        }

        // Pagination
        renderPagination('tvPagination', meta, (p) => loadTenancyVacancy(p));
    } catch (e) {
        console.error(e);
        document.getElementById('tvTableBody').innerHTML = '<tr><td colspan="6" class="px-6 py-12 text-center text-slate-400"><i class="fas fa-exclamation-circle text-red-400 mr-2"></i>Failed to load data.</td></tr>';
        toast('Failed to load tenancy & vacancy report', 'error');
    }
}

function resetTVFilters() {
    document.getElementById('tvPropertyFilter').value = '';
    document.getElementById('tvVacancyFilter').value = '';
    document.getElementById('tvOccupancyFilter').value = '';
    document.getElementById('tvTypeFilter').value = '';
    loadTenancyVacancy(1);
}

// Load properties for filter dropdown
async function loadTVProperties() {
    try {
        const res = await apiRequest(`${API}/properties`);
        const props = res.properties || [];
        const sel = document.getElementById('tvPropertyFilter');
        sel.innerHTML = '<option value="">All Properties</option>' + props.map(p => `<option value="${p.id}">${p.name}</option>`).join('');
    } catch (e) { /* ignore */ }
}

loadTVProperties();
loadTenancyVacancy(1);
</script>