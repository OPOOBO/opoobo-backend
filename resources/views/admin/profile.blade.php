@extends('layouts.app')
@section('title', 'Admin Profile')
@section('nav-title', 'Admin')

@section('content')
<div class="max-w-lg mx-auto">
  <h1 class="text-2xl font-bold text-gray-900 mb-1">Admin Profile</h1>
  <p class="text-gray-500 text-sm mb-6">Your admin account information.</p>

  {{-- Stats --}}
  <div class="grid grid-cols-2 gap-3 mb-6">
    <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
      <p class="text-2xl font-bold text-brand-500">{{ $appCount }}</p>
      <p class="text-xs text-gray-500 mt-1">Total Apps</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
      <p class="text-2xl font-bold text-blue-500">{{ $totalDevelopers }}</p>
      <p class="text-xs text-gray-500 mt-1">Developers</p>
    </div>
  </div>

  {{-- Account Details --}}
  <div class="bg-white rounded-xl border border-gray-200 p-6">
    <h2 class="font-semibold text-gray-900 text-sm mb-4">Account Information</h2>
    <div class="space-y-3 text-sm">
      <div class="flex justify-between">
        <span class="text-gray-500">Name</span>
        <span class="text-gray-900 font-medium">{{ $developer->name }}</span>
      </div>
      <div class="flex justify-between">
        <span class="text-gray-500">Email</span>
        <span class="text-gray-900">{{ $developer->email }}</span>
      </div>
      @if($developer->company)
        <div class="flex justify-between">
          <span class="text-gray-500">Company</span>
          <span class="text-gray-900">{{ $developer->company }}</span>
        </div>
      @endif
      @if($developer->website)
        <div class="flex justify-between">
          <span class="text-gray-500">Website</span>
          <a href="{{ $developer->website }}" target="_blank" class="text-brand-500 hover:underline">{{ $developer->website }}</a>
        </div>
      @endif
      <div class="flex justify-between">
        <span class="text-gray-500">Role</span>
        <span class="text-xs bg-brand-50 text-brand-600 px-2 py-0.5 rounded-full font-medium">Admin</span>
      </div>
      <div class="flex justify-between">
        <span class="text-gray-500">Member since</span>
        <span class="text-gray-900">{{ $developer->created_at->format('M d, Y') }}</span>
      </div>
    </div>
  </div>
</div>
@endsection
