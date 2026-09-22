@extends('layouts.app')
@section('title', 'Admin Dashboard')
@section('nav-title', 'Admin')

@section('content')
<div class="flex items-center justify-between mb-6">
  <div>
    <h1 class="text-2xl font-bold text-gray-900">Store Admin</h1>
    <p class="text-gray-500 text-sm">{{ $pending->count() }} pending review · {{ $active->count() }} live · {{ $totalDevelopers }} developers</p>
  </div>
</div>

{{-- Pending Review --}}
<h2 class="text-lg font-bold text-gray-900 mb-3">Pending Review</h2>
@if($pending->isEmpty())
  <div class="bg-white rounded-xl border border-gray-200 p-8 text-center mb-8">
    <p class="text-gray-400 text-sm">No pending submissions.</p>
  </div>
@else
  <div class="space-y-4 mb-8">
    @foreach($pending as $app)
      <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        {{-- Header --}}
        <div class="p-5">
          <div class="flex flex-col sm:flex-row sm:items-start gap-4">
            <div class="flex-1 min-w-0">
              <div class="flex items-center gap-2 mb-1">
                <h3 class="font-semibold text-gray-900 text-sm">{{ $app->display_name }}</h3>
                <span class="text-xs bg-yellow-50 text-yellow-600 px-2 py-0.5 rounded-full font-medium">Pending</span>
                @if($app->is_featured)
                  <span class="text-xs bg-orange-50 text-orange-600 px-2 py-0.5 rounded-full font-medium">★ Featured</span>
                @endif
              </div>
              <p class="text-xs text-gray-500 mb-1">{{ $app->description }}</p>
              <div class="flex flex-wrap gap-3 text-xs text-gray-400">
                <span>by {{ $app->developer_name ?? 'Unknown' }}</span>
                <span>{{ $app->category ?? 'No category' }}</span>
                <span>v{{ $app->version }}</span>
                @if($app->module_url)
                  <a href="{{ $app->module_url }}" target="_blank" class="text-brand-500 hover:underline">Visit URL →</a>
                @endif
                @if($app->test_mode_url)
                  <a href="{{ $app->test_mode_url }}" target="_blank" class="text-blue-500 hover:underline">Test URL →</a>
                @endif
              </div>
            </div>
          </div>
        </div>

        {{-- Pre-flight Results --}}
        <div class="px-5 pb-3">
          <div class="bg-gray-50 rounded-lg p-3">
            <div class="flex items-center justify-between mb-2">
              <span class="text-xs font-semibold text-gray-600">Pre-flight Check</span>
              @if($app->last_preflight_status)
                <span class="text-xs px-2 py-0.5 rounded-full font-medium {{ $app->last_preflight_status === 'passed' ? 'bg-green-50 text-green-600' : 'bg-red-50 text-red-600' }}">
                  {{ ucfirst($app->last_preflight_status) }}
                </span>
              @else
                <span class="text-xs bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full font-medium">Not run</span>
              @endif
            </div>
            <div class="flex gap-4 text-xs">
              <span class="{{ $app->ssl_valid ? 'text-green-600' : 'text-red-500' }}">
                SSL {{ $app->ssl_valid ? '✓' : '✗' }}
              </span>
              <span class="{{ $app->url_loads ? 'text-green-600' : 'text-red-500' }}">
                Loads {{ $app->url_loads ? '✓' : '✗' }}
              </span>
              @if($app->admin_tested)
                <span class="text-green-600">Tested ✓</span>
              @endif
            </div>
          </div>
        </div>

        {{-- Review Notes --}}
        @if($app->review_notes)
          <div class="px-5 pb-3">
            <div class="bg-blue-50 rounded-lg p-3">
              <span class="text-xs font-semibold text-blue-700">Review Notes:</span>
              <p class="text-xs text-blue-600 mt-1">{{ $app->review_notes }}</p>
            </div>
          </div>
        @endif

        {{-- Screenshots --}}
        @if(!empty($app->screenshots) && is_array($app->screenshots) && count($app->screenshots) > 0)
          <div class="px-5 pb-3">
            <div class="flex gap-2 overflow-x-auto">
              @foreach($app->screenshots as $screenshot)
                <img src="{{ $screenshot }}" class="h-24 rounded-lg border border-gray-200 object-cover" alt="Screenshot">
              @endforeach
            </div>
          </div>
        @endif

        {{-- Actions --}}
        <div class="px-5 pb-4 flex flex-wrap gap-2">
          <form method="POST" action="{{ route('admin.preflight', $app) }}">
            @csrf
            <button class="bg-blue-50 text-blue-600 text-xs font-semibold px-3 py-1.5 rounded-lg hover:bg-blue-100 transition">
              Run Pre-flight
            </button>
          </form>
          @if($app->module_url)
            <button onclick="openTestPanel('{{ $app->module_url }}', '{{ $app->display_name }}')"
              class="bg-purple-50 text-purple-600 text-xs font-semibold px-3 py-1.5 rounded-lg hover:bg-purple-100 transition">
              Test App
            </button>
          @endif
          @if(!$app->admin_tested)
            <form method="POST" action="{{ route('admin.markTested', $app) }}">
              @csrf
              <button class="bg-gray-100 text-gray-600 text-xs font-semibold px-3 py-1.5 rounded-lg hover:bg-gray-200 transition">
                Mark Tested
              </button>
            </form>
          @endif
          <form method="POST" action="{{ route('admin.approve', $app) }}">
            @csrf
            <button class="bg-green-500 text-white text-xs font-semibold px-4 py-1.5 rounded-lg hover:bg-green-600 transition">
              Approve
            </button>
          </form>
          <form method="POST" action="{{ route('admin.reject', $app) }}">
            @csrf
            <button class="bg-gray-200 text-gray-600 text-xs font-semibold px-3 py-1.5 rounded-lg hover:bg-gray-300 transition">
              Reject
            </button>
          </form>
          <form method="POST" action="{{ route('admin.delete', $app) }}">
            @csrf
            @method('DELETE')
            <button class="bg-red-50 text-red-500 text-xs font-semibold px-3 py-1.5 rounded-lg hover:bg-red-100 transition"
              onclick="return confirm('Delete this app permanently?')">
              Delete
            </button>
          </form>
        </div>

        {{-- Review Notes Editor --}}
        <div class="px-5 pb-4 border-t border-gray-100 pt-3">
          <form method="POST" action="{{ route('admin.updateReviewNotes', $app) }}" class="flex gap-2">
            @csrf
            <input type="text" name="review_notes" value="{{ $app->review_notes }}"
              placeholder="Add review notes..."
              class="flex-1 text-xs border border-gray-200 rounded-lg px-3 py-1.5 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none">
            <button class="text-xs bg-gray-100 text-gray-600 px-3 py-1.5 rounded-lg hover:bg-gray-200 transition">
              Save
            </button>
          </form>
        </div>
      </div>
    @endforeach
  </div>
