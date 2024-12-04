<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        "user_id",
        "first_name",
        "last_name",
        "phone",
        "your_phone",
        "country",
        "governoment",
        "city",
        "address",
        "email",
        "whatsapp",
        "sub_total",
        "status",
        "notes",
    ];

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id');
    }

    public function option()
    {
        return $this->belongsTo('App\Models\Option', 'option_id');
    }

    public function products()
    {
        return $this->hasMany('App\Models\Ordered_Product', 'order_id');
    }

    public function options()
    {
        return $this->hasManyThrough(Option::class, Ordered_Product::class, 'order_id', 'id', 'id', 'option_id');
    }


}
