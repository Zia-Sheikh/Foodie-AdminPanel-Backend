<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FavouriteRestaurant extends Model
{
    use HasFactory;
    protected $fillable = [
        'id', 'customer_id','restaurant_id'
    ];
}