@endif

{{-- Active / Live --}}
<h2 class="text-lg font-bold text-gray-900 mb-3">Live Apps</h2>
@if($active->isEmpty())
  <div class="bg-white rounded-xl border border-gray-200 p-8 text-center">
    <p class="text-gray-400 text-sm">No live apps.</p>
  </div>
@else
  <form method="POST" action="{{ route('admin.reorder') }}" id="reorderForm">
    @csrf
    <input type="hidden" name="order[]" id="orderInput" value="">
    <div class="space-y-2" id="sortableList">
      @foreach($active as $app)
        <div class="bg-white rounded-xl border border-gray-200 p-4 flex items-center gap-3" data-id="{{ $app->id }}">
          <div class="flex flex-col gap-0.5 shrink-0">
            <button type="button" onclick="moveUp(this)" class="text-gray-400 hover:text-gray-700 leading-none" title="Move up">▲</button>
            <button type="button" onclick="moveDown(this)" class="text-gray-400 hover:text-gray-700 leading-none" title="Move down">▼</button>
          </div>
          <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2">
              <span class="text-xs text-gray-400 font-mono">#{{ $app->sort_order }}</span>
              <h3 class="font-semibold text-gray-900 text-sm">{{ $app->display_name }}</h3>
              <span class="text-xs bg-green-50 text-green-600 px-2 py-0.5 rounded-full font-medium">Live</span>
              @if($app->is_featured)
                <span class="text-xs bg-orange-50 text-orange-600 px-2 py-0.5 rounded-full font-medium">★ Featured</span>
              @endif
            </div>
            <p class="text-xs text-gray-500 mt-0.5">{{ $app->description }}</p>
          </div>
          <div class="flex gap-2 shrink-0">
            <form method="POST" action="{{ route('admin.feature', $app) }}">
              @csrf
              <button class="text-xs font-semibold px-3 py-1.5 rounded-lg transition {{ $app->is_featured ? 'bg-orange-50 text-orange-600 hover:bg-orange-100' : 'bg-gray-200 text-gray-600 hover:bg-gray-300' }}">
                {{ $app->is_featured ? '★ Unfeature' : '☆ Feature' }}
              </button>
            </form>
            <form method="POST" action="{{ route('admin.reject', $app) }}">
              @csrf
              <button class="bg-gray-200 text-gray-600 text-xs font-semibold px-3 py-1.5 rounded-lg hover:bg-gray-300 transition">
                Deactivate
              </button>
            </form>
            <form method="POST" action="{{ route('admin.delete', $app) }}">
              @csrf
              @method('DELETE')
              <button class="bg-red-50 text-red-500 text-xs font-semibold px-3 py-1.5 rounded-lg hover:bg-red-100 transition"
                onclick="return confirm('Delete this app permanently?')">
                Delete
              </button>
            </form>
          </div>
        </div>
      @endforeach
    </div>
    <button type="submit" id="saveOrderBtn"
      class="mt-4 bg-brand-500 text-white text-sm font-semibold px-6 py-2.5 rounded-lg hover:bg-brand-600 transition hidden">
      Save Order
    </button>
  </form>
