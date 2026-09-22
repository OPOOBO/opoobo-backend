<?php

namespace App\Services;

use App\Models\Module;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiAssistantService
{
    /**
     * Answer a chat turn grounded in the active module catalogue.
     *
     * @param  array<int, array{role: string, content: string}>  $messages
     * @return array{reply: string, actions: array<int, array<string, string>>}
     */
    public function chat(array $messages): array
    {
        $modules = Module::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get(['name', 'display_name', 'description']);

        if ($this->hasProviderConfig()) {
            try {
                $result = $this->callProvider($messages, $modules);
                if ($result !== null) {
                    return $result;
                }
            } catch (\Throwable $e) {
                Log::warning('AI provider call failed, using local fallback', [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $this->localFallback($messages, $modules);
    }

    private function hasProviderConfig(): bool
    {
        return filled(config('services.ai.api_key'));
    }

    /**
     * @param  array<int, array{role: string, content: string}>  $messages
     * @param  Collection<int, Module>  $modules
     * @return array{reply: string, actions: array<int, array<string, string>>}|null
     */
    private function callProvider(array $messages, Collection $modules): ?array
    {
        $baseUrl = rtrim((string) config('services.ai.base_url'), '/');
        $model = (string) config('services.ai.model');
        $apiKey = (string) config('services.ai.api_key');

        $catalogue = $modules->map(fn (Module $m) => [
            'name' => $m->name,
            'displayName' => $m->display_name,
            'description' => $m->description,
        ])->values()->all();

        $system = <<<PROMPT
You are Ask OPOOBO, the in-app assistant for the OPOOBO super app.
Help users find services (modules), explain what each module does, and answer app questions.
Available modules (JSON):
PROMPT
            .json_encode($catalogue, JSON_UNESCAPED_UNICODE);

        $system .= <<<'PROMPT'

Respond with a single JSON object only (no markdown), shape:
{"reply":"string","actions":[{"type":"open_module","moduleName":"bus","label":"Open Bus"}]}
Use action type "open_module" with a real module name from the catalogue, or "open_search" with a "query" field.
Omit actions when none are useful. Keep replies concise and helpful.
PROMPT;

        $payloadMessages = [
            ['role' => 'system', 'content' => $system],
        ];

        foreach ($messages as $message) {
            $role = $message['role'] === 'assistant' ? 'assistant' : 'user';
            $payloadMessages[] = [
                'role' => $role,
                'content' => $message['content'],
            ];
        }

        $response = Http::withToken($apiKey)
            ->timeout(30)
            ->acceptJson()
            ->post($baseUrl.'/chat/completions', [
                'model' => $model,
                'messages' => $payloadMessages,
                'temperature' => 0.4,
                'response_format' => ['type' => 'json_object'],
            ]);

        if (! $response->successful()) {
            Log::warning('AI provider returned non-success status', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        }

        $content = data_get($response->json(), 'choices.0.message.content');
        if (! is_string($content) || $content === '') {
            return null;
        }

        $decoded = json_decode($content, true);
        if (! is_array($decoded) || ! isset($decoded['reply']) || ! is_string($decoded['reply'])) {
            return [
                'reply' => trim($content),
                'actions' => [],
            ];
        }

        return [
            'reply' => $decoded['reply'],
            'actions' => $this->normalizeActions($decoded['actions'] ?? [], $modules),
        ];
    }

    /**
     * @param  array<int, array{role: string, content: string}>  $messages
     * @param  Collection<int, Module>  $modules
     * @return array{reply: string, actions: array<int, array<string, string>>}
     */
    private function localFallback(array $messages, Collection $modules): array
    {
        $latestUser = '';
        for ($i = count($messages) - 1; $i >= 0; $i--) {
            if (($messages[$i]['role'] ?? '') === 'user') {
                $latestUser = strtolower(trim((string) $messages[$i]['content']));
                break;
            }
        }

        if ($latestUser === '') {
            return [
                'reply' => 'Ask me anything about OPOOBO services — for example “Where can I book a bus?” or “Find marketplace”.',
                'actions' => [],
            ];
        }

        $matches = $modules->filter(function (Module $module) use ($latestUser) {
            $haystack = strtolower(implode(' ', array_filter([
                $module->name,
                $module->display_name,
                $module->description,
            ])));

            foreach (preg_split('/\s+/', $latestUser) ?: [] as $token) {
                if (strlen($token) < 3) {
                    continue;
                }
                if (str_contains($haystack, $token)) {
                    return true;
                }
            }

            return str_contains($haystack, $latestUser);
        })->take(3)->values();

        if ($matches->isEmpty()) {
            $list = $modules->take(6)->map(fn (Module $m) => $m->display_name)->implode(', ');

            return [
                'reply' => $list !== ''
                    ? "I couldn't match a specific service. Available modules include: {$list}. Try naming one, or search the store."
                    : "I couldn't find matching services right now. Try searching in the app.",
                'actions' => [
                    [
                        'type' => 'open_search',
                        'query' => $latestUser,
                        'label' => 'Search app',
                    ],
                ],
            ];
        }

        $names = $matches->map(fn (Module $m) => $m->display_name)->implode(', ');
        $actions = $matches->map(fn (Module $m) => [
            'type' => 'open_module',
            'moduleName' => $m->name,
            'label' => 'Open '.$m->display_name,
        ])->all();

        return [
            'reply' => "Here's what I found for you: {$names}. Tap a service below to open it.",
            'actions' => $actions,
        ];
    }

    /**
     * @param  mixed  $actions
     * @param  Collection<int, Module>  $modules
     * @return array<int, array<string, string>>
     */
    private function normalizeActions(mixed $actions, Collection $modules): array
    {
        if (! is_array($actions)) {
            return [];
        }

        $validNames = $modules->pluck('name')->all();
        $normalized = [];

        foreach ($actions as $action) {
            if (! is_array($action)) {
                continue;
            }

            $type = (string) ($action['type'] ?? '');
            if ($type === 'open_module') {
                $moduleName = (string) ($action['moduleName'] ?? $action['module_name'] ?? '');
                if ($moduleName === '' || ! in_array($moduleName, $validNames, true)) {
                    continue;
                }
                $label = (string) ($action['label'] ?? ('Open '.$moduleName));
                $normalized[] = [
                    'type' => 'open_module',
                    'moduleName' => $moduleName,
                    'label' => $label,
                ];
            } elseif ($type === 'open_search') {
                $query = trim((string) ($action['query'] ?? ''));
                if ($query === '') {
                    continue;
                }
                $normalized[] = [
                    'type' => 'open_search',
                    'query' => $query,
                    'label' => (string) ($action['label'] ?? 'Search'),
                ];
            }
        }

        return array_values($normalized);
    }
}
