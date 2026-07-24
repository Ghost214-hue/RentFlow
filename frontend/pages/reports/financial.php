<?php
/**
 * Financial Report View
 * Renders financial summaries, income analysis, and payment metrics.
 * All data is loaded via the API controller.
 */
?>
<div class="space-y-6">
    <!-- Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4" id="finSummaryCards">
        <div class="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-5">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-50 to-blue-100 flex items-center justify-center"><i class="fas fa-dollar-sign text-blue-600"></i></div>
            </div>
            <p class="text-2xl font-bold text-slate-900" id="finTotalRevenue">KES 0</p>
            <p class="text-sm text-slate-500">Total Revenue</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-5">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-50 to-emerald-100 flex items-center justify-center"><i class="fas fa-check-circle text-emerald-600"></i></div>
            </div>
            <p class="text-2xl font-bold text-slate-900" id="finCollected">KES 0</p>
            <p class="text-sm text-slate-500">Amount Collected</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-5">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-50 to-amber-100 flex items-center justify-center"><i class="fas fa-exclamation-circle text-amber-600"></i></div>
            </div>
            <p class="text-2xl font-bold text-slate-900" id="finOutstanding">KES 0</p>
            <p class="text-sm text-slate-500">Outstanding</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-5">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-purple-50 to-purple-100 flex items-center justify-center"><i class="fas fa-percentage text-purple-600"></i></div>
            </div>
            <p class="text-2xl font-bold text-slate-900" id="finCollectionRate">0%</p>
            <p class="text-sm text-slate-500">Collection Rate</p>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-5">
            <h3 class="font-semibold text-slate-900 mb-4">Monthly Revenue Trend</h3>
            <div class="relative h-72"><canvas id="finRevenueChart"></canvas></div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-5">
            <h3 class="font-semibold text-slate-900 mb-4">Income by Type</h3>
            <div class="relative h-72"><canvas id="finIncomeChart"></canvas></div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-4 sm:p-5">
        <div class="flex flex-wrap items-center gap-3">
            <div class="flex-1 min-w-[150px]">
                <label class="block text-xs font-medium text-slate-500 mb-1">Date From</label>
                <input type="date" id="finDateFrom" value="<?php echo date('Y-m-01'); ?>" class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-white text-slate-900 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all text-sm">
            </div>
            <div class="flex-1 min-w-[150px]">
                <label class="block text-xs font-medium text-slate-500 mb-1">Date To</label>
                <input type="date" id="finDateTo" value="<?php echo date('Y-m-t'); ?>" class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-white text-slate-900 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all text-sm">
            </div>
            <div class="flex-1 min-w-[150px]">
                <label class="block text-xs font-medium text-slate-500 mb-1">Property</label>
                <select id="finPropertyFilter" class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-white text-slate-900 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all text-sm">
                    <option value="">All Properties</option>
                </select>
            </div>
            <div class="flex-1 min-w-[150px]">
                <label class="block text-xs font-medium text-slate-500 mb-1">Payment Status</label>
                <select id="finStatusFilter" class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-white text-slate-900 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all text-sm">
                    <option value="">All</option>
                    <option value="completed">Completed</option>
                    <option value="pending">Pending</option>
                    <option value="failed">Failed</option>
                </select>
            </div>
            <div class="flex-1 min-w-[150px]">
                <label class="block text-xs font-medium text-slate-500 mb-1">Income Type</label>
                <select id="finTypeFilter" class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-white text-slate-900 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all text-sm">
                    <option value="">All Types</option>
                    <option value="Rent">Rent</option>
                    <option value="Water">Water</option>
                    <option value="Electricity">Electricity</option>
                    <option value="Deposit">Deposit</option>
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button onclick="loadFinancial()" class="px-4 py-2 bg-blue-600 text-white rounded-xl text-sm font-medium hover:bg-blue-700 transition-colors"><i class="fas fa-search mr-1"></i>Filter</button>
                <button onclick="resetFinFilters()" class="px-4 py-2 bg-slate-100 text-slate-600 rounded-xl text-sm font-medium hover:bg-slate-200 transition-colors"><i class="fas fa-undo mr-1"></i>Reset</button>
            </div>
        </div>
    </div>

    <!-- Payment Method Breakdown -->
    <div class="bg-white rounded-2xl shadow-sm border border-blue-100/50 p-5">
        <h3 class="font-semibold text-slate-900 mb-4">Payment Method Breakdown</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4" id="finMethodBreakdown"></div>
    </div>

    <!-- Transactions Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-blue-100/50 overflow-hidden">
        <div class="px-6 py-4 border-b border-blue-50 flex items-center justify-between">
            <h3 class="font-semibold text-slate-900">Transactions</h3>
            <span class="text-xs text-slate-400" id="finRecordCount">Loading...</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wider border-b border-blue-50 bg-blue-50/50">
                        <th class="px-6 py-4">Date</th>
                        <th class="px-6 py-4">Property</th>
                        <th class="px-6 py-4">Tenant</th>
                        <th class="px-6 py-4">Type</th>
                        <th class="px-6 py-4">Method</th>
                        <th class="px-6 py-4">Amount</th>
                        <th class="px-6 py-4">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-blue-50" id="finTableBody">
                    <tr><td colspan="7" class="px-6 py-12 text-center text-slate-400"><div class="flex flex-col items-center gap-3"><i class="fas fa-spinner fa-spin text-3xl text-blue-400"></i><span>Loading transactions...</span></div></td></tr>
                </tbody>
            </table>
        </div>
        <div id="finPagination" class="px-6 py-4 border-t border-blue-50"></div>
    </div>
