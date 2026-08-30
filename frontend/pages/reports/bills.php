<?php
/**
 * Bills Report View
 * Renders bill summaries, paid/pending/overdue bills, and billing statistics.
 * All data is loaded via the API controller.
 */
?>
<div class="space-y-6">
    <!-- Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4" id="billsSummaryCards">
        <div class="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-5">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-50 to-blue-100 flex items-center justify-center"><i class="fas fa-file-invoice text-blue-600"></i></div>
            </div>
            <p class="text-2xl font-bold text-slate-900" id="billsTotal">0</p>
            <p class="text-sm text-slate-500">Total Bills</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-5">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-50 to-emerald-100 flex items-center justify-center"><i class="fas fa-check-circle text-emerald-600"></i></div>
            </div>
            <p class="text-2xl font-bold text-slate-900" id="billsPaid">0</p>
            <p class="text-sm text-slate-500">Paid Bills</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-5">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-50 to-amber-100 flex items-center justify-center"><i class="fas fa-clock text-amber-600"></i></div>
            </div>
            <p class="text-2xl font-bold text-slate-900" id="billsPending">0</p>
            <p class="text-sm text-slate-500">Pending Bills</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-5">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-red-50 to-red-100 flex items-center justify-center"><i class="fas fa-exclamation-triangle text-red-600"></i></div>
            </div>
            <p class="text-2xl font-bold text-slate-900" id="billsOverdue">0</p>
            <p class="text-sm text-slate-500">Overdue Bills</p>
        </div>
    </div>

    <!-- Bill Amount Summary -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-5">
            <h3 class="font-semibold text-slate-900 mb-3">Total Amount</h3>
            <p class="text-2xl font-bold text-slate-900" id="billsTotalAmount">KES 0</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-5">
            <h3 class="font-semibold text-slate-900 mb-3">Current Month</h3>
            <p class="text-2xl font-bold text-blue-600" id="billsCurrentMonth">KES 0</p>
            <p class="text-xs text-slate-400 mt-1" id="billsCurrentCount">0 bills</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-5">
            <h3 class="font-semibold text-slate-900 mb-3">Overdue Amount</h3>
            <p class="text-2xl font-bold text-red-600" id="billsOverdueAmount">KES 0</p>
            <p class="text-xs text-slate-400 mt-1" id="billsOverdueCount">0 bills</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-4 sm:p-5">
        <div class="flex flex-wrap items-center gap-3">
            <div class="flex-1 min-w-[150px]">
                <label class="block text-xs font-medium text-slate-500 mb-1">Property</label>
                <select id="billsPropertyFilter" class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-white text-slate-900 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all text-sm">
                    <option value="">All Properties</option>
                </select>
            </div>
            <div class="flex-1 min-w-[150px]">
                <label class="block text-xs font-medium text-slate-500 mb-1">Bill Type</label>
                <select id="billsTypeFilter" class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-white text-slate-900 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all text-sm">
                    <option value="">All Types</option>
                    <option value="Rent">Rent</option>
                    <option value="Water">Water</option>
                    <option value="Electricity">Electricity</option>
                </select>
            </div>
            <div class="flex-1 min-w-[150px]">
                <label class="block text-xs font-medium text-slate-500 mb-1">Bill Status</label>
                <select id="billsStatusFilter" class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-white text-slate-900 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all text-sm">
                    <option value="">All</option>
                    <option value="paid">Paid</option>
                    <option value="partial">Partial</option>
                    <option value="pending">Pending</option>
                    <option value="overdue">Overdue</option>
                </select>
            </div>
            <div class="flex-1 min-w-[150px]">
                <label class="block text-xs font-medium text-slate-500 mb-1">Due Date From</label>
                <input type="date" id="billsDueFrom" class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-white text-slate-900 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all text-sm">
            </div>
            <div class="flex-1 min-w-[150px]">
                <label class="block text-xs font-medium text-slate-500 mb-1">Due Date To</label>
                <input type="date" id="billsDueTo" class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-white text-slate-900 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all text-sm">
            </div>
            <div class="flex items-end gap-2">
                <button onclick="loadBills()" class="px-4 py-2 bg-blue-600 text-white rounded-xl text-sm font-medium hover:bg-blue-700 transition-colors"><i class="fas fa-search mr-1"></i>Filter</button>
                <button onclick="resetBillsFilters()" class="px-4 py-2 bg-slate-100 text-slate-600 rounded-xl text-sm font-medium hover:bg-slate-200 transition-colors"><i class="fas fa-undo mr-1"></i>Reset</button>
            </div>
        </div>
    </div>

    <!-- Bills by Type Summary -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4" id="billsByType"></div>

    <!-- Bills Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-blue-100/50 overflow-hidden">
        <div class="px-6 py-4 border-b border-blue-50 flex items-center justify-between">
            <h3 class="font-semibold text-slate-900">Bills Details</h3>
            <span class="text-xs text-slate-400" id="billsRecordCount">Loading...</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wider border-b border-blue-50 bg-blue-50/50">
                        <th class="px-6 py-4">Month</th>
                        <th class="px-6 py-4">Property</th>
                        <th class="px-6 py-4">Tenant</th>
                        <th class="px-6 py-4">Type</th>
                        <th class="px-6 py-4">Amount</th>
                        <th class="px-6 py-4">Paid</th>
                        <th class="px-6 py-4">Balance</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4">Due Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-blue-50" id="billsTableBody">
                    <tr><td colspan="9" class="px-6 py-12 text-center text-slate-400"><div class="flex flex-col items-center gap-3"><i class="fas fa-spinner fa-spin text-3xl text-blue-400"></i><span>Loading bills...</span></div></td></tr>
                </tbody>
            </table>
        </div>
        <div id="billsPagination" class="px-6 py-4 border-t border-blue-50"></div>
    </div>
