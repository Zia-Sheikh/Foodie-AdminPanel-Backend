<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderCommission extends Model
{
    use HasFactory;
    protected $fillable = [
        'id', 'order_id', 'role','user_id','amount','created_at','updated_at'
    ];
}
