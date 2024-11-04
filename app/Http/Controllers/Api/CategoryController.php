<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\RestResource;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Fetch all categories
     *
     * @return void
     */
    public function index()
    {
        $categories = Category::query()->with('posts')->get();

        // Jika data kategori kosong
        if ($categories->isEmpty()) {
            return response()->json(new RestResource(null, 'Data kategori tidak ada!', false), 404);
        }

        // Jika data kategori tidak kosong
        return response()->json(new RestResource($categories, 'Data kategori berhasil diambil', true), 200);
    }

    /**
     * Show category by id
     *
     * @param  mixed $id
     * @return void
     */
    public function show($id)
    {
        $category = Category::query()->with('posts')->find($id);

        // Jika data kategori tidak ditemukan
        if (!$category) {
            return response()->json(new RestResource(null, 'Data kategori tidak ditemukan!', false), 404);
        }

        // Jika data kategori ditemukan
        return response()->json(new RestResource($category, 'Data kategori berhasil diambil', true), 200);
    }
}