</div>

<!-- Pagination Template -->
<div id="__rf_pagination_template" style="display:none">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <nav class="rf-pagination flex items-center justify-center gap-3 text-sm text-slate-700" aria-label="Pagination">
            <button data-action="first" class="px-3 py-1 rounded-lg bg-white border border-slate-200">«</button>
            <button data-action="prev" class="px-3 py-1 rounded-lg bg-white border border-slate-200">‹</button>
            <span data-role="pages" class="px-2"></span>
            <button data-action="next" class="px-3 py-1 rounded-lg bg-white border border-slate-200">›</button>
            <button data-action="last" class="px-3 py-1 rounded-lg bg-white border border-slate-200">»</button>
        </nav>
        <div class="flex items-center gap-2 text-sm text-slate-600">
            <label class="font-medium" for="__rf_per_page_select">Rows:</label>
            <select data-role="per-page-select" class="px-3 py-2 rounded-xl border border-slate-200 bg-white text-slate-900 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all text-sm">
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select>
        </div>
    </div>
</div>

<script>
let billsCurrentPage = 1;
let billsPerPage = 25;

async function loadBills(page) {
    if (page === undefined) page = 1;
    billsCurrentPage = page;
    
    const params = new URLSearchParams({
        page: page,
        per_page: billsPerPage,
        property_id: document.getElementById('billsPropertyFilter').value || '',
        bill_type: document.getElementById('billsTypeFilter').value || '',
        bill_status: document.getElementById('billsStatusFilter').value || '',
        due_date_from: document.getElementById('billsDueFrom').value || '',
        due_date_to: document.getElementById('billsDueTo').value || ''
    });
    
    const url = API + '/reports/bills?' + params.toString();
    
    let data = [];
    let meta = {};
    let summary = {};

    try {
        console.log('Fetching bills:', url);
        var res = await apiRequest(url);
        console.log('Bills response:', res);
        data = res.data || [];
        summary = res.summary || {};
        meta = res.meta || {};

        document.getElementById('billsTotal').textContent = summary.total_bills || 0;
        document.getElementById('billsPaid').textContent = (summary.paid && summary.paid.count) || 0;
        document.getElementById('billsPending').textContent = (summary.pending && summary.pending.count) || 0;
        document.getElementById('billsOverdue').textContent = summary.overdue_count || 0;
        document.getElementById('billsTotalAmount').textContent = fmtCurrency(summary.total_amount || 0);
        document.getElementById('billsCurrentMonth').textContent = fmtCurrency(summary.current_month_total || 0);
        document.getElementById('billsCurrentCount').textContent = (summary.current_month_count || 0) + ' bills';
        document.getElementById('billsOverdueAmount').textContent = fmtCurrency(summary.overdue_amount || 0);
        document.getElementById('billsOverdueCount').textContent = (summary.overdue_count || 0) + ' bills';
        document.getElementById('billsRecordCount').textContent = ((meta && meta.total) || 0) + ' records';

        const byTypeContainer = document.getElementById('billsByType');
        const byType = summary.by_type || [];
        if (byType.length) {
            byTypeContainer.innerHTML = byType.map(function(t) {
                return '<div class="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-5">' +
                    '<h3 class="font-semibold text-slate-900 mb-2">' + (t.type || 'Other') + '</h3>' +
                    '<p class="text-2xl font-bold text-slate-900">' + fmtCurrency(t.total) + '</p>' +
                    '<p class="text-sm text-slate-500">' + (t.count || 0) + ' bills</p>' +
                '</div>';
            }).join('');
        } else {
            byTypeContainer.innerHTML = '<div class="col-span-full text-center text-slate-400 py-4">No bill type data available</div>';
        }

        var tbody = document.getElementById('billsTableBody');
        if (data.length) {
            tbody.innerHTML = data.map(function(row) {
                var tenantAvatar = '';
                if (row.tenant_name) {
                    var initials = row.tenant_name.split(' ').map(function(s) { return s[0]; }).join('').substring(0,2).toUpperCase();
                    tenantAvatar = '<div class="flex items-center gap-2">' +
                        '<div class="w-7 h-7 rounded-full bg-gradient-to-br from-blue-50 to-blue-100 text-blue-600 flex items-center justify-center text-xs font-bold">' + initials + '</div>' +
                        '<span class="text-sm font-medium text-slate-900">' + row.tenant_name + '</span>' +
                    '</div>';
                } else {
                    tenantAvatar = '<span class="text-sm text-slate-400">N/A</span>';
                }
                
                var dueDate = 'N/A';
                if (row.due_date) {
                    var d = new Date(row.due_date);
                    dueDate = d.toLocaleDateString('en-GB', {day:'numeric', month:'short', year:'numeric'});
                }
                
                var balance = (row.balance || 0);
                var balanceClass = balance > 0 ? 'text-red-600' : 'text-slate-900';
                
                return '<tr class="hover:bg-blue-50/30 transition-colors">' +
                    '<td class="px-6 py-4 text-sm text-slate-500">' + (row.month || 'N/A') + '</td>' +
                    '<td class="px-6 py-4 text-sm font-medium text-slate-900">' + (row.property_name || 'N/A') + '</td>' +
                    '<td class="px-6 py-4">' + tenantAvatar + '</td>' +
                    '<td class="px-6 py-4 text-sm text-slate-500">' + (row.type || 'Rent') + '</td>' +
                    '<td class="px-6 py-4 text-sm font-medium text-slate-900">' + fmtCurrency(row.total) + '</td>' +
                    '<td class="px-6 py-4 text-sm font-medium text-emerald-600">' + fmtCurrency(row.paid || 0) + '</td>' +
                    '<td class="px-6 py-4 text-sm font-medium ' + balanceClass + '">' + fmtCurrency(balance) + '</td>' +
                    '<td class="px-6 py-4">' + getStatusBadge(row.bill_status || row.payment_status || 'pending') + '</td>' +
                    '<td class="px-6 py-4 text-sm text-slate-500">' + dueDate + '</td>' +
                '</tr>';
            }).join('');
        } else {
            tbody.innerHTML = '<tr><td colspan="9" class="px-6 py-12 text-center text-slate-400">No bills found matching the filters.</td></tr>';
        }

        renderPagination('billsPagination', meta, function(p) { loadBills(p); });
    } catch (e) {
        console.error('loadBills error:', e);
        showBillsError('Failed to load bills report: ' + e.message);
        toast('Failed to load bills report', 'error');
    } finally {
        var safeMeta = meta || {};
        document.getElementById('billsRecordCount').textContent = (safeMeta.total || 0) + ' records';
        var safeData = Array.isArray(data) ? data : [];
        if (!safeData.length) {
            var tbody = document.getElementById('billsTableBody');
            if (tbody && !tbody.innerHTML.includes('No bills found')) {
                tbody.innerHTML = '<tr><td colspan="9" class="px-6 py-12 text-center text-slate-400">No bills found matching the filters.</td></tr>';
            }
        }
    }
}

