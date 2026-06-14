<?php

namespace App\Services;

use Carbon\Carbon;
use Generator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatbotService
{
    private const CACHE_TTL = 3600;
    private const MAX_MESSAGES = 10;
    private const CACHE_PREFIX = 'chatbot:';

    private string $ollamaHost;
    private string $ollamaModel;

    public function __construct()
    {
        $this->ollamaHost = env('OLLAMA_HOST', 'http://127.0.0.1:11434');
        $this->ollamaModel = env('OLLAMA_MODEL', 'llama3.2:3b');
    }

    // ─── System Message ───────────────────────────────────────────────────────

    private function getSystemMessage(): array
    {
        return [
            'role' => 'system',
            'content' => $this->buildSystemPrompt(),
        ];
    }

    // ─── Build System Prompt ──────────────────────────────────────────────────

    private function buildSystemPrompt(): string
    {
        $today = Carbon::now()->format('l, F j, Y');
        $isLoggedIn = auth()->check();

        // ── Guest user — strict prompt, no vehicle info ──
        if (!$isLoggedIn) {
            return 'You are a receptionist for Morwarid Car Rental in Kabul, Afghanistan.' . "\n"
                . 'The visitor is NOT logged in.' . "\n\n"
                . 'RULES:' . "\n"
                . '1. Never mention any car names/models.' . "\n"
                . '2. If asked about vehicles, prices, or availability, say exactly:' . "\n"
                . '   "I can only show vehicle details to registered customers. Please login or create an account to see our full fleet and book a vehicle."' . "\n"
                . '3. You CAN answer registration requirements and general company info.' . "\n\n"
                . 'To register, customer needs: full name, email, phone (optional), password (8+ chars), driver license number + photo, must be 21+.' . "\n\n"
                . 'Company: Dasht-e-Barchi, Kabul, Afghanistan | Phone: +93 730 751 894 | Hours: 8AM-8PM (Sat-Thu).' . "\n\n"
                . 'Be warm and friendly. Default language: English. Reply in Dari if asked in Dari, Pashto if asked in Pashto. Never use Urdu or Arabic.' . "\n"
                . 'Today: ' . $today . "\n";
        }

        // ── Logged in user — full prompt with all vehicle data ──
        $user = auth()->user();
        $userName = $user->name;

        $vehicles = \App\Models\Vehicle::with([
            'category',
            'pricingRules' => fn($q) => $q->where('is_active', true),
        ])
            ->whereNull('deleted_at')
            ->get();

        $bookings = \App\Models\Booking::with('vehicle')
            ->whereIn('status', ['confirmed', 'active', 'pending'])
            ->where('return_date', '>=', now()->startOfDay())
            ->orderBy('pickup_date')
            ->get()
            ->groupBy('vehicle_id');

        $vehicleLines = '';

        foreach ($vehicles as $v) {
            if ($v->status === 'maintenance') {
                continue; // skip maintenance vehicles entirely — never suggest them
            }

            $dailyRule = $v->pricingRules->where('type', 'daily')->first();
            $dailyRate = $dailyRule ? number_format($dailyRule->base_rate) : 'N/A';

            $category = $v->category ? $v->category->name : 'General';

            // Reserved date ranges for this vehicle
            $vehicleBookings = $bookings->get($v->id, collect());
            if ($vehicleBookings->isNotEmpty()) {
                $ranges = $vehicleBookings->map(function ($b) {
                    return $b->pickup_date->format('M d') . '-' . $b->return_date->format('M d');
                })->implode(', ');
            } else {
                $ranges = 'none';
            }

            // Compact single-line format
            $vehicleLines .= sprintf(
                "- %s | %s | %s seats | %s | %s | %s AFN/day | Reserved: %s\n",
                $v->full_name . ' (' . $v->year . ')',
                $category,
                $v->seats,
                ucfirst($v->fuel_type) . '/' . ucfirst($v->transmission),
                $v->license_plate,
                $dailyRate,
                $ranges
            );
        }

        if (empty($vehicleLines)) {
            $vehicleLines = 'No vehicles in fleet.';
        }

        // This customer's own bookings (compact)
        $myBookings = \App\Models\Booking::with('vehicle')
            ->where('customer_id', $user->id)
            ->latest()
            ->take(3)
            ->get();

        $myBookingsInfo = '';
        foreach ($myBookings as $b) {
            $myBookingsInfo .= '- ' . ($b->vehicle ? $b->vehicle->full_name : 'Unknown')
                . ' | ' . $b->pickup_date->format('M d') . '-' . $b->return_date->format('M d, Y')
                . ' | ' . ucfirst($b->status)
                . ' | Ref: ' . $b->reference_code . "\n";
        }

        if (empty($myBookingsInfo)) {
            $myBookingsInfo = 'None.';
        }

        return 'You are an AI assistant for Morwarid Car Rental, Kabul, Afghanistan.' . "\n"
            . 'Customer: ' . $userName . ' (' . $user->email . ')' . "\n"
            . 'Today: ' . $today . "\n\n"

            . '=== CUSTOMER\'S BOOKINGS ===' . "\n"
            . $myBookingsInfo . "\n"

            . '=== COMPANY INFO ===' . "\n"
            . 'Location: Dasht-e-Barchi, Kabul | Phone: +93 730 751 894 | Hours: 8AM-8PM (Sat-Thu)' . "\n"
            . 'Payment: Cash, Bank Transfer, Card (Visa/Mastercard) | Fuel: Full-to-Full' . "\n"
            . 'Free cancellation up to 2h before pickup. Security deposit required at pickup.' . "\n\n"

            . '=== FLEET (each line: Name | Category | Seats | Fuel/Transmission | Plate | Daily Rate | Reserved dates) ===' . "\n"
            . $vehicleLines . "\n"

            . '=== AVAILABILITY RULES ===' . "\n"
            . '- "Reserved: none" = available on ANY date.' . "\n"
            . '- "Reserved: Jun 13-Jun 14" means booked ONLY during that range — available on all other dates.' . "\n"
            . '- For "is X available on [date]" or "which cars are free on [date]", check each vehicle\'s Reserved field against that date. If the date is not inside any reserved range, it IS available.' . "\n"
            . '- Never suggest a vehicle not listed in the fleet above.' . "\n\n"

            . '=== RECOMMENDING A VEHICLE ===' . "\n"
            . 'Ask: passengers count, pickup/return dates, budget (AFN), purpose, fuel/transmission preference.' . "\n"
            . 'Recommend the best match that is free for those dates. Calculate total = daily rate x days.' . "\n\n"

            . '=== BOOKING STEPS ===' . "\n"
            . '1. Go to Vehicles page 2. Click vehicle 3. Select dates 4. Choose payment 5. Confirm Booking.' . "\n"
            . 'Cash: pay within 5h or auto-cancelled. Bank transfer: confirmed in 2-4h. Card: instant.' . "\n\n"

            . 'LANGUAGE: Default English. Reply in Dari if asked in Dari, Pashto if asked in Pashto. Never use Urdu or Arabic.' . "\n";
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
        $systemMessage = $this->getSystemMessage();
        $otherMessages = array_slice($messages, 1);

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

        // Always refresh system message with latest data
        $messages[0] = $this->getSystemMessage();

        $messages[] = [
            'role' => 'user',
            'content' => $userMessage,
        ];

        $fullResponse = '';

        try {
            $response = Http::withOptions(['stream' => true])
                ->timeout(240)
                ->post($this->ollamaHost . '/api/chat', [
                    'model' => $this->ollamaModel,
                    'messages' => $messages,
                    'stream' => true,
                    'keep_alive' => '30m',
                    'options' => [
                        'temperature' => 0.3,
                        'num_predict' => 500,
                        'num_ctx' => 4096,
                    ],
                ]);

            $body = $response->getBody();
            $buffer = '';

            while (!$body->eof()) {
                $chunk = $body->read(1024);
                $buffer .= $chunk;

                while (($pos = strpos($buffer, "\n")) !== false) {
                    $line = substr($buffer, 0, $pos);
                    $buffer = substr($buffer, $pos + 1);
                    $line = trim($line);

                    if (empty($line))
                        continue;

                    $data = json_decode($line, true);

                    if (!is_array($data))
                        continue;

                    $content = $data['message']['content'] ?? '';

                    if (!empty($content)) {
                        $fullResponse .= $content;
                        yield $content;
                    }

                    if (isset($data['done']) && $data['done'] === true) {
                        break 2;
                    }
                }
            }

        } catch (\Exception $e) {
            Log::error('Ollama stream error: ' . $e->getMessage());
            yield 'Sorry, I am having trouble right now. Please try again.';
            return;
        }

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