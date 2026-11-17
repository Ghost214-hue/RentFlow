@extends('layouts.admin')

@section('content')
<div class="max-w-md mx-auto py-8">
  <div class="bg-white rounded-lg shadow p-6">
    <h2 class="text-2xl font-bold mb-6">Security Metrics</h2>

    {{-- Failed Logins --}}
    <div class="mb-6">
      <h3 class="font-bold mb-2">Failed Login Attempts</h3>
      <p class="text-3x font-bold text-red-600">{{ $failedLogins }}</p>
      <p class="text-sm text-gray-500">Rate limiter: admin-login (5 attempts per minute)</p>
    </div>

    {{-- Rate Limiters --}}
    <div class="grid grid-cols-2 gap-4 mb-6">
      @foreach ([
        'login' => 'Login',
        'password-reset' => 'Password Reset',
        'register' => 'Register',
        'setup' => 'Setup',
        'mail' => 'Mail',
        'writes' => 'Writes',
        'reads' => 'Reads',
        'session' => 'Session',
        'api' => 'API',
        'admin-login' => 'Admin Login',
      ] as $key => $label)
        <div class="bg-gray-50 rounded-lg p-3">
          <div class="font-bold">{{ $label }}</div>
          <div class="text-sm text-gray-500">Defined: {{ config(\"rate.limiter.{$key}\", 'not configured') }}</div>
        </div>
      @endforeach
    </div>

    {{-- Active Sessions --}}
    <div>
      <h3 class="font-bold mb-2">Active Admin Sessions</h3>
      <p>View session store for active sessions.</p>
    </div>
  </div>
</div>
@endsection