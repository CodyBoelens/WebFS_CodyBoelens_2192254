<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id', 'product_id', 'quantity', 'unit_price', 'rice_choice', 'note',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
    ];

    // US-11: geldige rijst-keuzes
    public const RICE_CHOICES = [
        'witte_rijst'   => 'Witte rijst',
        'nasi_goreng'   => 'Nasi goreng',
        'bami_goreng'   => 'Bami goreng',
        'mihoen_goreng' => 'Mihoen goreng',
        'chinese_bami'  => 'Chinese bami',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
