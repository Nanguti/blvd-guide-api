<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Area;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Areas",
 *     description="API Endpoints for managing city areas"
 * )
 */
class AreaController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/cities/{city}/areas",
     *     summary="Get list of areas in a city",
     *     tags={"Areas"},
     *     @OA\Parameter(
     *         name="city",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of areas with property count",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Area"))
     *     )
     * )
     */
    public function index(City $city)
    {
        $areas = $city->areas()->withCount('properties')->get();
        return response()->json($areas);
    }

    /**
     * @OA\Post(
     *     path="/api/cities/{city}/areas",
     *     summary="Create a new area in a city",
     *     tags={"Areas"},
     *     @OA\Parameter(
     *         name="city",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", maxLength=255),
     *             @OA\Property(property="description", type="string", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Area created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Area")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
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

    /**
     * @OA\Get(
     *     path="/api/cities/{city}/areas/{area}",
     *     summary="Get area details",
     *     tags={"Areas"},
     *     @OA\Parameter(
     *         name="city",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="area",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Area details with related properties",
     *         @OA\JsonContent(ref="#/components/schemas/Area")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Area not found in specified city"
     *     )
     * )
     */
    public function show(City $city, Area $area)
    {
        if ($area->city_id !== $city->id) {
            return response()->json(['message' => 'Area not found in specified city'], 404);
        }

        return response()->json($area->load(['properties' => function ($query) {
            $query->where('published_status', 'published')->latest()->limit(10);
        }]));
    }

    /**
     * @OA\Put(
     *     path="/api/cities/{city}/areas/{area}",
     *     summary="Update area details",
     *     tags={"Areas"},
     *     @OA\Parameter(
     *         name="city",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="area",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", maxLength=255),
     *             @OA\Property(property="description", type="string", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Area updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Area")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Area not found in specified city"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
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

    /**
     * @OA\Delete(
     *     path="/api/cities/{city}/areas/{area}",
     *     summary="Delete an area",
     *     tags={"Areas"},
     *     @OA\Parameter(
     *         name="city",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="area",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Area deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Area not found in specified city"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Cannot delete area with associated properties"
     *     )
     * )
     */
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
