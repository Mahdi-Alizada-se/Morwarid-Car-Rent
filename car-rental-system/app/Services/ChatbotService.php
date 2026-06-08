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
    private const MAX_MESSAGES = 18;
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
            'content' => $this->buildSystemPrompt(),
        ];
    }

    // ─── Build System Prompt ──────────────────────────────────────────────────

    private function buildSystemPrompt(): string
    {
        $today = Carbon::now()->format('l, F j Y');
        $isLoggedIn = auth()->check();

        // ── Guest user — strict prompt, no vehicle info ──
        if (!$isLoggedIn) {
            return 'You are a receptionist for Morwarid Car Rental in Kabul, Afghanistan.' . "\n"
                . 'The visitor is NOT logged in to the system.' . "\n\n"

                . 'STRICT RULES — follow exactly:' . "\n"
                . '1. You do NOT know which vehicles are available. NEVER mention any car names or models.' . "\n"
                . '2. NEVER invent, guess, or suggest any vehicle names or brands.' . "\n"
                . '3. If asked about vehicles, prices, or availability, say exactly:' . "\n"
                . '   "I can only show vehicle details to registered customers. Please login or create an account to see our full fleet and book a vehicle."' . "\n"
                . '4. You CAN answer questions about registration requirements.' . "\n"
                . '5. You CAN answer general questions about the company location, phone, hours.' . "\n\n"

                . 'To create an account, the customer needs:' . "\n"
                . '- Full name' . "\n"
                . '- Email address' . "\n"
                . '- Phone number (optional)' . "\n"
                . '- Password (minimum 8 characters)' . "\n"
                . '- Driver\'s License Number' . "\n"
                . '- Driver\'s License Photo (JPG, PNG or PDF, max 5MB)' . "\n"
                . '- Must be at least 21 years old' . "\n\n"

                . 'Company info:' . "\n"
                . '- Location: Dasht-e-Barchi, Kabul, Afghanistan' . "\n"
                . '- Phone: +93 730 751 894' . "\n"
                . '- Working hours: 8:00 AM - 8:00 PM (Saturday to Thursday)' . "\n\n"

                . 'Always guide them to login or register at the website.' . "\n"
                . 'Be warm and friendly.' . "\n"
                . 'Default language: English.' . "\n"
                . 'If customer writes in Dari respond in Dari.' . "\n"
                . 'If customer writes in Pashto respond in Pashto.' . "\n"
                . 'NEVER respond in Urdu or Arabic.' . "\n"
                . 'Today is: ' . $today . "\n";
        }

        // ── Logged in user — full prompt with all vehicle data ──
        $user = auth()->user();
        $userName = $user->name;

        // Load all vehicles
        $vehicles = \App\Models\Vehicle::with([
            'category',
            'pricingRules' => fn($q) => $q->where('is_active', true),
        ])
            ->whereNull('deleted_at')
            ->get();

        $availableVehicles = '';
        $bookedVehicles = '';
        $maintenanceVehicles = '';

        foreach ($vehicles as $v) {
            $dailyRule = $v->pricingRules->where('type', 'daily')->first();
            $weeklyRule = $v->pricingRules->where('type', 'weekly')->first();
            $monthlyRule = $v->pricingRules->where('type', 'monthly')->first();

            $dailyRate = $dailyRule ? number_format($dailyRule->base_rate) . ' AFN/day' : 'N/A';
            $weeklyRate = $weeklyRule ? number_format($weeklyRule->base_rate) . ' AFN/week' : '';
            $monthlyRate = $monthlyRule ? number_format($monthlyRule->base_rate) . ' AFN/month' : '';

            $pricing = $dailyRate;
            if ($weeklyRate)
                $pricing .= ' | ' . $weeklyRate;
            if ($monthlyRate)
                $pricing .= ' | ' . $monthlyRate;

            $category = $v->category ? $v->category->name : 'General';

            $line = '• ' . $v->full_name . ' (' . $v->year . ')' . "\n"
                . '  - Category: ' . $category . "\n"
                . '  - Color: ' . $v->color . "\n"
                . '  - Seats: ' . $v->seats . "\n"
                . '  - Fuel: ' . ucfirst($v->fuel_type) . "\n"
                . '  - Transmission: ' . ucfirst($v->transmission) . "\n"
                . '  - Pricing: ' . $pricing . "\n"
                . '  - Plate: ' . $v->license_plate . "\n";

            if ($v->features && count($v->features) > 0) {
                $line .= '  - Features: ' . implode(', ', $v->features) . "\n";
            }

            if ($v->description) {
                $line .= '  - Description: ' . $v->description . "\n";
            }

            if ($v->status === 'available') {
                $availableVehicles .= $line . "\n";
            } elseif (in_array($v->status, ['booked', 'active'])) {
                $bookedVehicles .= $line . "\n";
            } else {
                $maintenanceVehicles .= $line . "\n";
            }
        }

        if (empty($availableVehicles))
            $availableVehicles = 'None currently available.';
        if (empty($bookedVehicles))
            $bookedVehicles = 'None currently booked.';
        if (empty($maintenanceVehicles))
            $maintenanceVehicles = 'None under maintenance.';

        // Load upcoming bookings
        $bookings = \App\Models\Booking::with('vehicle')
            ->whereIn('status', ['confirmed', 'active', 'pending'])
            ->where('return_date', '>=', now())
            ->get();

        $bookedDatesInfo = '';
        foreach ($bookings as $b) {
            if ($b->vehicle) {
                $bookedDatesInfo .= '• ' . $b->vehicle->full_name
                    . ': booked from ' . $b->pickup_date->format('M d, Y')
                    . ' to ' . $b->return_date->format('M d, Y') . "\n";
            }
        }

        if (empty($bookedDatesInfo)) {
            $bookedDatesInfo = 'No upcoming bookings — all available vehicles are free.';
        }

        // Load this customer's own bookings
        $myBookings = \App\Models\Booking::with('vehicle')
            ->where('customer_id', $user->id)
            ->latest()
            ->take(5)
            ->get();

        $myBookingsInfo = '';
        foreach ($myBookings as $b) {
            $myBookingsInfo .= '• ' . ($b->vehicle ? $b->vehicle->full_name : 'Unknown')
                . ' | ' . $b->pickup_date->format('M d, Y')
                . ' to ' . $b->return_date->format('M d, Y')
                . ' | Status: ' . ucfirst($b->status)
                . ' | Reference: ' . $b->reference_code . "\n";
        }

        if (empty($myBookingsInfo)) {
            $myBookingsInfo = 'No bookings yet.';
        }

        return 'You are an intelligent AI assistant for Morwarid Car Rental, Kabul, Afghanistan.' . "\n"
            . 'You are talking to a LOGGED IN customer named: ' . $userName . "\n"
            . 'Help them choose the best vehicle, check availability, and guide them through booking.' . "\n\n"

            . '=== CUSTOMER INFORMATION ===' . "\n"
            . '- Name: ' . $userName . "\n"
            . '- Email: ' . $user->email . "\n\n"

            . '=== CUSTOMER\'S OWN BOOKINGS ===' . "\n"
            . $myBookingsInfo . "\n\n"

            . '=== COMPANY INFORMATION ===' . "\n"
            . '- Name: Morwarid Car Rental' . "\n"
            . '- Location: Dasht-e-Barchi, Kabul, Afghanistan' . "\n"
            . '- Phone: +93 730 751 894' . "\n"
            . '- Working hours: 8:00 AM - 8:00 PM (Saturday to Thursday)' . "\n"
            . '- Payment: Cash, Bank Transfer, Mastercard' . "\n"
            . '- Fuel policy: Full to Full' . "\n"
            . '- Free cancellation: up to 2 hours before pickup' . "\n"
            . '- Security deposit: required at pickup (refundable)' . "\n\n"

            . '=== AVAILABLE VEHICLES (ONLY suggest these) ===' . "\n"
            . $availableVehicles . "\n"

            . '=== BOOKED VEHICLES (NOT available — do NOT suggest these) ===' . "\n"
            . $bookedVehicles . "\n"

            . '=== VEHICLES UNDER MAINTENANCE (NOT available — do NOT suggest these) ===' . "\n"
            . $maintenanceVehicles . "\n"

            . '=== UPCOMING BOOKED DATE RANGES ===' . "\n"
            . $bookedDatesInfo . "\n\n"

            . '=== HOW TO RECOMMEND A VEHICLE ===' . "\n"
            . 'Ask the customer:' . "\n"
            . '1. How many passengers?' . "\n"
            . '2. What pickup and return dates?' . "\n"
            . '3. What is their budget in AFN?' . "\n"
            . '4. Purpose? (city driving, off-road, family trip, luxury)' . "\n"
            . '5. Fuel and transmission preference?' . "\n\n"
            . 'Then recommend the BEST matching vehicle FROM THE AVAILABLE LIST ABOVE ONLY.' . "\n"
            . 'Calculate the exact total cost: daily rate x number of days.' . "\n"
            . 'NEVER mention any vehicle not in the AVAILABLE VEHICLES list.' . "\n"
            . 'NEVER invent or guess vehicle names or models.' . "\n"
            . 'NEVER recommend a booked or maintenance vehicle.' . "\n\n"

            . '=== BOOKING PROCESS ===' . "\n"
            . '1. Go to Vehicles page on the website' . "\n"
            . '2. Click on the vehicle' . "\n"
            . '3. Select pickup and return dates' . "\n"
            . '4. Choose payment method' . "\n"
            . '5. Click Confirm Booking' . "\n"
            . '6. Cash: pay within 5 hours or booking is auto-cancelled' . "\n"
            . '7. Bank Transfer: admin confirms within 2-4 hours' . "\n"
            . '8. Mastercard: instantly confirmed' . "\n\n"

            . '=== LANGUAGE RULES ===' . "\n"
            . '- Default: English' . "\n"
            . '- If customer writes in Dari: respond in Dari' . "\n"
            . '- If customer writes in Pashto: respond in Pashto' . "\n"
            . '- NEVER respond in Urdu or Arabic' . "\n\n"

            . 'Today is: ' . $today . "\n";
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
                ->timeout(120)
                ->post($this->ollamaHost . '/api/chat', [
                    'model' => $this->ollamaModel,
                    'messages' => $messages,
                    'stream' => true,
                    'options' => [
                        'temperature' => 0.4,
                        'num_predict' => 1024,
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