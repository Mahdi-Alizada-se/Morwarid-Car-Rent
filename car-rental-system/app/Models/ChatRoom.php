<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property int $customer_id
 * @property \Illuminate\Support\Carbon|null $last_message_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $customer
 * @property-read \App\Models\Message|null $latestMessage
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Message> $messages
 * @property-read int|null $messages_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatRoom newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatRoom newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatRoom query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatRoom whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatRoom whereCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatRoom whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatRoom whereLastMessageAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChatRoom whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ChatRoom extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'last_message_at',
    ];

    protected function casts(): array
    {
        return [
            'last_message_at' => 'datetime',
        ];
    }

    // ─── Relationships ───────────────────────────────────────────────────────────

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->orderBy('created_at');
    }

    public function latestMessage(): HasOne
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────────

    public function getUnreadCountFor(int $userId): int
    {
        return $this->messages()
            ->where('sender_id', '!=', $userId)
            ->where('is_read', false)
            ->count();
    }

    public function touchLastMessage(): void
    {
        $this->update(['last_message_at' => now()]);
    }
}