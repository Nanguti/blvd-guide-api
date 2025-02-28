<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PropertyStatus;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Property Statuses",
 *     description="API Endpoints for managing property statuses"
 * )
 */
class PropertyStatusController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/property-statuses",
     *     summary="Get list of property statuses",
     *     tags={"Property Statuses"},
     *     @OA\Response(
     *         response=200,
     *         description="List of property statuses with property count",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/PropertyStatus"))
     *     )
     * )
     */
    public function index()
    {
        $statuses = PropertyStatus::withCount('properties')->get();
        return response()->json($statuses);
    }

    /**
     * @OA\Post(
     *     path="/api/property-statuses",
     *     summary="Create a new property status",
     *     tags={"Property Statuses"},
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
     *         description="Property status created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/PropertyStatus")
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
            'name' => 'required|string|max:255|unique:property_statuses',
            'description' => 'nullable|string'
        ]);

        $status = PropertyStatus::create($validated);

        return response()->json($status, 201);
    }

    /**
     * @OA\Get(
     *     path="/api/property-statuses/{propertyStatus}",
     *     summary="Get property status details",
     *     tags={"Property Statuses"},
     *     @OA\Parameter(
     *         name="propertyStatus",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Property status details with related properties",
     *         @OA\JsonContent(ref="#/components/schemas/PropertyStatus")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Property status not found"
     *     )
     * )
     */
    public function show(PropertyStatus $propertyStatus)
    {
        return response()->json($propertyStatus->load(['properties' => function ($query) {
            $query->where('published_status', 'published')
                ->latest()
                ->limit(10);
        }]));
    }

    /**
     * @OA\Put(
     *     path="/api/property-statuses/{propertyStatus}",
     *     summary="Update property status details",
     *     tags={"Property Statuses"},
     *     @OA\Parameter(
     *         name="propertyStatus",
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
     *         description="Property status updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/PropertyStatus")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function update(Request $request, PropertyStatus $propertyStatus)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('property_statuses')->ignore($propertyStatus->id)],
            'description' => 'nullable|string'
        ]);

        $propertyStatus->update($validated);

        return response()->json($propertyStatus);
    }

    /**
     * @OA\Delete(
     *     path="/api/property-statuses/{propertyStatus}",
     *     summary="Delete a property status",
     *     tags={"Property Statuses"},
     *     @OA\Parameter(
     *         name="propertyStatus",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Property status deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Cannot delete property status with associated properties"
     *     )
     * )
     */
    public function destroy(PropertyStatus $propertyStatus)
    {
        if ($propertyStatus->properties()->exists()) {
            return response()->json(['message' => 'Cannot delete property status with associated properties'], 422);
        }

        $propertyStatus->delete();
        return response()->json(null, 204);
    }
}
