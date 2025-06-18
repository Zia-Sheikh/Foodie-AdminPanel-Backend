<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    protected $fillable = [
        'id', 'customer_id', 'restaurant_id','address_id','total','sub_total','discount','tax','promo_id','delivery_charge','delivery_instruction','status','payment_mode','items','rating_update_status','delivered_by','order_type','order_date','platform_fee','token'
        
    ];
}
