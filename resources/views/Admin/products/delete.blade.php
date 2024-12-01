@extends('Admin.layouts.main')

@section("title", "Products - Delete")
@section("loading_txt", "Delete")

@section("content")
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Delete Product</h1>
    <a href="{{ route('admin.products.show') }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
        <i class="fas fa-arrow-left fa-sm text-white-50"></i> Back
    </a>
</div>

<div class="card p-3 mb-3" id="products_wrapper">
    <div class="card-header mb-3">
        <h3 class="text-danger text-center mb-0">Are you sure you want to delete this product?</h3>
    </div>
    
    <form action="{{ route('admin.products.delete') }}" method="POST">
        @csrf
        <input type="hidden" name="id" value="{{ $product->id }}">
        
        <div class="d-flex justify-content-between" style="gap: 16px">
            <div class="w-50">
                <div class="form-group w-100">
                    <label for="name" class="form-label">Name</label>
                    <input type="text" class="form-control" id="name" name="name" value="{{ $product->name }}" readonly>
                </div>
                <div class="form-group w-100">
                    <label for="price" class="form-label">Sell Price</label>
                    <input type="number" class="form-control" id="price" name="price" value="{{ $product->price }}" readonly>
                </div>
                <div class="form-group w-100">
                    <label for="quantity" class="form-label">Quantity</label>
                    <input type="number" class="form-control" id="quantity" name="quantity" value="{{ $product->quantity }}" readonly>
                </div>
            </div>
            <div class="form-group w-50">
                <label for="description" class="form-label">Description</label>
                <textarea rows="15" class="form-control" id="description" name="description" style="resize: none" readonly>{{ $product->description }}</textarea>
            </div>
        </div>
        
        <div class="form-group w-100 d-flex justify-content-center" style="gap: 16px">
            <a href="{{ route('admin.products.show') }}" class="btn btn-secondary w-25">Cancel</a>
            <button type="submit" class="btn btn-danger w-25">Delete</button>
        </div>
    </form>
</div>

@endsection
