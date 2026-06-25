<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Promotion extends Model
{
    protected $fillable = [
        'name', 'description', 'discount_amount', 'discount_percent',
        'valid_from', 'valid_until', 'active',
    ];

    protected $casts = [
        'discount_amount'  => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'valid_from'       => 'date',
        'valid_until'      => 'date',
        'active'           => 'boolean',
    ];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class);
    }

    public function scopeActive($query)
    {
        return $query->where('active', true)
            ->where('valid_from', '<=', now()->toDateString())
            ->where('valid_until', '>=', now()->toDateString());
    }
}
