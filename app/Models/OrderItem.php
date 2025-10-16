<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * OrderItem Model
 *
 * @author igniparra
 * @description Item belonging to an Order
 */
class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'name',
        'quantity',
        'unit_price',
        'line_total',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
