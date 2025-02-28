<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Amenity;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Amenities",
 *     description="API Endpoints for property amenities"
 * )
 */
class AmenityController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/amenities",
     *     summary="Get list of amenities",
     *     tags={"Amenities"},
     *     @OA\Response(
     *         response=200,
     *         description="List of amenities",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Amenity"))
     *     )
     * )
     */
    public function index()
    {
        $amenities = Amenity::withCount('properties')->get();
        return response()->json($amenities);
    }

    /**
     * @OA\Post(
     *     path="/api/amenities",
     *     summary="Create a new amenity",
     *     tags={"Amenities"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", maxLength=255),
     *             @OA\Property(property="icon", type="string", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Amenity created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Amenity")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:amenities',
            'icon' => 'nullable|string'
        ]);

        $amenity = Amenity::create($validated);

        return response()->json($amenity, 201);
    }

    /**
     * @OA\Get(
     *     path="/api/amenities/{amenity}",
     *     summary="Get amenity details",
     *     tags={"Amenities"},
     *     @OA\Parameter(
     *         name="amenity",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Amenity details with related properties",
     *         @OA\JsonContent(ref="#/components/schemas/Amenity")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Amenity not found"
     *     )
     * )
     */
    public function show(Amenity $amenity)
    {
        return response()->json($amenity->load(['properties' => function ($query) {
            $query->where('published_status', 'published')
                ->latest()
                ->limit(10);
        }]));
    }

    /**
     * @OA\Put(
     *     path="/api/amenities/{amenity}",
     *     summary="Update amenity details",
     *     tags={"Amenities"},
     *     @OA\Parameter(
     *         name="amenity",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", maxLength=255),
     *             @OA\Property(property="icon", type="string", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Amenity updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Amenity")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function update(Request $request, Amenity $amenity)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('amenities')->ignore($amenity->id)],
            'icon' => 'nullable|string'
        ]);

        $amenity->update($validated);

        return response()->json($amenity);
    }

    /**
     * @OA\Delete(
     *     path="/api/amenities/{amenity}",
     *     summary="Delete an amenity",
     *     tags={"Amenities"},
     *     @OA\Parameter(
     *         name="amenity",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Amenity deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Cannot delete amenity with associated properties"
     *     )
     * )
     */
    public function destroy(Amenity $amenity)
    {
        if ($amenity->properties()->exists()) {
            return response()->json(['message' => 'Cannot delete amenity with associated properties'], 422);
        }

        $amenity->delete();
        return response()->json(null, 204);
    }
}
