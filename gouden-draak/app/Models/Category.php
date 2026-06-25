<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = ['name', 'slug', 'sort_order', 'active'];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class)->orderBy('sort_order');
    }

    public function activeProducts(): HasMany
    {
        return $this->hasMany(Product::class)->where('active', true)->orderBy('sort_order');
    }
}
