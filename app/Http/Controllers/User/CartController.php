<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\HandleResponseTrait;
use App\Models\Product;
use App\Models\Cart;
use App\Models\Option;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
{
    use HandleResponseTrait;

    // public function addProductToCart(Request $request) {
    //     try{
    //     $validator = Validator::make($request->all(), [
    //         "product_id" => ["required"],
    //         "quantity" => ["numeric"],
    //         "option_id" => ["nullable"], // Validate option_id if provided
    //     ]);

    //     if ($validator->fails()) {
    //         return $this->handleResponse(
    //             false,
    //             "",
    //             [$validator->errors()->first()],
    //             [],
    //             [
    //                 "في حالة كان المنتج موجود في عربة المستخم فالكمية تزداد بواحد او بالعدد المرسل من المستخدم في ال quantity"
    //             ]
    //         );
    //     }

    //     $product = Product::find($request->product_id);
    //     $quantity = $request->quantity ? $request->quantity : 1;
    //     $option_id = $request->input('option_id', null);

    //     if ($product) {
    //         $user = $request->user();
    //         $product_if_in_user_cart = $user->cart()->where("product_id", $request->product_id)
    //                                              ->where("option_id", $option_id)
    //                                              ->first();

    //         if ($product_if_in_user_cart) {
    //             $prod_quantity = (int) $product_if_in_user_cart->quantity + (int) $quantity;
    //             if ((int) $prod_quantity > (int) $product->quantity)
    //                 return $this->handleResponse(
    //                     true,
    //                     "هذه الكمية غير متوفرة من المنتج",
    //                     [],
    //                     [],
    //                     [
    //                         "في حالة كان المنتج موجود في عربة المستخم فالكمية تزداد بواحد او بالعدد المرسل من المستخدم في ال quantity"
    //                     ]
    //                 );

    //             $product_if_in_user_cart->quantity = $prod_quantity;
    //             $product_if_in_user_cart->save();

    //             return $this->handleResponse(
    //                 true,
    //                 "تم اضافة المنتج للعربة بنجاح",
    //                 [],
    //                 [],
    //                 [
    //                     "في حالة كان المنتج موجود في عربة المستخم فالكمية تزداد بواحد او بالعدد المرسل من المستخدم في ال quantity"
    //                 ]
    //             );
    //         } else {
    //             if ((int) $quantity > (int) $product->quantity)
    //                 return $this->handleResponse(
    //                     true,
    //                     "هذه الكمية غير متوفرة من المنتج",
    //                     [],
    //                     [],
    //                     [
    //                         "في حالة كان المنتج موجود في عربة المستخم فالكمية تزداد بواحد او بالعدد المرسل من المستخدم في ال quantity"
    //                     ]
    //                 );

    //             $cart_item = Cart::create([
    //                 "user_id" => $user->id,
    //                 "product_id" => $product->id,
    //                 "quantity" => $quantity,
    //                 "option_id" => $option_id ?? null, // Add option_id here
    //             ]);

    //             return $this->handleResponse(
    //                 true,
    //                 "تم اضافة المنتج للعربة بنجاح",
    //                 [],
    //                 [],
    //                 [
    //                     "في حالة كان المنتج موجود في عربة المستخم فالكمية تزداد بواحد او بالعدد المرسل من المستخدم في ال quantity"
    //                 ]
    //             );
    //         }
    //     } else {
    //         return $this->handleResponse(
    //             false,
    //             "",
    //             ["هذا المنتج غير متاح"],
    //             [],
    //             [
    //                 "في حالة كان المنتج موجود في عربة المستخم فالكمية تزداد بواحد او بالعدد المرسل من المستخدم في ال quantity"
    //             ]
    //         );
    //     }
    //     } catch (\Exception $e) {
    //         return $this->handleResponse(
    //             false,
    //             "Error Signing UP",
    //             [
    //                 $e->getMessage()
    //             ],
    //             [],
    //             []
    //         );
    //     }
    // }

    public function addProductToCart(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                "product_id" => ["required", "exists:products,id"], // Ensure product exists
                "quantity" => ["required", "numeric", "min:1"],
                "option_id" => ["required", "exists:options,id"], // Ensure option exists and is required
            ]);
    
            if ($validator->fails()) {
                return $this->handleResponse(
                    false,
                    "",
                    [$validator->errors()->first()],
                    [],
                    [
                        "في حالة كان المنتج موجود في عربة المستخم فالكمية تزداد بواحد او بالعدد المرسل من المستخدم في ال quantity"
                    ]
                );
            }
    
            $quantity = $request->input('quantity', 1);
            $option_id = $request->input('option_id');
    
            // Retrieve the option and ensure it belongs to the specified product
            $option = Option::where('id', $option_id)
                ->where('product_id', $request->product_id)
                ->first();
    
            if (!$option) {
                return $this->handleResponse(
                    false,
                    "الخيار المحدد غير متوفر لهذا المنتج",
                    [],
                    [],
                    []
                );
            }
    
            if ((int) $quantity > (int) $option->quantity) {
                return $this->handleResponse(
                    true,
                    "هذه الكمية غير متوفرة من الخيار المحدد",
                    [],
                    [],
                    []
                );
            }
    
            $user = $request->user();
            $product_if_in_user_cart = $user->cart()
                ->where("product_id", $request->product_id)
                ->where("option_id", $option_id)
                ->first();
    
            if ($product_if_in_user_cart) {
                $newQuantity = (int) $product_if_in_user_cart->quantity + (int) $quantity;
    
                if ($newQuantity > (int) $option->quantity) {
                    return $this->handleResponse(
                        true,
                        "هذه الكمية غير متوفرة من الخيار المحدد",
                        [],
                        [],
                        []
                    );
                }
    
                $product_if_in_user_cart->quantity = $newQuantity;
                $product_if_in_user_cart->save();
    
                return $this->handleResponse(
                    true,
                    "تم تحديث الكمية في العربة بنجاح",
                    [],
                    [],
                    []
                );
            } else {
                Cart::create([
                    "user_id" => $user->id,
                    "product_id" => $request->product_id,
                    "quantity" => $quantity,
                    "option_id" => $option_id,
                ]);
    
                return $this->handleResponse(
                    true,
                    "تم اضافة المنتج للعربة بنجاح",
                    [],
                    [],
                    []
                );
            }
        } catch (\Exception $e) {
            return $this->handleResponse(
                false,
                "Error Adding Product to Cart",
                [$e->getMessage()],
                [],
                []
            );
        }
    }
    
    

    public function removeProductFromCart(Request $request) {
        $validator = Validator::make($request->all(), [
            "product_id" => ["required"],
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

        $product = Product::find($request->product_id);

        if ($product) {
            $user = $request->user();
            $product_if_in_user_cart = $user->cart()->where("product_id", $product->id)->first();
            if ($product_if_in_user_cart)
                $product_if_in_user_cart->delete();

            return $this->handleResponse(
                true,
                "تم حذف المنتج من العربة بنجاح",
                [],
                [],
                []
            );
        } else {
            return $this->handleResponse(
                false,
                "",
                ["هذا المنتج غير متاح"],
                [],
                [
                    "في حالة كان المنتج موجود في عربة المستخم فالكمية تزداد بواحد او بالعدد المرسل من المستخدم في ال quantity"
                ]
            );
        }
    }

    public function updateProductQuantityAtCart(Request $request) {
        $validator = Validator::make($request->all(), [
            "product_id" => ["required"],
            "quantity" => ["required", "numeric"],
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

        $product = Product::find($request->product_id);
        $quantity = $request->quantity;

        if ($product) {
            $user = $request->user();
            $product_if_in_user_cart = $user->cart()->where("product_id", $request->product_id)->first();
            if ($product_if_in_user_cart) {
                if ((int) $quantity > (int) $product->quantity)
                    return $this->handleResponse(
                        true,
                        "هذه الكمية غير متوفرة من المنتج",
                        [],
                        [],
                        []
                    );

                $product_if_in_user_cart->quantity = $quantity;
                $product_if_in_user_cart->save();

                if ($product_if_in_user_cart)
                    return $this->handleResponse(
                        true,
                        "تم تحديث العدد بنجاح",
                        [],
                        [],
                        []
                    );
            } else {
                return $this->handleResponse(
                    false,
                    "",
                    ["هذا المنتج غير متاح لديك في العربة"],
                    [],
                    []
                );
            }
        } else {
            return $this->handleResponse(
                false,
                "",
                ["هذا المنتج غير متاح"],
                [],
                [
                    "في حالة كان المنتج موجود في عربة المستخم فالكمية تزداد بواحد او بالعدد المرسل من المستخدم في ال quantity"
                ]
            );
        }

    }

    public function getCartDetails(Request $request) {
        $user = $request->user();
        $cart = $user->cart()->with("option")->get();
        $sub_total = 0;

        if ($cart->count() > 0)
            foreach ($cart as $item) {
                $item_product = $item->product()->with(["gallery" => function ($q) {
                    $q->take(1);
                }])->first();
                if ($item_product) :
                    $item->total = ((int) $item_product->price * (int) $item->quantity);
                    $sub_total += $item->total;
                endif;
                $item->dose_product_missing = $item_product ? false : true;
                $item->product = $item_product ?? "This product is missing may deleted!";
            }

        $cartDetails = [
            "products" => $cart,
            "sub_total" => $sub_total
        ];

        return $this->handleResponse(
            true,
            "عملية ناجحة",
            [],
            [$cartDetails],
            []
        );
    }
}