function resetBillsFilters() {
    document.getElementById('billsPropertyFilter').value = '';
    document.getElementById('billsTypeFilter').value = '';
    document.getElementById('billsStatusFilter').value = '';
    document.getElementById('billsDueFrom').value = '';
    document.getElementById('billsDueTo').value = '';
    loadBills(1);
}

async function loadBillsProperties() {
    try {
        var res = await apiRequest(API + '/properties');
        var props = res.properties || [];
        document.getElementById('billsPropertyFilter').innerHTML = '<option value="">All Properties</option>' + props.map(function(p) {
            return '<option value="' + p.id + '">' + p.name + '</option>';
        }).join('');
    } catch (e) { 
        console.warn('Could not load properties', e);
    }
}

function showBillsError(msg) {
    var tbody = document.getElementById('billsTableBody');
    if (tbody) {
        tbody.innerHTML = '<tr><td colspan="9" class="px-6 py-12 text-center text-red-600"><i class="fas fa-exclamation-circle mr-2"></i>' + msg + '</td></tr>';
    }
}

// Initialize with explicit error fallback
try {
    loadBillsProperties().then(function() {
        return loadBills(1);
    }).catch(function(e) {
        console.error('Bills report init failed:', e);
        showBillsError('Report failed to load: ' + (e.message || 'Unknown error'));
        toast('Report failed to load', 'error');
    });
} catch(e) {
    console.error('Bills report sync error:', e);
    showBillsError('JavaScript error: ' + e.message);
}
</script>
