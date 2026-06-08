<?php

namespace App\Services;

class FaqService
{
    /**
     * Hardcoded FAQ array for Morwarid Car Rental.
     * Each entry has keywords and a full answer.
     */
    private array $faqs = [
        [
            'keywords' => ['document', 'documents', 'need', 'require', 'id', 'passport', 'license', 'licence', 'paper'],
            'answer' => "To rent a vehicle from Morwarid Car Rental, you need the following documents:\n\n"
                . "1. **National ID or Passport** — valid government-issued photo ID\n"
                . "2. **Driver's License** — valid driving license (held for at least 1 year)\n"
                . "3. **Security Deposit** — a refundable deposit is required at pickup\n\n"
                . "All documents must be originals. Photocopies are not accepted.",
        ],
        [
            'keywords' => ['cancel', 'cancellation', 'refund', 'policy', 'free cancel'],
            'answer' => "Our cancellation policy at Morwarid Car Rental:\n\n"
                . "✅ **Free cancellation** if cancelled at least **2 hours before** your scheduled pickup time.\n"
                . "❌ **No refund** for cancellations made less than 2 hours before pickup.\n"
                . "❌ **No refund** for no-shows.\n\n"
                . "To cancel, log in to your account and go to My Bookings, or contact us directly.",
        ],
        [
            'keywords' => ['payment', 'pay', 'cash', 'bank', 'transfer', 'method', 'how to pay', 'stripe', 'online'],
            'answer' => "Morwarid Car Rental accepts the following payment methods:\n\n"
                . "💵 **Cash at Counter** — pay in Afghan Afghani (AFN) when you pick up the vehicle.\n\n"
                . "🏦 **Bank Transfer** — transfer the amount to our bank account and upload your receipt. "
                . "We confirm within 2-4 hours.\n\n"
                . "💳 **Mastercard** — pay with your Mastercard card online.\n\n"
                . "We do not currently accept other online payment gateways.",
        ],
        [
            'keywords' => ['breakdown', 'broke', 'broken', 'accident', 'problem', 'stuck', 'emergency', 'support number'],
            'answer' => "In case of a vehicle breakdown or emergency:\n\n"
                . "📞 **Call our 24/7 support line immediately**: +93 700 000 000\n\n"
                . "Our team will:\n"
                . "• Send roadside assistance as soon as possible\n"
                . "• Provide a **replacement vehicle within 2 hours** in Kabul city\n"
                . "• Handle all paperwork for insurance claims\n\n"
                . "Do not leave the vehicle unattended in case of an accident.",
        ],
        [
            'keywords' => ['extend', 'extension', 'longer', 'more days', 'keep longer', 'late return'],
            'answer' => "To extend your rental period:\n\n"
                . "📞 Contact us **at least 4 hours before** your scheduled return time.\n"
                . "• Call: +93 700 000 000\n"
                . "• Or message us through this chat\n\n"
                . "Extensions are subject to vehicle availability. "
                . "Additional charges will apply at the same daily rate. "
                . "Late returns without prior notice may incur extra fees.",
        ],
        [
            'keywords' => ['age', 'minimum age', 'how old', 'young', 'years old', '21'],
            'answer' => "**Minimum age requirement** at Morwarid Car Rental:\n\n"
                . "🎂 You must be at least **21 years old** to rent a vehicle.\n"
                . "🪪 You must have held a valid driver's license for **at least 1 year**.\n\n"
                . "Drivers under 25 may be required to pay a young driver surcharge for certain vehicle categories.",
        ],
        [
            'keywords' => ['insurance', 'insured', 'coverage', 'cover', 'damage', 'accident cover'],
            'answer' => "**Insurance coverage** at Morwarid Car Rental:\n\n"
                . "✅ **Basic insurance is included** with every rental at no extra cost.\n"
                . "🛡️ **Full coverage** (comprehensive) is available for an additional daily fee.\n\n"
                . "Basic coverage includes:\n"
                . "• Third-party liability\n"
                . "• Theft protection (with conditions)\n\n"
                . "Full coverage additionally includes:\n"
                . "• Collision damage waiver\n"
                . "• Zero excess option\n\n"
                . "Please ask our staff for full coverage details at pickup.",
        ],
        [
            'keywords' => ['airport', 'pickup', 'delivery', 'drop off', 'dropoff', 'location', 'bring car', 'deliver'],
            'answer' => "**Airport pickup and delivery** service:\n\n"
                . "✅ Yes! Morwarid Car Rental offers airport pickup and drop-off service.\n\n"
                . "• Available at **Hamid Karzai International Airport**, Kabul\n"
                . "• An **additional fee** applies for this service\n"
                . "• Please request this when making your booking\n"
                . "• Our driver will meet you at the arrivals terminal\n\n"
                . "Contact us at least 24 hours in advance to arrange airport service.",
        ],
        [
            'keywords' => ['booking status', 'check booking', 'my booking', 'reference', 'reference code', 'reservation'],
            'answer' => "**How to check your booking status:**\n\n"
                . "1. **Log in** to your account at our website\n"
                . "2. Go to **My Bookings** from the menu\n"
                . "3. You will see all your bookings with their current status\n\n"
                . "Alternatively:\n"
                . "• Share your **booking reference code** (e.g. CR-20240101-ABCDE) with our staff\n"
                . "• Call us at +93 700 000 000\n\n"
                . "Booking statuses: Pending → Confirmed → Active → Completed",
        ],
        [
            'keywords' => ['fuel', 'petrol', 'gas', 'gasoline', 'tank', 'fill up', 'return fuel'],
            'answer' => "**Fuel policy** at Morwarid Car Rental:\n\n"
                . "⛽ Vehicles are provided with a **full tank of fuel**.\n"
                . "⛽ You must **return the vehicle with a full tank**.\n\n"
                . "If the vehicle is returned with less fuel:\n"
                . "• We will charge the cost of refueling at current market rates\n"
                . "• Plus a small refueling service fee\n\n"
                . "We recommend filling up at a petrol station near our office before returning.",
        ],
    ];

    /**
     * Find an answer by matching keywords in the question.
     * Returns null if no match found.
     */
    public function findAnswer(string $question): ?string
    {
        $question = strtolower(trim($question));

        foreach ($this->faqs as $faq) {
            foreach ($faq['keywords'] as $keyword) {
                if (str_contains($question, strtolower($keyword))) {
                    return $faq['answer'];
                }
            }
        }

        return null;
    }

    /**
     * Get all FAQs for display purposes.
     */
    public function getAllFaqs(): array
    {
        return $this->faqs;
    }
}