@extends('layouts.app')
@section('title', 'Login')
@section('nav-title', 'Developer Portal')

@section('content')
<div class="max-w-md mx-auto">
  <div class="text-center mb-8">
    <div class="w-16 h-16 bg-brand-500/10 rounded-2xl flex items-center justify-center mx-auto mb-4">
      <svg class="w-8 h-8 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
    </div>
    <h1 class="text-2xl font-bold text-gray-900 mb-1">Developer Login</h1>
    <p class="text-gray-500 text-sm">Manage your mini-apps on OPOOBO.</p>
  </div>

  <form method="POST" action="{{ route('developer.login') }}" class="space-y-4">
    @csrf
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
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
      Don't have an account? <a href="{{ route('developer.register') }}" class="text-brand-500 hover:underline font-medium">Register</a>
    </p>
    <p class="text-xs text-gray-400 mt-2">Are you an admin? <a href="{{ route('admin.login') }}" class="text-brand-500 hover:underline">Admin Login</a></p>
  </div>
</div>
@endsection
