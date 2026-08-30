<?php
/**
 * Complaints Report View
 * Renders complaint statistics, resolution status, and category breakdowns.
 * All data is loaded via the API controller.
 */
?>
<div class="space-y-6">
    <!-- Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4" id="compSummaryCards">
        <div class="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-5">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-50 to-blue-100 flex items-center justify-center"><i class="fas fa-exclamation-triangle text-blue-600"></i></div>
            </div>
            <p class="text-2xl font-bold text-slate-900" id="compTotal">0</p>
            <p class="text-sm text-slate-500">Total Complaints</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-5">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-50 to-emerald-100 flex items-center justify-center"><i class="fas fa-check-circle text-emerald-600"></i></div>
            </div>
            <p class="text-2xl font-bold text-slate-900" id="compResolved">0</p>
            <p class="text-sm text-slate-500">Resolved</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-5">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-50 to-amber-100 flex items-center justify-center"><i class="fas fa-spinner text-amber-600"></i></div>
            </div>
            <p class="text-2xl font-bold text-slate-900" id="compOpen">0</p>
            <p class="text-sm text-slate-500">Open / In Progress</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-5">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-red-50 to-red-100 flex items-center justify-center"><i class="fas fa-arrow-up text-red-600"></i></div>
            </div>
            <p class="text-2xl font-bold text-slate-900" id="compHighPriority">0</p>
            <p class="text-sm text-slate-500">High Priority Open</p>
        </div>
    </div>

    <!-- Stats Row -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-5">
            <h3 class="font-semibold text-slate-900 mb-3">Resolution Rate</h3>
            <div id="compResolutionRate" class="flex items-center gap-4">
                <span class="text-3xl font-bold text-emerald-600">0%</span>
                <div class="flex-1 bg-slate-100 rounded-full h-3 overflow-hidden">
                    <div id="compResolutionBar" class="h-full bg-gradient-to-r from-emerald-500 to-emerald-400 rounded-full transition-all duration-500" style="width:0%"></div>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-5">
            <h3 class="font-semibold text-slate-900 mb-3">By Priority</h3>
            <div id="compByPriority" class="flex items-center gap-4">
                <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-emerald-500"></span><span class="text-sm text-slate-600">Low: <strong id="compLowCount">0</strong></span></div>
                <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-amber-500"></span><span class="text-sm text-slate-600">Medium: <strong id="compMediumCount">0</strong></span></div>
                <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-red-500"></span><span class="text-sm text-slate-600">High: <strong id="compHighCount">0</strong></span></div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-4 sm:p-5">
        <div class="flex flex-wrap items-center gap-3">
            <div class="flex-1 min-w-[150px]">
                <label class="block text-xs font-medium text-slate-500 mb-1">Property</label>
                <select id="compPropertyFilter" class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-white text-slate-900 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all text-sm">
                    <option value="">All Properties</option>
                </select>
            </div>
            <div class="flex-1 min-w-[150px]">
                <label class="block text-xs font-medium text-slate-500 mb-1">Category</label>
                <select id="compCategoryFilter" class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-white text-slate-900 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all text-sm">
                    <option value="">All Categories</option>
                </select>
            </div>
            <div class="flex-1 min-w-[150px]">
                <label class="block text-xs font-medium text-slate-500 mb-1">Status</label>
                <select id="compStatusFilter" class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-white text-slate-900 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all text-sm">
                    <option value="">All</option>
                    <option value="open">Open</option>
                    <option value="in-progress">In Progress</option>
                    <option value="resolved">Resolved</option>
                </select>
            </div>
            <div class="flex-1 min-w-[150px]">
                <label class="block text-xs font-medium text-slate-500 mb-1">Priority</label>
                <select id="compPriorityFilter" class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-white text-slate-900 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all text-sm">
                    <option value="">All</option>
                    <option value="low">Low</option>
                    <option value="medium">Medium</option>
                    <option value="high">High</option>
                </select>
            </div>
            <div class="flex-1 min-w-[150px]">
                <label class="block text-xs font-medium text-slate-500 mb-1">Date From</label>
                <input type="date" id="compDateFrom" class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-white text-slate-900 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all text-sm">
            </div>
            <div class="flex-1 min-w-[150px]">
                <label class="block text-xs font-medium text-slate-500 mb-1">Date To</label>
                <input type="date" id="compDateTo" class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-white text-slate-900 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all text-sm">
            </div>
            <div class="flex items-end gap-2">
                <button onclick="loadComplaints()" class="px-4 py-2 bg-blue-600 text-white rounded-xl text-sm font-medium hover:bg-blue-700 transition-colors"><i class="fas fa-search mr-1"></i>Filter</button>
                <button onclick="resetCompFilters()" class="px-4 py-2 bg-slate-100 text-slate-600 rounded-xl text-sm font-medium hover:bg-slate-200 transition-colors"><i class="fas fa-undo mr-1"></i>Reset</button>
            </div>
        </div>
    </div>

    <!-- Category Breakdown -->
    <div class="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-5">
        <h3 class="font-semibold text-slate-900 mb-4">Complaints by Category</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4" id="compByCategory"></div>
    </div>

    <!-- Complaints Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-blue-100/50 overflow-hidden">
        <div class="px-6 py-4 border-b border-blue-50 flex items-center justify-between">
            <h3 class="font-semibold text-slate-900">Complaints Details</h3>
            <span class="text-xs text-slate-400" id="compRecordCount">Loading...</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wider border-b border-blue-50 bg-blue-50/50">
                        <th class="px-6 py-4">Date</th>
                        <th class="px-6 py-4">Title</th>
                        <th class="px-6 py-4">Property</th>
                        <th class="px-6 py-4">Tenant</th>
                        <th class="px-6 py-4">Category</th>
                        <th class="px-6 py-4">Priority</th>
                        <th class="px-6 py-4">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-blue-50" id="compTableBody">
                    <tr><td colspan="7" class="px-6 py-12 text-center text-slate-400"><div class="flex flex-col items-center gap-3"><i class="fas fa-spinner fa-spin text-3xl text-blue-400"></i><span>Loading complaints...</span></div></td></tr>
                </tbody>
            </table>
        </div>
        <div id="compPagination" class="px-6 py-4 border-t border-blue-50"></div>
    </div>
