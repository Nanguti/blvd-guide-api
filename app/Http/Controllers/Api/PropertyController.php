<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
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
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'type' => ['required', Rule::in(['sale', 'rent'])],
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
            'features' => 'nullable|array',
            'published_status' => ['required', Rule::in(['draft', 'published', 'archived'])],
            'city_id' => 'required|exists:cities,id',
            'amenities' => 'nullable|array|exists:amenities,id'
        ]);

        $property = Property::create($validated + ['user_id' => Auth::id()]);

        if (isset($validated['amenities'])) {
            $property->amenities()->sync($validated['amenities']);
        }

        return response()->json($property->load(['propertyType', 'propertyStatus', 'amenities']), 201);
    }

    public function show(Property $property)
    {
        return response()->json($property->load([
            'propertyType',
            'propertyStatus',
            'user',
            'city',
            'media',
            'floorPlans',
            'amenities',
            'reviews' => function ($query) {
                $query->where('status', 'approved');
            }
        ]));
    }

    public function update(Request $request, Property $property)
    {
        $this->authorize('update', $property);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'type' => ['sometimes', Rule::in(['sale', 'rent'])],
            'property_type_id' => 'sometimes|exists:property_types,id',
            'property_status_id' => 'sometimes|exists:property_statuses,id',
            'price' => 'sometimes|numeric|min:0',
            'area' => 'sometimes|numeric|min:0',
            'bedrooms' => 'nullable|integer|min:0',
            'bathrooms' => 'nullable|integer|min:0',
            'garages' => 'nullable|integer|min:0',
            'year_built' => 'nullable|integer|min:1800',
            'address' => 'sometimes|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'features' => 'nullable|array',
            'published_status' => ['sometimes', Rule::in(['draft', 'published', 'archived'])],
            'city_id' => 'sometimes|exists:cities,id',
            'amenities' => 'nullable|array|exists:amenities,id'
        ]);

        $property->update($validated);

        if (isset($validated['amenities'])) {
            $property->amenities()->sync($validated['amenities']);
        }

        return response()->json($property->load(['propertyType', 'propertyStatus', 'amenities']));
    }

    public function destroy(Property $property)
    {
        $this->authorize('delete', $property);

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
