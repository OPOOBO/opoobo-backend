<?php

namespace App\Services;

use App\Models\AiProvider;
use App\Models\KnowledgebaseFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiAssistantService
{
    private const NOT_COVERED = 'The knowledgebase does not cover that question.';

    private const PASSAGE_LIMIT = 4000;

    /**
     * Answer a chat turn from active knowledgebase files.
     *
     * @param  array<int, array{role: string, content: string}>  $messages
     * @return array{reply: string, actions: array<int, array<string, string>>}
     */
    public function chat(array $messages): array
    {
        $query = $this->latestUserText($messages);
        if ($query === '') {
            return [
                'reply' => 'Ask a question and I will answer from the OPOOBO knowledgebase.',
                'actions' => [],
            ];
        }

        $passages = $this->passagesFor($query);
        if ($passages['best'] === '') {
            return [
                'reply' => self::NOT_COVERED,
                'actions' => [],
            ];
        }

        $provider = $this->resolveChatProvider();
        if ($provider === null) {
            return [
                'reply' => $passages['best'],
                'actions' => [],
            ];
        }

        try {
            $reply = $this->callKnowledgeProvider($messages, $passages['text'], $provider);
            if (is_string($reply) && $reply !== '') {
                return [
                    'reply' => $reply,
                    'actions' => [],
                ];
            }
        } catch (\Throwable $e) {
            Log::warning('AI provider call failed, returning knowledgebase passage', [
                'error' => $e->getMessage(),
            ]);
        }

        return [
            'reply' => $passages['best'],
            'actions' => [],
        ];
    }

    /**
     * @param  array<int, array{role: string, content: string}>  $messages
     */
    private function latestUserText(array $messages): string
    {
        for ($i = count($messages) - 1; $i >= 0; $i--) {
            if (($messages[$i]['role'] ?? '') === 'user') {
                return trim((string) $messages[$i]['content']);
            }
        }

        return '';
    }

    /**
     * @return array{text: string, best: string}
     */
    private function passagesFor(string $query): array
    {
        $tokens = $this->tokens($query);
        if ($tokens === []) {
            return ['text' => '', 'best' => ''];
        }

        $ranked = [];
        $files = KnowledgebaseFile::query()->where('is_active', true)->get();

        foreach ($files as $file) {
            $body = (string) $file->body;
            $haystack = mb_strtolower($body);
            $score = 0;
            $position = null;

            foreach ($tokens as $token) {
                $found = mb_strpos($haystack, $token);
                if ($found === false) {
                    continue;
                }
                $score++;
                if ($position === null || $found < $position) {
                    $position = $found;
                }
            }

            if ($score === 0 || $position === null) {
                continue;
            }

            $ranked[] = [
                'score' => $score,
                'excerpt' => $this->excerpt($body, $position),
            ];
        }

        if ($ranked === []) {
            return ['text' => '', 'best' => ''];
        }

        usort($ranked, fn (array $a, array $b) => $b['score'] <=> $a['score']);

        $best = $ranked[0]['excerpt'];
        $text = '';
        foreach ($ranked as $item) {
            $next = $text === '' ? $item['excerpt'] : $text."\n\n".$item['excerpt'];
            if (mb_strlen($next) > self::PASSAGE_LIMIT) {
                break;
            }
            $text = $next;
        }

        if ($text === '') {
            $text = mb_substr($best, 0, self::PASSAGE_LIMIT);
        }

        return ['text' => $text, 'best' => $best];
    }

    /**
     * @return list<string>
     */
    private function tokens(string $query): array
    {
        $parts = preg_split('/[^\p{L}\p{N}]+/u', mb_strtolower($query)) ?: [];
        $tokens = [];

        foreach ($parts as $part) {
            if (mb_strlen($part) < 3) {
                continue;
            }
            $tokens[$part] = true;
            if (count($tokens) >= 12) {
                break;
            }
        }

        return array_keys($tokens);
    }

    private function excerpt(string $body, int $position): string
    {
        $start = max(0, $position - 200);

        return trim(mb_substr($body, $start, 1200));
    }

    /**
     * Enabled admin provider, or the env provider when none is enabled.
     *
     * @return array{base_url: string, model: string, api_key: string}|null
     */
    private function resolveChatProvider(): ?array
    {
        $row = AiProvider::query()
            ->where('is_enabled', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->first();

        if ($row !== null && filled($row->api_key)) {
            return [
                'base_url' => rtrim($row->base_url, '/'),
                'model' => $row->model,
                'api_key' => (string) $row->api_key,
            ];
        }

        if (filled(config('services.ai.api_key'))) {
            return [
                'base_url' => rtrim((string) config('services.ai.base_url'), '/'),
                'model' => (string) config('services.ai.model'),
                'api_key' => (string) config('services.ai.api_key'),
            ];
        }

        return null;
    }

    /**
     * @param  array<int, array{role: string, content: string}>  $messages
     * @param  array{base_url: string, model: string, api_key: string}  $provider
     */
    private function callKnowledgeProvider(array $messages, string $passages, array $provider): ?string
    {
        $system = <<<PROMPT
You are Ask OPOOBO. Answer the user's question using only the knowledgebase passages below.
If the passages do not contain the answer, say the knowledgebase does not cover that question.
Do not invent facts. Respond with a single JSON object and no markdown: {"reply":"string"}

Knowledgebase passages:
{$passages}
PROMPT;

        $payloadMessages = [
            ['role' => 'system', 'content' => $system],
        ];

        foreach ($messages as $message) {
            $role = ($message['role'] ?? '') === 'assistant' ? 'assistant' : 'user';
            if (($message['role'] ?? '') === 'system') {
                continue;
            }
            $payloadMessages[] = [
                'role' => $role,
                'content' => (string) $message['content'],
            ];
        }

        $response = Http::withToken($provider['api_key'])
            ->timeout(30)
            ->acceptJson()
            ->post($provider['base_url'].'/chat/completions', [
                'model' => $provider['model'],
                'temperature' => 0.2,
                'response_format' => ['type' => 'json_object'],
                'messages' => $payloadMessages,
            ]);

        if (! $response->successful()) {
            Log::warning('AI provider returned non-success status', [
                'status' => $response->status(),
            ]);

            return null;
        }

        $content = data_get($response->json(), 'choices.0.message.content');
        if (! is_string($content) || trim($content) === '') {
            return null;
        }

        $decoded = json_decode($content, true);
        if (is_array($decoded) && isset($decoded['reply']) && is_string($decoded['reply']) && trim($decoded['reply']) !== '') {
            return trim($decoded['reply']);
        }

        return trim($content);
    }

    private function hasProviderConfig(): bool
    {
        return filled(config('services.ai.api_key'));
    }

    /**
     * Identify a product photo. Returns null when the provider is missing or fails.
     * Does not invent a price.
     *
     * @return array{name: string, description: string, price_note: string}|null
     */
    public function describeProduct(string $binary, string $mime): ?array
    {
        if (! $this->hasProviderConfig()) {
            return null;
        }

        $baseUrl = rtrim((string) config('services.ai.base_url'), '/');
        $model = (string) config('services.ai.model');
        $apiKey = (string) config('services.ai.api_key');
        $dataUrl = 'data:'.$mime.';base64,'.base64_encode($binary);

        try {
            $response = Http::withToken($apiKey)
                ->timeout(45)
                ->acceptJson()
                ->post($baseUrl.'/chat/completions', [
                    'model' => $model,
                    'temperature' => 0.2,
                    'response_format' => ['type' => 'json_object'],
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'You identify consumer products from a photo for the OPOOBO app. Respond with JSON only: {"name":"string","description":"string","price_note":"string"}. Describe what is visible. If a price is not visible or you cannot support one, set price_note to "Pricing unavailable". Do not invent a specific price.',
                        ],
                        [
                            'role' => 'user',
                            'content' => [
                                [
                                    'type' => 'text',
                                    'text' => 'What product is this, and what can you say about pricing?',
                                ],
                                [
                                    'type' => 'image_url',
                                    'image_url' => ['url' => $dataUrl],
                                ],
                            ],
                        ],
                    ],
                ]);
        } catch (\Throwable $e) {
            Log::warning('Product scan provider call failed', [
                'error' => $e->getMessage(),
            ]);

            return null;
        }

        if (! $response->successful()) {
            Log::warning('Product scan provider returned non-success status', [
                'status' => $response->status(),
            ]);

            return null;
        }

        $content = data_get($response->json(), 'choices.0.message.content');
        if (! is_string($content) || $content === '') {
            return null;
        }

        $decoded = json_decode($content, true);
        if (! is_array($decoded)) {
            return null;
        }

        $name = trim((string) ($decoded['name'] ?? ''));
        $description = trim((string) ($decoded['description'] ?? ''));
        $priceNote = trim((string) ($decoded['price_note'] ?? ''));

        if ($name === '' && $description === '') {
            return null;
        }

        return [
            'name' => $name !== '' ? $name : 'Product',
            'description' => $description,
            'price_note' => $priceNote !== '' ? $priceNote : 'Pricing unavailable',
        ];
    }
}
