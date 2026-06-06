<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    /** @use HasFactory<\Database\Factories\OrderFactory> */
    use HasFactory;

    public const STATUSES = ['pending', 'paid', 'processing', 'shipped', 'delivered', 'cancelled'];

    protected $fillable = [
        'user_id',
        'order_number',
        'status',
        'delivery_method',
        'subtotal',
        'tax',
        'shipping',
        'total',
        'shipping_name',
        'shipping_address',
        'shipping_county',
        'shipping_town',
        'shipping_landmark',
        'shipping_city',
        'shipping_state',
        'shipping_zip',
        'shipping_phone',
        'notes',
        'delivered_at',
        'receipt_confirmed_at',
        'payment_status',
        'stripe_session_id',
        'payment_snapshot',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'tax' => 'decimal:2',
            'shipping' => 'decimal:2',
            'total' => 'decimal:2',
            'payment_snapshot' => 'array',
            'delivered_at' => 'datetime',
            'receipt_confirmed_at' => 'datetime',
        ];
    }

    public static function generateOrderNumber(): string
    {
        $year = now()->year;
        $sequence = static::query()
            ->whereYear('created_at', $year)
            ->count() + 1;

        return sprintf('ORD-%d-%05d', $year, $sequence);
    }

    public function isPaid(): bool
    {
        return $this->payment_status === 'paid' || $this->status === 'paid';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /** @return array<int, string> */
    public function timelineSteps(): array
    {
        $steps = ['pending' => 'Pending', 'paid' => 'Paid', 'processing' => 'Processing', 'shipped' => 'Shipped', 'delivered' => 'Delivered'];
        $currentIndex = array_search($this->status, array_keys($steps), true);

        if ($currentIndex === false) {
            $currentIndex = 0;
        }

        return collect($steps)->map(function (string $label, string $key) use ($currentIndex, $steps) {
            $index = array_search($key, array_keys($steps), true);

            return [
                'key' => $key,
                'label' => $label,
                'complete' => $index !== false && $index <= $currentIndex,
                'current' => $key === $this->status,
            ];
        })->values()->all();
    }
}
