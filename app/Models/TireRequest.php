<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TireRequest extends Model
{
    protected $fillable = [
        'user_id',
        'phone',
        'width',
        'aspect_ratio',
        'rim_diameter',
        'make',
        'model',
        'year',
        'preference',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
        ];
    }

    public function formattedSize(): string
    {
        return "{$this->width}/{$this->aspect_ratio}R{$this->rim_diameter}";
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
