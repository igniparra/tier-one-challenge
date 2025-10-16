<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Order Model
 *
 * @author igniparra
 * @description A Client can create Orders with multiple Items
 */
class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'status',
        'total_amount',
    ];

    // This prevents strings from MySQL
    protected $casts = [
        'total_amount' => 'float',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
}
