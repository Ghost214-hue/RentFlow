            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900">Email Delivery</h1>
                    <p class="text-slate-500 mt-1">Track email delivery status to tenants and next of kin</p>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-slate-500">Total Emails</p>
                            <p class="text-2xl font-bold text-slate-900" id="statTotal">-</p>
                        </div>
                        <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center"><i class="fas fa-envelope text-blue-600 text-xl"></i></div>
                    </div>
                </div>
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-slate-500">Sent</p>
                            <p class="text-2xl font-bold text-emerald-600" id="statSent">-</p>
                        </div>
                        <div class="w-12 h-12 bg-emerald-100 rounded-xl flex items-center justify-center"><i class="fas fa-check-circle text-emerald-600 text-xl"></i></div>
                    </div>
                </div>
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-slate-500">Failed</p>
                            <p class="text-2xl font-bold text-red-600" id="statFailed">-</p>
                        </div>
                        <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center"><i class="fas fa-times-circle text-red-600 text-xl"></i></div>
                    </div>
                </div>
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-slate-500">Success Rate</p>
                            <p class="text-2xl font-bold text-blue-600" id="statRate">-</p>
                        </div>
                        <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center"><i class="fas fa-chart-line text-blue-600 text-xl"></i></div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-4 mb-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Status</label>
                        <select id="filterStatus" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all">
                            <option value="">All Status</option>
                            <option value="sent">Sent</option>
                            <option value="failed">Failed</option>
                            <option value="pending">Pending</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Email Type</label>
                        <select id="filterType" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all">
                            <option value="">All Types</option>
                            <option value="welcome">Welcome / Setup</option>
                            <option value="rent_reminder">Rent Reminders</option>
                            <option value="bill">Bills / Invoices</option>
                            <option value="receipt">Payment Receipts</option>
                            <option value="complaint">Complaints / Notices</option>
                            <option value="maintenance">Maintenance</option>
                            <option value="vacate">Vacate / Termination</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">From Date</label>
                        <input type="date" id="filterDateFrom" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">To Date</label>
                        <input type="date" id="filterDateTo" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Search</label>
                        <input type="text" id="filterSearch" placeholder="Search email, name, subject..." class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 outline-none transition-all">
                    </div>
                </div>
                <div class="flex justify-end gap-3 mt-4">
                    <button onclick="applyFilters()" class="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold rounded-xl shadow-lg shadow-blue-500/30 hover:shadow-blue-500/40 transition-all inline-flex items-center gap-2"><i class="fas fa-filter"></i>Apply Filters</button>
                    <button onclick="resetFilters()" class="px-5 py-2.5 bg-white text-slate-700 border border-slate-200 rounded-xl font-medium hover:bg-slate-50 transition-all">Reset</button>
                </div>
            </div>

            <!-- Email Logs Table -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Recipient</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Subject</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Type</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Sent At</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Property / House</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Error</th>
                            </tr>
                        </thead>
                        <tbody id="logsTableBody" class="divide-y divide-slate-100">
                            <tr><td colspan="7" class="px-6 py-12 text-center text-slate-400"><div class="flex flex-col items-center gap-3"><i class="fas fa-spinner fa-spin text-3xl text-blue-400"></i><span>Loading email logs...</span></div></td></tr>
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-4 border-t border-slate-200 flex items-center justify-between">
                    <div id="paginationInfo" class="text-sm text-slate-500"></div>
                    <div id="paginationButtons" class="flex gap-2"></div>
                </div>
            </div>
        </main>
    </div>
    <script>
        const API = "<?php echo $basePath; ?>/api";
        const token = "<?php echo $token; ?>";
        let currentPage = 1;
        let currentFilters = {status:"", email_type:"", date_from:"", date_to:"", search:""};
        function getToken() {
            const cookies = document.cookie.split(";");
            let cookieToken = "";
            for (let c of cookies) {
                const [k, v] = c.trim().split("=");
                if (k === "rf_token") { cookieToken = decodeURIComponent(v); break; }
            }
            return cookieToken || localStorage.getItem("rf_token") || token;
        }
        function getHeaders() {
            const t = getToken();
            return t ? {"Authorization":"Bearer "+t, "Content-Type":"application/json"} : {"Content-Type":"application/json"};
        }
        async function loadStats() {
            try {
                const res = await fetch(`${API}/email-logs/stats`, { headers: getHeaders() });
                if (res.ok) {
                    const data = await res.json();
                    const s = data.stats || {};
                    document.getElementById("statTotal").textContent = s.total || 0;
                    document.getElementById("statSent").textContent = s.sent || 0;
                    document.getElementById("statFailed").textContent = s.failed || 0;
                    document.getElementById("statRate").textContent = (s.success_rate || 0) + "%";
                }
            } catch (e) { console.warn("Failed to load stats:", e); }
        }
        async function loadLogs(page = 1) {
            currentPage = page;
            const params = new URLSearchParams({page, per_page: 25, ...currentFilters});
            try {
                const res = await fetch(`${API}/email-logs?${params}`, { headers: getHeaders() });
                if (res.ok) {
                    const data = await res.json();
                    renderLogs(data.logs || []);
                    renderPagination(data.pagination || {});
                } else {
                    document.getElementById("logsTableBody").innerHTML = "<tr><td colspan=\"7\" class=\"px-6 py-12 text-center text-red-400\">Failed to load email logs</td></tr>";
                }
            } catch (e) {
                console.error("Failed to load logs:", e);
                document.getElementById("logsTableBody").innerHTML = "<tr><td colspan=\"7\" class=\"px-6 py-12 text-center text-red-400\">Error loading email logs</td></tr>";
            }
        }
        function renderLogs(logs) {
            const tbody = document.getElementById("logsTableBody");
            if (!logs.length) { tbody.innerHTML = "<tr><td colspan=\"7\" class=\"px-6 py-12 text-center text-slate-400\">No email logs found</td></tr>"; return; }
            tbody.innerHTML = logs.map(log => {
                const statusColors = {sent: "bg-emerald-100 text-emerald-700", failed: "bg-red-100 text-red-700", pending: "bg-amber-100 text-amber-700"};
                const statusIcons = {sent: "fa-check", failed: "fa-times", pending: "fa-clock"};
                let type = "Other";
                const subject = (log.subject || "").toLowerCase();
                if (subject.includes("welcome") || subject.includes("setup") || subject.includes("password")) type = "Welcome";
                else if (subject.includes("rent") && subject.includes("reminder")) type = "Rent Reminder";
                else if (subject.includes("payment") && subject.includes("reminder")) type = "Rent Reminder";
                else if (subject.includes("bill") || subject.includes("invoice")) type = "Bill";
                else if (subject.includes("receipt") || subject.includes("payment received")) type = "Receipt";
                else if (subject.includes("complaint") || subject.includes("notice")) type = "Complaint";
                else if (subject.includes("maintenance")) type = "Maintenance";
                else if (subject.includes("vacate") || subject.includes("termination")) type = "Vacate";
                const propInfo = [log.property_name, log.house_unit].filter(Boolean).join(" - ") || "-";
                return `<tr class="hover:bg-slate-50">` +
                    `<td class="px-6 py-4"><div><p class="text-sm font-medium text-slate-900">${log.to_name || "-"}</p><p class="text-xs text-slate-500">${log.to_email}</p></div></td>` +
                    `<td class="px-6 py-4"><p class="text-sm text-slate-700">${log.subject || "-"}</p></td>` +
                    `<td class="px-6 py-4"><span class="px-2.5 py-1 text-xs font-medium rounded-lg bg-slate-100 text-slate-700">${type}</span></td>` +
                    `<td class="px-6 py-4"><span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium rounded-lg ${statusColors[log.status] || "bg-slate-100 text-slate-700"}"><i class="fas ${statusIcons[log.status] || "fa-question"}"></i>${log.status || "unknown"}</span></td>` +
                    `<td class="px-6 py-4 text-sm text-slate-600">${log.sent_at ? new Date(log.sent_at).toLocaleString() : "-"}</td>` +
                    `<td class="px-6 py-4 text-sm text-slate-600">${propInfo}</td>` +
                    `<td class="px-6 py-4 text-sm text-red-600">${log.error || "-"}</td>` +
                `</tr>`;
            }).join("");
        }
        function renderPagination(pagination) {
            const info = document.getElementById("paginationInfo");
            const buttons = document.getElementById("paginationButtons");
            if (!pagination.total) { info.textContent = ""; buttons.innerHTML = ""; return; }
            info.textContent = `Page ${pagination.page} of ${pagination.total_pages} (${pagination.total} total)`;
            let html = "";
            if (pagination.page > 1) {
                html += `<button onclick=\"loadLogs(${pagination.page - 1})\" class=\"px-3 py-1.5 text-sm border border-slate-200 rounded-lg hover:bg-slate-50\">Previous</button>`;
            }
            if (pagination.page < pagination.total_pages) {
                html += `<button onclick=\"loadLogs(${pagination.page + 1})\" class=\"px-3 py-1.5 text-sm border border-slate-200 rounded-lg hover:bg-slate-50\">Next</button>`;
            }
            buttons.innerHTML = html;
        }
        function applyFilters() {
            currentFilters = {
                status: document.getElementById("filterStatus").value,
                email_type: document.getElementById("filterType").value,
                date_from: document.getElementById("filterDateFrom").value,
                date_to: document.getElementById("filterDateTo").value,
                search: document.getElementById("filterSearch").value.trim(),
            };
            loadLogs(1);
        }
        function resetFilters() {
            document.getElementById("filterStatus").value = "";
            document.getElementById("filterType").value = "";
            document.getElementById("filterDateFrom").value = "";
            document.getElementById("filterDateTo").value = "";
            document.getElementById("filterSearch").value = "";
            currentFilters = {status:"", email_type:"", date_from:"", date_to:"", search:""};
            loadLogs(1);
        }
        document.addEventListener("DOMContentLoaded", () => { loadStats(); loadLogs(1); });
    </script>
</body>
</html>
