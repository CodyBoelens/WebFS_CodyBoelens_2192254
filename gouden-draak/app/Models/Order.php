<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'table_id', 'source', 'status', 'round',
        'customer_name', 'customer_phone', 'total', 'last_order_at',
    ];

    protected $casts = [
        'total'          => 'decimal:2',
        'last_order_at'  => 'datetime',
    ];

    public function table(): BelongsTo
    {
        return $this->belongsTo(Table::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Herbereken en sla het totaalbedrag op.
     */
    public function recalculateTotal(): void
    {
        $this->total = $this->items()->sum(\DB::raw('quantity * unit_price'));
        $this->save();
    }

    /**
     * US-1: mag er een nieuwe ronde besteld worden?
     * Max 5 rondes, en minimaal 10 minuten tussen bestellingen.
     */
    public function canPlaceNewRound(): bool
    {
        if ($this->round >= 5) {
            return false;
        }
        if ($this->last_order_at && $this->last_order_at->diffInMinutes(now()) < 10) {
            return false;
        }
        return true;
    }
}
