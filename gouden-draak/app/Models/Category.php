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
        return $this->hasMany(Product::class)
            ->orderByRaw("CAST(menu_number AS INTEGER), LENGTH(menu_number), menu_number");
    }

    public function activeProducts(): HasMany
    {
        return $this->hasMany(Product::class)
            ->where('active', true)
            ->orderByRaw("CAST(menu_number AS INTEGER), LENGTH(menu_number), menu_number");
    }
}
