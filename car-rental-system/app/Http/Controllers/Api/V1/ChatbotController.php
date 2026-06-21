<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\ChatbotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ChatbotController extends Controller
{
    public function __construct(
        private ChatbotService $chatbot,
    ) {
    }

    // ─── Health Check ─────────────────────────────────────────────────────────

    public function health(): JsonResponse
    {
        $isRunning = $this->chatbot->isOllamaRunning();

        return response()->json([
            'status' => $isRunning ? 'online' : 'offline',
            'model' => env('OLLAMA_MODEL', 'llama3.2:3b'),
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

        return $this->streamOllamaAnswer($sessionId, $userMessage);
    }

    // ─── Get History ──────────────────────────────────────────────────────────

    public function history(string $sessionId): JsonResponse
    {
        $messages = $this->chatbot->getHistory($sessionId);

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

    // ─── Stream Ollama Answer ─────────────────────────────────────────────────

    private function streamOllamaAnswer(string $sessionId, string $userMessage): StreamedResponse
    {
        return response()->stream(function () use ($sessionId, $userMessage) {

            // Prevent output buffering issues on first message
            while (ob_get_level() > 0) {
                ob_end_clean();
            }

            $generator = $this->chatbot->streamChat($sessionId, $userMessage);

            foreach ($generator as $chunk) {
                $data = json_encode([
                    'delta' => $chunk,
                    'done' => false,
                    'source' => 'ai',
                ]);

                echo "data: {$data}\n\n";
                flush();
            }

            // Final done signal
            $done = json_encode(['done' => true, 'source' => 'ai']);
            echo "data: {$done}\n\n";
            flush();

        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'X-Accel-Buffering' => 'no',
            'Connection' => 'keep-alive',
            'Pragma' => 'no-cache',
        ]);
    }
}