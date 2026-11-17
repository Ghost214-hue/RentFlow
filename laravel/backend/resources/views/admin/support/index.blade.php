@extends('layouts.admin')

@section('content')
<div class="max-w-7xl mx-auto py-8">
  <div class="bg-white rounded-lg shadow p-6">
    <h2 class="text-2xl font-bold mb-6">Support Access Grants</h2>

    {{-- Create Grant Form --}}
    <div class="mb-6">
      <h3 class="font-bold mb-4">Grant Support Access</h3>
      <form method="POST" action="{{ route('admin.support.store') }}">
        @csrf

        <div class="grid grid-cols-2 gap-4 mb-4">
          <div>
            <label class="block text-gray-700 text-sm font-bold mb-2" for="owner_id">Owner</label>
            <select class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight" name="owner_id" id="owner_id" required>
              <option value="">Select Owner</option>
              @foreach ($owners as $o)
                <option value="{{ $o->id }}">{{ $o->name }}</option>
              @endforeach
            </select>
          </div>
          <div>
            <label class="block text-gray-700 text-sm font-bold mb-2" for="admin_id">Admin</label>
            <select class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight" name="admin_id" id="admin_id" required>
              <option value="">Select Admin</option>
              @foreach ($admins as $a)
                <option value="{{ $a->id }}">{{ $a->name }}</option>
              @endforeach
            </select>
          </div>
        </div>

        <div>
          <label class="block text-gray-700 text-sm font-bold mb-2" for="scope">Scope</label>
          <select class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight" name="scope" id="scope" required>
            <option value="">Select Scope</option>
            <option value="names">Names</option>
            <option value="phones">Phones</option>
            <option value="id_numbers">ID Numbers</option>
            <option value="payment_lines">Payment Lines</option>
          </select>
        </div>

        <div>
          <label class="block text-gray-700 text-sm font-bold mb-2" for="reason">Reason</label>
          <textarea class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight" name="reason" id="reason" rows="3" required></textarea>
        </div>

        <div>
          <label class="block text-gray-700 text-sm font-bold mb-2" for="expires_at">Expires After (days)</label>
          <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight" type="number" name="expires_at" id="expires_at" placeholder="0 = never expires">
        </div>

        <button class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded" type="submit">
          Grant Access
        </button>
      </form>
    </div>

    {{-- Existing Grants --}}
    <div class="overflow-x-auto">
      <table class="min-w-full bg-white rounded-lg shadow">
        <thead>
          <tr class="bg-gray-100">
            <th class="py-3 px-6 text-left text-xs font-medium text-gray-500">#</th>
            <th class="py-3 px-6 text-left text-xs font-medium text-gray-500">Owner</th>
            <th class="py-3 px-6 text-left text-xs font-medium text-gray-500">Admin</th>
            <th class="py-3 px-6 text-left text-xs font-medium text-gray-500">Scope</th>
            <th class="py-3 px-6 text-left text-xs font-medium text-gray-500">Reason</th>
            <th class="py-3 px-6 text-left text-xs font-medium text-gray-500">Expires</th>
            <th class="py-3 px-6 text-left text-xs font-medium text-gray-500">Actions</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($grants as $grant)
            <tr class="border-b last:border-none">
              <td class="py-3 px-6">{{ $loop->iteration }}</td>
              <td class="py-3 px-6">{{ $grant->owner->name }}</td>
              <td class="py-3 px-6">{{ $grant->admin->name }}</td>
              <td class="py-3 px-6">{{ $grant->scope }}</td>
              <td class="py-3 px-6">{{ $grant->reason }}</td>
              <td class="py-3 px-6">
                @if ($grant->expires_at)
                  {{ $grant->expires_at->format('M d, Y') }}
                @else
                  Never
                @endif
              </td>
              <td class="py-3 px-6">
                <form method="POST" action="{{ route('admin.support.destroy', $grant->id) }}" onsubmit="return confirm('Revoke this grant?');">
                  @csrf @method('DELETE')
                  <button class="text-red-600 hover:text-red-800 text-sm">Revoke</button>
                </form>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection