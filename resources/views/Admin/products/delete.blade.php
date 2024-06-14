@extends('Admin.layouts.main')

@section("title", "Products - Delete")
@section("loading_txt", "Delete")

@section("content")
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Delete Product</h1>
    <a href="{{ route("admin.products.show") }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm"><i
            class="fas fa-arrow-left fa-sm text-white-50"></i> Back</a>
</div>

<div class="card p-3 mb-3" id="products_wrapper">
    <div class="card-header mb-3">
        <h3 class="text-danger text-center mb-0">Are you sure you want to delete this product?</h3>
    </div>
    <div class="d-flex justify-content-between" style="gap: 16px">
        <div class="w-50">
            <div class="form-group w-100">
                <label for="name" class="form-label">Name</label>
                <input type="text" class="form-control" id="name"  placeholder="Product Name" v-model="name">
            </div>
            <div class="form-group w-100">
                <label for="price" class="form-label">Sell Price</label>
                <input type="number" class="form-control" id="price"  placeholder="Sell Price" v-model="price">
            </div>
            <div class="form-group w-100">
                <label for="quantity" class="form-label">Quantity</label>
                <input type="number" class="form-control" id="quantity"  placeholder="Quantity" v-model="quantity">
            </div>
        </div>
        <div class="form-group w-50">
            <label for="Description" class="form-label">Description</label>
            <textarea rows="15" class="form-control" id="Description"  placeholder="Description" style="resize: none" v-model="description">
            </textarea>
        </div>
    </div>
    <div class="form-group w-100 d-flex justify-content-center" style="gap: 16px">
        <a href="{{ route("admin.products.show") }}" class="btn btn-secondary w-25">Cancel</a>
        <button class="btn btn-danger w-25" @click="deleteProduct">Delete</button>
    </div>
</div>

@endSection

@section("scripts")
<script>
const { createApp, ref } = Vue

createApp({
    data() {
        return {
            id: '{{ $product->id }}',
            name: '{{ $product->name }}',
            description: '{{ $product->description }}',
            price: '{{ $product->price }}',
            quantity: '{{ $product->quantity }}',
        }
    },
    methods: {
        async deleteProduct() {
            $('.loader').fadeIn().css('display', 'flex')
            try {
                const response = await axios.post(`{{ route("admin.products.delete") }}`, {
                    id: this.id
                },
                {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                },
                );
                if (response.data.status === true) {
                    document.getElementById('errors').innerHTML = ''
                    let error = document.createElement('div')
                    error.classList = 'success'
                    error.innerHTML = response.data.message
                    document.getElementById('errors').append(error)
                    $('#errors').fadeIn('slow')
                    setTimeout(() => {
                        $('.loader').fadeOut()
                        $('#errors').fadeOut('slow')
                        window.location.href = '{{ route("admin.products.show") }}'
                    }, 1300);
                } else {
                    $('.loader').fadeOut()
                    document.getElementById('errors').innerHTML = ''
                    $.each(response.data.errors, function (key, value) {
                        let error = document.createElement('div')
                        error.classList = 'error'
                        error.innerHTML = value
                        document.getElementById('errors').append(error)
                    });
                    $('#errors').fadeIn('slow')
                    setTimeout(() => {
                        $('#errors').fadeOut('slow')
                    }, 5000);
                }

            } catch (error) {
                document.getElementById('errors').innerHTML = ''
                let err = document.createElement('div')
                err.classList = 'error'
                err.innerHTML = 'server error try again later'
                document.getElementById('errors').append(err)
                $('#errors').fadeIn('slow')
                $('.loader').fadeOut()

                setTimeout(() => {
                    $('#errors').fadeOut('slow')
                }, 3500);

                console.error(error);
            }
        }
    },
}).mount('#products_wrapper')
</script>
@endSection
