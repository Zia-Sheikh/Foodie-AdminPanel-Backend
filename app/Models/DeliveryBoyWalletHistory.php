<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryBoyWalletHistory extends Model
{
    use HasFactory;
    protected $fillable = [
        'id', 'delivery_boy_id', 'amount','type','message','amount',
    ];
}
