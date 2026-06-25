<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'category_id', 'menu_number', 'name', 'description',
        'price', 'image', 'allergens', 'active', 'sort_order',
    ];

    protected $casts = [
        'price'     => 'decimal:2',
        'allergens' => 'array',
        'active'    => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function promotions(): BelongsToMany
    {
        return $this->belongsToMany(Promotion::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Geeft de huidige actieve prijs terug (rekening houdend met aanbiedingen).
     */
    public function getCurrentPriceAttribute(): float
    {
        $activePromo = $this->promotions()
            ->where('active', true)
            ->where('valid_from', '<=', now()->toDateString())
            ->where('valid_until', '>=', now()->toDateString())
            ->first();

        if (! $activePromo) {
            return (float) $this->price;
        }

        if ($activePromo->discount_percent) {
            return round((float) $this->price * (1 - $activePromo->discount_percent / 100), 2);
        }

        if ($activePromo->discount_amount) {
            return max(0, (float) $this->price - (float) $activePromo->discount_amount);
        }

        return (float) $this->price;
    }
}
