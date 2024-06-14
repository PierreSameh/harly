@extends('Admin.layouts.main')

@section("title", "Products")

@php
    $products = App\Models\Product::all();
@endphp

@section("content")
<style>
    #dataTable_wrapper {
        width: 100%
    }
</style>
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Products</h1>
    <a href="{{ route("admin.products.add") }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm"><i
            class="fas fa-plus fa-sm text-white-50"></i> Create Product</a>
</div>

<div class="card shadow mb-4">
    <div class="card-body">
        <div class="table-responsive" style="width: auto">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Avilable quantity</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($products as $prod)
                        <tr>
                            <td>{{ $prod->name }}</td>
                            <td>{{ substr($prod->description, 0, 100) }}</td>
                            <td>{{ $prod->quantity }}</td>
                            <td>
                                <div class="btns" style="display: flex;gap: 4px">
                                    <a href="{{ route("admin.products.edit", ["id" => $prod->id]) }}" class="btn btn-success">Edit</a>
                                    <a href="{{ route("admin.products.delete.confirm", ["id" => $prod->id]) }}" class="btn btn-danger">Delete</a>
                                    <form action="{{ route("admin.products.toggleDis", ["id" => $prod->id]) }}">
                                        <button type="submit" style="background: transparent; border: none">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-discount-2" width="35" height="35" viewBox="0 0 24 24" stroke-width="2" stroke="{{ $prod->isDiscounted ? "#1cc88a" : "#e74a3b"}}" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                <path d="M9 15l6 -6" />
                                                <circle cx="9.5" cy="9.5" r=".5" fill="currentColor" />
                                                <circle cx="14.5" cy="14.5" r=".5" fill="currentColor" />
                                                <path d="M5 7.2a2.2 2.2 0 0 1 2.2 -2.2h1a2.2 2.2 0 0 0 1.55 -.64l.7 -.7a2.2 2.2 0 0 1 3.12 0l.7 .7a2.2 2.2 0 0 0 1.55 .64h1a2.2 2.2 0 0 1 2.2 2.2v1a2.2 2.2 0 0 0 .64 1.55l.7 .7a2.2 2.2 0 0 1 0 3.12l-.7 .7a2.2 2.2 0 0 0 -.64 1.55v1a2.2 2.2 0 0 1 -2.2 2.2h-1a2.2 2.2 0 0 0 -1.55 .64l-.7 .7a2.2 2.2 0 0 1 -3.12 0l-.7 -.7a2.2 2.2 0 0 0 -1.55 -.64h-1a2.2 2.2 0 0 1 -2.2 -2.2v-1a2.2 2.2 0 0 0 -.64 -1.55l-.7 -.7a2.2 2.2 0 0 1 0 -3.12l.7 -.7a2.2 2.2 0 0 0 .64 -1.55v-1" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@endSection


@section("scripts")
<script src="{{ asset('/admin/vendor/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('/admin/vendor/datatables/dataTables.bootstrap4.min.js') }}"></script>

<!-- Page level custom scripts -->
<script src="{{ asset('/admin/js/demo/datatables-demo.js') }}"></script>
@endSection
