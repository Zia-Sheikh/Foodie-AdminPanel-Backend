<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderRating extends Model
{
    use HasFactory;
    protected $fillable = [
        'id', 'order_id', 'customer_id','review','rating','restaurant_id'
    ];
}
