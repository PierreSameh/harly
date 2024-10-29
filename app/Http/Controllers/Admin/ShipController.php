<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ShipRate;

class ShipController extends Controller
{
    public function getShip(){
        $shipRates = ShipRate::all();
        return view("Admin.ship.get", compact("shipRates"));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'city' => 'required|string|max:255',
            'ship_rate' => 'required|numeric',
        ]);

        ShipRate::create($validatedData);
        return redirect()->route('admin.get.rates');
    }

    public function edit($id){
        $shipRate = ShipRate::find($id);
        return view('Admin.ship.edit', compact('shipRate'));
    }

    public function update(Request $request, $id){
        $validatedData = $request->validate([
            'city'=> 'required|string|max:255',
            'ship_rate'=> 'required|numeric',
            ]);
        $shipRate = ShipRate::find($id);
        if($shipRate){
            $shipRate->update($validatedData);
            return redirect()->route('admin.get.rates');
        }
        return redirect()->back()->with('error','Not Found');
    }

    public function delete($id){
        $shipRate = ShipRate::find($id);
        if ($shipRate){
            $shipRate->delete();
            return redirect()->back()->with('success','Shipping Rate Deleted Successfully');
        }
        return redirect()->back()->with('error',"Couldn't Delete");
    }
}