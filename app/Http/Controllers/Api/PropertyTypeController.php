<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PropertyType;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class PropertyTypeController extends Controller
{
    public function index()
    {
        $types = PropertyType::withCount('properties')->get();
        return response()->json($types);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:property_types',
            'description' => 'nullable|string'
        ]);

        $slug = Str::slug($validated['name']);
        if (PropertyType::where('slug', $slug)->exists()) {
            return response()->json(['message' => 'Property type already exists'], 400);
        }

        $validated['slug'] = $slug;
        $type = PropertyType::create($validated);

        return response()->json($type, 201);
    }

    public function show(PropertyType $propertyType)
    {
        return response()->json($propertyType->load(['properties' => function ($query) {
            $query->where('published_status', 'published')
                ->latest()
                ->limit(10);
        }]));
    }

    public function update(Request $request, PropertyType $propertyType)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('property_types')
                ->ignore($propertyType->id)],
            'description' => 'nullable|string'
        ]);
        //check if name has changed
        if ($request->has('name')) {
            $slug = Str::slug($validated['name']);
            if (PropertyType::where('slug', $slug)->exists()) {
                return response()->json(['message' => 'Property type already exists'], 400);
            }
            $validated['slug'] = $slug;
        }
        $propertyType->update($validated);

        return response()->json($propertyType);
    }

    public function destroy(PropertyType $propertyType)
    {
        if ($propertyType->properties()->exists()) {
            return response()->json(['message' => 'Cannot delete property type with associated properties'], 422);
        }

        $propertyType->delete();
        return response()->json(null, 204);
    }
}
