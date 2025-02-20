<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index()
    {
        return Category::all();
    }

    public function show(Category $category)
    {
        return $category;
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);
        $slug = Str::slug($request->name);
        if (Category::where('slug', $slug)->exists()) {
            return response()->json(['message' => 'Category already exists'], 400);
        }
        $data['slug'] = $slug;

        return Category::create($data);
    }

    public function update(Request $request, Category $category)
    {
        $data = $request->validate([
            'name' => 'string|max:255',
            'description' => 'nullable|string',
        ]);
        if ($request->has('name')) {
            $slug = Str::slug($request->name);

            if (Category::where('slug', $slug)->where('id', '!=', $category->id)
                ->exists()
            ) {
                return response()->json(['message' => 'Category with this name already exists'], 400);
            }

            $data['slug'] = $slug;
        }
        $category->update($data);
        return $category;
    }

    public function destroy(Category $category)
    {
        $category->delete();
        return response()->noContent();
    }
}
