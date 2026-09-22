@extends('layouts.app')
@section('title', 'Register')
@section('nav-title', 'Developer Portal')

@section('content')
<div class="max-w-md mx-auto">
  <h1 class="text-2xl font-bold text-gray-900 mb-1">Create Developer Account</h1>
  <p class="text-gray-500 text-sm mb-6">Submit and manage mini-apps on OPOOBO.</p>

  <form method="POST" action="{{ route('developer.register') }}" class="space-y-4">
    @csrf
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
      <input type="text" name="name" value="{{ old('name') }}" required
        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none">
    </div>
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
      <input type="email" name="email" value="{{ old('email') }}" required
        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none">
    </div>
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
      <input type="password" name="password" required
        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none">
    </div>
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
      <input type="password" name="password_confirmation" required
        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none">
    </div>
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Company <span class="text-gray-400">(optional)</span></label>
      <input type="text" name="company" value="{{ old('company') }}"
        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none">
    </div>
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Website <span class="text-gray-400">(optional)</span></label>
      <input type="url" name="website" value="{{ old('website') }}"
        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none">
    </div>
    <button type="submit"
      class="w-full bg-brand-500 text-white font-semibold py-2.5 rounded-lg hover:bg-brand-600 transition text-sm">
      Create Account
    </button>
  </form>

  <p class="text-center text-sm text-gray-500 mt-4">
    Already have an account? <a href="{{ route('developer.login') }}" class="text-brand-500 hover:underline">Sign in</a>
  </p>
</div>
@endsection
