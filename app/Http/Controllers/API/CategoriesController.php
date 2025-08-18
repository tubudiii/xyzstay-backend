<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Support\Str;

class CategoriesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Menampilkan semua kategori
        // Menampilkan semua kategori beserta boardinghouse yang terdaftar
        $categories = Category::with('boardingHouses')->get();
        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validasi request
        $validated = $request->validate([
            'name' => 'required|string|unique:categories,name',
            'image' => 'nullable|string',
        ]);

        // Membuat kategori baru
        $category = Category::create([
            'name' => $validated['name'],
            'image' => $validated['image'] ?? null,
            'slug' => Str::slug($validated['name']),
        ]);

        return response()->json([
            'success' => true,
            'data' => $category
        ], 201);
    }

    /**
     * Menampilkan kategori berdasarkan slug
     */
    public function showCategoryBySlug($slug)
    {
        $category = Category::with('boardingHouses')->where('slug', $slug)->first();
        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found.'
            ], 404);
        }
        return response()->json([
            'success' => true,
            'data' => $category
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
