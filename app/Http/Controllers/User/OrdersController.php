<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
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
            $cart = $user->cart()->get();

            // check if cart empty
            if (!$cart || $cart->count() === 0)
                return $this->handleResponse(
                    false,
                    "",
                    ["العربة فارغة قم بتعبئتها اولا"],
                    [],
                );

            // validate recipient info
            $validator = Validator::make($request->all(), [
                "your_name" => ["required"],
                "your_phone" => ["required"],
                "recipient_governorate" => ["required"],
                "recipient_address" => ["required"],
                "recipient_name" => ["required", "string"],
                "recipient_phone" => ["required"],
            ], [
                "your_name.required" => "الاسم مطلوب",
                "your_phone.required" => "رقم الهاتف مطلوب",
                "recipient_governorate.required" => "محافظة المستلم مطلوبة",
                "recipient_name.required" => "اسم المستلم مطلوب",
                "recipient_phone.required" => "رقم هاتف المستلم مطلوب",
                "recipient_address.required" => "عنوان المستلم مطلوب"
            ]);

            if ($validator->fails()) {
                return $this->handleResponse(
                    false,
                    "",
                    [$validator->errors()->first()],
                    [],
                );
            }

            $sub_total = 0;
            // get cart sub total
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


            $order = Order::create([
                "recipient_name"                => $request->recipient_name,
                "recipient_phone"               => $request->recipient_phone,
                "recipient_address"             => $request->recipient_address,
                "sub_total"                     => $sub_total,
                "user_id"                       => $user->id,
                "status"                        => 1,
                "your_name"                     => $request->your_name,
                "your_phone"                    => $request->your_phone,
                "your_sec_phone"                => $request->your_sec_phone ?? null,
                "recipient_governorate"         => $request->recipient_governorate,
                "notes"                         => $request->notes,
            ]);

            foreach ($cart as $item) {
                if (!$item->dose_product_missing) {
                    $record_product = Ordered_Product::create([
                        "order_id" => $order->id,
                        "product_id" => $item["product_id"],
                        "price_in_order" => $item["product"]["price"],
                        "ordered_quantity" => $item["quantity"],
                    ]);
                }
                $product = Product::find($item["product_id"]);
                if ($product) {
                    $product->quantity = (int) $product->quantity - (int) $item["quantity"];
                    $product->save();
                }
                $item->delete();
            }

            if ($order) {
                $msg_content = "<h1>";
                $msg_content = " طلب جديد بواسطة" . $user->name;
                $msg_content .= "</h1>";
                $msg_content .= "<br>";
                $msg_content .= "<h3>";
                $msg_content .= "تفاصيل الطلب: ";
                $msg_content .= "</h3>";

                $msg_content .= "<h4>";
                $msg_content .= "اسم المستلم: ";
                $msg_content .= $order->recipient_name;
                $msg_content .= "</h4>";


                $msg_content .= "<h4>";
                $msg_content .= "رقم هاتف المستلم: ";
                $msg_content .= $order->recipient_phone;
                $msg_content .= "</h4>";


                $msg_content .= "<h4>";
                $msg_content .= "عنوان المستلم: ";
                $msg_content .= $order->recipient_address;
                $msg_content .= "</h4>";


                $msg_content .= "<h4>";
                $msg_content .= "الاجمالي : ";
                $msg_content .= $order->sub_total;
                $msg_content .= "</h4>";


                $this->sendEmail("kotbekareem74@gmail.com", "طلب جديد", $msg_content);

            }

            DB::commit();

            return $this->handleResponse(
                true,
                "تم اكتمال الطلب بنجاح سوف نتواصل مع المستلم لتاكيد وارسال الطلب",
                [],
                [
                    $order
                ],
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
