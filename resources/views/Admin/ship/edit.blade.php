@extends('Admin.layouts.main')

@section('content')
<div class="container">
    <!-- Form to Add New Shipping Rate -->
    <div class="card mb-4">
        <div class="card-header">تعديل سعر الشحن</div>
        <div class="card-body">
            <form action="{{route('admin.update.rates', $shipRate->id)}}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="city">المحافظة</label>
                    <input type="text" class="form-control" id="city" value="{{$shipRate->city}}" name="city" required>
                </div>
                <div class="form-group">
                    <label for="ship_rate">تكلفة الشحن</label>
                    <input type="double" class="form-control" id="ship_rate" value="{{$shipRate->ship_rate}}" name="ship_rate" required>
                </div>
                <button type="submit" class="btn btn-primary">تعديل سعر الشحن</button>
            </form>
        </div>
    </div>
    @endsection