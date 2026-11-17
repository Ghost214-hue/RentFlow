@extends('layouts.admin')

@section('content')

<div class="max-w-7xl mx-auto py-8">
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-2xl font-bold mb-6">Audit Log</h2>
        
        <div class="mb-4">
            <!-- Filters -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Action</label>
                    <select id="actionFilter" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight">
                        <option value="">All Actions</option>
                        <option value="create">Create</option>
                        <option value="update">Update</option>
                        <option value="delete">Delete</option>
                        <option value="view">View</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Actor</label>
                    <select id="actorFilter" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight">
                        <option value="">All Actors</option>
                        <option value="admin">Admin</option>
                        <option value="owner">Owner</option>
                        <option value="caretaker">Caretaker</option>
                        <option value="renter">Renter</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">From Date</label>
                    <input type="date" id="fromDateFilter" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">To Date</label>
                    <input type="date" id="toDateFilter" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight">
                </div>
            </div>
        </div>
        
        <div class="overflow-x-auto mt-4">
            <table class="min-w-full bg-white rounded-lg shadow">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="py-3 px-6 text-left text-xs font-medium text-gray-500">#</th>
                        <th class="py-3 px-6 text-left text-xs font-medium text-gray-500">Date</th>
                        <th class="py-3 px-6 text-left text-xs font-medium text-gray-500">Actor</th>
                        <th class="py-3 px-6 text-left text-xs font-medium text-gray-500">Action</th>
                        <th class="py-3 px-6 text-left text-xs font-medium text-gray-500">Subject</th>
                        <th class="py-3 px-6 text-left text-xs font-medium text-gray-500">IP</th>
                        <th class="py-3 px-6 text-left text-xs font-medium text-gray-500">Details</th>
                    </tr>
                </thead>
                <tbody id="auditTable">
                    <!-- Rows will be populated by AJAX -->
                </tbody>
            </table>
        </div>
        
        <div class="mt-6 flex justify-between items-center">
            <p class="text-sm text-gray-500">Showing <span id="auditCount">0</span> audit entries</p>
            <div id="auditPagination" class="flex"></div>
        </div>
    </div>
</div>

<script>
    // Initialize DataTable or simple row loading
    let currentPage = 1;
    let rowsPerPage = 25;
    let totalRows = 0;
    
    // Load audit logs
    function loadAuditLogs(page = 1) {
        const actionFilter = $('#actionFilter').val();
        const actorFilter = $('#actorFilter').val();
        const fromDate = $('#fromDateFilter').val();
        const toDate = $('#toDateFilter').val();
        
        $.ajax({
            url: '{{ route('admin.audit.index') }}',
            data: {
                page: page,
                action: actionFilter,
                actor: actorFilter,
                from_date: fromDate,
                to_date: toDate
            },
            success: function(response) {
                $('#auditTable').html(response.html);
                $('#auditCount').text(response.total);
                // Initialize pagination
                // ... (would use DataTable or simple pagination)
            }
        });
    }
    
    // Initial load
    $(document).ready(function() {
        loadAuditLogs();
        
        // Add filter change handlers
        $('#actionFilter, #actorFilter, #fromDateFilter, #toDateFilter').on('change', function() {
            currentPage = 1;
            loadAuditLogs(currentPage);
        });
    });
</script>
</div>
@endsection