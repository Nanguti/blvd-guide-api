<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Amenity;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AmenityController extends Controller
{
    public function index()
    {
        $amenities = Amenity::withCount('properties')->get();
        return response()->json($amenities);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:amenities',
            'icon' => 'nullable|string'
        ]);

        $amenity = Amenity::create($validated);

        return response()->json($amenity, 201);
    }

    public function show(Amenity $amenity)
    {
        return response()->json($amenity->load(['properties' => function ($query) {
            $query->where('published_status', 'published')
                ->latest()
                ->limit(10);
        }]));
    }

    public function update(Request $request, Amenity $amenity)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('amenities')->ignore($amenity->id)],
            'icon' => 'nullable|string'
        ]);

        $amenity->update($validated);

        return response()->json($amenity);
    }

    public function destroy(Amenity $amenity)
    {
        if ($amenity->properties()->exists()) {
            return response()->json(['message' => 'Cannot delete amenity with associated properties'], 422);
        }

        $amenity->delete();
        return response()->json(null, 204);
    }
}
