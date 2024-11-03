<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Option extends Model
{
    use HasFactory;
    protected $fillable = [
        'product_id',
        'size',
        'flavour',
        'nicotine',
        'photo',
        'price',
        'color',
        'resistance',
        'quantity'
    ];

    public $timestamps = false;
}
