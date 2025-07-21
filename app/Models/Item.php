<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;
    protected $fillable = [
        'id', 'restaurant_id', 'in_stock'
    ];
    
    public function item_options()
    {
        return $this->hasMany(ItemOption::class,'item_id');
    }
}
