@extends('layouts.admin')

@section('content')
<div class="max-w-md mx-auto py-8">
  <div class="bg-white rounded-lg shadow p-6">
    <h2 class="text-2xl font-bold mb-6">System Status</h2>

    {{-- App Version --}}
    <div class="mb-4">
      <h3 class="font-bold mb-2">Application Version</h3>
      <p>{{ config('app.version', '1.0.0') }}</p>
    </div>

    {{-- Environment --}}
    <div class="mb-4">
      <h3 class="font-bold mb-2">Environment</h3>
      <p>{{ config('app.env', 'local') }}</p>
    </div>

    {{-- Debug Mode --}}
    <div class="mb-4">
      <h3 class="font-bold mb-2">Debug Mode</h3>
      <p>{{ config('app.debug', false) ? 'Enabled' : 'Disabled' }}</p>
    </div>

    {{-- Session Driver --}}
    <div class="mb-4">
      <h3 class="font-bold mb-2">Session Driver</h3>
      <p>{{ config('session.driver', 'file') }}</p>
    </div>

    {{-- Queue Worker --}}
    <div class="mb-4">
      <h3 class="font-bold mb-2">Queue Status</h3>
      <p>Queue Connection: {{ config('queue.default', 'sync') }}</p>
    </div>

    {{-- Last Scheduler Run --}}
    <div>
      <h3 class="font-bold mb-2">Last Scheduler Run</h3>
      <p>Check logs for last scheduled task execution.</p>
    </div>
  </div>
</div>
@endsection