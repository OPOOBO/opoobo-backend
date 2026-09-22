<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('title', 'OPOOBO') — Mini-App Store</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            brand: { 500: '#FF4500', 600: '#E63E00', 700: '#BD5A00' },
          }
        }
      }
    }
  </script>
  <style>
    body { font-family: 'Inter', system-ui, -apple-system, sans-serif; }
    .dropdown-menu { display: none; }
    .dropdown-menu.show { display: block; }
  </style>
</head>
<body class="bg-gray-50 min-h-screen">
  @php
    $isLoggedIn = session()->has('developer_id');
    $userName = session('developer_name', '');
    $userEmail = session('developer_email', '');
    $loginVia = session('login_via', 'developer');
    $isAdmin = $isLoggedIn && $loginVia === 'admin';
    $isOnAdminPage = request()->is('admin*');
  @endphp

  <nav class="bg-white border-b border-gray-200 sticky top-0 z-40">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between h-14">
        <div class="flex items-center gap-3">
          <a href="{{ $isAdmin ? route('admin.dashboard') : ($isLoggedIn ? route('developer.dashboard') : '/') }}" class="text-brand-500 font-bold text-lg tracking-tight">OPOOBO</a>
          <span class="text-gray-300">/</span>
          <span class="text-gray-600 text-sm font-semibold">@yield('nav-title', 'Portal')</span>
        </div>

        <div class="flex items-center gap-3">
          @if($isLoggedIn)
            @if($isAdmin && !$isOnAdminPage)
              <a href="{{ route('admin.dashboard') }}" class="text-xs bg-brand-50 text-brand-600 px-3 py-1.5 rounded-full font-semibold hover:bg-brand-100 transition">Admin</a>
            @endif
            @if($isAdmin && $isOnAdminPage)
              <a href="{{ route('developer.dashboard') }}" class="text-xs bg-gray-100 text-gray-600 px-3 py-1.5 rounded-full font-semibold hover:bg-gray-200 transition">Developer</a>
            @endif

            <div class="relative" id="profileDropdown">
              <button onclick="toggleDropdown()" class="flex items-center gap-2 hover:bg-gray-50 rounded-lg px-2 py-1.5 transition">
                <div class="w-8 h-8 bg-brand-500/10 rounded-full flex items-center justify-center">
                  <span class="text-brand-500 text-sm font-bold">{{ strtoupper(substr($userName, 0, 1)) }}</span>
                </div>
                <span class="text-sm text-gray-700 font-medium hidden sm:block">{{ $userName }}</span>
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
              </button>
              <div id="dropdownMenu" class="dropdown-menu absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-lg border border-gray-200 py-1 z-50">
                <div class="px-4 py-3 border-b border-gray-100">
                  <p class="text-sm font-semibold text-gray-900">{{ $userName }}</p>
                  <p class="text-xs text-gray-500 truncate">{{ $userEmail }}</p>
                  <span class="text-xs mt-1 inline-block px-2 py-0.5 rounded-full font-medium {{ $isAdmin ? 'bg-brand-50 text-brand-600' : 'bg-blue-50 text-blue-600' }}">
                    {{ $isAdmin ? 'Admin' : 'Developer' }}
                  </span>
                </div>
                <a href="{{ $isAdmin ? route('admin.dashboard') : route('developer.dashboard') }}" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                  Dashboard
                </a>
                <a href="{{ $isAdmin ? route('admin.profile') : route('developer.profile') }}" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                  My Profile
                </a>
                @if($isAdmin)
                  <a href="{{ route('developer.dashboard') }}" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                    Developer Portal
                  </a>
                @endif
                <a href="{{ route('developer.docs') }}" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                  Documentation
                </a>
                <div class="border-t border-gray-100 my-1"></div>
                <form method="POST" action="{{ $isAdmin ? route('admin.logout') : route('developer.logout') }}">
                  @csrf
                  <button class="w-full flex items-center gap-2 px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    Logout
                  </button>
                </form>
              </div>
            </div>
          @else
            <a href="{{ $isOnAdminPage ? route('admin.login') : route('developer.login') }}" class="text-sm text-gray-600 hover:text-gray-900 font-medium">Sign in</a>
          @endif
        </div>
      </div>
    </div>
  </nav>

  {{-- Flash messages --}}
  @if(session('success'))
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
      <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm flex items-center justify-between">
        <span>{{ session('success') }}</span>
        <button onclick="this.parentElement.remove()" class="text-green-500 hover:text-green-700 text-lg leading-none">&times;</button>
      </div>
    </div>
  @endif

  @if(session('error'))
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
      <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm flex items-center justify-between">
        <span>{{ session('error') }}</span>
        <button onclick="this.parentElement.remove()" class="text-red-500 hover:text-red-700 text-lg leading-none">&times;</button>
      </div>
    </div>
  @endif

  @if($errors->any())
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
      <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">
        <ul class="list-disc list-inside">
          @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    </div>
  @endif

  <main class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    @yield('content')
  </main>

  <script>
    function toggleDropdown() {
      document.getElementById('dropdownMenu').classList.toggle('show');
    }
    document.addEventListener('click', function(e) {
      var dropdown = document.getElementById('profileDropdown');
      if (dropdown && !dropdown.contains(e.target)) {
        document.getElementById('dropdownMenu').classList.remove('show');
      }
    });
  </script>
</body>
</html>
