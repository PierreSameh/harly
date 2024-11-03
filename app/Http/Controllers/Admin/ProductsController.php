<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\HandleResponseTrait;
use App\SaveImageTrait;
use App\DeleteImageTrait;
use App\Models\Additional;
use App\Models\Product;
use App\Models\Option;
use App\Models\Gallery;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ProductsController extends Controller
{
    use HandleResponseTrait, SaveImageTrait, DeleteImageTrait;

    public function index() {
        return view('Admin.products.index');
    }

    public function add() {
        return view("Admin.products.create");
    }

    public function edit($id) {
        $product = Product::with("gallery")->latest()->find($id);

        if ($product)
            return view("Admin.products.edit")->with(compact("product"));

        return $this->handleResponse(
            false,
            "Product not exits",
            ["Product id not valid"],
            [],
            []
        );
    }

    public function create(Request $request) {
        DB::beginTransaction();

        try {

        $validator = Validator::make($request->all(), [
            "name" => ["required"],
            "description" => ["required"],
            "quantity" => ["required", "numeric"],
            "price" => ["required", "numeric"],
            "category_id" => ["required"],
            'images.*' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'main_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ], [
            "name.required" => "ادخل اسم المنتج",
            "main_image.required" => "ارفع الصورة الرئيسية للمنتج",
            "category_id.required" => "اختر القسم",
            "description.required" => "ادخل وصف المنتج",
            "quantity.required" => "ادخل الكمية المتاحة من المنتج",
            "price.required" => "ادخل سعر المنتج المنتج",
            "images.required" => "يجب ان ترفع ما لايقل عن 4 صور لكل منتج ",
            "images.min_images" => "يجب ان ترفع ما لايقل عن 4 صور لكل منتج ",
            "images.mimes" => "يجب ان تكون الصورة بين هذه الصيغ (jpeg, png, jpg, gif)",
        ]);

        if ($validator->fails()) {
            return $this->handleResponse(
                false,
                "",
                [$validator->errors()->first()],
                [],
                []
            );
        }

        if (collect($request->file('images'))->count() < 4) {
            return $this->handleResponse(
                false,
                "",
                ["يجب ان ترفع ما لايقل عن 4 صور لكل منتج "],
                [],
                []
            );
        }

        $main_image_name = $this->saveImg($request->main_image, 'images/uploads/Products');
        $product = Product::create([
            "name" => $request->name,
            "description" => $request->description,
            "quantity" => $request->quantity,
            "price" => $request->price,
            "prev_price" => $request->prev_price,
            "code" => $request->code,
            "group" => $request->group,
            "hashtag" => $request->hashtag,
            "main_image" => '/images/uploads/Products/' . $main_image_name,
            "category_id" => $request->category_id,
        ]);

        if ($request->images && $product) {
            foreach ($request->images as $img) {
                $image = $this->saveImg($img, 'images/uploads/Products');
                $gallery = Gallery::create([
                    "path" => '/images/uploads/Products/' . $image,
                    "product_id" => $product->id
                ]);
            }
        }

        if ($request->options && $product) {
            foreach ($request->options as $option) {
                $photo = array_key_exists('photo', $option) && $option['photo'] ? 
                $this->saveImg($option['photo'], 'images/uploads/Options') : null;
                $option = Option::create([
                    "product_id" => $product->id,
                    "size" => $option["size"] ?? null,
                    "flavour" => $option["flavour"] ?? null,
                    "nicotine" => $option["nicotine"] ?? null,
                    "price" => $option["price"] ?? null,
                    "photo" => $photo ? '/images/uploads/Options/' . $photo : null,
                    "color" => $option["color"] ?? null,
                    "resistance" => $option['resistance'] ?? null,
                    "quantity" =>$option['quantity'] ?? null
                ]);
            }
        }

        if ($request->additional_data && $product) {
            foreach ($request->additional_data as $option) {
                $option = Additional::create([
                    "product_id" => $product->id,
                    "key" => $option["key"] ?? null,
                    "value" => $option["value"] ?? null,
                ]);
            }
        }


        DB::commit();
            return $this->handleResponse(
                true,
                "تم اضافة المنتج بنجاح",
                [],
                [],
                []
            );

        } catch (\Exception $e) {
            DB::rollBack();

            return $this->handleResponse(
                false,
                "فشل الاضافة",
                [$e->getMessage()],
                [],
                []
            );
        }

    }

    public function update(Request $request) {
        try{
        $validator = Validator::make($request->all(), [
            "id" => ["required"],
            "name" => ["required"],
            "description" => ["required"],
            'code'=> ["required"],
            'group'=> ["required"],
            'hashtag' => ["required"],
            "quantity" => ["required", "numeric"],
            "price" => ["required", "numeric"],
            "category_id" => ["required"],
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'main_image' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ], [
            "name.required" => "ادخل اسم المنتج",
            "description.required" => "ادخل وصف المنتج",
            "quantity.required" => "ادخل الكمية المتاحة من المنتج",
            "price.required" => "ادخل سعر المنتج المنتج",
            "images.required" => "يجب ان ترفع ما لايقل عن 4 صور لكل منتج ",
            "images.min_images" => "يجب ان ترفع ما لايقل عن 4 صور لكل منتج ",
            "images.mimes" => "يجب ان تكون الصورة بين هذه الصيغ (jpeg, png, jpg, gif)",
        ]);

        if ($validator->fails()) {
            return $this->handleResponse(
                false,
                "",
                [$validator->errors()->first()],
                [],
                []
            );
        }

        $product = Product::with("gallery")->find($request->id);

        if ((collect($request->file('images'))->count() + ($product->gallery->count() - collect($request->deleted_gallery ? $request->deleted_gallery : [])->count()) < 4)) {
            return $this->handleResponse(
                false,
                "",
                ["يجب ان ترفع ما لايقل عن 4 صور لكل منتج "],
                [],
                []
            );
        }

        if ($request->main_image) {
            $main_image_name = $this->saveImg($request->main_image, 'images/uploads/Products');
            $product->main_image = '/images/uploads/Products/' . $main_image_name;
        }

        $product->name = $request->name;
        $product->description = $request->description;
        $product->quantity = $request->quantity;
        $product->price = $request->price;
        $product->prev_price = $request->prev_price;
        $product->code = $request->code;
        $product->group = $request->group;
        $product->hashtag = $request->hashtag;
        $product->category_id = $request->category_id;

        if ($request->deleted_gallery) {
            foreach ($request->deleted_gallery as $img) {
                $this->deleteFile(base_path($img['path']));
                $imageD = Gallery::find($img['id']);
                $imageD->delete();
            }
        }

        if ($request->images && $product) {
            foreach ($request->images as $img) {
                $image = $this->saveImg($img, 'images/uploads/Products');
                $gallery = Gallery::create([
                    "path" => '/images/uploads/Products/' . $image,
                    "product_id" => $product->id
                ]);
            }
        }
        foreach ( $product->options as $option) {
            $option->delete();
        }
        if ($request->options && $product) {
            foreach ($request->options as $option) {
                $photo = null;
                
                // Check if 'photo' exists and is not null
                if (isset($option['photo'])) {
                    $photo = $this->saveImg($option['photo'], 'images/uploads/Options');
                }
        
                // Check if 'id' exists to update, or create a new record if not
                $exists = isset($option['id']) ? Option::find($option['id']) : null;
        
                if ($exists) {
                    // Update existing option
                    $exists->size = $option['size'] ?? null;
                    $exists->flavour = $option['flavour'] ?? null;
                    $exists->nicotine = $option['nicotine'] ?? null;
                    $exists->price = $option['price'] ?? null;
                    $exists->photo = $photo ? '/images/uploads/Options/' . $photo : null;
                    $exists->save();
                } else {
                    // Create a new option
                    Option::create([
                        "product_id" => $product->id,
                        "size" => $option["size"] ?? null,
                        "flavour" => $option["flavour"] ?? null,
                        "nicotine" => $option["nicotine"] ?? null,
                        "price" => $option["price"] ?? null,
                        "photo" => $photo ? '/images/uploads/Options/' . $photo : null,
                    ]);
                }
            }
        }
        
        foreach ( $product->additional_data as $option) {
            $option->delete();
        }

        if ($request->additional_data && $product) {
            foreach ($request->additional_data as $option) {
                $option = Additional::create([
                    "product_id" => $product->id,
                    "key" => $option["key"] ?? null,
                    "value" => $option["value"] ?? null,
                ]);
            }
        }

        $product->save();

        if ($product)
            return $this->handleResponse(
                true,
                "تم تحديث المنتج بنجاح",
                [],
                [],
                []
            );
        } catch(\Exception $e){
            return $this->handleResponse(
                false,
                '',
                [$e->getMessage()],
                [],
                []
            );
        }


    }

    public function deleteIndex($id) {
        $product = Product::find($id);

        if ($product)
            return view("Admin.products.delete")->with(compact("product"));

        return $this->handleResponse(
            false,
            "Product not exits",
            ["Product id not valid"],
            [],
            []
        );
    }

    public function delete(Request $request) {
        $validator = Validator::make($request->all(), [
            "id" => ["required"],
        ], [
        ]);

        if ($validator->fails()) {
            return $this->handleResponse(
                false,
                "",
                [$validator->errors()->first()],
                [],
                []
            );
        }

        $product = Product::with("gallery")->find($request->id);

        if ($product->gallery) {
            foreach ($product->gallery as $img) {
                $this->deleteFile(base_path($img['path']));
                $imageD = Gallery::find($img['id']);
                $imageD->delete();
            }
        }


        $product->delete();

        if ($product)
            return $this->handleResponse(
                true,
                "تم حذف المنتج بنجاح",
                [],
                [],
                []
            );

    }
    public function toggleProductDiscounted($id) {
        $product = Product::find($id);
        if ($product) {
            $product->isDiscounted = !$product->isDiscounted;
            $product->save();
        }

        return redirect()->back();
    }

}
