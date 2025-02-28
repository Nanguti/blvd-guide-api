<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Feature;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Features",
 *     description="API Endpoints for property features"
 * )
 */
class FeatureController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/features",
     *     summary="Get list of features",
     *     tags={"Features"},
     *     @OA\Response(
     *         response=200,
     *         description="List of features",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Feature"))
     *     )
     * )
     */
    public function index()
    {
        return Feature::all();
    }

    /**
     * @OA\Get(
     *     path="/api/features/{feature}",
     *     summary="Get feature details",
     *     tags={"Features"},
     *     @OA\Parameter(
     *         name="feature",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Feature details",
     *         @OA\JsonContent(ref="#/components/schemas/Feature")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Feature not found"
     *     )
     * )
     */
    public function show(Feature $feature)
    {
        return $feature;
    }

    /**
     * @OA\Post(
     *     path="/api/features",
     *     summary="Create a new feature",
     *     tags={"Features"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", maxLength=255),
     *             @OA\Property(property="description", type="string", nullable=true),
     *             @OA\Property(property="icon", type="string", maxLength=255, nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Feature created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Feature")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Feature already exists"
     *     )
     * )
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:255',
        ]);

        $slug = Str::slug($request->name);
        if (Feature::where('slug', $slug)->exists()) {
            return response()->json(['message' => 'Feature already exists'], 400);
        }
        $data['slug'] = $slug;

        return Feature::create($data);
    }

    /**
     * @OA\Put(
     *     path="/api/features/{feature}",
     *     summary="Update feature details",
     *     tags={"Features"},
     *     @OA\Parameter(
     *         name="feature",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", maxLength=255),
     *             @OA\Property(property="description", type="string", nullable=true),
     *             @OA\Property(property="icon", type="string", maxLength=255, nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Feature updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Feature")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Feature with this name already exists"
     *     )
     * )
     */
    public function update(Request $request, Feature $feature)
    {
        $data = $request->validate([
            'name' => 'string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:255',
        ]);

        if ($request->has('name')) {
            $slug = Str::slug($request->name);
            if (Feature::where('slug', $slug)
                ->where('id', '!=', $feature->id)->exists()
            ) {
                return response()->json(['message' => 'Feature with this name already exists'], 400);
            }
            $data['slug'] = $slug;
        }

        $feature->update($data);
        return $feature;
    }

    /**
     * @OA\Delete(
     *     path="/api/features/{feature}",
     *     summary="Delete a feature",
     *     tags={"Features"},
     *     @OA\Parameter(
     *         name="feature",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Feature deleted successfully"
     *     )
     * )
     */
    public function destroy(Feature $feature)
    {
        $feature->delete();
        return response()->noContent();
    }
}
