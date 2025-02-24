<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\PropertyFloorPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PropertyFloorPlanController extends Controller
{
    public function index(Property $property)
    {
        return response()->json($property->floorPlans);
    }

    public function store(Request $request, Property $property)
    {

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'required|file|mimes:jpg,jpeg,png|max:5120', // 5MB max
            'price' => 'nullable|numeric|min:0',
            'size' => 'nullable|numeric|min:0'
        ]);

        $path = $request->file('image')->store('floor-plans', 'public');

        $floorPlan = $property->floorPlans()->create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'image' => $path,
            'price' => $validated['price'],
            'size' => $validated['size']
        ]);

        return response()->json($floorPlan, 201);
    }

    public function update(Request $request, Property $property, PropertyFloorPlan $floorPlan)
    {

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|file|mimes:jpg,jpeg,png|max:5120',
            'price' => 'nullable|numeric|min:0',
            'size' => 'nullable|numeric|min:0'
        ]);

        if ($request->hasFile('image')) {
            // Delete old image
            if (Storage::disk('public')->exists($floorPlan->image)) {
                Storage::disk('public')->delete($floorPlan->image);
            }
            $validated['image'] = $request->file('image')->store('floor-plans', 'public');
        }

        $floorPlan->update($validated);

        return response()->json($floorPlan);
    }

    public function destroy(Property $property, PropertyFloorPlan $floorPlan)
    {

        if (Storage::disk('public')->exists($floorPlan->image)) {
            Storage::disk('public')->delete($floorPlan->image);
        }

        $floorPlan->delete();
        return response()->json(null, 204);
    }
}