</div>

<script>
let compCurrentPage = 1;
let compPerPage = 25;

async function loadComplaints(page = 1) {
    compCurrentPage = page;
    const params = new URLSearchParams({
        page: page,
        per_page: compPerPage,
        property_id: document.getElementById('compPropertyFilter')?.value || '',
        complaint_category: document.getElementById('compCategoryFilter')?.value || '',
        complaint_status: document.getElementById('compStatusFilter')?.value || '',
        priority: document.getElementById('compPriorityFilter')?.value || '',
        date_from: document.getElementById('compDateFrom')?.value || '',
        date_to: document.getElementById('compDateTo')?.value || '',
    });

    try {
        const res = await apiRequest(`${API}/reports/complaints?${params}`);
        const data = res.data || [];
        const summary = res.summary || {};
        const meta = res.meta || {};

        document.getElementById('compTotal').textContent = summary.total_complaints || 0;
        document.getElementById('compResolved').textContent = summary.resolved || 0;
        document.getElementById('compOpen').textContent = summary.open || 0;
        document.getElementById('compHighPriority').textContent = summary.high_priority_open || 0;
        document.getElementById('compRecordCount').textContent = `${meta.total || 0} records`;

        const rate = summary.resolution_rate || 0;
        document.querySelector('#compResolutionRate span').textContent = rate + '%';
        document.getElementById('compResolutionBar').style.width = rate + '%';

        const byPriority = summary.by_priority || [];
        const priorityMap = {};
        byPriority.forEach(p => { priorityMap[p.priority] = p.count; });
        document.getElementById('compLowCount').textContent = priorityMap['low'] || 0;
        document.getElementById('compMediumCount').textContent = priorityMap['medium'] || 0;
        document.getElementById('compHighCount').textContent = priorityMap['high'] || 0;

        const byCategory = document.getElementById('compByCategory');
        const cats = summary.by_category || [];
        if (cats.length) {
            byCategory.innerHTML = cats.map(c => `
                <div class="bg-slate-50 rounded-xl p-4 border border-slate-100">
                    <p class="text-lg font-bold text-slate-900">${c.count || 0}</p>
                    <p class="text-sm text-slate-500">${c.category || 'Other'}</p>
                </div>
            `).join('');
        } else {
            byCategory.innerHTML = '<div class="col-span-full text-center text-slate-400 py-4">No category data</div>';
        }

        const tbody = document.getElementById('compTableBody');
        if (data.length) {
            tbody.innerHTML = data.map(row => `
                <tr class="hover:bg-blue-50/30 transition-colors">
                    <td class="px-6 py-4 text-sm text-slate-500">${row.date ? new Date(row.date).toLocaleDateString('en-GB',{day:'numeric',month:'short',year:'numeric'}) : 'N/A'}</td>
                    <td class="px-6 py-4 text-sm font-medium text-slate-900 max-w-[200px] truncate" title="${row.title || ''}">${row.title || 'N/A'}</td>
                    <td class="px-6 py-4 text-sm text-slate-900">${row.property_name || 'N/A'}</td>
                    <td class="px-6 py-4 text-sm text-slate-500">${row.tenant_name || 'N/A'}</td>
                    <td class="px-6 py-4 text-sm text-slate-500">${row.category || 'Other'}</td>
                    <td class="px-6 py-4">${getStatusBadge(row.priority)}</td>
                    <td class="px-6 py-4">${getStatusBadge(row.status)}</td>
                </tr>
            `).join('');
        } else {
            tbody.innerHTML = '<tr><td colspan="7" class="px-6 py-12 text-center text-slate-400">No complaints found matching the filters.</td></tr>';
        }

        renderPagination('compPagination', meta, (p) => loadComplaints(p));
    } catch (e) {
        console.error(e);
        document.getElementById('compTableBody').innerHTML = '<tr><td colspan="7" class="px-6 py-12 text-center text-slate-400"><i class="fas fa-exclamation-circle text-red-400 mr-2"></i>Failed to load data.</td></tr>';
        toast('Failed to load complaints report', 'error');
    }
}

function resetCompFilters() {
    document.getElementById('compPropertyFilter').value = '';
    document.getElementById('compCategoryFilter').value = '';
    document.getElementById('compStatusFilter').value = '';
    document.getElementById('compPriorityFilter').value = '';
    document.getElementById('compDateFrom').value = '';
    document.getElementById('compDateTo').value = '';
    loadComplaints(1);
}

async function loadCompProperties() {
    try {
        const res = await apiRequest(`${API}/properties`);
        const props = res.properties || [];
        document.getElementById('compPropertyFilter').innerHTML = '<option value="">All Properties</option>' + props.map(p => `<option value="${p.id}">${p.name}</option>`).join('');
    } catch (e) { /* ignore */ }
}

loadCompProperties();
loadComplaints(1);
</script>