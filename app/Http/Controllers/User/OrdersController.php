<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Option;
use Illuminate\Http\Request;
use App\HandleResponseTrait;
use App\Models\Order;
use App\Models\Money_request;
use App\Models\Product;
use App\Models\Ordered_Product;
use Illuminate\Support\Facades\Validator;
use App\SendEmailTrait;
use Illuminate\Support\Facades\DB;

class OrdersController extends Controller
{
    use HandleResponseTrait, SendEmailTrait;

    public function placeOrder(Request $request) {
        DB::beginTransaction();

        try {
            $user = $request->user();
            $cart = $user->cart()->with("option")->get();

            // Check if cart is empty
            if (!$cart || $cart->count() === 0) {
                return $this->handleResponse(
                    false,
                    "",
                    ["العربة فارغة قم بتعبئتها اولا"],
                    [],
                    []
                );
            }

            // Validate recipient info
            $validator = Validator::make($request->all(), [
                "first_name" => ["required"],
                "last_name" => ["required"],
                "your_phone" => ["required"],
                "country" => ["required"],
                "governoment" => ["required"],
                "city" => ["required"],
                "address" => ["required"],
                "email" => ["required", "email"],
                'ship_rate' => ['required', 'numeric']
            ], [
                "your_phone.required" => "رقم الهاتف مطلوب",
                "email.required" => "البريد الإلكتروني مطلوب",
                "email.email" => "البريد الإلكتروني غير صالح",
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

            $sub_total = $request->ship_rate;
            // Calculate cart sub total
            foreach ($cart as $item) {
                $item_product = $item->product()->with(["gallery" => function ($q) {
                    $q->take(1);
                },])->first();
                $item_option = $item->option;
                if ($item_product) {
                    if($item_option){
                        $item->total = ((int) $item_option->price * (int) $item->quantity);
                    } else {
                        $item->total = ((int) $item_product->price * (int) $item->quantity);
                    }
                $sub_total += $item->total;
                } else {
                    $item->dose_product_missing = true;
                    $item->product = "This product is missing may be deleted!";
                }
            }

            // Create order
            $order = Order::create([
                "user_id" => $user->id,
                "first_name" => $request->first_name,
                "last_name" => $request->last_name,
                "phone" => $request->your_phone,
                "your_phone" => $request->your_phone,
                "country" => $request->country,
                "governoment" => $request->governoment,
                "city" => $request->city,
                "address" => $request->address,
                "email" => $request->email,
                "whatsapp" => $request->whatsapp ?? null,
                "sub_total" => $sub_total,
                "status" => 1,
                "notes" => $request->notes,
            ]);

            foreach ($cart as $item) {
                if (!$item->dose_product_missing) {
                    $product = Product::find($item["product_id"]);
                    $option = Option::find($item['option_id']);
                    if(!$product || !$option){
                        return response()->json(["message" => "product or option is not available at the moment"], 404);
                    }
                    Ordered_Product::create([
                        "order_id" => $order->id,
                        "product_id" => $item["product_id"],
                        "option_id" => $item["option_id"],
                        "price_in_order" => $product->price,
                        "ordered_quantity" => $item["quantity"],
                    ]);
                    if($option){
                        $option->quantity -= (int) $item["quantity"];
                        $option->save();
                    } elseif ($product) {
                        $product->quantity = (int) $product->quantity - (int) $item["quantity"];
                        $product->save();
                    }
                    $item->delete();
                }
            }

            if ($order) {
                $msg_content = "<h1>طلب جديد بواسطة " . $user->name . "</h1><br>";
                $msg_content .= "<h3>تفاصيل الطلب:</h3>";
                $msg_content .= "<h4>اسم المستلم: " . $order->first_name . " " . $order->last_name . "</h4>";
                $msg_content .= "<h4>رقم هاتف المستلم: " . $order->your_phone . "</h4>";
                $msg_content .= "<h4>عنوان المستلم: " . $order->address . ", " . $order->city . ", " . $order->governoment . ", " . $order->country . "</h4>";
                $msg_content .= "<h4>البريد الإلكتروني: " . $order->email . "</h4>";
                $msg_content .= "<h4>الاجمالي: " . $order->sub_total . "</h4>";

                $this->sendEmail("kotbekareem74@gmail.com", "طلب جديد", $msg_content);
            }

            DB::commit();

            return $this->handleResponse(
                true,
                "تم اكتمال الطلب بنجاح سوف نتواصل مع المستلم لتاكيد وارسال الطلب",
                [],
                [$order],
                []
            );

        } catch (\Exception $e) {
            DB::rollBack();

            return $this->handleResponse(
                false,
                "فشل اكمال الطلب",
                [$e->getMessage()],
                [],
                []
            );
        }
    }

    public function ordersAll(Request $request) {
        $user = $request->user();
        $status = $request->status;
        $order = $user->orders()->latest()->with(["products" => function ($q) {
            $q->with(["product" => function ($q) {
                $q->with("category");
            }]);
        }, "user"])->when($status !== null, function ($q) use ($status) {
            $q->where("status",  $status);
        })->get();

        return $this->handleResponse(
            true,
            "عملية ناجحة",
            [],
            [$order],
            [
                "parameters" => [
                    "note" => "ال status مش مفروضة",
                    "status" => [
                        1 => "تحت المراجعة",
                        2 => "تم التاكيد",
                        3 => "بداء الشحن",
                        4 => "اكتمل",
                        5 => "فشل او الغى",
                    ]
                ]
            ]
        );
    }

    public function ordersPagination(Request $request) {
        $per_page = $request->per_page ? $request->per_page : 10;

        $user = $request->user();
        $status = $request->status;
        $order = $user->orders()->latest()->with(["products" => function ($q) {
            $q->with(["product" => function ($q) {
                $q->with("category");
            }]);
        }, "user"])->when($status !== null, function ($q) use ($status) {
            $q->where("status",  $status);
        })->paginate($per_page);

        return $this->handleResponse(
            true,
            "عملية ناجحة",
            [],
            [$order],
            [
                "parameters" => [
                    "note" => "ال status مش مفروضة",
                    "status" => [
                        1 => "تحت المراجعة",
                        2 => "تم التاكيد",
                        3 => "بداء الشحن",
                        4 => "اكتمل",
                        5 => "فشل او الغى",
                    ]
                ]
            ]
        );
    }

    public function searchOrdersAll(Request $request) {
        $search = $request->search ? $request->search : '';

        $user = $request->user();
        $status = $request->status;
        $order = $user->orders()->latest()->with(["products" => function ($q) {
            $q->with(["product" => function ($q) {
                $q->with("category");
            }]);
        }, "user"])->when($status !== null, function ($q) use ($status) {
            $q->where("status",  $status);
        })
        ->where(function ($query) use ($search) {
            $query->where('recipient_name', 'like', '%' . $search . '%')
                  ->orWhere('recipient_phone', 'like', '%' . $search . '%')
                  ->orWhere('recipient_address', 'like', '%' . $search . '%');
        })
        ->get();

        return $this->handleResponse(
            true,
            "عملية ناجحة",
            [],
            [$order],
            [
                "parameters" => [
                    "note" => "ال status مش مفروضة",
                    "status" => [
                        1 => "تحت المراجعة",
                        2 => "تم التاكيد",
                        3 => "بداء الشحن",
                        4 => "اكتمل",
                        5 => "فشل او الغى",
                    ]
                ]
            ]
        );
    }

    public function searchOrdersPagination(Request $request) {
        $search = $request->search ? $request->search : '';

        $per_page = $request->per_page ? $request->per_page : 10;

        $user = $request->user();
        $status = $request->status;
        $order = $user->orders()->latest()->with(["products" => function ($q) {
            $q->with(["product" => function ($q) {
                $q->with("category");
            }]);
        }, "user"])->when($status !== null, function ($q) use ($status) {
            $q->where("status",  $status);
        })
        ->where(function ($query) use ($search) {
            $query->where('recipient_name', 'like', '%' . $search . '%')
                  ->orWhere('recipient_phone', 'like', '%' . $search . '%')
                  ->orWhere('recipient_address', 'like', '%' . $search . '%');
        })
        ->paginate($per_page);

        return $this->handleResponse(
            true,
            "عملية ناجحة",
            [],
            [$order],
            [
                "parameters" => [
                    "note" => "ال status مش مفروضة",
                    "status" => [
                        1 => "تحت المراجعة",
                        2 => "تم التاكيد",
                        3 => "بداء الشحن",
                        4 => "اكتمل",
                        5 => "فشل او الغى",
                    ]
                ]
            ]
        );
    }

    public function order($id) {
        $order = Order::with(["products" => function ($q) {
            $q->with(["product" => function ($q) {
                $q->with("category");
            }]);
        }, "user"])->find($id);
        if ($order)
            return $this->handleResponse(
                true,
                "عملية ناجحة",
                [],
                [$order],
                []
            );

        return $this->handleResponse(
            false,
            "",
            ["Invalid Order id"],
            [],
            []
        );
    }

}