</div>

<script>
let finChart1 = null;
let finChart2 = null;
let finCurrentPage = 1;
let finPerPage = 25;

async function loadFinancial(page = 1) {
    finCurrentPage = page;
    const params = new URLSearchParams({
        page: page,
        per_page: finPerPage,
        date_from: document.getElementById('finDateFrom')?.value || '',
        date_to: document.getElementById('finDateTo')?.value || '',
        property_id: document.getElementById('finPropertyFilter')?.value || '',
        payment_status: document.getElementById('finStatusFilter')?.value || '',
        income_type: document.getElementById('finTypeFilter')?.value || '',
    });

    try {
        const res = await apiRequest(`${API}/reports/financial?${params}`);
        const data = res.data || [];
        const summary = res.summary || {};
        const meta = res.meta || {};

        // Update summary cards
        document.getElementById('finTotalRevenue').textContent = fmtCurrency(summary.total_revenue || 0);
        document.getElementById('finCollected').textContent = fmtCurrency(summary.total_collected || 0);
        document.getElementById('finOutstanding').textContent = fmtCurrency(summary.outstanding || 0);
        document.getElementById('finCollectionRate').textContent = (summary.collection_rate || 0) + '%';
        document.getElementById('finRecordCount').textContent = `${meta.total || 0} records`;

        // Payment method breakdown
        const methodContainer = document.getElementById('finMethodBreakdown');
        const methods = summary.method_breakdown || [];
        if (methods.length) {
            methodContainer.innerHTML = methods.map(m => `
                <div class="bg-slate-50 rounded-xl p-4 border border-slate-100">
                    <p class="text-lg font-bold text-slate-900">${fmtCurrency(m.total)}</p>
                    <p class="text-sm text-slate-500">${m.method || 'N/A'} <span class="text-xs text-slate-400">(${m.count} txns)</span></p>
                </div>
            `).join('');
        } else {
            methodContainer.innerHTML = '<div class="col-span-full text-center text-slate-400 py-4">No payment data available</div>';
        }

        // Income by type breakdown
        const incomeTypes = summary.income_by_type || [];
        const incomeLabels = incomeTypes.map(t => t.type || 'Other');
        const incomeValues = incomeTypes.map(t => parseFloat(t.total) || 0);

        // Monthly trend
        const trend = summary.monthly_trend || [];
        const trendLabels = trend.map(t => t.month ? formatMonthLabel(t.month) : 'N/A');
        const trendValues = trend.map(t => parseFloat(t.total) || 0);

        // Charts
        if (finChart1) { finChart1.destroy(); finChart1 = null; }
        if (finChart2) { finChart2.destroy(); finChart2 = null; }

        const revCtx = document.getElementById('finRevenueChart');
        if (revCtx && trendLabels.length) {
            finChart1 = new Chart(revCtx, {
                type: 'bar',
                data: {
                    labels: trendLabels,
                    datasets: [{
                        label: 'Revenue',
                        data: trendValues,
                        backgroundColor: 'rgba(59,130,246,0.7)',
                        borderColor: '#3b82f6',
                        borderWidth: 1,
                        borderRadius: 6,
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' }, ticks: { color: '#64748b' } }, x: { grid: { display: false }, ticks: { color: '#64748b' } } }
                }
            });
        }

        const incCtx = document.getElementById('finIncomeChart');
        if (incCtx && incomeLabels.length) {
            finChart2 = new Chart(incCtx, {
                type: 'doughnut',
                data: {
                    labels: incomeLabels,
                    datasets: [{
                        data: incomeValues,
                        backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#ef4444', '#e2e8f0'],
                        borderWidth: 0,
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false, cutout: '60%',
                    plugins: { legend: { position: 'bottom' } }
                }
            });
        }

        // Render table
        const tbody = document.getElementById('finTableBody');
        if (data.length) {
            tbody.innerHTML = data.map(row => `
                <tr class="hover:bg-blue-50/30 transition-colors">
                    <td class="px-6 py-4 text-sm text-slate-500">${row.date ? new Date(row.date).toLocaleDateString('en-GB',{day:'numeric',month:'short',year:'numeric'}) : 'N/A'}</td>
                    <td class="px-6 py-4 text-sm text-slate-900">${row.property_name || 'N/A'}</td>
                    <td class="px-6 py-4"><div class="flex items-center gap-2"><div class="w-7 h-7 rounded-full bg-gradient-to-br from-blue-50 to-blue-100 text-blue-600 flex items-center justify-center text-xs font-bold">${(row.tenant_name||'??').split(' ').map(s => s[0]).join('').substring(0,2).toUpperCase()}</div><span class="text-sm font-medium text-slate-900">${row.tenant_name||'N/A'}</span></div></td>
                    <td class="px-6 py-4 text-sm text-slate-500">${row.type || 'Rent'}</td>
                    <td class="px-6 py-4 text-sm text-slate-500">${row.method || 'N/A'}</td>
                    <td class="px-6 py-4 text-sm font-medium text-slate-900">${fmtCurrency(row.amount)}</td>
                    <td class="px-6 py-4">${getStatusBadge(row.status)}</td>
                </tr>
            `).join('');
        } else {
            tbody.innerHTML = '<tr><td colspan="7" class="px-6 py-12 text-center text-slate-400">No transactions found matching the filters.</td></tr>';
        }

        // Pagination
        renderPagination('finPagination', meta, (p) => loadFinancial(p));
    } catch (e) {
        console.error(e);
        document.getElementById('finTableBody').innerHTML = '<tr><td colspan="7" class="px-6 py-12 text-center text-slate-400"><i class="fas fa-exclamation-circle text-red-400 mr-2"></i>Failed to load data.</td></tr>';
        toast('Failed to load financial report', 'error');
    }
}

function resetFinFilters() {
    document.getElementById('finDateFrom').value = '<?php echo date('Y-m-01'); ?>';
    document.getElementById('finDateTo').value = '<?php echo date('Y-m-t'); ?>';
    document.getElementById('finPropertyFilter').value = '';
    document.getElementById('finStatusFilter').value = '';
    document.getElementById('finTypeFilter').value = '';
    loadFinancial(1);
}

async function loadFinProperties() {
    try {
        const res = await apiRequest(`${API}/properties`);
        const props = res.properties || [];
        const sel = document.getElementById('finPropertyFilter');
        sel.innerHTML = '<option value="">All Properties</option>' + props.map(p => `<option value="${p.id}">${p.name}</option>`).join('');
    } catch (e) { /* ignore */ }
}

loadFinProperties();
loadFinancial(1);
</script>