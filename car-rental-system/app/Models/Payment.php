<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    // ─── Status Constants ─────────────────────────────────────────────────────────

    const STATUS_PENDING = 'pending';
    const STATUS_RECEIPT_UPLOADED = 'receipt_uploaded';
    const STATUS_PAID = 'paid';
    const STATUS_REJECTED = 'rejected';
    const STATUS_REFUNDED = 'refunded';
    const STATUS_FAILED = 'failed';

    // ─── Method Constants ─────────────────────────────────────────────────────────

    const METHOD_BANK_TRANSFER = 'bank_transfer';
    const METHOD_COUNTER = 'counter';

    // ─── Fillable ─────────────────────────────────────────────────────────────────

    protected $fillable = [
        'booking_id',
        'user_id',
        'method',
        'amount',
        'currency',
        'status',
        'transaction_id',
        'invoice_path',
        'receipt_path',
        'bank_reference',
        'rejection_reason',
        'confirmed_by',
        'paid_at',
    ];

    // ─── Casts ────────────────────────────────────────────────────────────────────

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    // ─── Relationships ────────────────────────────────────────────────────────────

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function confirmedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────────

    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    public function isRefunded(): bool
    {
        return $this->status === self::STATUS_REFUNDED;
    }

    public function isReceiptUploaded(): bool
    {
        return $this->status === self::STATUS_RECEIPT_UPLOADED;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function isCounter(): bool
    {
        return $this->method === self::METHOD_COUNTER;
    }

    public function isBankTransfer(): bool
    {
        return $this->method === self::METHOD_BANK_TRANSFER;
    }

    public function markAsPaid(): void
    {
        $this->update([
            'status' => self::STATUS_PAID,
            'paid_at' => now(),
        ]);
    }
}