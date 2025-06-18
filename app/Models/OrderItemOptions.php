<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItemOptions extends Model
{
    use HasFactory;
    protected $fillable = [
        'id','order_id','item_id','qty','slug','slug_value','price','total'
    ];
}
