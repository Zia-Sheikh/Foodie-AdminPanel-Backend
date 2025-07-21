<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RestaurantEarning extends Model
{
    use HasFactory;
    protected $fillable = [
        'id', 'restaurant_id', 'amount','order_id',
    ];
}
