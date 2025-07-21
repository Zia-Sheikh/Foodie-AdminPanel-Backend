<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemOption extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'id', 'option_name','option_name_ar','option_group_id', 'price'
    ];
    
    public function partners()
    {
        return $this->belongsTo(Item::class,'item_id');
    }
}
