<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesReport extends Model
{
    protected $fillable = ['report_date', 'total_revenue', 'total_orders', 'file_path'];

    protected $casts = [
        'report_date'   => 'date',
        'total_revenue' => 'decimal:2',
    ];
}
