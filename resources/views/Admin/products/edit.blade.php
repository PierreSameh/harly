@extends('Admin.layouts.main')

@section("title", "Products - Edit")
@section("loading_txt", "Update")

@section("content")
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Update Product</h1>
    <a href="{{ route("admin.products.show") }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm"><i
            class="fas fa-arrow-left fa-sm text-white-50"></i> Back</a>
</div>
@php
    $categories = App\Models\Category::latest()->get();
@endphp
<div class="card p-3 mb-3" id="products_wrapper">
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
                <label for="price" class="form-label">Previous Price</label>
                <input type="number" class="form-control" id="price"  placeholder="Previous Price" v-model="prev_price">
            </div>
            {{-- <div class="form-group w-100">
                <label for="quantity" class="form-label">Quantity</label>
                <input type="number" class="form-control" id="quantity"  placeholder="Quantity" v-model="quantity">
            </div> --}}
            <div class="form-group w-100">
                <label for="code" class="form-label">code</label>
                <input type="text" class="form-control" id="code" placeholder="code" v-model="code">
            </div>
            {{-- <div class="form-group w-100">
                <label for="group" class="form-label">group</label>
                <input type="text" class="form-control" id="group" placeholder="group" v-model="group">
            </div>
            <div class="form-group w-100">
                <label for="hashtag" class="form-label">hashtag</label>
                <input type="text" class="form-control" id="hashtag" placeholder="hashtag" v-model="hashtag">
            </div> --}}
            <div class="form-group w-100">
                <label for="categories" class="form-label">Category</label>
                <select name="categories" id="categories" class="form-control" v-model="category_id">
                    <option value="" disabled>Select ---</option>
                    <option v-for="cat in categories" :key="cat.id" :value="cat.id">@{{ cat.name }}</option>
                </select>
            </div>
        </div>
        <div class="form-group w-50">
            <label for="Description" class="form-label">Description</label>
            <textarea rows="7" class="form-control" id="Description"  placeholder="Description" style="resize: none" v-model="description">
            </textarea>
            <div class="form-group pt-4 pb-4" style="width: max-content; height: 300px;min-width: 100%">
                <label for="thumbnail" class="w-100 h-100">
                    <svg v-if="!thumbnail && !thumbnail_path" xmlns="http://www.w3.org/2000/svg" className="icon icon-tabler icon-tabler-photo-up" width="24" height="24" viewBox="0 0 24 24" strokeWidth="1.5" style="width: 100%; height: 100%; object-fit: cover; padding: 10px; border: 1px solid; border-radius: 1rem" stroke="#043343" fill="none" strokeLinecap="round" strokeLinejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                        <path d="M15 8h.01" />
                        <path d="M12.5 21h-6.5a3 3 0 0 1 -3 -3v-12a3 3 0 0 1 3 -3h12a3 3 0 0 1 3 3v6.5" />
                        <path d="M3 16l5 -5c.928 -.893 2.072 -.893 3 0l3.5 3.5" />
                        <path d="M14 14l1 -1c.679 -.653 1.473 -.829 2.214 -.526" />
                        <path d="M19 22v-6" />
                        <path d="M22 19l-3 -3l-3 3" />
                    </svg>
                    <img v-if="thumbnail_path" :src="thumbnail_path" style="width: 100%; height: 100%; object-fit: contain; padding: 10px; border: 1px solid; border-radius: 1rem" />
                </label>
            <input type="file" class="form-control d-none" id="thumbnail"  placeholder="Category Thumbnail Picture" @change="handleChangeThumbnail">
            </div>
        </div>
    </div>
    <div class="w-100 form-group">
        <label for="gallary" class="form-control"
        style="display: flex; flex-direction: column; justify-content: center; align-items: center; height: 140px; font-size: 22px;">Upload
        Product Image*
        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-photo-plus" width="55"
            height="55" viewBox="0 0 24 24" stroke-width="2" stroke="#2c3e50" fill="none" stroke-linecap="round"
            stroke-linejoin="round">
            <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
            <path d="M15 8h.01"></path>
            <path d="M12.5 21h-6.5a3 3 0 0 1 -3 -3v-12a3 3 0 0 1 3 -3h12a3 3 0 0 1 3 3v6.5"></path>
            <path d="M3 16l5 -5c.928 -.893 2.072 -.893 3 0l4 4"></path>
            <path d="M14 14l1 -1c.67 -.644 1.45 -.824 2.182 -.54"></path>
            <path d="M16 19h6"></path>
            <path d="M19 16v6"></path>
        </svg>
    </label>
        <input type="file" id="gallary" multiple="" class="form-control" @change="handleChangeImages" style="display: none;">
    </div>
    <div id="preview-gallery" class="mt-3">
        <div class="row">
           <div v-for="(img, index) in gallery" :key="index"
              class="col-lg-3 col-md-6 mb-4">
              <button
                 style="background: transparent; border: medium; border-radius: 50%; float: right;" @click="handleDeleteImageFromGallery(index)">
                 <svg
                    xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-x" width="24" height="24"
                    viewBox="0 0 24 24" stroke-width="1.5" stroke="#043343" fill="none" stroke-linecap="round"
                    stroke-linejoin="round">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                    <path d="M18 6l-12 12"></path>
                    <path d="M6 6l12 12"></path>
                 </svg>
              </button>
              <img :src="img.path"
                 style="width: 100%; height: 250px; object-fit: cover;" alt="gallery">
           </div>
           <div v-for="(img, index) in images_path" :key="index"
              class="col-lg-3 col-md-6 mb-4">
              <button
                 style="background: transparent; border: medium; border-radius: 50%; float: right;" @click="handleDeleteImage(index)">
                 <svg
                    xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-x" width="24" height="24"
                    viewBox="0 0 24 24" stroke-width="1.5" stroke="#043343" fill="none" stroke-linecap="round"
                    stroke-linejoin="round">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                    <path d="M18 6l-12 12"></path>
                    <path d="M6 6l12 12"></path>
                 </svg>
              </button>
              <img :src="img"
                 style="width: 100%; height: 250px; object-fit: cover;" alt="gallery">
           </div>
        </div>
     </div>
     <div class="d-flex justify-content-between mb-4">
        <h2>Additional Data</h2>
        <button class="btn btn-primary" @click="handleAddAdditionalData">Add Data</button>
    </div>
    <table class="table" v-if="additional_data && additional_data.length > 0">
        <thead>
            <tr>
                <th scope="col">Key</th>
                <th scope="col">Value</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="(data, index) in additional_data" :key="index">
                <td>
                    <input type="text" name="key" id="key" class="form-control" placeholder="Key" v-model="additional_data[index]['key']">
                </td>
                <td>
                    <input type="text" name="value" id="value" class="form-control" placeholder="Value" v-model="additional_data[index]['value']">
                </td>
                <td>
                    <button class="btn btn-danger" @click="handleRemoveAdditionalData(index)">Remove</button>
                </td>
            </tr>
        </tbody>
    </table>
    <div class="d-flex justify-content-between mb-4" v-if="category_id">
        <h2>Does this product has options?</h2>
        <button class="btn btn-primary" @click="handleAddOption">Add Option</button>
    </div>
    <table class="table" v-if="options.length > 0 && category_id">
        <thead>
            <tr>
                <th scope="col" v-if="availableFields.includes('size')">Size</th>
                <th scope="col" v-if="availableFields.includes('flavour')">Flavour</th>
                <th scope="col" v-if="availableFields.includes('nicotine')">Nicotine</th>
                <th scope="col" v-if="availableFields.includes('color')">Color</th>
                <th scope="col" v-if="availableFields.includes('resistance')">Resistance</th>
                {{-- <th scope="col">Price</th> --}}
                <th scope="col">Quantity</th>
                <th scope="col" v-if="availableFields.includes('photo')">Photo</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="(option, index) in options" :key="index">
                <td v-if="availableFields.includes('size')">
                    <input type="text" class="form-control" placeholder="Size" v-model="options[index].size">
                </td>
                <td v-if="availableFields.includes('flavour')">
                    <input type="text" class="form-control" placeholder="Flavour" v-model="options[index].flavour">
                </td>
                <td v-if="availableFields.includes('nicotine')">
                    <input type="text" class="form-control" placeholder="Nicotine" v-model="options[index].nicotine">
                </td>
                <td v-if="availableFields.includes('color')">
                    <input type="text" class="form-control" placeholder="Color" v-model="options[index].color">
                </td>
                <td v-if="availableFields.includes('resistance')">
                    <input type="text" class="form-control" placeholder="Resistance" v-model="options[index].resistance">
                </td>
                {{-- <td>
                    <input type="number" class="form-control" placeholder="Price" v-model="options[index].price">
                </td> --}}
                <td>
                    <input type="number" class="form-control" placeholder="Quantity" v-model="options[index].quantity">
                </td>
                <td v-if="availableFields.includes('photo')">
                    <input type="file" class="form-control" @change="handleOptionPhotoChange($event, index)">
                    <img v-if="options[index].photo_path" :src="options[index].photo_path" 
                         style="width: 100px; height: 100px; object-fit: cover; margin-top: 10px;" />
                </td>
                <td>
                    <button class="btn btn-danger" @click="handleRemoveOption(index)">Remove</button>
                </td>
            </tr>
        </tbody>
    </table>

    <div class="form-group">
        <button class="btn btn-success w-25" @click="update" style="display: block;margin: auto">Update</button>
    </div>
