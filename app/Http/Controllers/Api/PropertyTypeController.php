<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PropertyType;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Property Types",
 *     description="API Endpoints for managing property types"
 * )
 */
class PropertyTypeController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/property-types",
     *     summary="Get list of property types",
     *     tags={"Property Types"},
     *     @OA\Response(
     *         response=200,
     *         description="List of property types with property count",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/PropertyType"))
     *     )
     * )
     */
    public function index()
    {
        $types = PropertyType::withCount('properties')->get();
        return response()->json($types);
    }

    /**
     * @OA\Post(
     *     path="/api/property-types",
     *     summary="Create a new property type",
     *     tags={"Property Types"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "slug"},
     *             @OA\Property(property="name", type="string", maxLength=255),
     *             @OA\Property(property="slug", type="string", maxLength=255),
     *             @OA\Property(property="description", type="string", nullable=true),
     *             @OA\Property(property="icon", type="string", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Property type created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/PropertyType")
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

    /**
     * @OA\Get(
     *     path="/api/property-types/{propertyType}",
     *     summary="Get property type details",
     *     tags={"Property Types"},
     *     @OA\Parameter(
     *         name="propertyType",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Property type details with related properties",
     *         @OA\JsonContent(ref="#/components/schemas/PropertyType")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Property type not found"
     *     )
     * )
     */
    public function show(PropertyType $propertyType)
    {
        return response()->json($propertyType->load(['properties' => function ($query) {
            $query->where('published_status', 'published')
                ->latest()
                ->limit(10);
        }]));
    }

    /**
     * @OA\Put(
     *     path="/api/property-types/{propertyType}",
     *     summary="Update property type details",
     *     tags={"Property Types"},
     *     @OA\Parameter(
     *         name="propertyType",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "slug"},
     *             @OA\Property(property="name", type="string", maxLength=255),
     *             @OA\Property(property="slug", type="string", maxLength=255),
     *             @OA\Property(property="description", type="string", nullable=true),
     *             @OA\Property(property="icon", type="string", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Property type updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/PropertyType")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
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

    /**
     * @OA\Delete(
     *     path="/api/property-types/{propertyType}",
     *     summary="Delete a property type",
     *     tags={"Property Types"},
     *     @OA\Parameter(
     *         name="propertyType",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Property type deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Cannot delete property type with associated properties"
     *     )
     * )
     */
    public function destroy(PropertyType $propertyType)
    {
        if ($propertyType->properties()->exists()) {
            return response()->json(['message' => 'Cannot delete property type with associated properties'], 422);
        }

        $propertyType->delete();
        return response()->json(null, 204);
    }
}
