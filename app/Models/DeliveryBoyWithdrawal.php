<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryBoyWithdrawal extends Model
{
    use HasFactory;
     protected $fillable = [
        'id', 'delivery_boy_id', 'amount','reference_proof','reference_no','status','existing_wallet','message'
    ];
}
