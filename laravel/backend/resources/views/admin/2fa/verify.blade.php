@extends('layouts.admin')

@section('content')
<div class="max-w-md mx-auto py-12">
  <h2 class="text-2xl font-bold mb-4">Verify Two-Factor Authentication</h2>

  @if (session('status'))
    <div class="bg-green-100 border-green-400 text-green-700 px-4 py-3 rounded mb-4">
      {{ session('status') }}
    </div>
  @endif

  @if ($errors->any())
    <div class="bg-red-100 border-red-400 text-red-700 px-4 py-3 rounded mb-4">
      <ul>
        {{ $errors->first('code', '<li>• </li>') }}
      </ul>
    </div>
  @endif

  <form method="POST" action="{{ route('admin.2fa.verify.store') }}">
    @csrf

    <input type="hidden" name="secret" value="{{ $secret }}">

    <div class="mb-6">
      <label class="block text-gray-700 text-sm font-bold mb-2" for="code">
        Enter the 6-digit code from your authenticator app:
      </label>
      <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight" 
             type="text" name="code" id="code" required>
    </div>

    <div class="flex items-center justify-between">
      <button class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded" type="submit">
        Verify Code
      </button>
    </div>

    <p class="mt-4 text-sm text-gray-500">
      If you cannot generate a code, use a recovery code instead.
    </p>
  </form>
</div>
@endsection