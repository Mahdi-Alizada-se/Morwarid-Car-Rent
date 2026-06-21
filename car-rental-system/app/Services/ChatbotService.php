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
        $uiLocale = app()->getLocale();

        // ── Language instruction in English so model understands it reliably ──
        $languageInstruction = match ($uiLocale) {
            'fa' => 'LANGUAGE RULE (MANDATORY): You MUST respond ONLY in Dari Persian language. '
            . 'Dari Persian is spoken in Afghanistan and uses words like: سلام، چطور، می‌توانم، موتر، کرایه، امروز، ممنون. '
            . 'Do NOT use Pashto words like: ښه راغلاست، تاسو، ځواب، کارونکی، ددې. '
            . 'Do NOT respond in English, Pashto, Urdu, or Arabic. '
            . 'ONLY Dari Persian (دری). '
            . 'Correct Dari example: "سلام! چطور می‌توانم کمک کنم؟" '
            . 'WRONG (Pashto - forbidden): "سلام، تاسو ته ښه راغلاست"',

            'ps' => 'LANGUAGE RULE (MANDATORY): You MUST respond ONLY in Pashto language. '
            . 'Pashto is spoken in Afghanistan/Pakistan and uses words like: ښه راغلاست، تاسو، کولی شم، موټر، کرایه. '
            . 'Do NOT respond in Dari, English, Urdu, or Arabic. '
            . 'ONLY Pashto (پښتو). '
            . 'Correct Pashto example: "سلام! تاسو ته ښه راغلاست، زه څنګه مرسته کولی شم؟"',

            default => 'LANGUAGE RULE: Respond in English by default. '
            . 'If the customer writes in Dari Persian, respond in Dari. '
            . 'If the customer writes in Pashto, respond in Pashto. '
            . 'Never use Urdu or Arabic.',
        };

        $isLoggedIn = auth()->check();

        // ── Guest prompt ──────────────────────────────────────────────────────
        if (!$isLoggedIn) {
            return $languageInstruction . "\n\n"
                . 'You are a receptionist for Morwarid Car Rental in Kabul, Afghanistan.' . "\n"
                . 'The visitor is NOT logged in.' . "\n\n"
                . 'RULES:' . "\n"
                . '1. Never mention any car names/models.' . "\n"
                . '2. If asked about vehicles, prices, or availability say:' . "\n"
                . ($uiLocale === 'fa'
                    ? '   "من فقط می‌توانم جزئیات موترها را به مشتریان ثبت‌نام شده نشان دهم. لطفاً وارد شوید یا حساب کاربری بسازید."' . "\n"
                    : '   "I can only show vehicle details to registered customers. Please login or create an account."' . "\n")
                . '3. You CAN answer registration requirements and general company info.' . "\n\n"
                . 'To register: full name, email, phone (optional), password 8+ chars, driver license number + photo, must be 21+.' . "\n\n"
                . 'Company: Dasht-e-Barchi, Kabul | Phone: +93 730 751 894 | Hours: 8AM-8PM Sat-Thu.' . "\n"
                . 'Today: ' . $today . "\n";
        }

        // ── Logged-in user prompt ─────────────────────────────────────────────
        $user = auth()->user();
        $userName = $user->name;

        $vehicles = \App\Models\Vehicle::with([
            'category',
            'pricingRules' => fn($q) => $q->where('is_active', true),
        ])
            ->whereNull('deleted_at')
            ->where('status', '!=', 'maintenance')
            ->get();

        $allBookings = \App\Models\Booking::whereIn('status', ['confirmed', 'active', 'pending'])
            ->where('return_date', '>=', now()->startOfDay())
            ->orderBy('pickup_date')
            ->get()
            ->groupBy('vehicle_id');

        $vehicleData = '';
        foreach ($vehicles as $v) {
            $dailyRule = $v->pricingRules->where('type', 'daily')->first();
            $dailyRate = $dailyRule ? number_format($dailyRule->base_rate) : '0';
            $category = $v->category ? $v->category->name : 'General';
            $fullName = $v->full_name;

            $vehicleBookings = $allBookings->get($v->id, collect());
            $ranges = [];
            foreach ($vehicleBookings as $b) {
                $ranges[] = $b->pickup_date->format('Y-m-d')
                    . ' to '
                    . $b->return_date->format('Y-m-d');
            }

            $rangesText = empty($ranges) ? 'none' : implode(', ', $ranges);
            $vehicleData .= '[' . $fullName . ']'
                . ' category=' . $category
                . ' seats=' . $v->seats
                . ' fuel=' . $v->fuel_type
                . ' transmission=' . $v->transmission
                . ' price=' . $dailyRate . 'AFN/day'
                . ' booked_dates=' . $rangesText
                . "\n";
        }

        $myBookings = \App\Models\Booking::with('vehicle')
            ->where('customer_id', $user->id)
            ->latest()
            ->take(3)
            ->get();

        $myBookingsText = '';
        foreach ($myBookings as $b) {
            $myBookingsText .= '- ' . ($b->vehicle?->full_name ?? 'Unknown')
                . ' from ' . $b->pickup_date->format('M d, Y')
                . ' to ' . $b->return_date->format('M d, Y')
                . ' [' . $b->status . ']' . "\n";
        }
        if (empty($myBookingsText)) {
            $myBookingsText = 'No bookings yet.' . "\n";
        }

        $companyInfo = $uiLocale === 'fa'
            ? 'موقعیت: دشت برچی، کابل | تلفن: 894 751 730 93+ | ساعات: ۸ صبح تا ۸ شب، شنبه تا پنجشنبه' . "\n"
            . 'پرداخت: نقدی (ظرف ۵ ساعت)، انتقال بانکی (۲-۴ ساعت)، کارت/ویزا (فوری)' . "\n"
            . 'سیاست سوخت: پر به پر | لغو رایگان تا ۲ ساعت قبل از تحویل | ودیعه امنیتی الزامی'
            : 'Location: Dasht-e-Barchi, Kabul | Phone: +93 730 751 894 | Hours: 8AM-8PM Sat-Thu' . "\n"
            . 'Payment: Cash (pay within 5h), Bank Transfer (confirmed 2-4h), Card/Visa/Mastercard (instant)' . "\n"
            . 'Fuel policy: Full-to-Full | Free cancellation up to 2h before pickup | Security deposit required';

        return $languageInstruction . "\n\n"
            . 'You are an AI assistant for Morwarid Car Rental, Kabul, Afghanistan.' . "\n"
            . 'Customer name: ' . $userName . "\n"
            . 'Customer email: ' . $user->email . "\n"
            . 'Today: ' . $today . "\n\n"

            . '== COMPANY INFO ==' . "\n"
            . $companyInfo . "\n\n"

            . '== CUSTOMER BOOKINGS ==' . "\n"
            . $myBookingsText . "\n"

            . '== VEHICLE FLEET ==' . "\n"
            . 'Format: [Full Name] category seats fuel transmission price booked_dates' . "\n"
            . 'booked_dates shows when the vehicle is NOT available (YYYY-MM-DD to YYYY-MM-DD).' . "\n"
            . 'If booked_dates=none, the vehicle is free on any date.' . "\n\n"
            . $vehicleData . "\n"

            . '== AVAILABILITY RULE ==' . "\n"
            . 'AVAILABLE on date D = D does NOT fall within any booked_dates range.' . "\n"
            . 'NOT AVAILABLE on date D = D falls within a booked_dates range (inclusive).' . "\n"
            . 'Example: booked_dates=2026-06-15 to 2026-06-20 → NOT available Jun15-20. Available Jun21+.' . "\n\n"

            . '== RESPONSE RULES ==' . "\n"
            . '- Be friendly and concise.' . "\n"
            . '- Never show booked_dates, YYYY-MM-DD, or category= labels to customer.' . "\n"
            . '- Always write vehicle names exactly as given in [brackets].' . "\n"
            . '- Only calculate total cost when customer gives both pickup AND return dates.' . "\n"
            . '- NEVER use Urdu or Arabic script.' . "\n";
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

        // Always refresh system message with latest data and current locale
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
                        'num_predict' => 400,
                        'num_ctx' => 3072,
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
            $errorMsg = app()->getLocale() === 'fa'
                ? 'متأسفم، مشکلی پیش آمد. لطفاً دوباره تلاش کنید.'
                : 'Sorry, I am having trouble right now. Please try again.';
            yield $errorMsg;
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