@endif

{{-- Test Panel Modal --}}
<div id="testPanel" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center p-4">
  <div class="bg-white rounded-2xl w-full max-w-2xl overflow-hidden shadow-2xl">
    <div class="flex items-center justify-between px-5 py-3 border-b border-gray-200">
      <h3 id="testPanelTitle" class="font-semibold text-gray-900 text-sm">Testing App</h3>
      <button onclick="closeTestPanel()" class="text-gray-400 hover:text-gray-600">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
    </div>
    <div class="p-4">
      <p class="text-xs text-gray-500 mb-3">This opens the app in an embedded WebView with your admin session. You can interact with it as a user would.</p>
      <div id="testPanelFrame" class="border border-gray-200 rounded-xl overflow-hidden" style="height: 500px;">
        <p class="text-center text-gray-400 text-sm py-20">Loading...</p>
      </div>
    </div>
    <div class="px-5 py-3 border-t border-gray-200 flex justify-end gap-2">
      <button onclick="closeTestPanel()" class="text-xs bg-gray-100 text-gray-600 px-4 py-2 rounded-lg hover:bg-gray-200 transition">
        Close
      </button>
    </div>
  </div>
</div>

<script>
function moveUp(btn) {
  const item = btn.closest('[data-id]');
  const prev = item.previousElementSibling;
  if (prev) {
    item.parentNode.insertBefore(item, prev);
    markDirty();
  }
}
function moveDown(btn) {
  const item = btn.closest('[data-id]');
  const next = item.nextElementSibling;
  if (next) {
    item.parentNode.insertBefore(next, item);
    markDirty();
  }
}
function markDirty() {
  document.getElementById('saveOrderBtn').classList.remove('hidden');
  const items = document.querySelectorAll('#sortableList [data-id]');
  const ids = [];
  items.forEach((el, i) => {
    ids.push(el.dataset.id);
    const rank = el.querySelector('.font-mono');
    if (rank) rank.textContent = '#' + i;
  });
  document.getElementById('orderInput').value = ids.join(',');
}
markDirty();
document.getElementById('reorderForm').addEventListener('submit', function() {
  const items = document.querySelectorAll('#sortableList [data-id]');
  const ids = [];
  items.forEach(el => ids.push(el.dataset.id));
  document.getElementById('orderInput').value = ids.join(',');
});

// Test Panel
function openTestPanel(url, name) {
  document.getElementById('testPanelTitle').textContent = 'Testing: ' + name;
  document.getElementById('testPanelFrame').innerHTML =
    '<iframe src="' + url + '" style="width:100%;height:100%;border:none;" allow="clipboard-read; clipboard-write"></iframe>';
  const panel = document.getElementById('testPanel');
  panel.classList.remove('hidden');
  panel.classList.add('flex');
}
function closeTestPanel() {
  const panel = document.getElementById('testPanel');
  panel.classList.add('hidden');
  panel.classList.remove('flex');
  document.getElementById('testPanelFrame').innerHTML = '<p class="text-center text-gray-400 text-sm py-20">Loading...</p>';
}
</script>
@endsection
