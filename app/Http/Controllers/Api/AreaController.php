<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Area;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AreaController extends Controller
{
    public function index(City $city)
    {
        $areas = $city->areas()->withCount('properties')->get();
        return response()->json($areas);
    }

    public function store(Request $request, City $city)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('areas')->where(function ($query) use ($city) {
                    return $query->where('city_id', $city->id);
                })
            ],
            'description' => 'nullable|string'
        ]);

        $area = $city->areas()->create($validated);

        return response()->json($area, 201);
    }

    public function show(City $city, Area $area)
    {
        if ($area->city_id !== $city->id) {
            return response()->json(['message' => 'Area not found in specified city'], 404);
        }

        return response()->json($area->load(['properties' => function ($query) {
            $query->where('published_status', 'published')->latest()->limit(10);
        }]));
    }

    public function update(Request $request, City $city, Area $area)
    {
        if ($area->city_id !== $city->id) {
            return response()->json(['message' => 'Area not found in specified city'], 404);
        }

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('areas')->where(function ($query) use ($city) {
                    return $query->where('city_id', $city->id);
                })->ignore($area->id)
            ],
            'description' => 'nullable|string'
        ]);

        $area->update($validated);

        return response()->json($area);
    }

    public function destroy(City $city, Area $area)
    {
        if ($area->city_id !== $city->id) {
            return response()->json(['message' => 'Area not found in specified city'], 404);
        }

        if ($area->properties()->exists()) {
            return response()->json(['message' => 'Cannot delete area with associated properties'], 422);
        }

        $area->delete();
        return response()->json(null, 204);
    }
}
