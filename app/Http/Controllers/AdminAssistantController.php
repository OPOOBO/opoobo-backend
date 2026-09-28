<?php

namespace App\Http\Controllers;

use App\Models\AiProvider;
use App\Models\KnowledgebaseFile;
use App\Services\KnowledgebaseTextExtractor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AdminAssistantController extends Controller
{
    public function index(): View
    {
        $files = KnowledgebaseFile::query()->orderByDesc('id')->get();
        $providers = AiProvider::query()->orderBy('sort_order')->orderBy('id')->get();

        return view('admin.assistant', compact('files', 'providers'));
    }

    public function storeFile(Request $request, KnowledgebaseTextExtractor $extractor): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'extensions:txt,md,pdf', 'max:8192'],
        ]);

        $uploaded = $request->file('file');
        if (! $uploaded instanceof UploadedFile) {
            return back()->with('error', 'Choose a knowledgebase file to upload.');
        }

        $record = $this->persistFile($uploaded, $extractor);
        if ($record === null) {
            return back()->with('error', 'That file has no readable text.');
        }

        return back()->with('success', '"'.$record->name.'" added to the knowledgebase.');
    }

    public function replaceFile(Request $request, KnowledgebaseFile $file, KnowledgebaseTextExtractor $extractor): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'extensions:txt,md,pdf', 'max:8192'],
        ]);

        $uploaded = $request->file('file');
        if (! $uploaded instanceof UploadedFile) {
            return back()->with('error', 'Choose a replacement file.');
        }

        $body = $this->extractBody($uploaded, $extractor);
        if ($body === null) {
            return back()->with('error', 'That file has no readable text.');
        }

        $path = $uploaded->store('', 'knowledgebase');
        Storage::disk('knowledgebase')->delete($file->path);

        $file->update([
            'name' => basename($uploaded->getClientOriginalName()),
            'path' => $path,
            'mime' => $uploaded->getClientMimeType(),
            'size' => $uploaded->getSize() ?: 0,
            'body' => $body,
        ]);

        return back()->with('success', '"'.$file->name.'" replaced.');
    }

    public function toggleFile(KnowledgebaseFile $file): RedirectResponse
    {
        $file->update(['is_active' => ! $file->is_active]);
        $state = $file->is_active ? 'active' : 'inactive';

        return back()->with('success', '"'.$file->name.'" is now '.$state.'.');
    }

    public function destroyFile(KnowledgebaseFile $file): RedirectResponse
    {
        Storage::disk('knowledgebase')->delete($file->path);
        $name = $file->name;
        $file->delete();

        return back()->with('success', '"'.$name.'" removed from the knowledgebase.');
    }

    public function storeProvider(Request $request): RedirectResponse
    {
        $validated = $this->validateProvider($request, true);

        AiProvider::create([
            'name' => $validated['name'],
            'base_url' => rtrim($validated['base_url'], '/'),
            'model' => $validated['model'],
            'api_key' => $validated['api_key'],
            'is_enabled' => $request->boolean('is_enabled'),
            'sort_order' => ((int) AiProvider::query()->max('sort_order')) + 1,
        ]);

        return back()->with('success', 'LLM "'.$validated['name'].'" saved.');
    }

    public function updateProvider(Request $request, AiProvider $provider): RedirectResponse
    {
        $validated = $this->validateProvider($request, false);

        $provider->fill([
            'name' => $validated['name'],
            'base_url' => rtrim($validated['base_url'], '/'),
            'model' => $validated['model'],
            'is_enabled' => $request->boolean('is_enabled'),
        ]);

        if (filled($validated['api_key'] ?? null)) {
            $provider->api_key = $validated['api_key'];
        }

        $provider->save();

        return back()->with('success', 'LLM "'.$provider->name.'" updated.');
    }

    public function destroyProvider(AiProvider $provider): RedirectResponse
    {
        $name = $provider->name;
        $provider->delete();

        return back()->with('success', 'LLM "'.$name.'" removed.');
    }

    public function testProvider(AiProvider $provider): RedirectResponse
    {
        try {
            $response = Http::withToken($provider->api_key)
                ->timeout(20)
                ->acceptJson()
                ->post(rtrim($provider->base_url, '/').'/chat/completions', [
                    'model' => $provider->model,
                    'temperature' => 0,
                    'max_tokens' => 1,
                    'messages' => [
                        ['role' => 'user', 'content' => 'Reply with the word ok.'],
                    ],
                ]);
        } catch (\Throwable $e) {
            return back()->with('error', 'Provider test failed: '.$e->getMessage());
        }

        if (! $response->successful()) {
            return back()->with('error', 'Provider test failed: HTTP '.$response->status().'.');
        }

        return back()->with('success', '"'.$provider->name.'" responded.');
    }

    /**
     * @return array{name: string, base_url: string, model: string, api_key?: string|null}
     */
    private function validateProvider(Request $request, bool $keyRequired): array
    {
        $keyRule = $keyRequired ? ['required', 'string', 'max:2000'] : ['nullable', 'string', 'max:2000'];

        /** @var array{name: string, base_url: string, model: string, api_key?: string|null} $validated */
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'base_url' => ['required', 'url', 'max:500'],
            'model' => ['required', 'string', 'max:120'],
            'api_key' => $keyRule,
        ]);

        return $validated;
    }

    private function persistFile(UploadedFile $uploaded, KnowledgebaseTextExtractor $extractor): ?KnowledgebaseFile
    {
        $body = $this->extractBody($uploaded, $extractor);
        if ($body === null) {
            return null;
        }

        $path = $uploaded->store('', 'knowledgebase');

        return KnowledgebaseFile::create([
            'name' => basename($uploaded->getClientOriginalName()),
            'path' => $path,
            'mime' => $uploaded->getClientMimeType(),
            'size' => $uploaded->getSize() ?: 0,
            'body' => $body,
            'is_active' => true,
        ]);
    }

    private function extractBody(UploadedFile $uploaded, KnowledgebaseTextExtractor $extractor): ?string
    {
        try {
            $body = $extractor->extract($uploaded);
        } catch (\Throwable) {
            return null;
        }

        return $body === '' ? null : $body;
    }
}
