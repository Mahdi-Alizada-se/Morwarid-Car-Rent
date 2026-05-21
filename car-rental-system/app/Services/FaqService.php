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
                . "💵 **Cash at Counter** — pay in Afghan Afghani (AFN) when you pick up your vehicle.\n"
                . "<svg class="w-5 h-5 inline-block mr-1" viewBox="0 0 496 496" xmlns="http://www.w3.org/2000/svg" fill="#4f46e5">
    <path d="M208,120c0-13.232,10.768-24,24-24h40V80h-40c-22.056,0-40,17.944-40,40h-9.888L0,211.056V256h16v32h16v128H16v32H0v48h400v-48h-16v-32h-16V288h16v-32h16v-44.944L217.888,120H208z M32,432h48v16H32V432z M128,288v128h-16v32H96v-32H80V288h16v-32h16v32H128z M224,288v128h-16v32h-16v-32h-16V288h16v-32h16v32H224z M320,288v128h-16v32h-16v-32h-16V288h16v-32h16v32H320z M368,432v16h-48v-16H368z M336,416V288h16v128H336z M272,272h-48v-16h48V272z M256,288v128h-16V288H256z M272,432v16h-48v-16H272z M176,272h-48v-16h48V272z M160,288v128h-16V288H160z M176,432v16h-48v-16H176z M80,272H32v-16h48V272z M64,288v128H48V288H64z M384,464v16H16v-16H384z M368,272h-48v-16h48V272z M384,240h-80h-16h-80h-16h-80H96H16v-19.056L185.888,136H192v56h16v-56h6.112L384,220.944V240z"/>
    <rect x="64" y="208" width="272" height="16"/>
    <path d="M408,0c-48.52,0-88,39.48-88,88s39.48,88,88,88c48.52,0,88-39.48,88-88S456.52,0,408,0z M408,160c-39.704,0-72-32.296-72-72s32.296-72,72-72c39.704,0,72,32.296,72,72S447.704,160,408,160z"/>
    <path d="M400,64h16c4.416,0,8,3.584,8,8h16c0-13.232-10.768-24-24-24V32h-16v16c-13.232,0-24,10.768-24,24s10.768,24,24,24h16c4.416,0,8,3.584,8,8s-3.584,8-8,8h-16c-4.416,0-8-3.584-8-8h-16c0,13.232,10.768,24,24,24v16h16v-16c13.232,0,24-10.768,24-24s-10.768-24-24-24h-16c-4.416,0-8-3.584-8-8S395.584,64,400,64z"/>
    <rect x="288" y="80" width="16" height="16"/>
    <path d="M448,312c0,13.232-10.768,24-24,24h-8v16h8c22.056,0,40-17.944,40-40V184h-16V312z"/>
    <rect x="384" y="336" width="16" height="16"/>
</svg> **Bank Transfer** — transfer the amount to our bank account and upload your receipt. "
                . "We confirm within 2-4 hours.\n\n"
                . "We do not currently accept credit cards or online payment gateways.",
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