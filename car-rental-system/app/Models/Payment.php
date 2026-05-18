<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $booking_id
 * @property int $user_id
 * @property string $method
 * @property numeric $amount
 * @property string $currency
 * @property string|null $status
 * @property string|null $transaction_id
 * @property string|null $invoice_path
 * @property string|null $receipt_path
 * @property string|null $bank_reference
 * @property string|null $rejection_reason
 * @property int|null $confirmed_by
 * @property \Illuminate\Support\Carbon|null $paid_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Booking $booking
 * @property-read \App\Models\User|null $confirmedByUser
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereBankReference($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereBookingId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereConfirmedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereInvoicePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment wherePaidAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereReceiptPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereRejectionReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereTransactionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereUserId($value)
 * @mixin \Eloquent
 */
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