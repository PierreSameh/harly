<?php

namespace App\Http\Controllers\User;

use App\HandleResponseTrait;
use App\Http\Controllers\Controller;
use App\Models\ShipRate;
use Illuminate\Http\Request;


class ShipRateController extends Controller
{
    use HandleResponseTrait;

    public function get(Request $request) {
        if($request->city_id){
            $city = ShipRate::find($request->city_id);
            if(isset($city)){
                return $this->handleResponse(
                    true,
                    "",
                    [],
                    ["shipping_rate" => $city],
                    []
                );
            }
            return $this->handleResponse(
                false,
                "Invalid City ID",
                [],
                [],
                []
            );
        }
        $rates = ShipRate::all();
        return $this->handleResponse(
            true,
            "",
            [],
            [
                $rates
            ],
            []
        );
    }
}
