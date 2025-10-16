<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Client (tenant) Model
 *
 * @author igniparra
 * @description A Client (Tennant) of the system
 */
class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
