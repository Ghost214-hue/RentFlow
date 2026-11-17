@extends('layouts.admin')

@section('content')
<div class="max-w-2xl mx-auto py-8">
  <div class="bg-white rounded-lg shadow p-6">
    <h2 class="text-2xl font-bold mb-6">Owner Details: {{ $owner->name }}</h2>

    <dl class="grid grid-cols-2 gap-6">
      <dt class="font-bold">Email</dt>
      <dd>{{ $owner->email }}</dd>

      <dt class="font-bold">Role</dt>
      <dd>
        @if ($owner->isSuperAdmin())
          super_admin
        @elseif ($owner->admin_role === 'support')
          support
        @else
          auditor
        @endif
      </dd>

      <dt class="font-bold">Status</dt>
      <dd>
        @if ($owner->isSuspended())
          Suspended
        @else
          Active
        @endif
      </dd>

      <dt class="font-bold">Created At</dt>
      <dd>{{ $owner->created_at->format('M d, Y') }}</dd>
    </dl>

    {{-- Show associated properties --}}
    <div class="mt-8">
      <h3 class="font-bold mb-4">Properties (<span id="propertyCount">{{ count($owner->properties) }}</span>)</h3>
      <div class="grid grid-cols-2 gap-4">
        @foreach ($owner->properties as $property)
          <div class="bg-blue-50 rounded-lg p-4">
            <h4 class="font-bold">{{ $property->name }}</h4>
            <p class="text-sm text-gray-500">Units: {{ $property->units->count() }}</p>
          </div>
        @endforeach
        @if (count($owner->properties) === 0)
          <p class="text-gray-500">No properties assigned</p>
        @endif
      </div>
    </div>
  </div>
</div>
@endsection