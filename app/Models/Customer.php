<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;
     protected $fillable = [
        'id', 'customer_name', 'phone_number','email','password','profile_picture','wallet','status','fcm_token','phone_with_code','last_active_address','referral_code','refered_by'
    ];
}
