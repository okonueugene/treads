<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'is_vendor',
        'shop_name',
        'commission_rate',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_vendor' => 'boolean',
            'commission_rate' => 'decimal:2',
        ];
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'vendor_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'vendor_id');
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function tireRequests(): HasMany
    {
        return $this->hasMany(TireRequest::class);
    }

    /** Reviews left about this vendor (type = 'vendor'). */
    public function vendorReviews(): HasMany
    {
        return $this->hasMany(Review::class, 'vendor_id')->where('type', 'vendor');
    }

    public function averageVendorRating(): ?float
    {
        $avg = $this->vendorReviews()->avg('rating');

        return $avg !== null ? round((float) $avg, 1) : null;
    }

    public function vendorReviewCount(): int
    {
        return $this->vendorReviews()->count();
    }

    public function displayName(): string
    {
        return $this->shop_name ?: $this->name;
    }
}
