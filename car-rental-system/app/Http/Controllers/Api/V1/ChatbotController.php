<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\ChatbotService;
use App\Services\FaqService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ChatbotController extends Controller
{
    public function __construct(
        private ChatbotService $chatbot,
        private FaqService $faq,
    ) {
    }

    // ─── Health Check ─────────────────────────────────────────────────────────

    public function health(): JsonResponse
    {
        $isRunning = $this->chatbot->isOllamaRunning();

        return response()->json([
            'status' => $isRunning ? 'online' : 'offline',
            'model' => env('OLLAMA_MODEL', 'llama3.2:1b'),
            'host' => env('OLLAMA_HOST', 'http://127.0.0.1:11434'),
            'online' => $isRunning,
        ]);
    }

    // ─── Send Message ─────────────────────────────────────────────────────────

    public function message(Request $request): StreamedResponse
    {
        $request->validate([
            'message' => ['required', 'string', 'max:1000'],
            'session_id' => ['required', 'string', 'max:100'],
        ]);

        $userMessage = trim($request->message);
        $sessionId = $request->session_id;

        // Check FAQ first
        $faqAnswer = $this->faq->findAnswer($userMessage);

        if ($faqAnswer) {
            // Stream FAQ answer word by word
            return $this->streamFaqAnswer($faqAnswer);
        }

        // Stream from Ollama
        return $this->streamOllamaAnswer($sessionId, $userMessage);
    }

    // ─── Get History ──────────────────────────────────────────────────────────

    public function history(string $sessionId): JsonResponse
    {
        $messages = $this->chatbot->getHistory($sessionId);

        // Filter out system messages and return last 10
        $filtered = array_filter($messages, fn($m) => $m['role'] !== 'system');
        $filtered = array_slice(array_values($filtered), -10);

        return response()->json([
            'messages' => $filtered,
        ]);
    }

    // ─── Clear History ────────────────────────────────────────────────────────

    public function clearHistory(string $sessionId): JsonResponse
    {
        $this->chatbot->deleteHistory($sessionId);

        return response()->json(['cleared' => true]);
    }

    // ─── Stream FAQ Answer ────────────────────────────────────────────────────

    private function streamFaqAnswer(string $answer): StreamedResponse
    {
        return response()->stream(function () use ($answer) {
            $words = explode(' ', $answer);

            foreach ($words as $word) {
                $data = json_encode([
                    'delta' => $word . ' ',
                    'done' => false,
                    'source' => 'faq',
                ]);

                echo "data: {$data}\n\n";

                if (ob_get_level() > 0) {
                    ob_flush();
                }
                flush();

                // Natural typing effect — 50ms between words
                usleep(50000);
            }

            // Final done signal
            $done = json_encode(['done' => true, 'source' => 'faq']);
            echo "data: {$done}\n\n";

            if (ob_get_level() > 0) {
                ob_flush();
            }
            flush();

        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no',
            'Connection' => 'keep-alive',
        ]);
    }

    // ─── Stream Ollama Answer ─────────────────────────────────────────────────

    private function streamOllamaAnswer(string $sessionId, string $userMessage): StreamedResponse
    {
        return response()->stream(function () use ($sessionId, $userMessage) {
            $generator = $this->chatbot->streamChat($sessionId, $userMessage);

            foreach ($generator as $chunk) {
                $data = json_encode([
                    'delta' => $chunk,
                    'done' => false,
                    'source' => 'ai',
                ]);

                echo "data: {$data}\n\n";

                if (ob_get_level() > 0) {
                    ob_flush();
                }
                flush();
            }

            // Final done signal
            $done = json_encode(['done' => true, 'source' => 'ai']);
            echo "data: {$done}\n\n";

            if (ob_get_level() > 0) {
                ob_flush();
            }
            flush();

        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no',
            'Connection' => 'keep-alive',
        ]);
    }
}