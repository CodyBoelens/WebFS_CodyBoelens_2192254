<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HelpRequest extends Model
{
    protected $fillable = ['table_id', 'status', 'handled_by', 'handled_at'];

    protected $casts = [
        'handled_at' => 'datetime',
    ];

    public function table(): BelongsTo
    {
        return $this->belongsTo(Table::class);
    }

    public function handledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }
}
