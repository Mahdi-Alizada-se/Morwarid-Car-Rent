<?php

namespace App\Services;

use Generator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatbotService
{
    private const CACHE_TTL = 3600;      // 1 hour
    private const MAX_MESSAGES = 18;        // max user/assistant messages to keep
    private const CACHE_PREFIX = 'chatbot:';

    private string $ollamaHost;
    private string $ollamaModel;

    public function __construct()
    {
        $this->ollamaHost = env('OLLAMA_HOST', 'http://127.0.0.1:11434');
        $this->ollamaModel = env('OLLAMA_MODEL', 'llama3.2:1b');
    }

    // ─── System Message ───────────────────────────────────────────────────────

    private function getSystemMessage(): array
    {
        return [
            'role' => 'system',
            'content' => 'You are a helpful AI assistant that can answer any question on any topic. '
                . 'You have broad knowledge covering science, history, technology, math, language, culture, health, sports, entertainment, and general knowledge. '
                . 'You also have specific knowledge about Morwarid Car Rental, a car rental service in Kabul, Afghanistan. '
                . 'For car rental questions you can help with: vehicle availability, pricing in Afghan Afghani (AFN), booking process, required documents (national ID or passport, driver license), cancellation policy, fuel policy, and vehicle specifications. '
                . 'Be friendly, clear, and helpful for ALL questions — not just car rental topics. '
                . 'Answer in the same language the customer writes in (Dari, Pashto, or English). '
                . 'Keep answers concise and easy to understand. '
                . 'Never refuse to answer a general knowledge question.',
        ];
    }

    // ─── Get History ──────────────────────────────────────────────────────────

    public function getHistory(string $sessionId): array
    {
        $key = self::CACHE_PREFIX . $sessionId;
        $history = Cache::get($key);

        if (!$history) {
            return [$this->getSystemMessage()];
        }

        $decoded = json_decode($history, true);

        return is_array($decoded) ? $decoded : [$this->getSystemMessage()];
    }

    // ─── Save History ─────────────────────────────────────────────────────────

    public function saveHistory(string $sessionId, array $messages): void
    {
        $key = self::CACHE_PREFIX . $sessionId;

        // Always keep system message
        $systemMessage = $messages[0] ?? $this->getSystemMessage();
        $otherMessages = array_slice($messages, 1);

        // Keep only last MAX_MESSAGES user/assistant messages
        if (count($otherMessages) > self::MAX_MESSAGES) {
            $otherMessages = array_slice($otherMessages, -self::MAX_MESSAGES);
        }

        $toSave = array_merge([$systemMessage], $otherMessages);

        Cache::put($key, json_encode($toSave), self::CACHE_TTL);
    }

    // ─── Stream Chat ──────────────────────────────────────────────────────────

    public function streamChat(string $sessionId, string $userMessage): Generator
    {
        $messages = $this->getHistory($sessionId);

        // Append user message
        $messages[] = [
            'role' => 'user',
            'content' => $userMessage,
        ];

        $fullResponse = '';

        try {
            $response = Http::withOptions(['stream' => true])
                ->timeout(120)
                ->post($this->ollamaHost . '/api/chat', [
                    'model' => $this->ollamaModel,
                    'messages' => $messages,
                    'stream' => true,
                    'options' => [
                        'temperature' => 0.7,
                        'num_predict' => 500,
                    ],
                ]);

            $body = $response->getBody();

            // Read stream line by line
            $buffer = '';

            while (!$body->eof()) {
                $chunk = $body->read(1024);
                $buffer .= $chunk;

                // Process complete lines
                while (($pos = strpos($buffer, "\n")) !== false) {
                    $line = substr($buffer, 0, $pos);
                    $buffer = substr($buffer, $pos + 1);
                    $line = trim($line);

                    if (empty($line)) {
                        continue;
                    }

                    $data = json_decode($line, true);

                    if (!is_array($data)) {
                        continue;
                    }

                    $content = $data['message']['content'] ?? '';

                    if (!empty($content)) {
                        $fullResponse .= $content;
                        yield $content;
                    }

                    // Stream complete
                    if (isset($data['done']) && $data['done'] === true) {
                        break 2;
                    }
                }
            }

        } catch (\Exception $e) {
            Log::error('Ollama stream error: ' . $e->getMessage());
            yield 'Sorry, I am having trouble connecting to the AI service right now. Please try again in a moment.';
            return;
        }

        // Save complete response to history
        if (!empty($fullResponse)) {
            $messages[] = [
                'role' => 'assistant',
                'content' => $fullResponse,
            ];
            $this->saveHistory($sessionId, $messages);
        }
    }

    // ─── Check Ollama Status ──────────────────────────────────────────────────

    public function isOllamaRunning(): bool
    {
        try {
            $response = Http::timeout(3)->get($this->ollamaHost . '/api/tags');
            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }

    // ─── Delete History ───────────────────────────────────────────────────────

    public function deleteHistory(string $sessionId): void
    {
        Cache::forget(self::CACHE_PREFIX . $sessionId);
    }
}