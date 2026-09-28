@extends('layouts.app')
@section('title', 'AI Assistant')
@section('nav-title', 'Admin')

@section('content')
<div class="mb-6">
  <h1 class="text-2xl font-bold text-gray-900">AI Assistant</h1>
  <p class="text-gray-500 text-sm">Knowledgebase files the assistant may quote, and the LLM keys used to write replies.</p>
</div>

<h2 class="text-lg font-bold text-gray-900 mb-3">Knowledgebase files</h2>
<div class="bg-white rounded-xl border border-gray-200 p-5 mb-4">
  <form method="POST" action="{{ route('admin.assistant.files.store') }}" enctype="multipart/form-data" class="flex flex-col sm:flex-row sm:items-end gap-3">
    @csrf
    <div class="flex-1">
      <label class="block text-xs font-semibold text-gray-600 mb-1">Upload .txt, .md, or .pdf</label>
      <input type="file" name="file" accept=".txt,.md,.pdf,text/plain,text/markdown,application/pdf" required class="block w-full text-sm text-gray-700">
    </div>
    <button type="submit" class="bg-brand-500 text-white text-sm font-semibold px-4 py-2 rounded-lg hover:bg-brand-600">Add file</button>
  </form>
</div>

@if($files->isEmpty())
  <div class="bg-white rounded-xl border border-gray-200 p-8 text-center mb-10">
    <p class="text-gray-400 text-sm">No knowledgebase files yet.</p>
  </div>
@else
  <div class="space-y-3 mb-10">
    @foreach($files as $file)
      <div class="bg-white rounded-xl border border-gray-200 p-4">
        <div class="flex flex-col sm:flex-row sm:items-center gap-3">
          <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 mb-1">
              <h3 class="font-semibold text-gray-900 text-sm truncate">{{ $file->name }}</h3>
              <span class="text-xs px-2 py-0.5 rounded-full font-medium {{ $file->is_active ? 'bg-green-50 text-green-600' : 'bg-gray-100 text-gray-500' }}">
                {{ $file->is_active ? 'Active' : 'Inactive' }}
              </span>
            </div>
            <p class="text-xs text-gray-400">{{ number_format($file->size / 1024, 1) }} KB</p>
          </div>
          <div class="flex flex-wrap items-center gap-2">
            <form method="POST" action="{{ route('admin.assistant.files.active', $file) }}">
              @csrf
              <button class="text-xs font-semibold px-3 py-1.5 rounded-lg border border-gray-200 text-gray-700 hover:bg-gray-50">
                {{ $file->is_active ? 'Deactivate' : 'Activate' }}
              </button>
            </form>
            <form method="POST" action="{{ route('admin.assistant.files.destroy', $file) }}" onsubmit="return confirm('Remove this file?')">
              @csrf
              @method('DELETE')
              <button class="text-xs font-semibold px-3 py-1.5 rounded-lg border border-red-200 text-red-600 hover:bg-red-50">Delete</button>
            </form>
          </div>
        </div>
        <form method="POST" action="{{ route('admin.assistant.files.replace', $file) }}" enctype="multipart/form-data" class="mt-3 flex flex-col sm:flex-row sm:items-center gap-2">
          @csrf
          <input type="file" name="file" accept=".txt,.md,.pdf,text/plain,text/markdown,application/pdf" required class="block w-full text-xs text-gray-600">
          <button class="text-xs font-semibold px-3 py-1.5 rounded-lg border border-gray-200 text-gray-700 hover:bg-gray-50 shrink-0">Replace</button>
        </form>
      </div>
    @endforeach
  </div>
@endif

<h2 class="text-lg font-bold text-gray-900 mb-3">LLM configuration</h2>
<p class="text-gray-500 text-sm mb-3">Chat uses the first enabled model. Keys stay on this server.</p>

