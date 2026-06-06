<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Address extends Model
{
    protected $fillable = [
        'user_id',
        'label',
        'name',
        'phone',
        'county',
        'town',
        'address',
        'landmark',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function formatted(): string
    {
        $parts = array_filter([
            $this->address,
            $this->landmark,
            $this->town,
            $this->county,
        ]);

        return implode(', ', $parts);
    }
}
