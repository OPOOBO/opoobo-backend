@extends('layouts.app')
@section('title', 'Admin Login')
@section('nav-title', 'Admin')

@section('content')
<div class="max-w-md mx-auto">
  <div class="text-center mb-8">
    <div class="w-16 h-16 bg-brand-500/10 rounded-2xl flex items-center justify-center mx-auto mb-4">
      <svg class="w-8 h-8 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
    </div>
    <h1 class="text-2xl font-bold text-gray-900 mb-1">Admin Login</h1>
    <p class="text-gray-500 text-sm">Review and manage mini-app submissions.</p>
  </div>

  <form method="POST" action="{{ route('admin.login') }}" class="space-y-4">
    @csrf
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Admin Email</label>
      <input type="email" name="email" value="{{ old('email') }}" required autofocus
        class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none">
    </div>
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
      <input type="password" name="password" required
        class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none">
    </div>
    <button type="submit"
      class="w-full bg-brand-500 text-white font-semibold py-2.5 rounded-lg hover:bg-brand-600 transition text-sm">
      Sign In
    </button>
  </form>

  <div class="mt-6 text-center">
    <p class="text-sm text-gray-500">
      Not an admin? <a href="{{ route('developer.login') }}" class="text-brand-500 hover:underline font-medium">Developer Login</a>
    </p>
    <p class="text-xs text-gray-400 mt-2">Need a developer account? <a href="{{ route('developer.register') }}" class="text-brand-500 hover:underline">Register here</a></p>
  </div>
</div>
@endsection