<div class="bg-white rounded-xl border border-gray-200 p-5 mb-4">
  <form method="POST" action="{{ route('admin.assistant.providers.store') }}" class="grid grid-cols-1 sm:grid-cols-2 gap-3">
    @csrf
    <div>
      <label class="block text-xs font-semibold text-gray-600 mb-1">Name</label>
      <input name="name" required maxlength="120" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm" placeholder="OpenAI">
    </div>
    <div>
      <label class="block text-xs font-semibold text-gray-600 mb-1">Model</label>
      <input name="model" required maxlength="120" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm" placeholder="gpt-4o-mini">
    </div>
    <div class="sm:col-span-2">
      <label class="block text-xs font-semibold text-gray-600 mb-1">Base URL</label>
      <input name="base_url" type="url" required maxlength="500" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm" placeholder="https://api.openai.com/v1">
    </div>
    <div class="sm:col-span-2">
      <label class="block text-xs font-semibold text-gray-600 mb-1">API key</label>
      <input name="api_key" type="password" required maxlength="2000" autocomplete="new-password" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm" placeholder="sk-...">
    </div>
    <label class="flex items-center gap-2 text-sm text-gray-700">
      <input type="checkbox" name="is_enabled" value="1" checked class="rounded border-gray-300">
      Enabled
    </label>
    <div class="sm:text-right">
      <button type="submit" class="bg-brand-500 text-white text-sm font-semibold px-4 py-2 rounded-lg hover:bg-brand-600">Add LLM</button>
    </div>
  </form>
</div>

@if($providers->isEmpty())
  <div class="bg-white rounded-xl border border-gray-200 p-8 text-center">
    <p class="text-gray-400 text-sm">No LLM configured. Chat falls back to the server env key when one is set.</p>
  </div>
@else
  <div class="space-y-4">
    @foreach($providers as $provider)
      <div class="bg-white rounded-xl border border-gray-200 p-5">
        <form method="POST" action="{{ route('admin.assistant.providers.update', $provider) }}" class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          @csrf
          <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Name</label>
            <input name="name" required maxlength="120" value="{{ $provider->name }}" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
          </div>
          <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Model</label>
            <input name="model" required maxlength="120" value="{{ $provider->model }}" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
          </div>
          <div class="sm:col-span-2">
            <label class="block text-xs font-semibold text-gray-600 mb-1">Base URL</label>
            <input name="base_url" type="url" required maxlength="500" value="{{ $provider->base_url }}" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
          </div>
          <div class="sm:col-span-2">
            <label class="block text-xs font-semibold text-gray-600 mb-1">API key</label>
            <input name="api_key" type="password" maxlength="2000" autocomplete="new-password" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm" placeholder="Leave blank to keep {{ $provider->maskedKey() }}">
            <p class="text-xs text-gray-400 mt-1">Saved key {{ $provider->maskedKey() }}</p>
          </div>
          <label class="flex items-center gap-2 text-sm text-gray-700">
            <input type="checkbox" name="is_enabled" value="1" @checked($provider->is_enabled) class="rounded border-gray-300">
            Enabled
          </label>
          <div class="sm:text-right">
            <button type="submit" class="bg-brand-500 text-white text-sm font-semibold px-4 py-2 rounded-lg hover:bg-brand-600">Save</button>
          </div>
        </form>
        <div class="flex gap-2 mt-3">
          <form method="POST" action="{{ route('admin.assistant.providers.test', $provider) }}">
            @csrf
            <button class="text-xs font-semibold px-3 py-1.5 rounded-lg border border-gray-200 text-gray-700 hover:bg-gray-50">Test</button>
          </form>
          <form method="POST" action="{{ route('admin.assistant.providers.destroy', $provider) }}" onsubmit="return confirm('Remove this LLM?')">
            @csrf
            @method('DELETE')
            <button class="text-xs font-semibold px-3 py-1.5 rounded-lg border border-red-200 text-red-600 hover:bg-red-50">Delete</button>
          </form>
        </div>
      </div>
    @endforeach
  </div>
@endif
@endsection
