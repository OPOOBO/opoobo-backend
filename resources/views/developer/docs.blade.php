@extends('layouts.app')
@section('title', 'Developer Docs')
@section('nav-title', 'Developer Portal')

@section('content')
<style>
  .doc-nav a { display: block; padding: 6px 12px; border-radius: 8px; font-size: 13px; color: #666; transition: all .15s; text-decoration: none; }
  .doc-nav a:hover { background: #FFF5F0; color: #FF4500; }
  .doc-nav a.active { background: #FFF0EB; color: #FF4500; font-weight: 600; }
  .code-block { background: #1e1e2e; color: #cdd6f4; border-radius: 12px; padding: 16px 20px; font-family: 'JetBrains Mono', 'Fira Code', monospace; font-size: 13px; line-height: 1.6; overflow-x: auto; white-space: pre; }
  .code-block .kw { color: #cba6f7; }
  .code-block .str { color: #a6e3a1; }
  .code-block .fn { color: #89b4fa; }
  .code-block .cmt { color: #6c7086; }
  .code-block .num { color: #fab387; }
  .code-block .prop { color: #89dceb; }
  .api-card { border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden; }
  .api-card-header { padding: 12px 16px; background: #f9fafb; border-bottom: 1px solid #e5e7eb; display: flex; align-items: center; gap: 8px; }
  .api-card-body { padding: 16px; }
  .method-badge { display: inline-block; padding: 2px 8px; border-radius: 6px; font-size: 10px; font-weight: 700; font-family: monospace; }
  .method-badge.sync { background: #dbeafe; color: #1d4ed8; }
  .method-badge.async { background: #fef3c7; color: #92400e; }
  .method-badge.event { background: #ede9fe; color: #6d28d9; }
  .method-badge.action { background: #d1fae5; color: #065f46; }
  .step-num { width: 28px; height: 28px; border-radius: 50%; background: #FF4500; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 700; flex-shrink: 0; }
</style>

<div class="flex gap-8">
  {{-- Sidebar Navigation --}}
  <aside class="hidden lg:block w-48 shrink-0">
    <div class="doc-nav sticky top-8">
      <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2 px-3">Getting Started</p>
      <a href="#overview" class="active">Overview</a>
      <a href="#publishing">Publishing Process</a>
      <a href="#guidelines">App Guidelines</a>

      <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mt-4 mb-2 px-3">SDK Reference</p>
      <a href="#sdk-setup">SDK Setup</a>
      <a href="#identity">Identity</a>
      <a href="#sync-apis">Sync APIs</a>
      <a href="#async-apis">Async APIs</a>
      <a href="#events">Events</a>
      <a href="#actions">Actions</a>

      <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mt-4 mb-2 px-3">Reference</p>
      <a href="#ui-rules">UI Rules</a>
      <a href="#security">Security</a>
      <a href="#faq">FAQ</a>
    </div>
  </aside>

  {{-- Main Content --}}
  <div class="flex-1 min-w-0 max-w-3xl">

    {{-- Overview --}}
    <section id="overview" class="mb-12">
      <h1 class="text-3xl font-bold text-gray-900 mb-2">OPOOBO Mini-App SDK</h1>
      <p class="text-gray-500 text-sm mb-6">Build apps that run inside the OPOOBO super app with access to user data, payments, and services.</p>

      <div class="bg-gradient-to-r from-brand-500 to-brand-600 rounded-2xl p-5 text-white mb-6 flex flex-col sm:flex-row sm:items-center gap-4">
        <div class="flex-1">
          <h3 class="font-bold text-base mb-1">New here? Start with the demo tutorial</h3>
          <p class="text-sm text-white/85">Follow along and build a working "Hello Wallet" mini-app in ~20 minutes — then submit it.</p>
        </div>
        <a href="{{ route('developer.demo') }}" class="shrink-0 bg-white text-brand-600 text-sm font-bold px-5 py-2.5 rounded-lg hover:bg-brand-50 transition text-center">
          Start the Tutorial →
        </a>
      </div>

      <div class="bg-gradient-to-r from-brand-500 to-brand-600 rounded-2xl p-6 text-white mb-6">
        <h3 class="font-bold text-lg mb-2">What is a Mini-App?</h3>
        <p class="text-sm text-white/90 leading-relaxed">
          A mini-app is a web application that runs inside OPOOBO's WebView container. When a user opens your app, OPOOBO injects a JavaScript SDK (<code class="bg-white/20 px-1.5 py-0.5 rounded">window.opoobo</code>) that gives your app read-only access to the user's profile, wallet, addresses, and more — all without requiring separate authentication.
        </p>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white border border-gray-200 rounded-xl p-4">
          <div class="text-2xl mb-2">🌐</div>
          <h4 class="font-semibold text-gray-900 text-sm">HTTPS Required</h4>
          <p class="text-xs text-gray-500 mt-1">Your app must be served over HTTPS. HTTP is not allowed.</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-4">
          <div class="text-2xl mb-2">🔑</div>
          <h4 class="font-semibold text-gray-900 text-sm">Auto-Authenticated</h4>
          <p class="text-xs text-gray-500 mt-1">Users are already logged in. No sign-up or login screen needed.</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-4">
          <div class="text-2xl mb-2">📖</div>
          <h4 class="font-semibold text-gray-900 text-sm">Read-Only Access</h4>
          <p class="text-xs text-gray-500 mt-1">Read user data via the SDK. No write access to OPOOBO APIs.</p>
        </div>
      </div>
    </section>

    {{-- Publishing Process --}}
    <section id="publishing" class="mb-12">
      <h2 class="text-2xl font-bold text-gray-900 mb-4">Publishing Process</h2>
      <p class="text-gray-500 text-sm mb-6">Follow these steps to get your mini-app live on the OPOOBO Store.</p>

      <div class="space-y-4">
        <div class="flex gap-4">
          <div class="step-num">1</div>
          <div>
            <h4 class="font-semibold text-gray-900 text-sm">Register as a Developer</h4>
            <p class="text-xs text-gray-500 mt-1">Create an account at <a href="/developer/register" class="text-brand-500 hover:underline">one.opoobo.com/developer/register</a>. You need a name, email, and password.</p>
          </div>
        </div>

        <div class="flex gap-4">
          <div class="step-num">2</div>
          <div>
            <h4 class="font-semibold text-gray-900 text-sm">Build Your Web App</h4>
            <p class="text-xs text-gray-500 mt-1">Create a responsive web app that works in a mobile WebView. Follow the <a href="#guidelines" class="text-brand-500 hover:underline">App Guidelines</a> below.</p>
          </div>
        </div>

        <div class="flex gap-4">
          <div class="step-num">3</div>
          <div>
            <h4 class="font-semibold text-gray-900 text-sm">Integrate the SDK</h4>
            <p class="text-xs text-gray-500 mt-1">Add the OPOOBO SDK to your app. Listen for <code class="bg-gray-100 px-1 rounded text-[11px]">opoobo:ready</code> and use <code class="bg-gray-100 px-1 rounded text-[11px]">window.opoobo</code> to access user data.</p>
          </div>
        </div>

        <div class="flex gap-4">
          <div class="step-num">4</div>
          <div>
            <h4 class="font-semibold text-gray-900 text-sm">Submit for Review</h4>
            <p class="text-xs text-gray-500 mt-1">Go to <a href="/developer/submit" class="text-brand-500 hover:underline">Submit App</a>. Provide your app URL, description, icon, screenshots, and which SDK APIs you use.</p>
          </div>
        </div>

        <div class="flex gap-4">
          <div class="step-num">5</div>
          <div>
            <h4 class="font-semibold text-gray-900 text-sm">Admin Review</h4>
            <p class="text-xs text-gray-500 mt-1">Our team runs automated pre-flight checks (SSL, load time) and manually tests your app. You'll see the status in your dashboard.</p>
          </div>
        </div>

        <div class="flex gap-4">
          <div class="step-num">6</div>
          <div>
            <h4 class="font-semibold text-gray-900 text-sm">Go Live</h4>
            <p class="text-xs text-gray-500 mt-1">Once approved, your app appears in the OPOOBO Store and Services screen. Users can open it directly from the app.</p>
          </div>
        </div>
      </div>
    </section>

    {{-- App Guidelines --}}
    <section id="guidelines" class="mb-12">
      <h2 class="text-2xl font-bold text-gray-900 mb-4">App Guidelines</h2>

      <div class="space-y-4">
        <div class="bg-white border border-gray-200 rounded-xl p-5">
          <h4 class="font-semibold text-gray-900 text-sm mb-2">✅ Do</h4>
          <ul class="text-xs text-gray-600 space-y-1.5 list-disc list-inside">
            <li>Use responsive design — your app runs on mobile screens (360px–414px wide)</li>
            <li>Listen for the <code class="bg-gray-100 px-1 rounded">opoobo:ready</code> event before accessing user data</li>
            <li>Respect the user's theme preference (light/dark) via <code class="bg-gray-100 px-1 rounded">getTheme()</code></li>
            <li>Add <code class="bg-gray-100 px-1 rounded">padding-top: 56px</code> to your body to avoid overlap with the header</li>
            <li>Handle back navigation via <code class="bg-gray-100 px-1 rounded">requestBack()</code> or browser history</li>
            <li>Test your app on slow network connections</li>
            <li>Use the test mode URL field for staging environments</li>
          </ul>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl p-5">
          <h4 class="font-semibold text-gray-900 text-sm mb-2">❌ Don't</h4>
          <ul class="text-xs text-gray-600 space-y-1.5 list-disc list-inside">
            <li>Don't store the access token in <code class="bg-gray-100 px-1 rounded">localStorage</code> or send it to third-party servers</li>
            <li>Don't open external links — use the SDK's copy/share actions instead</li>
            <li>Don't require users to sign up or log in — they're already authenticated</li>
            <li>Don't overlap the OPOOBO header bar with your own navigation</li>
            <li>Don't use popups or alert dialogs that block the WebView</li>
            <li>Don't navigate to URLs outside your approved domain</li>
          </ul>
        </div>
      </div>
    </section>

    {{-- SDK Setup --}}
    <section id="sdk-setup" class="mb-12">
      <h2 class="text-2xl font-bold text-gray-900 mb-4">SDK Setup</h2>
      <p class="text-gray-500 text-sm mb-4">The SDK is automatically injected into your app when it loads inside OPOOBO. No scripts to add — just listen for the ready event.</p>

      <div class="code-block"><span class="cmt">// Wait for the SDK to be ready</span>
<span class="fn">window</span>.<span class="fn">addEventListener</span>(<span class="str">'opoobo:ready'</span>, <span class="kw">function</span>(<span class="prop">event</span>) {
  <span class="kw">const</span> <span class="prop">moduleName</span> = event.detail.moduleName;
  console.<span class="fn">log</span>(<span class="str">'OPOOBO SDK ready for:'</span>, moduleName);

  <span class="cmt">// Now you can safely access user data</span>
  <span class="kw">const</span> <span class="prop">profile</span> = <span class="fn">window.opoobo</span>.<span class="fn">getUserProfile</span>();
  console.<span class="fn">log</span>(<span class="str">'Hello'</span>, profile.name);
});

<span class="cmt">// Or check if the SDK is already loaded</span>
<span class="kw">if</span> (<span class="fn">window.opoobo</span> && <span class="fn">window.opoobo</span>._bridgeReady) {
  <span class="cmt">// SDK is ready, safe to call APIs</span>
}</div>
    </section>

    {{-- Identity --}}
    <section id="identity" class="mb-12">
      <h2 class="text-2xl font-bold text-gray-900 mb-4">Identity</h2>
      <p class="text-gray-500 text-sm mb-4">These properties are available immediately after the SDK loads.</p>

      <div class="api-card mb-4">
        <div class="api-card-header">
          <span class="method-badge sync">sync</span>
          <code class="text-sm font-mono text-gray-800">window.opoobo.accessToken</code>
        </div>
        <div class="api-card-body">
          <p class="text-xs text-gray-500">The Keycloak JWT access token. Use this to authenticate API calls to your own backend.</p>
          <div class="code-block mt-3"><span class="kw">const</span> <span class="prop">token</span> = <span class="fn">window.opoobo</span>.accessToken;
<span class="cmt">// "eyJhbGciOiJSUzI1NiIs..."</span></div>
        </div>
      </div>

      <div class="api-card mb-4">
        <div class="api-card-header">
          <span class="method-badge sync">sync</span>
          <code class="text-sm font-mono text-gray-800">window.opoobo.moduleName</code>
        </div>
        <div class="api-card-body">
          <p class="text-xs text-gray-500">Your module's slug name (e.g. <code>"my_finance_app"</code>).</p>
        </div>
      </div>

      <div class="api-card mb-4">
        <div class="api-card-header">
          <span class="method-badge sync">sync</span>
          <code class="text-sm font-mono text-gray-800">window.opoobo.moduleVersion</code>
        </div>
        <div class="api-card-body">
          <p class="text-xs text-gray-500">Your module's current version (e.g. <code>"1.0.0"</code>).</p>
        </div>
      </div>
    </section>

    {{-- Sync APIs --}}
    <section id="sync-apis" class="mb-12">
      <h2 class="text-2xl font-bold text-gray-900 mb-4">Sync APIs</h2>
      <p class="text-gray-500 text-sm mb-4">These return cached data immediately. No network calls.</p>

      <div class="api-card mb-4">
        <div class="api-card-header">
          <span class="method-badge sync">sync</span>
          <code class="text-sm font-mono text-gray-800">getUserProfile()</code>
        </div>
        <div class="api-card-body">
          <p class="text-xs text-gray-500 mb-3">Returns the authenticated user's profile.</p>
          <div class="code-block"><span class="kw">const</span> <span class="prop">profile</span> = <span class="fn">window.opoobo</span>.<span class="fn">getUserProfile</span>();
<span class="cmt">// → {</span>
<span class="cmt">//     sub: "a1b2c3d4-...",</span>
<span class="cmt">//     name: "Adebayo O.",</span>
<span class="cmt">//     email: "bayo@email.com",</span>
<span class="cmt">//     phone: "+2348012345678",</span>
<span class="cmt">//     avatarUrl: "https://...",</span>
<span class="cmt">//     emailVerified: true</span>
<span class="cmt">//   }</span></div>
        </div>
      </div>

      <div class="api-card mb-4">
        <div class="api-card-header">
          <span class="method-badge sync">sync</span>
          <code class="text-sm font-mono text-gray-800">getTheme()</code>
        </div>
        <div class="api-card-body">
          <p class="text-xs text-gray-500 mb-3">Returns the current theme. Use this to style your app for light or dark mode.</p>
          <div class="code-block"><span class="kw">const</span> <span class="prop">theme</span> = <span class="fn">window.opoobo</span>.<span class="fn">getTheme</span>();
<span class="cmt">// → "light" or "dark"</span>

<span class="kw">if</span> (theme === <span class="str">'dark'</span>) {
  document.body.classList.<span class="fn">add</span>(<span class="str">'dark-mode'</span>);
}</div>
        </div>
      </div>

      <div class="api-card mb-4">
        <div class="api-card-header">
          <span class="method-badge sync">sync</span>
          <code class="text-sm font-mono text-gray-800">getModuleInfo()</code>
        </div>
        <div class="api-card-body">
          <p class="text-xs text-gray-500 mb-3">Returns your module's metadata.</p>
          <div class="code-block"><span class="kw">const</span> <span class="prop">info</span> = <span class="fn">window.opoobo</span>.<span class="fn">getModuleInfo</span>();
<span class="cmt">// → { name: "my_app", version: "1.2.0" }</span></div>
        </div>
      </div>

      <div class="api-card mb-4">
        <div class="api-card-header">
          <span class="method-badge sync">sync</span>
          <code class="text-sm font-mono text-gray-800">getPlatform()</code>
        </div>
        <div class="api-card-body">
          <p class="text-xs text-gray-500 mb-3">Returns device/platform info.</p>
          <div class="code-block"><span class="kw">const</span> <span class="prop">platform</span> = <span class="fn">window.opoobo</span>.<span class="fn">getPlatform</span>();
<span class="cmt">// → { os: "android", appVersion: "1.0.0", locale: "en-NG" }</span></div>
        </div>
      </div>
    </section>

    {{-- Async APIs --}}
    <section id="async-apis" class="mb-12">
      <h2 class="text-2xl font-bold text-gray-900 mb-4">Async APIs</h2>
      <p class="text-gray-500 text-sm mb-4">These return Promises. They fetch data from OPOOBO's backend.</p>

      <div class="api-card mb-4">
        <div class="api-card-header">
          <span class="method-badge async">async</span>
          <code class="text-sm font-mono text-gray-800">getWalletBalance()</code>
        </div>
        <div class="api-card-body">
          <p class="text-xs text-gray-500 mb-3">Returns the user's OPOOBO wallet balance.</p>
          <div class="code-block"><span class="kw">try</span> {
  <span class="kw">const</span> <span class="prop">balance</span> = <span class="kw">await</span> <span class="fn">window.opoobo</span>.<span class="fn">getWalletBalance</span>();
  console.<span class="fn">log</span>(<span class="str">'Balance:'</span>, balance.currency, balance.amount);
} <span class="kw">catch</span> (err) {
  console.<span class="fn">error</span>(<span class="str">'Failed to get balance:'</span>, err);
}</div>
        </div>
      </div>

      <div class="api-card mb-4">
        <div class="api-card-header">
          <span class="method-badge async">async</span>
          <code class="text-sm font-mono text-gray-800">getSavedAddresses()</code>
        </div>
        <div class="api-card-body">
          <p class="text-xs text-gray-500 mb-3">Returns the user's saved addresses.</p>
          <div class="code-block"><span class="kw">const</span> <span class="prop">addresses</span> = <span class="kw">await</span> <span class="fn">window.opoobo</span>.<span class="fn">getSavedAddresses</span>();
<span class="cmt">// → [</span>
<span class="cmt">//     { id: 1, label: "Home", address: "12 Lekki Phase 1...", lat: 6.44, lng: 3.45 },</span>
<span class="cmt">//     { id: 2, label: "Office", address: "5 Victoria Island...", lat: 6.43, lng: 3.42 }</span>
<span class="cmt">//   ]</span></div>
        </div>
      </div>

      <div class="api-card mb-4">
        <div class="api-card-header">
          <span class="method-badge async">async</span>
          <code class="text-sm font-mono text-gray-800">getPaymentMethods()</code>
        </div>
        <div class="api-card-body">
          <p class="text-xs text-gray-500 mb-3">Returns the user's saved payment methods.</p>
          <div class="code-block"><span class="kw">const</span> <span class="prop">methods</span> = <span class="kw">await</span> <span class="fn">window.opoobo</span>.<span class="fn">getPaymentMethods</span>();
<span class="cmt">// → [</span>
<span class="cmt">//     { id: 1, type: "card", provider: "visa", lastFour: "4242", isDefault: true },</span>
<span class="cmt">//     { id: 2, type: "bank", provider: "gtbank", lastFour: "0123", isDefault: false }</span>
<span class="cmt">//   ]</span></div>
        </div>
      </div>
    </section>

    {{-- Events --}}
    <section id="events" class="mb-12">
      <h2 class="text-2xl font-bold text-gray-900 mb-4">Events</h2>
      <p class="text-gray-500 text-sm mb-4">Listen for these events on the <code class="bg-gray-100 px-1 rounded text-xs">window</code> object.</p>

      <div class="api-card mb-4">
        <div class="api-card-header">
          <span class="method-badge event">event</span>
          <code class="text-sm font-mono text-gray-800">opoobo:ready</code>
        </div>
        <div class="api-card-body">
          <p class="text-xs text-gray-500 mb-3">Fired when the SDK bridge is fully initialized and all APIs are available.</p>
          <div class="code-block">window.<span class="fn">addEventListener</span>(<span class="str">'opoobo:ready'</span>, (<span class="prop">e</span>) => {
  console.<span class="fn">log</span>(<span class="str">'SDK ready for:'</span>, e.detail.moduleName);
});</div>
        </div>
      </div>

      <div class="api-card mb-4">
        <div class="api-card-header">
          <span class="method-badge event">event</span>
          <code class="text-sm font-mono text-gray-800">opoobo:tokenReady</code>
        </div>
        <div class="api-card-body">
          <p class="text-xs text-gray-500 mb-3">Fired when the access token is injected. Backwards-compatible with older integrations.</p>
          <div class="code-block">window.<span class="fn">addEventListener</span>(<span class="str">'opoobo:tokenReady'</span>, (<span class="prop">e</span>) => {
  console.<span class="fn">log</span>(<span class="str">'Token:'</span>, e.detail.accessToken);
});</div>
        </div>
      </div>

      <div class="api-card mb-4">
        <div class="api-card-header">
          <span class="method-badge event">event</span>
          <code class="text-sm font-mono text-gray-800">opoobo:themeChanged</code>
        </div>
        <div class="api-card-body">
          <p class="text-xs text-gray-500 mb-3">Fired when the user toggles between light and dark mode while your app is open.</p>
          <div class="code-block">window.<span class="fn">addEventListener</span>(<span class="str">'opoobo:themeChanged'</span>, (<span class="prop">e</span>) => {
  <span class="kw">const</span> <span class="prop">theme</span> = e.detail.theme;
  <span class="fn">applyTheme</span>(theme);
});</div>
        </div>
      </div>

      <div class="api-card mb-4">
        <div class="api-card-header">
          <span class="method-badge event">event</span>
          <code class="text-sm font-mono text-gray-800">opoobo:appPause</code> / <code class="text-sm font-mono text-gray-800">opoobo:appResume</code>
        </div>
        <div class="api-card-body">
          <p class="text-xs text-gray-500 mb-3">Fired when the app goes to background or returns to foreground.</p>
          <div class="code-block">window.<span class="fn">addEventListener</span>(<span class="str">'opoobo:appPause'</span>, () => {
  <span class="fn">pauseVideo</span>();
  <span class="fn">saveState</span>();
});

window.<span class="fn">addEventListener</span>(<span class="str">'opoobo:appResume'</span>, () => {
  <span class="fn">resumeVideo</span>();
});</div>
        </div>
      </div>
    </section>

    {{-- Actions --}}
    <section id="actions" class="mb-12">
      <h2 class="text-2xl font-bold text-gray-900 mb-4">Actions</h2>
      <p class="text-gray-500 text-sm mb-4">Call these to interact with the OPOOBO shell.</p>

      <div class="api-card mb-4">
        <div class="api-card-header">
          <span class="method-badge action">action</span>
          <code class="text-sm font-mono text-gray-800">requestBack()</code>
        </div>
        <div class="api-card-body">
          <p class="text-xs text-gray-500 mb-3">Tells the OPOOBO shell to close your app and return to the previous screen.</p>
          <div class="code-block"><span class="cmt">// User taps your custom back button</span>
<span class="fn">document</span>.<span class="fn">getElementById</span>(<span class="str">'back-btn'</span>).<span class="fn">addEventListener</span>(<span class="str">'click'</span>, () => {
  <span class="fn">window.opoobo</span>.<span class="fn">requestBack</span>();
});</div>
        </div>
      </div>

      <div class="api-card mb-4">
        <div class="api-card-header">
          <span class="method-badge action">action</span>
          <code class="text-sm font-mono text-gray-800">showToast(message)</code>
        </div>
        <div class="api-card-body">
          <p class="text-xs text-gray-500 mb-3">Shows a native OPOOBO toast notification at the bottom of the screen.</p>
          <div class="code-block"><span class="fn">window.opoobo</span>.<span class="fn">showToast</span>(<span class="str">'Payment successful!'</span>);</div>
        </div>
      </div>

      <div class="api-card mb-4">
        <div class="api-card-header">
          <span class="method-badge action">action</span>
          <code class="text-sm font-mono text-gray-800">trackEvent(name, props?)</code>
        </div>
        <div class="api-card-body">
          <p class="text-xs text-gray-500 mb-3">Sends an analytics event to OPOOBO's backend. Optional properties object.</p>
          <div class="code-block"><span class="fn">window.opoobo</span>.<span class="fn">trackEvent</span>(<span class="str">'item_purchased'</span>, {
  itemId: <span class="str">'abc123'</span>,
  amount: <span class="num">5000</span>,
  currency: <span class="str">'NGN'</span>
});</div>
        </div>
      </div>
    </section>

    {{-- UI Rules --}}
    <section id="ui-rules" class="mb-12">
      <h2 class="text-2xl font-bold text-gray-900 mb-4">UI Rules</h2>

      <div class="bg-white border border-gray-200 rounded-xl p-5 mb-4">
        <h4 class="font-semibold text-gray-900 text-sm mb-3">Layout Rules</h4>
        <ul class="text-xs text-gray-600 space-y-2 list-disc list-inside">
          <li><strong>Header:</strong> OPOOBO renders a 56px header bar at the top. Your app starts below it.</li>
          <li><strong>Safe area:</strong> Add <code class="bg-gray-100 px-1 rounded">padding-top: 56px</code> to your <code class="bg-gray-100 px-1 rounded">&lt;body&gt;</code> or main container</li>
          <li><strong>Screen width:</strong> 360px to 414px (most Android phones), up to 428px on larger devices</li>
          <li><strong>Screen height:</strong> Varies — use <code class="bg-gray-100 px-1 rounded">min-height: 100vh</code> for full-screen layouts</li>
          <li><strong>No fixed headers:</strong> Don't add your own fixed/sticky header that overlaps the OPOOBO shell</li>
          <li><strong>Scrolling:</strong> Your app scrolls within the WebView. Use <code class="bg-gray-100 px-1 rounded">overflow-y: auto</code> for scrollable areas.</li>
        </ul>
      </div>

      <div class="bg-white border border-gray-200 rounded-xl p-5">
        <h4 class="font-semibold text-gray-900 text-sm mb-3">Recommended CSS Reset</h4>
        <div class="code-block"><span class="kw">body</span> {
  <span class="prop">margin</span>: <span class="num">0</span>;
  <span class="prop">padding</span>: <span class="num">56px 0 0 0</span>;  <span class="cmt">/* Clear OPOOBO header */</span>
  <span class="prop">font-family</span>: -apple-system, BlinkMacSystemFont, <span class="str">'Segoe UI'</span>, sans-serif;
  <span class="prop">-webkit-font-smoothing</span>: antialiased;
  <span class="prop">overscroll-behavior</span>: none;  <span class="cmt">/* Prevent pull-to-refresh */</span>
}

<span class="cmt">/* Dark mode support */</span>
<span class="kw">body</span>.dark-mode {
  <span class="prop">background</span>: <span class="str">#0f0f0f</span>;
  <span class="prop">color</span>: <span class="str">#ffffff</span>;
}</div>
      </div>
    </section>

    {{-- Security --}}
    <section id="security" class="mb-12">
      <h2 class="text-2xl font-bold text-gray-900 mb-4">Security</h2>

      <div class="space-y-4">
        <div class="bg-red-50 border border-red-200 rounded-xl p-5">
          <h4 class="font-semibold text-red-800 text-sm mb-2">🔒 Access Token</h4>
          <ul class="text-xs text-red-700 space-y-1.5 list-disc list-inside">
            <li>The <code class="bg-red-100 px-1 rounded">accessToken</code> is a JWT that grants read-only access to the user's OPOOBO data</li>
            <li><strong>Never</strong> store it in <code class="bg-red-100 px-1 rounded">localStorage</code>, <code class="bg-red-100 px-1 rounded">sessionStorage</code>, or cookies</li>
            <li><strong>Never</strong> send it to third-party servers — only send it to your own backend</li>
            <li>Use it as a Bearer token in API calls to your backend: <code class="bg-red-100 px-1 rounded">Authorization: Bearer &lt;token&gt;</code></li>
            <li>The token is re-injected on every page load — treat it as ephemeral</li>
          </ul>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl p-5">
          <h4 class="font-semibold text-gray-900 text-sm mb-2">🌐 Domain Restrictions</h4>
          <ul class="text-xs text-gray-600 space-y-1.5 list-disc list-inside">
            <li>Your app can only navigate within your approved domain (set during submission)</li>
            <li>Attempts to navigate outside your domain are blocked by the OPOOBO shell</li>
            <li>External links (tel:, sms:, mailto:) are handled by the shell, not your app</li>
          </ul>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl p-5">
          <h4 class="font-semibold text-gray-900 text-sm mb-2">✅ HTTPS Enforcement</h4>
          <ul class="text-xs text-gray-600 space-y-1.5 list-disc list-inside">
            <li>Your app <strong>must</strong> be served over HTTPS</li>
            <li>HTTP URLs are rejected during submission</li>
            <li>The automated pre-flight check verifies your SSL certificate</li>
          </ul>
        </div>
      </div>
    </section>

    {{-- FAQ --}}
    <section id="faq" class="mb-12">
      <h2 class="text-2xl font-bold text-gray-900 mb-4">FAQ</h2>

      <div class="space-y-4">
        <div class="bg-white border border-gray-200 rounded-xl p-5">
          <h4 class="font-semibold text-gray-900 text-sm mb-2">Can I use React, Vue, or Angular?</h4>
          <p class="text-xs text-gray-500">Yes. Any web framework that runs in a browser works. Your app is loaded in a standard WebView — no framework restrictions.</p>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl p-5">
          <h4 class="font-semibold text-gray-900 text-sm mb-2">Can I call OPOOBO's backend APIs directly?</h4>
          <p class="text-xs text-gray-500">Use the SDK APIs instead. Direct API access is not supported. The SDK provides read-only access to user data through the bridge.</p>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl p-5">
          <h4 class="font-semibold text-gray-900 text-sm mb-2">Can I send push notifications?</h4>
          <p class="text-xs text-gray-500">Not currently. Push notifications are handled by OPOOBO. In the future, we may add a notification API to the SDK.</p>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl p-5">
          <h4 class="font-semibold text-gray-900 text-sm mb-2">How do I handle payments?</h4>
          <p class="text-xs text-gray-500">You can use the access token to authenticate with your own payment backend. OPOOBO does not provide a payment API for mini-apps at this time.</p>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl p-5">
          <h4 class="font-semibold text-gray-900 text-sm mb-2">How long does review take?</h4>
          <p class="text-xs text-gray-500">Typically 1-3 business days. Automated pre-flight checks run instantly. Manual review is done by our team.</p>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl p-5">
          <h4 class="font-semibold text-gray-900 text-sm mb-2">Can I update my app after it's approved?</h4>
          <p class="text-xs text-gray-500">Yes. Go to your dashboard, click Edit on your app, make changes, and save. Updates go through a lighter review process.</p>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl p-5">
          <h4 class="font-semibold text-gray-900 text-sm mb-2">What if my app is rejected?</h4>
          <p class="text-xs text-gray-500">You'll see the rejection reason in your dashboard under "Review Notes". Fix the issues, update your app, and it will be reconsidered automatically.</p>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl p-5">
          <h4 class="font-semibold text-gray-900 text-sm mb-2">Can I test my app before submitting?</h4>
          <p class="text-xs text-gray-500">Yes. Add a test mode URL in your submission. Our admins will use it to preview your app before going live. You can also test locally by adding <code class="bg-gray-100 px-1 rounded">window.opoobo</code> to your browser's console.</p>
        </div>
      </div>
    </section>

    {{-- Support --}}
    <section class="mb-12">
      <div class="bg-gradient-to-r from-gray-900 to-gray-800 rounded-2xl p-6 text-white">
        <h3 class="font-bold text-lg mb-2">Need Help?</h3>
        <p class="text-sm text-white/70 mb-4">Contact our developer support team for integration help or questions about the SDK.</p>
        <div class="flex gap-3">
          <a href="mailto:developers@opoobo.com" class="bg-white/10 hover:bg-white/20 text-white text-sm font-semibold px-4 py-2 rounded-lg transition">
            developers@opoobo.com
          </a>
          <a href="/developer/submit" class="bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold px-4 py-2 rounded-lg transition">
            Submit Your App →
          </a>
        </div>
      </div>
    </section>

  </div>
</div>

<script>
// Smooth scroll for sidebar links
document.querySelectorAll('.doc-nav a').forEach(a => {
  a.addEventListener('click', function(e) {
    const href = this.getAttribute('href');
    if (href.startsWith('#')) {
      e.preventDefault();
      const target = document.querySelector(href);
      if (target) {
        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        document.querySelectorAll('.doc-nav a').forEach(l => l.classList.remove('active'));
        this.classList.add('active');
      }
    }
  });
});

// Highlight active section on scroll
const sections = document.querySelectorAll('section[id]');
const navLinks = document.querySelectorAll('.doc-nav a');
window.addEventListener('scroll', () => {
  let current = '';
  sections.forEach(section => {
    const top = section.offsetTop - 100;
    if (window.scrollY >= top) current = section.getAttribute('id');
  });
  navLinks.forEach(link => {
    link.classList.remove('active');
    if (link.getAttribute('href') === '#' + current) link.classList.add('active');
  });
});
</script>
@endsection
