@extends('layouts.admin')

@section('content')
<div class="max-w-md mx-auto py-12">
  <h2 class="text-2xl font-bold mb-4">Two-Factor Authentication Setup</h2>

  @if (session('status'))
    <div class="bg-green-100 border-green-400 text-green-700 px-4 py-3 rounded mb-4">
      {{ session('status') }}
    </div>
  @endif

  @if ($errors->any())
    <div class="bg-red-100 border-red-400 text-red-700 px-4 py-3 rounded mb-4">
      <ul>
        {{ $errors->first('email', '<li>• </li>') }}
      </ul>
    </div>
  @endif

  <form method="POST" action="{{ route('admin.2fa.enroll.store') }}">
    @csrf

    <div class="mb-4">
      <label class="block text-gray-700 text-sm font-bold mb-2" for="qr-code">
        Scan the QR code below with your authenticator app:
      </label>
      <img src="{{ url('/admin/2fa/qr?secret=' . urlencode($admin->two_factor_secret)) }}"
           alt="QR code for Google Authenticator"
           class="border rounded w-full h-64 object-contain">
      <input type="hidden" name="secret" value="{{ $admin->two_factor_secret }}">
    </div>

    <div class="mb-6">
      <label class="block text-gray-700 text-sm font-bold mb-2" for="code">
        Enter the 6-digit code from your authenticator app:
      </label>
      <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight" 
             type="text" name="code" id="code" required>
    </div>

    <div class="flex items-center justify-between">
      <button class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded" type="submit" disabled id="verify-btn">
        Verify Code
      </button>
      <a class="text-blue-600 hover:underline" href="{{ route('admin.dashboard') }}">
        Skip (not recommended)
      </a>
    </div>

    <p class="mt-4 text-sm text-gray-500">
      If you cannot scan the QR code, manually enter this secret:
      <code class="bg-gray-100 p-1 rounded">{{ $admin->two_factor_secret }}</code>
    </p>
  </form>
</div>
@endsection