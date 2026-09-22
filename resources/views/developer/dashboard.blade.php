@extends('layouts.app')
@section('title', 'My Apps')
@section('nav-title', 'Developer Portal')

@section('content')
<div class="flex items-center justify-between mb-6">
  <div>
    <h1 class="text-2xl font-bold text-gray-900">My Apps</h1>
    <p class="text-gray-500 text-sm">{{ $apps->count() }} app(s) submitted</p>
  </div>
  <div class="flex gap-2">
    <a href="{{ route('developer.demo') }}"
      class="border border-brand-200 text-brand-600 text-sm font-semibold px-4 py-2 rounded-lg hover:bg-brand-50 transition">
      Demo Tutorial
    </a>
    <a href="{{ route('developer.docs') }}"
      class="border border-gray-200 text-gray-600 text-sm font-semibold px-4 py-2 rounded-lg hover:bg-gray-50 transition">
      Docs
    </a>
    <a href="{{ route('developer.submit') }}"
      class="bg-brand-500 text-white text-sm font-semibold px-4 py-2 rounded-lg hover:bg-brand-600 transition">
      + Submit New App
    </a>
  </div>
</div>

@if($apps->isNotEmpty())
  @php
    $liveCount = $apps->where('is_active', true)->count();
    $pendingCount = $apps->where('is_active', false)->where('review_status', 'pending')->count();
    $totalInstalls = $apps->sum('install_count');
  @endphp
  <div class="grid grid-cols-3 gap-3 mb-6">
    <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
      <p class="text-2xl font-bold text-green-500">{{ $liveCount }}</p>
      <p class="text-xs text-gray-500 mt-1">Live</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
      <p class="text-2xl font-bold text-yellow-500">{{ $pendingCount }}</p>
      <p class="text-xs text-gray-500 mt-1">Pending</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
      <p class="text-2xl font-bold text-blue-500">{{ number_format($totalInstalls) }}</p>
      <p class="text-xs text-gray-500 mt-1">Total Installs</p>
    </div>
  </div>
@endif

@if($apps->isEmpty())
  <div class="bg-white rounded-xl border border-gray-200 p-12 text-center">
    <p class="text-gray-400 text-sm">No apps yet. Submit your first mini-app to get started.</p>
  </div>
@else
  <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
    @foreach($apps as $app)
      <div class="bg-white rounded-xl border border-gray-200 p-5 hover:shadow-md transition">
        <div class="flex items-start justify-between mb-3">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-brand-500/10 rounded-xl flex items-center justify-center">
              <span class="text-brand-500 text-lg">📦</span>
            </div>
            <div>
              <h3 class="font-semibold text-gray-900 text-sm">{{ $app->display_name }}</h3>
              <p class="text-xs text-gray-400">v{{ $app->version }}</p>
            </div>
          </div>
          @if($app->is_active)
            <span class="text-xs bg-green-50 text-green-600 px-2 py-0.5 rounded-full font-medium">Live</span>
          @elseif($app->review_status === 'rejected')
            <span class="text-xs bg-red-50 text-red-600 px-2 py-0.5 rounded-full font-medium">Rejected</span>
          @elseif($app->review_status === 'in_review')
            <span class="text-xs bg-blue-50 text-blue-600 px-2 py-0.5 rounded-full font-medium">In Review</span>
          @else
            <span class="text-xs bg-yellow-50 text-yellow-600 px-2 py-0.5 rounded-full font-medium">Pending</span>
          @endif
        </div>
        <p class="text-gray-500 text-xs mb-3 line-clamp-2">{{ $app->description }}</p>

        {{-- Review Notes --}}
        @if($app->review_notes)
          <div class="bg-blue-50 rounded-lg p-2 mb-3">
            <p class="text-xs text-blue-700"><strong>Review notes:</strong> {{ $app->review_notes }}</p>
          </div>
        @endif

        <div class="flex items-center justify-between text-xs text-gray-400 mb-3">
          <span>{{ $app->category ?? 'Uncategorized' }}</span>
          <span>{{ number_format($app->install_count) }} installs</span>
        </div>

        {{-- Pre-flight Status --}}
        @if($app->last_preflight_status)
          <div class="flex gap-3 text-xs mb-3">
            <span class="{{ $app->ssl_valid ? 'text-green-600' : 'text-red-500' }}">
              SSL {{ $app->ssl_valid ? '✓' : '✗' }}
            </span>
            <span class="{{ $app->url_loads ? 'text-green-600' : 'text-red-500' }}">
              Loads {{ $app->url_loads ? '✓' : '✗' }}
            </span>
          </div>
        @endif

        <div class="flex gap-2">
          <a href="{{ route('developer.edit', $app) }}"
            class="flex-1 text-center text-xs border border-gray-200 text-gray-600 py-1.5 rounded-lg hover:bg-gray-50 transition">
            Edit
          </a>
          @if($app->module_url)
            <a href="{{ $app->module_url }}" target="_blank"
              class="flex-1 text-center text-xs border border-gray-200 text-gray-600 py-1.5 rounded-lg hover:bg-gray-50 transition">
              Visit
            </a>
          @endif
          @if(!$app->is_active)
            <form method="POST" action="{{ route('developer.withdraw', $app) }}" class="flex-1">
              @csrf
              @method('DELETE')
              <button class="w-full text-center text-xs border border-red-200 text-red-500 py-1.5 rounded-lg hover:bg-red-50 transition"
                onclick="return confirm('Withdraw this submission?')">
                Withdraw
              </button>
            </form>
          @endif
        </div>
      </div>
    @endforeach
  </div>
@endif
@endsection
