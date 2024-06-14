<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\HandleResponseTrait;
use App\Models\Product;
use App\Models\User;
use App\Models\Order;
use App\Models\Wishlist;
use App\Models\Category;
use Illuminate\Support\Facades\Validator;
use Laravel\Sanctum\PersonalAccessToken;
class ProductsController extends Controller
{
    use HandleResponseTrait;

    public function addIsFavKey($products, $authorization) {
        $user = null;

        $authorizationHeader = $authorization ? $authorization : false;

        if ($authorizationHeader) {
            try {
                // Extract token from the header (assuming 'Bearer' prefix)
                $hashedTooken = str_replace('Bearer ', '', $authorizationHeader);
                $token = PersonalAccessToken::findToken($hashedTooken);
                $user = $token?->tokenable;

            } catch (Exception $e) {
                // Handle potential exceptions during token validation
                // Log the error or return an appropriate response
            }
        }

        if ($user) {
            $user_id = $user->id;

            // Add isFav key to each product
            $products->each(function ($product) use ($user_id) {
                $product->isFav = Wishlist::where('user_id', $user_id)->where('product_id', $product->id)->exists();
            });
        } else {
            // Add isFav key to each product as false if not logged in
            $products->each(function ($product) {
                $product->isFav = false;
            });
        }
        return $products;
    }

    public function get(Request $request) {
        $per_page = $request->per_page ? $request->per_page : 10;

        $sortKey =($request->sort && $request->sort == "HP") || ( $request->sort && $request->sort == "LP") ? "price" :"created_at";
        $sortWay = $request->sort && $request->sort == "HP" ? "desc" : ( $request->sort && $request->sort  == "LP" ? "asc" : "desc");

        $products = Product::with(["gallery", "options"])->orderBy($sortKey, $sortWay)->paginate($per_page);

        $products = $this->addIsFavKey($products, $request->header('Authorization'));

        return $this->handleResponse(
            true,
            "عملية ناجحة",
            [],
            [
                $products
            ],
            [
                "parameters" => [
                    "per_page" => "لتحديد عدد العناصر لكل صفحة",
                    "page" => "لتحديد صفحة",
                    "sort" => [
                        "HP" => "height price",
                        "LP" => "lowest price",
                    ]
                ],
                "sort" => [
                    "default" => "لو مبعتش حاجة هيفلتر ع اساس الاحدث",
                    "sort = HP" => "لو بعت ال 'sort' ب 'HP' هيفلتر من الاغلى للارخص",
                    "sort = LP" => "لو بعت ال 'sort' ب 'LP' هيفلتر من الارخص للاغلى",
                ]
            ]
        );
    }

    public function getAll(Request $request) {
        $sortKey =($request->sort && $request->sort == "HP") || ( $request->sort && $request->sort == "LP") ? "price" :"created_at";
        $sortWay = $request->sort && $request->sort == "HP" ? "desc" : ( $request->sort && $request->sort  == "LP" ? "asc" : "desc");

        $products = Product::with(["gallery", "options"])->orderBy($sortKey, $sortWay)->get();

        $products = $this->addIsFavKey($products, $request->header('Authorization'));

        return $this->handleResponse(
            true,
            "عملية ناجحة",
            [],
            [
                $products
            ],
            [
                "parameters" => [
                    "sort" => [
                        "HP" => "height price",
                        "LP" => "lowest price",
                    ]
                ],
                "sort" => [
                    "default" => "لو مبعتش حاجة هيفلتر ع اساس الاحدث",
                    "sort = HP" => "لو بعت ال 'sort' ب 'HP' هيفلتر من الاغلى للارخص",
                    "sort = LP" => "لو بعت ال 'sort' ب 'LP' هيفلتر من الارخص للاغلى",
                ]
            ]
        );
    }

    public function search(Request $request) {
        $per_page = $request->per_page ? $request->per_page : 10;

        $sortKey =($request->sort && $request->sort == "HP") || ( $request->sort && $request->sort == "LP") ? "price" :"created_at";
        $sortWay = $request->sort && $request->sort == "HP" ? "desc" : ( $request->sort && $request->sort  == "LP" ? "asc" : "desc");
        $search = $request->search ? $request->search : '';

        $products = Product::with(["gallery", "options"])->where('name', 'like', '%' . $search . '%')
        ->orWhere('description', 'like', '%' . $search . '%')->orderBy($sortKey, $sortWay)->paginate($per_page);

        $products = $this->addIsFavKey($products, $request->header('Authorization'));

        return $this->handleResponse(
            true,
            "عملية ناجحة",
            [],
            [
                $products
            ],
            [
                "search" => "البحث بالاسم او اي كلمة في المحتوى",
                "parameters" => [
                    "per_page" => "لتحديد عدد العناصر لكل صفحة",
                    "page" => "لتحديد صفحة",
                    "sort" => [
                        "HP" => "height price",
                        "LP" => "lowest price",
                    ]
                ],
                "sort" => [
                    "default" => "لو مبعتش حاجة هيفلتر ع اساس الاحدث",
                    "sort = HP" => "لو بعت ال 'sort' ب 'HP' هيفلتر من الاغلى للارخص",
                    "sort = LP" => "لو بعت ال 'sort' ب 'LP' هيفلتر من الارخص للاغلى",
                ]
            ]
        );
    }

