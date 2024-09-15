<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\HandleResponseTrait;
use App\Models\Category;

class CategoriesController extends Controller
{
    use HandleResponseTrait;

    public function get() {
        $categories = Category::latest()->get();

        return $this->handleResponse(
            true,
            "عملية ناجحة",
            [],
            [
                $categories
            ],
            []
        );
    }

    public function search(Request $request) {
        $search = $request->search ? $request->search : '';
        $categories = Category::where('name', 'like', '%' . $search . '%')
        ->orWhere('description', 'like', '%' . $search . '%')->orderBy('position', 'asc')->get();

        return $this->handleResponse(
            true,
            "عملية ناجحة",
            [],
            [
                $categories
            ],
            [
                "search" => "البحث بالاسم او المحتوي"
            ]
        );
    }
}
