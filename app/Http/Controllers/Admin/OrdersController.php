<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\Order;
use App\HandleResponseTrait;
use App\SendEmailTrait;

class OrdersController extends Controller
{
    use HandleResponseTrait, SendEmailTrait;

    public function index() {
        return view("Admin.orders.all");
    }

    public function indexReview() {
        return view("Admin.orders.reviews");
    }

    public function indexConfirmed() {
        return view("Admin.orders.confirmed");
    }

    public function indexDelivary() {
        return view("Admin.orders.delivary");
    }

    public function indexCompleted() {
        return view("Admin.orders.completed");
    }

    public function indexCanceled() {
        return view("Admin.orders.canceled");
    }

    public function order($id) {
//        $order = Order::with(["products" => function ($q) {
        //     $q->with(["product" => function ($q) {
        //         $q->with("category");
        //     }]);
        // }, "user", "option"])->find($id);
        $order = Order::with(['products.product.category', 'products.option', 'user'])->findOrFail($id);

        if ($order)
            return view("Admin.orders.order")->with(compact("order"));

        return $this->handleResponse(
            false,
            "",
            ["Invalid Order id"],
            [],
            []
        );
    }

    public function approveIndex($id) {
        $order = Order::with(["products" => function ($q) {
            $q->with(["product" => function ($q) {
                $q->with("category");
            }]);
        }, "user"])->find($id);
        if ($order && $order->status !== 4 && $order->status !== 0)
            return view("Admin.orders.approve")->with(compact("order"));

        return $this->handleResponse(
            false,
            "",
            ["Invalid Order status"],
            [],
            []
        );
    }

    public function cancelIndex($id) {
        $order = Order::with(["products" => function ($q) {
            $q->with(["product" => function ($q) {
                $q->with("category");
            }]);
        }, "user"])->find($id);
        if ($order && $order->status !== 4 && $order->status !== 0)
            return view("Admin.orders.cancel")->with(compact("order"));

        return $this->handleResponse(
            false,
            "",
            ["Invalid Order status"],
            [],
            []
        );
    }

    public function approve($id) {
        $order = Order::with(["products" => function ($q) {
            $q->with(["product" => function ($q) {
                $q->with("category");
            }]);
        }, "user"])->find($id);

        if ($order->status === 1) {
            $order->status = 2;
            $order->save();
        }

        else if ($order->status === 2) {
            $order->status = 3;
            $order->save();
        }

        else if ($order->status === 3) {
            $order->status = 4;
            $order->save();
        }

        if ($order) {
            return redirect('/admin/orders/order/success/' . $order->id);
        }

        return $this->handleResponse(
            false,
            "",
            ["Fail Proccess"],
            [],
            []
        );
    }

    public function cancel($id) {
        $order = Order::with(["products" => function ($q) {
            $q->with(["product" => function ($q) {
                $q->with("category");
            }]);
        }, "user"])->find($id);

       if ($order->status != 4 && $order->status != 0) {
            $order->status = 0;
            $order->save();
        }

        if ($order) {
            return redirect('/admin/orders/order/success/' . $order->id);
        }

        return $this->handleResponse(
            false,
            "",
            ["Fail Proccess"],
            [],
            []
        );
    }

    public function successIndex($id) {
        $order = Order::with(["products" => function ($q) {
            $q->with(["product" => function ($q) {
                $q->with("category");
            }]);
        }, "user"])->find($id);
        if ($order)
            return view("Admin.orders.success")->with(compact("order"));

        return $this->handleResponse(
            false,
            "",
            ["Invalid Order id"],
            [],
            []
        );
    }

}
