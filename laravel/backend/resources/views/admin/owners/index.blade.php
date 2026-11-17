@extends('layouts.admin')

@section('content')
<div class="max-w-7xl mx-auto py-8">
  <div class="bg-white rounded-lg shadow p-6">
    <h2 class="text-2xl font-bold mb-6">Owner Management</h2>

    {{-- Filters --}}
    <div class="mb-6">
      <select class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight" id="statusFilter">
        <option value="">All Owners</option>
        <option value="active">Active</option>
        <option value="suspended">Suspended</option>
      </select>
    </div>

    {{-- Owners Table --}}
    <div class="overflow-x-auto">
      <table class="min-w-full bg-white rounded-lg shadow">
        <thead>
          <tr class="bg-gray-100">
            <th class="py-3 px-6 text-left text-xs font-medium text-gray-500">#</th>
            <th class="py-3 px-6 text-left text-xs font-medium text-gray-500">Name</th>
            <th class="py-3 px-6 text-left text-xs font-medium text-gray-500">Email</th>
            <th class="py-3 px-6 text-left text-xs font-medium text-gray-500">Role</th>
            <th class="py-3 px-6 text-left text-xs font-medium text-gray-500">Status</th>
            <th class="py-3 px-6 text-left text-xs font-medium text-gray-500">Actions</th>
          </tr>
        </thead>
        <tbody id="ownersTable">
          @foreach ($owners as $owner)
            <tr class="border-b last:border-none">
              <td class="py-3 px-6">{{ $loop->iteration }}</td>
              <td class="py-3 px-6">{{ $owner->name }}</td>
              <td class="py-3 px-6">{{ $owner->email }}</td>
              <td class="py-3 px-6">
                @if ($owner->isSuperAdmin())
                  super_admin
                @elseif ($owner->admin_role === 'support')
                  support
                @else
                  auditor
                @endif
              </td>
              <td class="py-3 px-6">
                @if ($owner->isSuspended())
                  Suspended
                @else
                  Active
                @endif
              </td>
              <td class="py-3 px-6">
                <form method="POST" action="{{ route('admin.owners.suspend', $owner->id) }}" onsubmit="return confirm('Suspend this owner?');">
                  @csrf @method('POST')
                  <button class="text-red-600 hover:text-red-800 text-sm">Suspend</button>
                </form>
                <form method="POST" action="{{ route('admin.owners.activate', $owner->id) }}" onsubmit="return confirm('Activate this owner?');">
                  @csrf @method('POST')
                  <button class="text-green-600 hover:text-green-800 text-sm">Activate</button>
                </form>
                <form method="POST" action="{{ route('admin.owners.reset', $owner->id) }}" onsubmit="return confirm('Send reset email?');">
                  @csrf @method('POST')
                  <button class="text-blue-600 hover:text-blue-800 text-sm">Reset</button>
                </form>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>
  // Filter owners by status when selector changes
  document.getElementById('statusFilter').addEventListener('change', function() {
    const status = this.value;
    const rows = document.querySelectorAll('#ownersTable tr');
    
    rows.forEach((row, index) => {
      if (index === 0) return; // Skip header
      const statusCell = row.cells[4]; // Status column
      if (status === '' || statusCell.textContent === status) {
        row.style.display = '';
      } else {
        row.style.display = 'none';
      }
    });
  });
</script>
@endsection