@extends('Admin.layouts.main')

@section('content')
<div class="container">
    <!-- Form to Add New Shipping Rate -->
    <div class="card mb-4">
        <div class="card-header">إضافة سعر شحن</div>
        <div class="card-body">
            <form action="{{route('admin.store.rates')}}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="city">المحافظة</label>
                    <input type="text" class="form-control" id="city" name="city" required>
                </div>
                <div class="form-group">
                    <label for="ship_rate">تكلفة الشحن</label>
                    <input type="number" class="form-control" id="ship_rate" name="ship_rate" required>
                </div>
                <button type="submit" class="btn btn-primary">إضافة سعر شحن</button>
            </form>
        </div>
    </div>

    <!-- Table to Display Existing Shipping Rates -->
    <div class="card">
        <div class="card-header">اسعار الشحن الحالية</div>
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>المحافظة</th>
                        <th>اسعار الشحن</th>
                        <th>الخيارات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($shipRates as $rate)
                    <tr>
                        <td>{{ $rate->id }}</td>
                        <td>{{ $rate->city }}</td>
                        <td>{{ $rate->ship_rate }}</td>
                        <td>
                            <!-- Edit and Delete buttons (if needed) -->
                            <a href="{{route('admin.edit.rates', $rate->id)}}" class="btn btn-sm btn-warning">تعديل</a>
                            <form action="{{route('admin.delete.rates', $rate->id)}}" method="POST" style="display:inline;">
                                @csrf
                                <button onclick="return confirm('Are you Sure?')" type="submit" class="btn btn-sm btn-danger">إزالة</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection