<?php

namespace App\Services;

use App\Models\PricingRule;
use App\Models\Vehicle;
use Carbon\Carbon;

class PricingCalculator
{
    /**
     * Calculate the rental price for a vehicle between two dates.
     *
     * Priority: seasonal rule (date_from+date_to set) > weekly > daily > hourly > fallback
     *
     * @return array{amount: float, currency: string, days: int, hours: int, breakdown: string, rule_type: string}
     */
    public function calculate(Vehicle $vehicle, Carbon $from, Carbon $to): array
    {
        $hours = (int) ceil($from->diffInHours($to));
        $days = (int) ceil($from->diffInDays($to)) ?: 1;

        // ─── Find Best Rule ───────────────────────────────────────────────────────

        $rule = $this->findBestRule($vehicle, $from, $to);

        if ($rule) {
            return $this->calculateWithRule($rule, $from, $to, $days, $hours);
        }

        // ─── Fallback: no rule found ──────────────────────────────────────────────

        return [
            'amount' => 0.0,
            'currency' => 'AFN',
            'days' => $days,
            'hours' => $hours,
            'breakdown' => 'No pricing rule available for this vehicle.',
            'rule_type' => 'none',
        ];
    }

    /**
     * Find the most specific active pricing rule.
     * Seasonal (has date range) > weekly > daily > hourly
     */
    private function findBestRule(Vehicle $vehicle, Carbon $from, Carbon $to): ?PricingRule
    {
        $dateStr = $from->toDateString();

        $rules = PricingRule::where('vehicle_id', $vehicle->id)
            ->where('is_active', true)
            ->where(function ($q) use ($dateStr) {
                $q->whereNull('date_from')->orWhere('date_from', '<=', $dateStr);
            })
            ->where(function ($q) use ($dateStr) {
                $q->whereNull('date_to')->orWhere('date_to', '>=', $dateStr);
            })
            ->get();

        if ($rules->isEmpty()) {
            return null;
        }

        // Seasonal rule: has both date_from and date_to set — highest priority
        $seasonal = $rules->filter(fn($r) => $r->date_from && $r->date_to)->first();
        if ($seasonal) {
            return $seasonal;
        }

        // Type priority: weekly > daily > hourly
        $typePriority = ['weekly' => 3, 'monthly' => 4, 'daily' => 2, 'hourly' => 1];

        return $rules->sortByDesc(fn($r) => $typePriority[$r->type] ?? 0)->first();
    }

    /**
     * Calculate amount based on a rule.
     */
    private function calculateWithRule(PricingRule $rule, Carbon $from, Carbon $to, int $days, int $hours): array
    {
        $effectiveRate = (float) $rule->base_rate * (float) $rule->multiplier;
        $amount = 0.0;
        $breakdown = '';

        switch ($rule->type) {
            case 'hourly':
                $amount = $effectiveRate * $hours;
                $breakdown = "AFN " . number_format($effectiveRate, 2) . " × {$hours} hours";
                break;

            case 'daily':
                $amount = $effectiveRate * $days;
                $breakdown = "AFN " . number_format($effectiveRate, 2) . " × {$days} days";
                break;

            case 'weekly':
                $weeks = (int) floor($days / 7);
                $remainingDays = $days % 7;
                $weeklyAmount = $effectiveRate * $weeks;

                // Remaining days billed at daily rate (weekly / 7)
                $dailyRate = $effectiveRate / 7;
                $dailyAmount = $dailyRate * $remainingDays;
                $amount = $weeklyAmount + $dailyAmount;

                $breakdown = "AFN " . number_format($effectiveRate, 2) . " × {$weeks} weeks";
                if ($remainingDays > 0) {
                    $breakdown .= " + AFN " . number_format($dailyRate, 2) . " × {$remainingDays} days";
                }
                break;

            case 'monthly':
                $months = (int) floor($days / 30);
                $remainingDays = $days % 30;
                $monthlyAmount = $effectiveRate * $months;

                // Remaining days billed at daily rate (monthly / 30)
                $dailyRate = $effectiveRate / 30;
                $dailyAmount = $dailyRate * $remainingDays;
                $amount = $monthlyAmount + $dailyAmount;

                $breakdown = "AFN " . number_format($effectiveRate, 2) . " × {$months} months";
                if ($remainingDays > 0) {
                    $breakdown .= " + AFN " . number_format($dailyRate, 2) . " × {$remainingDays} days";
                }
                break;
        }

        // Apply multiplier note if not 1.0
        if ((float) $rule->multiplier !== 1.0) {
            $breakdown .= " (×{$rule->multiplier} seasonal multiplier)";
        }

        return [
            'amount' => round($amount, 2),
            'currency' => $rule->currency ?? 'AFN',
            'days' => $days,
            'hours' => $hours,
            'breakdown' => $breakdown,
            'rule_type' => $rule->type,
        ];
    }
}