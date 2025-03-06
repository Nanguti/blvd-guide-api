<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;

/**
 * @OA\Tag(
 *     name="Properties",
 *     description="API Endpoints for property management"
 * )
 */
class PropertyController extends Controller
{
    use AuthorizesRequests;

    /**
     * @OA\Get(
     *     path="/api/v1/properties",
     *     summary="Get list of properties",
     *     tags={"Properties"},
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="Filter by property type",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="property_type_id",
     *         in="query",
     *         description="Filter by property type ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="city_id",
     *         in="query",
     *         description="Filter by city ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="min_price",
     *         in="query",
     *         description="Minimum price",
     *         required=false,
     *         @OA\Schema(type="number")
     *     ),
     *     @OA\Parameter(
     *         name="max_price",
     *         in="query",
     *         description="Maximum price",
     *         required=false,
     *         @OA\Schema(type="number")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of properties",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Property")),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $query = Property::query()
            ->with(['propertyType', 'propertyStatus', 'user', 'city'])
            ->when($request->type, function ($q, $type) {
                return $q->where('type', $type);
            })
            ->when($request->property_type_id, function ($q, $propertyTypeId) {
                return $q->where('property_type_id', $propertyTypeId);
            })
            ->when($request->city_id, function ($q, $cityId) {
                return $q->where('city_id', $cityId);
            })
            ->when($request->min_price, function ($q, $minPrice) {
                return $q->where('price', '>=', $minPrice);
            })
            ->when($request->max_price, function ($q, $maxPrice) {
                return $q->where('price', '<=', $maxPrice);
            })
            ->when($request->bedrooms, function ($q, $bedrooms) {
                return $q->where('bedrooms', '>=', $bedrooms);
            })
            ->when($request->bathrooms, function ($q, $bathrooms) {
                return $q->where('bathrooms', '>=', $bathrooms);
            });

        return response()->json($query->paginate(12));
    }

    /**
     * @OA\Get(
     *     path="/api/v1/properties/filter/{type}",
     *     summary="Filter properties by type",
     *     tags={"Properties"},
     *     @OA\Parameter(
     *         name="type",
     *         in="path",
     *         description="Filter type (for-sale, for-rent, new-development, recently-sold)",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Filtered properties",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Property"))
     *         )
     *     )
     * )
     */
    public function filterByType(Request $request, $type)
    {
        $query = Property::query()
            ->with(['propertyType', 'propertyStatus', 'user', 'city', 'media'])
            ->where('published_status', 'published');

        switch ($type) {
            case 'for-sale':
                $query->whereHas('propertyStatus', function ($q) {
                    $q->where('name', 'For Sale');
                });
                break;
            case 'for-rent':
                $query->whereHas('propertyStatus', function ($q) {
                    $q->where('name', 'For Rent');
                });
                break;
            case 'new-development':
                $query->where('year_built', '>=', date('Y'))
                    ->whereHas('propertyStatus', function ($q) {
                        $q->where('name', 'For Sale');
                    });
                break;
            case 'recently-sold':
                $query->whereHas('propertyStatus', function ($q) {
                    $q->where('name', 'Sold');
                })
                    ->orderBy('updated_at', 'desc');
                break;
        }

        // Additional filters
        if ($request->has('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }
        if ($request->has('bedrooms')) {
            $query->where('bedrooms', '>=', $request->bedrooms);
        }
        if ($request->has('bathrooms')) {
            $query->where('bathrooms', '>=', $request->bathrooms);
        }
        if ($request->has('city_id')) {
            $query->where('city_id', $request->city_id);
        }
        if ($request->has('property_type_id')) {
            $query->where('property_type_id', $request->property_type_id);
        }

        // Sort options
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        return response()->json($query->paginate(12));
    }

    //featured properties. where published_status is published and featured true and limit 6
    public function featuredProperties()
    {
        return response()->json(Property::with(['propertyType', 'user', 'city'])
            ->where('published_status', 'published')
            ->where('featured', true)->limit(6)->latest()->get());
    }

    //Mark property as featured
    public function markFeatured(Property $property)
    {
        $property->featured = true;
        $property->save();
        return response()->json($property);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/properties",
     *     summary="Create a new property",
     *     tags={"Properties"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title","description","property_type_id","property_status_id","price","area","address","published_status","city_id"},
     *             @OA\Property(property="title", type="string"),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="property_type_id", type="integer"),
     *             @OA\Property(property="property_status_id", type="integer"),
     *             @OA\Property(property="price", type="number"),
     *             @OA\Property(property="area", type="number"),
     *             @OA\Property(property="bedrooms", type="integer"),
     *             @OA\Property(property="bathrooms", type="integer"),
     *             @OA\Property(property="garages", type="integer"),
     *             @OA\Property(property="year_built", type="integer"),
     *             @OA\Property(property="address", type="string"),
     *             @OA\Property(property="latitude", type="number"),
     *             @OA\Property(property="longitude", type="number"),
     *             @OA\Property(property="published_status", type="string", enum={"draft","published"}),
     *             @OA\Property(property="city_id", type="integer"),
     *             @OA\Property(property="amenities", type="array", @OA\Items(type="integer")),
     *             @OA\Property(property="features", type="array", @OA\Items(type="integer"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Property created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Property")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'property_type_id' => 'required|exists:property_types,id',
            'property_status_id' => 'required|exists:property_statuses,id',
            'price' => 'required|numeric|min:0',
            'area' => 'required|numeric|min:0',
            'bedrooms' => 'nullable|integer|min:0',
            'bathrooms' => 'nullable|integer|min:0',
            'garages' => 'nullable|integer|min:0',
            'year_built' => 'nullable|integer|min:1800',
            'address' => 'required|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'published_status' => 'required|in:draft,published',
            'city_id' => 'required|exists:cities,id',
            'amenities' => 'nullable|array',
            'amenities.*' => 'exists:amenities,id',
            'features' => 'nullable|array',
            'features.*' => 'exists:features,id',
            'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);
        // Upload featured image
        if ($request->hasFile('featured_image')) {
            $image = $request->file('featured_image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('featured_images'), $imageName);
            $data['featured_image'] = $imageName;
        }

        $property = Auth::user()->properties()->create($data);

        if ($request->has('amenities')) {
            $property->amenities()->attach($request->amenities);
        }

        if ($request->has('features')) {
            $property->features()->attach($request->features);
        }

        return $property->load(['amenities', 'features', 'propertyType', 'propertyStatus', 'city']);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/properties/{property}",
     *     summary="Get property details",
     *     tags={"Properties"},
     *     @OA\Parameter(
     *         name="property",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Property details",
     *         @OA\JsonContent(ref="#/components/schemas/Property")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Property not found"
     *     )
     * )
     */
    public function show(Property $property)
    {
        return $property->load([
            'amenities',
            'features',
            'propertyType',
            'propertyStatus',
            'city',
            'media',
            'floorPlans',
            'inquiries',
            'schedules'
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/properties/{property}",
     *     summary="Update property details",
     *     tags={"Properties"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="property",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Property")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Property updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Property")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized action"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Property not found"
     *     )
     * )
     */
    public function update(Request $request, Property $property)
    {
        if ($property->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $data = $request->validate([
            'title' => 'string|max:255',
            'description' => 'string',
            'property_type_id' => 'exists:property_types,id',
            'property_status_id' => 'exists:property_statuses,id',
            'price' => 'numeric|min:0',
            'area' => 'numeric|min:0',
            'bedrooms' => 'nullable|integer|min:0',
            'bathrooms' => 'nullable|integer|min:0',
            'garages' => 'nullable|integer|min:0',
            'year_built' => 'nullable|integer|min:1800',
            'address' => 'string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'published_status' => 'in:draft,published',
            'city_id' => 'exists:cities,id',
            'amenities' => 'nullable|array',
            'amenities.*' => 'exists:amenities,id',
            'features' => 'nullable|array',
            'features.*' => 'exists:features,id',
        ]);

        $property->update($data);

        if ($request->has('amenities')) {
            $property->amenities()->sync($request->amenities);
        }

        if ($request->has('features')) {
            $property->features()->sync($request->features);
        }

        return $property->load(['amenities', 'features', 'propertyType', 'propertyStatus', 'city']);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/properties/{property}",
     *     summary="Delete a property",
     *     tags={"Properties"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="property",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Property deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized action"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Property not found"
     *     )
     * )
     */
    public function destroy(Property $property)
    {
        if ($property->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        $property->delete();
        return response()->json(null, 204);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/properties/{property}/favorite",
     *     summary="Toggle property favorite status",
     *     tags={"Properties"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="property",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Favorite status toggled",
     *         @OA\JsonContent(
     *             @OA\Property(property="is_favorited", type="boolean")
     *         )
     *     )
     * )
     */
    public function toggleFavorite(Property $property)
    {
        $user = Auth::user();
        $user->favorites()->toggle($property->id);

        return response()->json([
            'is_favorited' => $user->favorites()->where('properties.id', $property->id)->exists()
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/properties/{property}/compare",
     *     summary="Toggle property compare status",
     *     tags={"Properties"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="property",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Compare status toggled",
     *         @OA\JsonContent(
     *             @OA\Property(property="is_compared", type="boolean")
     *         )
     *     )
     * )
     */
    public function toggleCompare(Property $property)
    {
        $user = Auth::user();
        $user->compares()->toggle($property->id);

        return response()->json([
            'is_compared' => $user->compares()->where('property_id', $property->id)->exists()
        ]);
    }
}