</div>
@if ($errors->any())
<div class="alert alert-danger">
    <ul>
    @foreach ($errors->all() as $error)
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">X</button>
        <li>{{ $error }}</li>
    @endforeach
    </ul>
</div>
@endif
@endSection

@section("scripts")
<script>
const { createApp, ref } = Vue

createApp({
    data() {
        return {
            id: '{{ $product->id }}',
            category_id: '{{ $product->category_id }}',
            name: '{{ $product->name }}',
            description: '{{ $product->description }}',
            price: '{{ $product->price }}',
            prev_price: '{{ $product->prev_price }}',
            quantity: '{{ $product->quantity }}',
            hashtag: '{{ $product->quantity }}',
            group: '{{ $product->quantity }}',
            code: '{{ $product->quantity }}',
            thumbnail: null,
            thumbnail_path: '{{ $product->main_image }}',
            gallery: @json($product->gallery),
            categories: @json($categories),
            additional_data: @json($product->additional_data ?? []),
            deletedGallery: [],
            images_path: [],
            options: @json($product->options),
            images: [],
            currentCategory: null, // Add this to store current category object
            categoryOptions: {
                'vape_kits': ['color', 'photo'],
                'premium_liquid': ['nicotine', 'flavour', 'size', 'photo'],
                'egyptian_liquid': ['nicotine', 'flavour', 'size', 'photo'],
                'disposables': ['flavour', 'photo'],
                'vape_coil_and_cartridge': ['resistance'],
                'vape_pod': ['color', 'photo'],
                'batteries': ['resistance'],
                'vape_tanks': ['color']
            }
        }
    },
    mounted() {
        // Initialize category after component is mounted
        this.initializeCategory();
    },
    computed: {
        availableFields() {
            // Convert category_id to number for comparison
            const categoryIdNum = parseInt(this.category_id);
            // console.log('Current category_id:', categoryIdNum);
            // console.log('Available categories:', this.categories);
            
            const category = this.categories.find(c => parseInt(c.id) === categoryIdNum);
            
            if (!category) {
                // console.log('No category found for ID:', categoryIdNum);
                return [];
            }

            this.currentCategory = category;
            // console.log('Current category:', category.name);
            
            const categoryKey = category.name
                .toLowerCase()
                .replace(/&/g, 'and')
                .replace(/[^a-z0-9]+/g, '_')
                .replace(/^_+|_+$/g, '');
                
            // console.log('Category key:', categoryKey);
            // console.log('Available fields:', this.categoryOptions[categoryKey] || []);
            
            return this.categoryOptions[categoryKey] || [];
        }
    },
    methods: {
        initializeCategory() {
            // console.log('Initializing category...');
            // console.log('Initial category_id:', this.category_id);
            // console.log('Categories:', this.categories);
            
            // Convert category_id to number for comparison
            const categoryIdNum = parseInt(this.category_id);
            
            const initialCategory = this.categories.find(c => parseInt(c.id) === categoryIdNum);
            
            if (initialCategory) {
                this.currentCategory = initialCategory;
                // console.log('Initial category set:', initialCategory.name);
            } else {
                // console.log('Failed to find initial category for ID:', categoryIdNum);
            }
        },
        handleAddAdditionalData() {
            this.additional_data.push({
                key: "",
                value: ""
            });
        },
        handleAddOption() {
            this.options.push({
                size: "",
                flavour: "",
                nicotine: "",
                price: "",
                photo: null,
                photo_path: null
            });
        },
        handleRemoveAdditionalData(index) {
            this.additional_data.splice(index, 1);
        },
        handleRemoveOption(index) {
            this.options.splice(index, 1);
        },
        handleChangeThumbnail(event) {
            this.thumbnail = event.target.files[0];
            this.thumbnail_path = URL.createObjectURL(event.target.files[0]);
        },
        handleOptionPhotoChange(event, index) {
            const file = event.target.files[0];
            this.options[index].photo = file;
            this.options[index].photo_path = URL.createObjectURL(file);
        },
        handleChangeImages(event) {
            let files = Array.from(event.target.files);
            files.map(file => {
                this.images.push(file);
                this.images_path.push(URL.createObjectURL(file));
            });
        },
        handleDeleteImage(index) {
            this.images.splice(index, 1);
            this.images_path.splice(index, 1);
        },
        handleDeleteImageFromGallery(index) {
            this.deletedGallery.push(this.gallery[index]);
            this.gallery.splice(index, 1);
        },
        async update() {
            $('.loader').fadeIn().css('display', 'flex');
            try {
                const response = await axios.post(`{{ route("admin.products.update") }}`, {
                    id: this.id,
                    name: this.name,
                    description: this.description,
                    price: this.price,
                    prev_price: this.prev_price,
                    quantity: this.quantity,
                    images: this.images,
                    deleted_gallery: this.deletedGallery,
                    category_id: this.category_id,
                    options: this.options,
                    hashtag: this.hashtag,
                    group: this.group,
                    code: this.code,
                    additional_data: this.additional_data
                }, {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                });
                
                if (response.data.status === true) {
                    document.getElementById('errors').innerHTML = '';
                    let error = document.createElement('div');
                    error.classList = 'success';
                    error.innerHTML = response.data.message;
                    document.getElementById('errors').append(error);
                    $('#errors').fadeIn('slow');
                    setTimeout(() => {
                        $('.loader').fadeOut();
                        $('#errors').fadeOut('slow');
                        window.location.href = '{{ route("admin.products.show") }}';
                    }, 1300);
                } else {
                    $('.loader').fadeOut();
                    document.getElementById('errors').innerHTML = '';
                    $.each(response.data.errors, function (key, value) {
                        let error = document.createElement('div');
                        error.classList = 'error';
                        error.innerHTML = value;
                        document.getElementById('errors').append(error);
                    });
                    $('#errors').fadeIn('slow');
                    setTimeout(() => {
                        $('#errors').fadeOut('slow');
                    }, 5000);
                }
            } catch (error) {
                document.getElementById('errors').innerHTML = '';
                let err = document.createElement('div');
                err.classList = 'error';
                err.innerHTML = 'server error, please try again later';
                // err.innerHTML = error.message;
                document.getElementById('errors').append(err);
                $('#errors').fadeIn('slow');
                $('.loader').fadeOut();

                setTimeout(() => {
                    $('#errors').fadeOut('slow');
                }, 3500);

                console.error(error);
            }
        }
    },
    watch: {
        category_id: {
            immediate: true,
            handler(newVal) {
                const categoryIdNum = parseInt(newVal);
                const category = this.categories.find(c => parseInt(c.id) === categoryIdNum);
                if (category) {
                    this.currentCategory = category;
                    // console.log('Category changed to:', category.name);
                } else {
                    // console.log('No category found for ID:', categoryIdNum);
                }
            }
        }
    }
}).mount('#products_wrapper');
</script>
@endSection
