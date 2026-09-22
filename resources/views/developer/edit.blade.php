@extends('layouts.app')
@section('title', 'Edit App')
@section('nav-title', 'Developer Portal')

@section('content')
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,1,0" />
<style>
  .icon-card { display: flex; align-items: center; gap: 10px; padding: 8px 12px; border: 1px solid #e5e7eb; border-radius: 10px; background: #fff; cursor: pointer; transition: all .15s; }
  .icon-card:hover { border-color: #FF4500; background: #FFF5F0; }
  .icon-card.selected { border-color: #FF4500; background: #FFF0EB; box-shadow: 0 0 0 2px rgba(255,69,0,.15); }
  .icon-card .material-symbols-rounded { font-size: 28px; color: #FF4500; }
  .icon-card .icon-label { font-size: 12px; font-weight: 600; color: #2C2620; font-family: monospace; }
  .icon-card .icon-desc { font-size: 11px; color: #878078; }
  .tab-btn { padding: 6px 16px; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; border: 1px solid #e5e7eb; background: #fff; color: #666; transition: all .15s; }
  .tab-btn.active { background: #FF4500; color: #fff; border-color: #FF4500; }
  .upload-zone { border: 2px dashed #d1d5db; border-radius: 12px; padding: 24px; text-align: center; cursor: pointer; transition: all .15s; }
  .upload-zone:hover { border-color: #FF4500; background: #FFF5F0; }
  .upload-zone.has-file { border-color: #2D9F6F; background: #F0FFF8; }
  .screenshot-item { position: relative; border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden; }
  .screenshot-item input { width: 100%; border: none; padding: 8px 10px; font-size: 12px; outline: none; }
  .screenshot-item .remove-btn { position: absolute; right: 4px; top: 50%; transform: translateY(-50%); background: #FEE2E2; color: #DC2626; border: none; border-radius: 50%; width: 20px; height: 20px; font-size: 12px; cursor: pointer; display: flex; align-items: center; justify-content: center; }
  .bridge-check { display: flex; align-items: center; gap: 8px; padding: 6px 10px; border: 1px solid #e5e7eb; border-radius: 8px; background: #fff; cursor: pointer; transition: all .15s; font-size: 12px; }
  .bridge-check:hover { border-color: #FF4500; }
  .bridge-check.checked { border-color: #FF4500; background: #FFF0EB; }
  .bridge-check input { accent-color: #FF4500; }
</style>

<div class="max-w-lg mx-auto">
  <h1 class="text-2xl font-bold text-gray-900 mb-1">Edit {{ $app->display_name }}</h1>
  <p class="text-gray-500 text-sm mb-6">Update your app details and version.</p>

  {{-- Review Status --}}
  @if($app->review_status)
    <div class="mb-4 p-3 rounded-lg text-sm
      {{ $app->review_status === 'approved' ? 'bg-green-50 text-green-700 border border-green-200' :
         ($app->review_status === 'rejected' ? 'bg-red-50 text-red-700 border border-red-200' :
         'bg-yellow-50 text-yellow-700 border border-yellow-200') }}">
      @if($app->review_status === 'approved')
        ✓ Approved and live
      @elseif($app->review_status === 'rejected')
        ✗ Rejected
        @if($app->review_notes)
          <p class="mt-1 text-xs opacity-75">Reason: {{ $app->review_notes }}</p>
        @endif
      @else
        Under review
      @endif
    </div>
  @endif

  <form method="POST" action="{{ route('developer.update', $app) }}" enctype="multipart/form-data" class="space-y-4" id="editForm">
    @csrf
    @method('PUT')
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">App Name *</label>
      <input type="text" name="display_name" value="{{ old('display_name', $app->display_name) }}" required maxlength="100"
        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none"
        placeholder="e.g. OPOOBO Pay">
      <p class="text-xs text-gray-400 mt-1">The name users see in the store (max 100 chars)</p>
    </div>
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Description *</label>
      <textarea name="description" required maxlength="500" rows="3"
        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none"
        placeholder="e.g. Send money, pay bills, and buy airtime — all from your OPOOBO wallet.">{{ old('description', $app->description) }}</textarea>
      <p class="text-xs text-gray-400 mt-1">Brief summary shown in the store (max 500 chars)</p>
    </div>
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">App URL *</label>
      <input type="url" name="module_url" value="{{ old('module_url', $app->module_url) }}" required maxlength="500"
        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none"
        placeholder="https://pay.opoobo.com">
      <p class="text-xs text-gray-400 mt-1">The full URL that opens inside OPOOBO's WebView. Must be HTTPS.</p>
    </div>
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Test Mode URL</label>
      <input type="url" name="test_mode_url" value="{{ old('test_mode_url', $app->test_mode_url) }}" maxlength="500"
        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none"
        placeholder="https://staging.pay.opoobo.com">
      <p class="text-xs text-gray-400 mt-1">Optional URL for admin to preview before approving</p>
    </div>
    <div class="grid grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Version *</label>
        <input type="text" name="version" value="{{ old('version', $app->version) }}" required maxlength="20"
          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none"
          placeholder="1.0.0">
        <p class="text-xs text-gray-400 mt-1">Semantic versioning (e.g. 1.0.0, 1.2.3)</p>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
        <input type="text" name="category" value="{{ old('category', $app->category) }}" maxlength="100"
          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none"
          placeholder="e.g. Finance, Transport, Shopping">
        <p class="text-xs text-gray-400 mt-1">Groups your app in the store (free text)</p>
      </div>
    </div>
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Icon *</label>
      <input type="text" name="icon" id="iconInput" value="{{ old('icon', $app->icon) }}" maxlength="100"
        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none"
        placeholder="payments_rounded" readonly>
      <p class="text-xs text-gray-400 mt-1">Pick a Material icon or upload your own image</p>
    </div>

    {{-- Icon Picker --}}
    <div class="border border-gray-200 rounded-xl overflow-hidden">
      <div class="px-4 py-3 bg-gray-50 border-b border-gray-200 flex items-center justify-between">
        <div>
          <p class="text-sm font-semibold text-gray-700">App Icon</p>
          <p class="text-xs text-gray-400 mt-0.5">Pick a built-in icon or upload your own image (any size/ratio)</p>
        </div>
        <div class="flex gap-2">
          <button type="button" class="tab-btn {{ !$app->icon_path ? 'active' : '' }}" onclick="showTab('material')">Material Icons</button>
          <button type="button" class="tab-btn {{ $app->icon_path ? 'active' : '' }}" onclick="showTab('upload')">Upload Image</button>
        </div>
      </div>

      {{-- Material Icons Tab --}}
      <div id="tab-material" class="p-4 {{ $app->icon_path ? 'hidden' : '' }}">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2" id="iconGrid">
          @foreach([
            ['payments_rounded', 'Payments', 'Money / Wallet'],
            ['account_balance_rounded', 'account_balance', 'Bank / Finance'],
            ['savings_rounded', 'savings', 'Savings'],
            ['credit_card_rounded', 'credit_card', 'Cards'],
            ['qr_code_scanner_rounded', 'qr_code_scanner', 'QR / Scan'],
            ['local_shipping_rounded', 'local_shipping', 'Delivery'],
            ['directions_bus_rounded', 'directions_bus', 'Bus'],
            ['directions_car_rounded', 'directions_car', 'Ride / Go'],
            ['flight_rounded', 'flight', 'Flights'],
            ['hotel_rounded', 'hotel', 'Hotels'],
            ['shopping_cart_rounded', 'shopping_cart', 'Shopping'],
            ['storefront_rounded', 'storefront', 'Store / Mall'],
            ['inventory_2_rounded', 'inventory_2', 'Orders'],
            ['receipt_long_rounded', 'receipt_long', 'Bills'],
            ['phone_android_rounded', 'phone_android', 'Airtime / Data'],
            ['wifi_rounded', 'wifi', 'Internet'],
            ['bolt_rounded', 'bolt', 'Electricity'],
            ['water_drop_rounded', 'water_drop', 'Water'],
            ['health_and_safety_rounded', 'health_and_safety', 'Health'],
            ['school_rounded', 'school', 'Education'],
            ['work_outline_rounded', 'work_outline', 'Jobs'],
            ['chat_rounded', 'chat', 'Chat / Social'],
            ['play_circle_outline_rounded', 'play_circle_outline', 'Video / Reels'],
            ['card_giftcard_rounded', 'card_giftcard', 'Rewards'],
            ['account_balance_wallet_rounded', 'account_balance_wallet', 'Wallet'],
            ['lock_rounded', 'lock', 'Security'],
            ['verified_rounded', 'verified', 'Verified'],
            ['star_rounded', 'star', 'Featured'],
            ['apps_rounded', 'apps', 'General'],
            ['widgets_rounded', 'widgets', 'Default App'],
          ] as [$name, $materialName, $desc])
            <div class="icon-card {{ old('icon', $app->icon) == $name && !$app->icon_path ? 'selected' : '' }}"
                 data-icon="{{ $name }}" onclick="selectIcon(this)">
              <span class="material-symbols-rounded">{{ $materialName }}</span>
              <div class="min-w-0">
                <div class="icon-label">{{ $name }}</div>
                <div class="icon-desc">{{ $desc }}</div>
              </div>
            </div>
          @endforeach
        </div>
      </div>

      {{-- Upload Tab --}}
      <div id="tab-upload" class="p-4 {{ $app->icon_path ? '' : 'hidden' }}">
        @if($app->icon_path)
          <div class="upload-zone has-file" id="uploadZone" onclick="document.getElementById('iconFile').click()">
            <input type="file" name="icon_file" id="iconFile" accept="image/png,image/jpeg,image/jpg,image/svg+xml,image/webp" class="hidden"
              onchange="handleFileSelect(this)">
            <div id="uploadPlaceholder" class="hidden">
              <div class="text-3xl mb-2">📁</div>
              <p class="text-sm font-medium text-gray-700">Click to upload an image</p>
              <p class="text-xs text-gray-400 mt-1">PNG, JPG, SVG, or WebP — max 2MB</p>
              <p class="text-xs text-gray-400">Any size and ratio — you decide how it looks</p>
            </div>
            <div id="uploadPreview">
              <img id="previewImg" src="{{ Storage::disk('public')->url($app->icon_path) }}" class="mx-auto rounded-lg mb-2" style="max-height:120px; max-width:120px;">
              <p id="previewName" class="text-xs text-gray-600 font-medium">{{ basename($app->icon_path) }}</p>
              <p class="text-xs text-gray-400 mt-1">Current icon — click to change</p>
            </div>
          </div>
        @else
          <div class="upload-zone" id="uploadZone" onclick="document.getElementById('iconFile').click()">
            <input type="file" name="icon_file" id="iconFile" accept="image/png,image/jpeg,image/jpg,image/svg+xml,image/webp" class="hidden"
              onchange="handleFileSelect(this)">
            <div id="uploadPlaceholder">
              <div class="text-3xl mb-2">📁</div>
              <p class="text-sm font-medium text-gray-700">Click to upload an image</p>
              <p class="text-xs text-gray-400 mt-1">PNG, JPG, SVG, or WebP — max 2MB</p>
              <p class="text-xs text-gray-400">Any size and ratio — you decide how it looks</p>
            </div>
            <div id="uploadPreview" class="hidden">
              <img id="previewImg" class="mx-auto rounded-lg mb-2" style="max-height:120px; max-width:120px;">
              <p id="previewName" class="text-xs text-gray-600 font-medium"></p>
              <p class="text-xs text-gray-400 mt-1">Click to change</p>
            </div>
          </div>
        @endif
      </div>
    </div>

    {{-- Screenshots --}}
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Screenshots</label>
      <p class="text-xs text-gray-400 mb-2">Add up to 5 screenshot URLs (optional)</p>
      <div id="screenshotsContainer" class="space-y-2">
        @if(!empty($app->screenshots) && is_array($app->screenshots) && count($app->screenshots) > 0)
          @foreach($app->screenshots as $screenshot)
            <div class="screenshot-item">
              <input type="url" name="screenshots[]" value="{{ $screenshot }}" placeholder="https://example.com/screenshot.png" maxlength="500">
              <button type="button" class="remove-btn" onclick="removeScreenshot(this)">×</button>
            </div>
          @endforeach
        @else
          <div class="screenshot-item">
            <input type="url" name="screenshots[]" placeholder="https://example.com/screenshot1.png" maxlength="500">
          </div>
        @endif
      </div>
      <button type="button" onclick="addScreenshot()" class="mt-2 text-xs text-brand-500 font-semibold hover:text-brand-600">
        + Add Screenshot
      </button>
    </div>

    {{-- Required Bridge APIs --}}
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Which OPOOBO APIs does your app use?</label>
      <p class="text-xs text-gray-400 mb-2">Select all that apply</p>
      @php $existingApis = old('required_bridge_apis', $app->required_bridge_apis ?? []); @endphp
      <div class="grid grid-cols-2 gap-2">
        @foreach([
          'getUserProfile' => 'User Profile',
          'getTheme' => 'Theme (Light/Dark)',
          'getWalletBalance' => 'Wallet Balance',
          'getSavedAddresses' => 'Saved Addresses',
          'getPaymentMethods' => 'Payment Methods',
          'requestBack' => 'Back Navigation',
          'showToast' => 'Toast Messages',
          'trackEvent' => 'Analytics Events',
        ] as $key => $label)
          <label class="bridge-check {{ in_array($key, $existingApis) ? 'checked' : '' }}">
            <input type="checkbox" name="required_bridge_apis[]" value="{{ $key }}"
              {{ in_array($key, $existingApis) ? 'checked' : '' }}
              onchange="this.parentElement.classList.toggle('checked', this.checked)">
            {{ $label }}
          </label>
        @endforeach
      </div>
    </div>

    <button type="submit"
      class="w-full bg-brand-500 text-white font-semibold py-2.5 rounded-lg hover:bg-brand-600 transition text-sm">
      Save Changes
    </button>
  </form>
</div>

<script>
function selectIcon(el) {
  document.querySelectorAll('.icon-card').forEach(c => c.classList.remove('selected'));
  el.classList.add('selected');
  document.getElementById('iconInput').value = el.dataset.icon;
  document.getElementById('iconFile').value = '';
  document.getElementById('uploadPreview').classList.add('hidden');
  document.getElementById('uploadPlaceholder').classList.remove('hidden');
  document.getElementById('uploadZone').classList.remove('has-file');
}

function showTab(tab) {
  document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
  event.target.classList.add('active');
  document.getElementById('tab-material').classList.toggle('hidden', tab !== 'material');
  document.getElementById('tab-upload').classList.toggle('hidden', tab !== 'upload');

  if (tab === 'material') {
    document.getElementById('iconFile').value = '';
    document.getElementById('uploadPreview').classList.add('hidden');
    document.getElementById('uploadPlaceholder').classList.remove('hidden');
    document.getElementById('uploadZone').classList.remove('has-file');
  }
}

function handleFileSelect(input) {
  if (input.files && input.files[0]) {
    const file = input.files[0];
    document.getElementById('uploadPlaceholder').classList.add('hidden');
    document.getElementById('uploadPreview').classList.remove('hidden');
    document.getElementById('uploadZone').classList.add('has-file');
    document.getElementById('previewName').textContent = file.name;

    const reader = new FileReader();
    reader.onload = (e) => {
      document.getElementById('previewImg').src = e.target.result;
    };
    reader.readAsDataURL(file);

    document.querySelectorAll('.icon-card').forEach(c => c.classList.remove('selected'));
    document.getElementById('iconInput').value = '';
  }
}

function addScreenshot() {
  const container = document.getElementById('screenshotsContainer');
  const count = container.querySelectorAll('.screenshot-item').length;
  if (count >= 5) return;
  const div = document.createElement('div');
  div.className = 'screenshot-item';
  div.innerHTML = '<input type="url" name="screenshots[]" placeholder="https://example.com/screenshot' + (count + 1) + '.png" maxlength="500">' +
    '<button type="button" class="remove-btn" onclick="removeScreenshot(this)">×</button>';
  container.appendChild(div);
}

function removeScreenshot(btn) {
  const container = document.getElementById('screenshotsContainer');
  if (container.querySelectorAll('.screenshot-item').length <= 1) return;
  btn.closest('.screenshot-item').remove();
}
</script>
@endsection
