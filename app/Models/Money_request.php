<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Money_request extends Model
{
    use HasFactory;
    protected $fillable = [
        "user_id",
        "amount",
        "way_of_getting_money",
        "wallet_or_card_number",
        "status",
    ];

    public $table = "money_requests";

    // relationhsips
    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id');
    }

}
