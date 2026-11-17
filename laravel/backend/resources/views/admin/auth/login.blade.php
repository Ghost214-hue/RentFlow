@extends('layouts.admin')

@section('content')

<div class="max-w-md mx-auto py-12">
  <h2 class="text-2xl font-bold mb-4">Admin Login</h2>

  @if (session('error'))
  <div class="bg-red-100 border-red-400 text-red-700 px-4 py-3 rounded mb-4">
    {{ session('error') }}
  </div>
  @endif

  <form method="POST" action="{{ route('admin.login.store') }}">
    @csrf
    <div class="mb-4">
      <label class="block text-gray-700 text-sm font-bold mb-2" for="email">Email</label>
      <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight" type="email" name="email" id="email" required />
    </div>
    <div class="mb-6">
      <label class="block text-gray-700 text-sm font-bold mb-2" for="password">Password</label>
      <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight" type="password" name="password" id="password" required />
    </div>

    <div>
      <button class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded" type="submit">Sign in</button>
    </div>
  </form>
</div>

@endsection