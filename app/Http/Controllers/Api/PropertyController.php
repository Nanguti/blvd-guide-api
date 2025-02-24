<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;

class PropertyController extends Controller
{
    use AuthorizesRequests;

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

    public function show(Property $property)
    {
        return $property->load([
            'amenities',
            'features',
            'propertyType',
            'propertyStatus',
            'city',
            'media',
            'floorPlans'
        ]);
    }

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

    public function destroy(Property $property)
    {
        if ($property->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        $property->delete();
        return response()->json(null, 204);
    }

    public function toggleFavorite(Property $property)
    {
        $user = Auth::user();
        $user->favorites()->toggle($property->id);

        return response()->json([
            'is_favorited' => $user->favorites()->where('property_id', $property->id)->exists()
        ]);
    }

    public function toggleCompare(Property $property)
    {
        $user = Auth::user();
        $user->compares()->toggle($property->id);

        return response()->json([
            'is_compared' => $user->compares()->where('property_id', $property->id)->exists()
        ]);
    }
}
