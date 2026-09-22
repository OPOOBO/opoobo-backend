@extends('layouts.app')
@section('title', 'My Profile')
@section('nav-title', 'Developer Portal')

@section('content')
<div class="max-w-lg mx-auto">
  <h1 class="text-2xl font-bold text-gray-900 mb-1">My Profile</h1>
  <p class="text-gray-500 text-sm mb-6">Manage your developer account settings.</p>

  {{-- Stats --}}
  <div class="grid grid-cols-3 gap-3 mb-6">
    <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
      <p class="text-2xl font-bold text-brand-500">{{ $appCount }}</p>
      <p class="text-xs text-gray-500 mt-1">Total Apps</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
      <p class="text-2xl font-bold text-green-500">{{ $liveApps }}</p>
      <p class="text-xs text-gray-500 mt-1">Live</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
      <p class="text-2xl font-bold text-blue-500">{{ number_format($totalInstalls) }}</p>
      <p class="text-xs text-gray-500 mt-1">Installs</p>
    </div>
  </div>

  {{-- Profile Info --}}
  <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
    <h2 class="font-semibold text-gray-900 text-sm mb-4">Account Information</h2>
    <form method="POST" action="{{ route('developer.profile.update') }}" class="space-y-4">
      @csrf
      @method('PUT')
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
        <input type="text" name="name" value="{{ old('name', $developer->name) }}" required
          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none">
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
        <input type="email" value="{{ $developer->email }}" disabled
          class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm bg-gray-50 text-gray-500 cursor-not-allowed">
        <p class="text-xs text-gray-400 mt-1">Email cannot be changed</p>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Company</label>
        <input type="text" name="company" value="{{ old('company', $developer->company) }}"
          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none"
          placeholder="Your company name">
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Website</label>
        <input type="url" name="website" value="{{ old('website', $developer->website) }}"
          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none"
          placeholder="https://yourwebsite.com">
      </div>
      <div class="flex justify-end">
        <button type="submit" class="bg-brand-500 text-white text-sm font-semibold px-5 py-2 rounded-lg hover:bg-brand-600 transition">
          Save Changes
        </button>
      </div>
    </form>
  </div>

  {{-- Change Password --}}
  <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
    <h2 class="font-semibold text-gray-900 text-sm mb-4">Change Password</h2>
    <form method="POST" action="{{ route('developer.password.update') }}" class="space-y-4">
      @csrf
      @method('PUT')
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Current Password</label>
        <input type="password" name="current_password" required
          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none">
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
        <input type="password" name="password" required
          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none">
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
        <input type="password" name="password_confirmation" required
          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none">
      </div>
      <div class="flex justify-end">
        <button type="submit" class="bg-gray-900 text-white text-sm font-semibold px-5 py-2 rounded-lg hover:bg-gray-800 transition">
          Update Password
        </button>
      </div>
    </form>
  </div>

  {{-- Account Details --}}
  <div class="bg-white rounded-xl border border-gray-200 p-6">
    <h2 class="font-semibold text-gray-900 text-sm mb-3">Account Details</h2>
    <div class="space-y-2 text-sm">
      <div class="flex justify-between">
        <span class="text-gray-500">Member since</span>
        <span class="text-gray-900">{{ $developer->created_at->format('M d, Y') }}</span>
      </div>
      <div class="flex justify-between">
        <span class="text-gray-500">Last updated</span>
        <span class="text-gray-900">{{ $developer->updated_at->format('M d, Y') }}</span>
      </div>
    </div>
  </div>
</div>
@endsection
