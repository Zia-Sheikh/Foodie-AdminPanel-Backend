<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartnerRejection extends Model
{
    use HasFactory;
    protected $fillable = [
        'id', 'order_id','partner_id'
    ];
}
