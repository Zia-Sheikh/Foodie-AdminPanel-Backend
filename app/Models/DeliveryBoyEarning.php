<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryBoyEarning extends Model
{
    use HasFactory;
    protected $fillable = [
        'id', 'order_id', 'delivery_boy_id','amount','created_at','updated_at'
    ];
}