    public function getProductsPerCategoryAll(Request $request) {
        $validator = Validator::make($request->all(), [
            "category_id" => ["required"],
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

        $category = Category::with("products")->find($request->category_id);

        if ($category) {
            $sortKey = ($request->sort && $request->sort == "HP") || ( $request->sort && $request->sort == "LP") ? "price" :"created_at";
            $sortWay = $request->sort && $request->sort == "HP" ? "desc" : ( $request->sort && $request->sort  == "LP" ? "asc" : "desc");

            $products = $category->products()->orderBy($sortKey, $sortWay)->get();

            $products = $this->addIsFavKey($products, $request->header('Authorization'));

            return $this->handleResponse(
                true,
                "عملية ناجحة",
                [],
                [
                    $products
                ],
                [
                    "parameters" => [
                        "sort" => [
                            "HP" => "height price",
                            "LP" => "lowest price",
                        ]
                    ],
                    "sort" => [
                        "default" => "لو مبعتش حاجة هيفلتر ع اساس الاحدث",
                        "sort = HP" => "لو بعت ال 'sort' ب 'HP' هيفلتر من الاغلى للارخص",
                        "sort = LP" => "لو بعت ال 'sort' ب 'LP' هيفلتر من الارخص للاغلى",
                    ]
                ]
            );
        }

        return $this->handleResponse(
            false,
            "",
            ["القسم غير موجود"],
            [],
            []
        );
    }

    public function getProductsPerCategoryPagination(Request $request) {
        $validator = Validator::make($request->all(), [
            "category_id" => ["required"],
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

        $category = Category::with("products")->find($request->category_id);

        if ($category) {
            $per_page = $request->per_page ? $request->per_page : 10;

            $sortKey =($request->sort && $request->sort == "HP") || ( $request->sort && $request->sort == "LP") ? "price" :"created_at";
            $sortWay = $request->sort && $request->sort == "HP" ? "desc" : ( $request->sort && $request->sort  == "LP" ? "asc" : "desc");

            $products = $category->products()->orderBy($sortKey, $sortWay)->paginate($per_page);

            $products = $this->addIsFavKey($products, $request->header('Authorization'));
            return $this->handleResponse(
                true,
                "عملية ناجحة",
                [],
                [
                    $products
                ],
                [
                    "parameters" => [
                        "sort" => [
                            "HP" => "height price",
                            "LP" => "lowest price",
                        ]
                    ],
                    "sort" => [
                        "default" => "لو مبعتش حاجة هيفلتر ع اساس الاحدث",
                        "sort = HP" => "لو بعت ال 'sort' ب 'HP' هيفلتر من الاغلى للارخص",
                        "sort = LP" => "لو بعت ال 'sort' ب 'LP' هيفلتر من الارخص للاغلى",
                    ]
                ]
            );
        }

        return $this->handleResponse(
            false,
            "",
            ["القسم غير موجود"],
            [],
            []
        );
    }

    public function getProduct(Request $request) {
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

        $product = Product::with(["gallery", "options"])->find($request->product_id);

        if ($product) {
            return $this->handleResponse(
                true,
                "عملية ناجحة",
                [],
                [
                    $product
                ],
                []
            );
        } else {
            return $this->handleResponse(
                false,
                "",
                ["المنتج غير موجود"],
                [],
                []
            );
        }
    }

    public function getMostSelled(Request $request) {
        $per_page = $request->per_page ? $request->per_page : 10;

        $completedOrders = Order::with("products")->where("status", 4)->get();
        $topProducts = Product::with(["gallery", "options"])->
        withCount('orders') // Count occurrences of each product in orders
        ->orderBy('orders_count', 'desc') // Order by descending count
        ->paginate($per_page);

        $topProducts = $this->addIsFavKey( $topProducts, $request->header('Authorization'));

        return $this->handleResponse(
            true,
            "عملية ناجحة",
            [],
            [
                $topProducts
            ],
            [
                "parameters" => [
                    "per_page" => "لتحديد عدد العناصر لكل صفحة",
                    "page" => "لتحديد صفحة",
                ],
            ]
        );
    }

    public function getDiscounted(Request $request) {
        $per_page = $request->per_page ? $request->per_page : 10;

        $topProducts = Product::with(["gallery", "options"])->where("isDiscounted", true)
        ->paginate($per_page);

        $topProducts = $this->addIsFavKey( $topProducts, $request->header('Authorization'));

        return $this->handleResponse(
            true,
            "عملية ناجحة",
            [],
            [
                $topProducts
            ],
            [
                "parameters" => [
                    "per_page" => "لتحديد عدد العناصر لكل صفحة",
                    "page" => "لتحديد صفحة",
                ],
            ]
        );
    }

}
