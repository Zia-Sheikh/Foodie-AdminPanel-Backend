<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Restaurant extends Model
{
    use HasFactory;
    protected $fillable = [
        'id', 'restaurant_name', 'manual_address','email','contact_person_name','restaurant_phone_number','google_address','username','fcm_token','phone_with_code','lat','lng','zip_code','password','status','admin_user_id','restaurant_image','certificate','wallet','is_open','wallet','order_id','order_status','license_no','restaurant_name_ar'
    ];
}
