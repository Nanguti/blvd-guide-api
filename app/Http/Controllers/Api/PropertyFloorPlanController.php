<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\PropertyFloorPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Property Floor Plans",
 *     description="API Endpoints for managing property floor plans"
 * )
 */
class PropertyFloorPlanController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/properties/{property}/floor-plans",
     *     summary="Get list of property floor plans",
     *     tags={"Property Floor Plans"},
     *     @OA\Parameter(
     *         name="property",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of property floor plans",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/PropertyFloorPlan"))
     *     )
     * )
     */
    public function index(Property $property)
    {
        return response()->json($property->floorPlans);
    }

    public function store(Request $request, Property $property)
    {

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|file|mimes:jpg,jpeg,png|max:5120',
            'price' => 'nullable|numeric|min:0',
            'size' => 'nullable|numeric|min:0'
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('floor-plans', 'public');
        } else {
            $path = null;
        }

        $floorPlan = $property->floorPlans()->create([
            'title' => $request->title,
            'description' => $request->description,
            'image' => $path,
            'price' => $request->price,
            'size' => $request->size
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

        // if (Storage::disk('public')->exists($floorPlan->image)) {
        //     Storage::disk('public')->delete($floorPlan->image);
        // }

        $floorPlan->delete();
        return response()->json(null, 204);
    }
}
