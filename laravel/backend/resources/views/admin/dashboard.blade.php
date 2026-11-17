@extends('layouts.admin')

@section('content')
<div class="max-w-7xl mx-auto py-8">
  <div class="bg-white rounded-lg shadow p-6">
    <h2 class="text-2xl font-bold mb-6">Platform Dashboard</h2>

    {{-- Compute platform totals using CrossTenant to avoid owner-scope issues --}}
    {{-- We use CrossTenant::run() with a reason for cross-owner queries --}}

    {{-- Owner stats --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <div class="bg-blue-50 rounded-lg p-4">
        <div class="text-3x font-bold text-blue-600">{{ $ownerCount }}</div>
        <div class="text-sm text-gray-500">Total Owners</div>
      </div>
      <div class="bg-green-50 rounded-lg p-4">
        <div class="text-3x font-bold text-green-600">{{ $activeOwnerCount }}</div>
        <div class="text-sm text-gray-500">Active Owners</div>
      </div>
      <div class="bg-yellow-50 rounded-lg p-4">
        <div class="text-3x font-bold text-yellow-600">{{ $suspendedOwnerCount }}</div>
        <div class="text-sm text-gray-500">Suspended Owners</div>
      </div>
      <div class="bg-purple-50 rounded-lg p-4">
        <div class="text-3x font-bold text-purple-600">{{ $newSignupCount }}</div>
        <div class="text-sm text-gray-500">New This Month</div>
      </div>
    </div>

    {{-- Financial stats --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <div class="bg-gray-50 rounded-lg p-4">
        <div class="text-2xl font-bold text-gray-900">${{ number_format($billedTotal, 2) }}</div>
        <div class="text-sm text-gray-500">Billed This Month</div>
      </div>
      <div class="bg-gray-50 rounded-lg p-4">
        <div class="text-2xl font-bold text-gray-900">${{ number_format($collectedTotal, 2) }}</div>
        <div class="text-sm text-gray-500">Collected This Month</div>
      </div>
      <div class="bg-gray-50 rounded-lg p-4">
        <div class="text-2xl font-bold text-gray-900">${{ number_format($outstandingTotal, 2) }}</div>
        <div class="text-sm text-gray-500">Outstanding</div>
      </div>
      <div class="bg-gray-50 rounded-lg p-4">
        <div class="text-sm text-gray-500 collection-rate">{{ number_format($collectionRate * 100, 1) }%</div>
        <div class="text-3x font-bold text-gray-900">Collection Rate</div>
      </div>
    </div>

    {{-- Email failures --}}
    <div class="bg-gray-50 rounded-lg p-4 mt-8">
      <h3 class="font-bold mb-4">Email Failures (Last 24h)</h3>
      <p class="text-gray-500">Failed: {{ $emailFailuresLast24h }} | Successful: {{ $emailSuccessLast24h }}</p>
    </div>
  </div>
</div>
@endsection