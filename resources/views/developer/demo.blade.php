@extends('layouts.app')
@section('title', 'Demo Tutorial')
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
  .step-num { width: 28px; height: 28px; border-radius: 50%; background: #FF4500; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 700; flex-shrink: 0; }
  .check li { margin-bottom: 6px; }
</style>

<div class="flex gap-8">
  {{-- Sidebar Navigation --}}
  <aside class="hidden lg:block w-48 shrink-0">
    <div class="doc-nav sticky top-8">
      <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2 px-3">Getting Started</p>
      <a href="#overview" class="active">Overview</a>
      <a href="#build">What You'll Build</a>

      <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mt-4 mb-2 px-3">Tutorial</p>
      <a href="#step1">1. Scaffold the File</a>
      <a href="#step2">2. Preview in Browser</a>
      <a href="#step3">3. Add Theming</a>
      <a href="#step4">4. Read Wallet Balance</a>
      <a href="#step5">5. Add Actions</a>
      <a href="#step6">6. Host over HTTPS</a>
      <a href="#step7">7. Submit for Review</a>

      <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mt-4 mb-2 px-3">Reference</p>
      <a href="#mistakes">Common Mistakes</a>
      <a href="#next">Next Steps</a>
    </div>
  </aside>

  {{-- Main Content --}}
  <div class="flex-1 min-w-0 max-w-3xl">

    {{-- Overview --}}
    <section id="overview" class="mb-12">
      <h1 class="text-3xl font-bold text-gray-900 mb-2">Build Your First Mini-App</h1>
      <p class="text-gray-500 text-sm mb-6">A follow-along tutorial. In about 20 minutes you will build, preview, and submit a working demo mini-app — no frameworks, no build tools, just one <code class="bg-gray-100 px-1 rounded text-xs">index.html</code> file.</p>

      <div class="bg-white border border-gray-200 rounded-xl p-5 mb-4">
        <h4 class="font-semibold text-gray-900 text-sm mb-3">What you need before starting</h4>
        <ul class="text-xs text-gray-600 space-y-2 list-disc list-inside check">
          <li>A developer account (<a href="{{ route('developer.register') }}" class="text-brand-500 hover:underline">register here</a> if you don't have one)</li>
          <li>A text editor (VS Code, Notepad, anything)</li>
          <li>A way to serve the file over HTTPS later (covered in Step 6 — free options included)</li>
          <li>No SDK download needed — OPOOBO injects <code class="bg-gray-100 px-1 rounded">window.opoobo</code> into your app automatically</li>
        </ul>
      </div>
    </section>

    {{-- What You'll Build --}}
    <section id="build" class="mb-12">
      <h2 class="text-2xl font-bold text-gray-900 mb-4">What You'll Build</h2>
      <p class="text-gray-500 text-sm mb-4">A demo app called <strong>Hello Wallet</strong> that:</p>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="bg-white border border-gray-200 rounded-xl p-4">
          <div class="text-2xl mb-2">👤</div>
          <h4 class="font-semibold text-gray-900 text-sm">Shows the user's profile</h4>
          <p class="text-xs text-gray-500 mt-1">Name and email via <code class="bg-gray-100 px-1 rounded">getUserProfile()</code> — no login screen needed.</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-4">
          <div class="text-2xl mb-2">🌗</div>
          <h4 class="font-semibold text-gray-900 text-sm">Follows light / dark mode</h4>
          <p class="text-xs text-gray-500 mt-1">Adapts instantly using <code class="bg-gray-100 px-1 rounded">getTheme()</code> and the theme-changed event.</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-4">
          <div class="text-2xl mb-2">💰</div>
          <h4 class="font-semibold text-gray-900 text-sm">Reads the wallet balance</h4>
          <p class="text-xs text-gray-500 mt-1">Async read via <code class="bg-gray-100 px-1 rounded">getWalletBalance()</code> with a refresh button.</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-4">
          <div class="text-2xl mb-2">⚡</div>
          <h4 class="font-semibold text-gray-900 text-sm">Uses native actions</h4>
          <p class="text-xs text-gray-500 mt-1">Toast messages and closing the app via <code class="bg-gray-100 px-1 rounded">showToast()</code> and <code class="bg-gray-100 px-1 rounded">requestBack()</code>.</p>
        </div>
      </div>
    </section>

    {{-- Step 1 --}}
    <section id="step1" class="mb-12">
      <div class="flex gap-4 mb-4">
        <div class="step-num">1</div>
        <div>
          <h2 class="text-2xl font-bold text-gray-900">Scaffold the File</h2>
          <p class="text-gray-500 text-sm mt-1">Create a file named <code class="bg-gray-100 px-1 rounded text-xs">index.html</code> and paste this in. Three things to notice: the <code class="bg-gray-100 px-1 rounded text-xs">56px top padding</code> that clears the OPOOBO header, the <code class="bg-gray-100 px-1 rounded text-xs">opoobo:ready</code> listener, and the <code class="bg-gray-100 px-1 rounded text-xs">800ms fallback</code> so the page also works in a normal browser with mock data.</p>
        </div>
      </div>

      <div class="code-block">&lt;!DOCTYPE html&gt;
&lt;html lang=<span class="str">"en"</span>&gt;
&lt;head&gt;
&lt;meta charset=<span class="str">"UTF-8"</span>&gt;
&lt;meta name=<span class="str">"viewport"</span> content=<span class="str">"width=device-width, initial-scale=1.0"</span>&gt;
&lt;title&gt;Hello Wallet — OPOOBO Demo&lt;/title&gt;
&lt;style&gt;
  * { box-sizing: border-box; }
  <span class="cmt">/* 56px clears the OPOOBO header bar */</span>
  body {
    margin: <span class="num">0</span>;
    padding: <span class="num">56px 16px 24px</span>;
    font-family: -apple-system, BlinkMacSystemFont, <span class="str">'Segoe UI'</span>, sans-serif;
    background: <span class="str">#ffffff</span>; color: <span class="str">#1a1a1a</span>;
  }
  body.dark { background: <span class="str">#0f0f0f</span>; color: <span class="str">#ffffff</span>; }
  .card { border: <span class="num">1px</span> solid <span class="str">#e5e7eb</span>; border-radius: <span class="num">12px</span>;
          padding: <span class="num">16px</span>; margin-bottom: <span class="num">12px</span>; }
  body.dark .card { border-color: <span class="str">#333</span>; }
  button { padding: <span class="num">10px 16px</span>; border-radius: <span class="num">8px</span>; border: none;
           background: <span class="str">#FF4500</span>; color: <span class="str">#fff</span>;
           font-weight: <span class="num">600</span>; margin-right: <span class="num">8px</span>; }
  #banner { text-align: center; font-size: <span class="num">12px</span>;
            color: <span class="str">#92400e</span>; margin-bottom: <span class="num">8px</span>; }
&lt;/style&gt;
&lt;/head&gt;
&lt;body&gt;
  &lt;div id=<span class="str">"banner"</span>&gt;&lt;/div&gt;
  &lt;h1&gt;Hello Wallet&lt;/h1&gt;
  &lt;p id=<span class="str">"subtitle"</span>&gt;Connecting…&lt;/p&gt;

  &lt;div class=<span class="str">"card"</span>&gt;
    &lt;h2&gt;Profile&lt;/h2&gt;
    &lt;p id=<span class="str">"profile"</span>&gt;…&lt;/p&gt;
  &lt;/div&gt;

  &lt;div class=<span class="str">"card"</span>&gt;
    &lt;h2&gt;Wallet&lt;/h2&gt;
    &lt;p id=<span class="str">"wallet"</span>&gt;…&lt;/p&gt;
    &lt;button id=<span class="str">"refresh"</span>&gt;Refresh balance&lt;/button&gt;
  &lt;/div&gt;

  &lt;div class=<span class="str">"card"</span>&gt;
    &lt;h2&gt;Actions&lt;/h2&gt;
    &lt;button id=<span class="str">"toast"</span>&gt;Show toast&lt;/button&gt;
    &lt;button id=<span class="str">"back"</span>&gt;Close app&lt;/button&gt;
  &lt;/div&gt;

&lt;script&gt;
  <span class="kw">var</span> <span class="prop">sdkReady</span> = <span class="kw">false</span>;
  <span class="kw">var</span> <span class="prop">mockProfile</span> = { name: <span class="str">'Demo User'</span>, email: <span class="str">'demo@example.com'</span> };

  <span class="kw">function</span> <span class="fn">el</span>(id) { <span class="kw">return</span> document.<span class="fn">getElementById</span>(id); }

  <span class="cmt">// Runs when the OPOOBO SDK is injected and ready</span>
  window.<span class="fn">addEventListener</span>(<span class="str">'opoobo:ready'</span>, <span class="kw">function</span>(e) {
    sdkReady = <span class="kw">true</span>;
    <span class="fn">render</span>();
  });

  <span class="cmt">// Fallback: also render in a plain browser (mock data)</span>
  <span class="fn">setTimeout</span>(<span class="kw">function</span>() { <span class="kw">if</span> (!sdkReady) <span class="fn">render</span>(); }, <span class="num">800</span>);

  <span class="kw">function</span> <span class="fn">render</span>() {
    <span class="kw">var</span> <span class="prop">p</span> = (window.opoobo &amp;&amp; window.opoobo.<span class="fn">getUserProfile</span>())
      ? window.opoobo.<span class="fn">getUserProfile</span>() : mockProfile;
    <span class="fn">el</span>(<span class="str">'profile'</span>).textContent = p.name + <span class="str">' ('</span> + p.email + <span class="str">')'</span>;
    <span class="fn">el</span>(<span class="str">'subtitle'</span>).textContent = sdkReady
      ? <span class="str">'Connected via OPOOBO SDK'</span> : <span class="str">'Preview mode — open inside OPOOBO to go live'</span>;
    <span class="fn">el</span>(<span class="str">'banner'</span>).textContent = sdkReady ? <span class="str">''</span> : <span class="str">'PREVIEW MODE (mock data)'</span>;
  }
&lt;/script&gt;
&lt;/body&gt;
&lt;/html&gt;</div>
    </section>

    {{-- Step 2 --}}
    <section id="step2" class="mb-12">
      <div class="flex gap-4 mb-4">
        <div class="step-num">2</div>
        <div>
          <h2 class="text-2xl font-bold text-gray-900">Preview in Your Browser</h2>
          <p class="text-gray-500 text-sm mt-1">Double-click <code class="bg-gray-100 px-1 rounded text-xs">index.html</code> (or run <code class="bg-gray-100 px-1 rounded text-xs">npx serve .</code> in its folder). You should see the page with a <strong>PREVIEW MODE</strong> banner, the mock profile, and three cards. The wallet and buttons don't work yet — that's Steps 4 and 5.</p>
        </div>
      </div>
      <div class="bg-green-50 border border-green-200 rounded-xl p-4">
        <p class="text-xs text-green-700"><strong>Checkpoint:</strong> page renders, no blank screen, PREVIEW MODE banner visible. If you see a blank page, check the browser console (F12) for a JavaScript error — usually a typo in an element id.</p>
      </div>
    </section>

    {{-- Step 3 --}}
    <section id="step3" class="mb-12">
      <div class="flex gap-4 mb-4">
        <div class="step-num">3</div>
        <div>
          <h2 class="text-2xl font-bold text-gray-900">Add Theming</h2>
          <p class="text-gray-500 text-sm mt-1">Add these two snippets inside your <code class="bg-gray-100 px-1 rounded text-xs">&lt;script&gt;</code> tag. The first reads the current theme on load; the second reacts when the user toggles light/dark mode while your app is open.</p>
        </div>
      </div>
      <div class="code-block"><span class="cmt">// Read the theme every time we render</span>
<span class="kw">function</span> <span class="fn">applyTheme</span>() {
  <span class="kw">var</span> <span class="prop">theme</span> = window.opoobo ? window.opoobo.<span class="fn">getTheme</span>() : <span class="str">'light'</span>;
  document.body.classList.<span class="fn">toggle</span>(<span class="str">'dark'</span>, theme === <span class="str">'dark'</span>);
}

<span class="cmt">// React to live theme changes inside OPOOBO</span>
window.<span class="fn">addEventListener</span>(<span class="str">'opoobo:themeChanged'</span>, <span class="kw">function</span>(e) {
  document.body.classList.<span class="fn">toggle</span>(<span class="str">'dark'</span>, e.detail.theme === <span class="str">'dark'</span>);
});</div>
      <p class="text-gray-500 text-xs mt-3">Then call <code class="bg-gray-100 px-1 rounded text-xs">applyTheme()</code> at the end of your <code class="bg-gray-100 px-1 rounded text-xs">render()</code> function. Test it in the browser by manually adding <code class="bg-gray-100 px-1 rounded text-xs">class="dark"</code> to the <code class="bg-gray-100 px-1 rounded text-xs">&lt;body&gt;</code> tag — the page should go dark.</p>
    </section>

    {{-- Step 4 --}}
    <section id="step4" class="mb-12">
      <div class="flex gap-4 mb-4">
        <div class="step-num">4</div>
        <div>
          <h2 class="text-2xl font-bold text-gray-900">Read the Wallet Balance</h2>
          <p class="text-gray-500 text-sm mt-1">Unlike the profile, the balance is fetched asynchronously. Add this function and wire it to the Refresh button. Note the <code class="bg-gray-100 px-1 rounded text-xs">catch</code> — always handle the case where data is unavailable.</p>
        </div>
      </div>
      <div class="code-block"><span class="kw">function</span> <span class="fn">loadWallet</span>() {
  <span class="kw">if</span> (window.opoobo &amp;&amp; window.opoobo.<span class="fn">getWalletBalance</span>) {
    window.opoobo.<span class="fn">getWalletBalance</span>().<span class="fn">then</span>(<span class="kw">function</span>(b) {
      <span class="fn">el</span>(<span class="str">'wallet'</span>).textContent = b.currency + <span class="str">' '</span> + b.amount;
    }).<span class="fn">catch</span>(<span class="kw">function</span>() {
      <span class="fn">el</span>(<span class="str">'wallet'</span>).textContent = <span class="str">'Unavailable'</span>;
    });
  } <span class="kw">else</span> {
    <span class="fn">el</span>(<span class="str">'wallet'</span>).textContent = <span class="str">'NGN 25,000.00 (mock)'</span>;
  }
}

<span class="fn">el</span>(<span class="str">'refresh'</span>).<span class="fn">addEventListener</span>(<span class="str">'click'</span>, loadWallet);</div>
      <p class="text-gray-500 text-xs mt-3">Also call <code class="bg-gray-100 px-1 rounded text-xs">loadWallet()</code> at the end of <code class="bg-gray-100 px-1 rounded text-xs">render()</code> so the balance loads automatically. In the browser you'll see the mock balance; inside OPOOBO you'll see the real one.</p>
    </section>

    {{-- Step 5 --}}
    <section id="step5" class="mb-12">
      <div class="flex gap-4 mb-4">
        <div class="step-num">5</div>
        <div>
          <h2 class="text-2xl font-bold text-gray-900">Add Actions</h2>
          <p class="text-gray-500 text-sm mt-1">Wire the two buttons. Each has a graceful fallback so the demo stays clickable in a plain browser.</p>
        </div>
      </div>
      <div class="code-block"><span class="fn">el</span>(<span class="str">'toast'</span>).<span class="fn">addEventListener</span>(<span class="str">'click'</span>, <span class="kw">function</span>() {
  <span class="kw">if</span> (window.opoobo) window.opoobo.<span class="fn">showToast</span>(<span class="str">'Hello from the demo app!'</span>);
  <span class="kw">else</span> <span class="fn">alert</span>(<span class="str">'Hello from the demo app! (toast shows inside OPOOBO)'</span>);
});

<span class="fn">el</span>(<span class="str">'back'</span>).<span class="fn">addEventListener</span>(<span class="str">'click'</span>, <span class="kw">function</span>() {
  <span class="kw">if</span> (window.opoobo) window.opoobo.<span class="fn">requestBack</span>();
  <span class="kw">else</span> <span class="fn">alert</span>(<span class="str">'requestBack() closes the app inside OPOOBO'</span>);
});</div>
      <div class="bg-green-50 border border-green-200 rounded-xl p-4 mt-4">
        <p class="text-xs text-green-700"><strong>Checkpoint:</strong> reload the page in your browser. Profile card, mock balance, theme toggle via body class, and both buttons (showing alerts) should all work. Your demo logic is complete.</p>
      </div>
    </section>

    {{-- Step 6 --}}
    <section id="step6" class="mb-12">
      <div class="flex gap-4 mb-4">
        <div class="step-num">6</div>
        <div>
          <h2 class="text-2xl font-bold text-gray-900">Host It over HTTPS</h2>
          <p class="text-gray-500 text-sm mt-1">OPOOBO only loads apps over <strong>HTTPS</strong> — plain HTTP is rejected at submission and fails the pre-flight check. Any of these free options works:</p>
        </div>
      </div>
      <div class="space-y-3">
        <div class="bg-white border border-gray-200 rounded-xl p-4">
          <h4 class="font-semibold text-gray-900 text-sm">Option A — Static host (easiest)</h4>
          <p class="text-xs text-gray-500 mt-1">Upload <code class="bg-gray-100 px-1 rounded">index.html</code> to Netlify, Vercel, GitHub Pages, or Cloudflare Pages. You instantly get an <code class="bg-gray-100 px-1 rounded">https://…</code> URL. Use it as your App URL.</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-4">
          <h4 class="font-semibold text-gray-900 text-sm">Option B — Tunnel your laptop (for testing)</h4>
          <p class="text-xs text-gray-500 mt-1">Run <code class="bg-gray-100 px-1 rounded">npx serve .</code> in the file's folder, then expose it with <code class="bg-gray-100 px-1 rounded">ngrok http 3000</code> or <code class="bg-gray-100 px-1 rounded">cloudflared tunnel --url http://localhost:3000</code>. Use the resulting <code class="bg-gray-100 px-1 rounded">https://…</code> URL as your <strong>Test Mode URL</strong> so admins can preview it.</p>
        </div>
      </div>
    </section>

    {{-- Step 7 --}}
    <section id="step7" class="mb-12">
      <div class="flex gap-4 mb-4">
        <div class="step-num">7</div>
        <div>
          <h2 class="text-2xl font-bold text-gray-900">Submit for Review</h2>
          <p class="text-gray-500 text-sm mt-1">Go to <a href="{{ route('developer.submit') }}" class="text-brand-500 hover:underline">Submit App</a> and fill the form with your demo values:</p>
        </div>
      </div>
      <div class="bg-white border border-gray-200 rounded-xl p-5">
        <ul class="text-xs text-gray-600 space-y-2 list-disc list-inside check">
          <li><strong>App Name:</strong> Hello Wallet (Demo)</li>
          <li><strong>Description:</strong> Demo mini-app: shows profile, wallet balance, theme support, and native actions.</li>
          <li><strong>App URL:</strong> your <code class="bg-gray-100 px-1 rounded">https://…</code> address from Step 6</li>
          <li><strong>Test Mode URL:</strong> same URL (or your tunnel URL if still testing)</li>
          <li><strong>Icon:</strong> pick <code class="bg-gray-100 px-1 rounded">account_balance_wallet_rounded</code> from the picker</li>
          <li><strong>APIs used:</strong> tick User Profile, Theme, Wallet Balance, Back Navigation, Toast Messages</li>
        </ul>
      </div>
      <p class="text-gray-500 text-xs mt-3">After submitting, track progress on your <a href="{{ route('developer.dashboard') }}" class="text-brand-500 hover:underline">dashboard</a>. Our team runs an automated pre-flight check (HTTPS valid? page loads?) and then manually opens your app. Typical turnaround is 1–3 business days. If anything fails, you'll see the reason under <strong>Review notes</strong> — fix it and update your app.</p>
      <a href="{{ route('developer.submit') }}" class="inline-block mt-4 bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold px-6 py-2.5 rounded-lg transition">
        Submit Your Demo App →
      </a>
    </section>

    {{-- Mistakes --}}
    <section id="mistakes" class="mb-12">
      <h2 class="text-2xl font-bold text-gray-900 mb-4">Common Mistakes</h2>
      <div class="space-y-3">
        <div class="bg-white border border-gray-200 rounded-xl p-4">
          <h4 class="font-semibold text-gray-900 text-sm mb-1">Calling the SDK before it's ready</h4>
          <p class="text-xs text-gray-500">Always wait for <code class="bg-gray-100 px-1 rounded">opoobo:ready</code> before calling <code class="bg-gray-100 px-1 rounded">getUserProfile()</code> or any async API. Calling them at the top level of your script is the #1 cause of blank screens.</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-4">
          <h4 class="font-semibold text-gray-900 text-sm mb-1">Forgetting the 56px header offset</h4>
          <p class="text-xs text-gray-500">Without <code class="bg-gray-100 px-1 rounded">padding-top: 56px</code>, your content hides under OPOOBO's header bar and your app fails review.</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-4">
          <h4 class="font-semibold text-gray-900 text-sm mb-1">Submitting an HTTP URL</h4>
          <p class="text-xs text-gray-500">HTTP is rejected automatically. If you only have <code class="bg-gray-100 px-1 rounded">http://localhost</code>, use a tunnel (Step 6, Option B) to get an HTTPS URL.</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-4">
          <h4 class="font-semibold text-gray-900 text-sm mb-1">Storing the access token</h4>
          <p class="text-xs text-gray-500">Never put <code class="bg-gray-100 px-1 rounded">accessToken</code> in <code class="bg-gray-100 px-1 rounded">localStorage</code> or send it anywhere except your own backend. The demo app doesn't need the token at all.</p>
        </div>
      </div>
    </section>

    {{-- Next --}}
    <section id="next" class="mb-12">
      <div class="bg-gradient-to-r from-gray-900 to-gray-800 rounded-2xl p-6 text-white">
        <h3 class="font-bold text-lg mb-2">Demo live? Level up.</h3>
        <p class="text-sm text-white/70 mb-4">Read the full SDK reference for saved addresses, payment methods, analytics events, and the complete publishing rules.</p>
        <div class="flex gap-3">
          <a href="{{ route('developer.docs') }}" class="bg-white/10 hover:bg-white/20 text-white text-sm font-semibold px-4 py-2 rounded-lg transition">
            SDK Documentation
          </a>
          <a href="{{ route('developer.dashboard') }}" class="bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold px-4 py-2 rounded-lg transition">
            My Apps →
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
