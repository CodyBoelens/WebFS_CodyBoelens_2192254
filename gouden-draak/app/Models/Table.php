<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Table extends Model
{
    protected $fillable = ['number', 'seats', 'active'];

    protected $casts = ['active' => 'boolean'];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function helpRequests(): HasMany
    {
        return $this->hasMany(HelpRequest::class);
    }

    public function openOrder(): ?Order
    {
        return $this->orders()->whereIn('status', ['open', 'in_behandeling'])->latest()->first();
    }
}
