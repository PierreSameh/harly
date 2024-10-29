<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShipRate extends Model
{
    use HasFactory;

    protected $fillable = [
        'city',
        'ship_rate'
    ];
}
