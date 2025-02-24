<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Models\Property;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function index()
    {
        $users = User::paginate(10);
        return response()->json($users);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'phone' => 'nullable|string|max:20',
            'role' => ['required', Rule::in(['admin', 'agent', 'user'])],
            'profile_image' => 'nullable|string',
            'bio' => 'nullable|string',
            'social_media_links' => 'nullable|array',
            'agency_id' => 'nullable|exists:agencies,id',
            'license_number' => 'nullable|string',
            'experience_years' => 'nullable|integer',
            'specialties' => 'nullable|array'
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $user = User::create($validated);

        return response()->json($user, 201);
    }

    public function show(User $user)
    {
        return response()->json($user->load(['properties', 'agency', 'favorites', 'reviews']));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => ['sometimes', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'sometimes|string|min:8',
            'phone' => 'nullable|string|max:20',
            'role' => ['sometimes', Rule::in(['admin', 'agent', 'user'])],
            'profile_image' => 'nullable|string',
            'bio' => 'nullable|string',
            'social_media_links' => 'nullable|array',
            'agency_id' => 'nullable|exists:agencies,id',
            'license_number' => 'nullable|string',
            'experience_years' => 'nullable|integer',
            'specialties' => 'nullable|array'
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);

        return response()->json($user);
    }

    public function destroy(User $user)
    {
        $user->delete();
        return response()->json(null, 204);
    }

    public function agents()
    {
        $agents = User::agents()->with('agency')->paginate(10);
        return response()->json($agents);
    }

    public function properties(User $user)
    {
        $properties = $user->properties()->with(['propertyType', 'propertyStatus'])->paginate(10);
        return response()->json($properties);
    }

    public function userFavorites(User $user)
    {
        $favorites = $user->favorites()->with('property')
            ->paginate(10);
        return response()->json($favorites);
    }

    /**
     * Add property to user's favorites
     */
    public function addFavorite(Property $property)
    {
        $user = Auth::user();

        if (!$user->favorites()->where('property_id', $property->id)->exists()) {
            $user->favorites()->attach($property->id);
            return response()->json(['message' => 'Property added to favorites']);
        }

        return response()->json(['message' => 'Property already in favorites'], 400);
    }

    /**
     * Remove property from user's favorites
     */
    public function removeFavorite(Property $property)
    {
        $user = Auth::user();

        if ($user->favorites()->where('property_id', $property->id)->exists()) {
            $user->favorites()->detach($property->id);
            return response()->json(['message' => 'Property removed from favorites']);
        }

        return response()->json(['message' => 'Property not in favorites'], 400);
    }

    /**
     * Get user's favorite properties
     */
    public function myFavorites()
    {
        return Auth::user()->favorites()
            ->with(['propertyType', 'propertyStatus', 'city'])
            ->paginate(12);
    }

    /**
     * Add property to user's compare list
     */
    public function addCompare(Property $property)
    {
        $user = Auth::user();

        // Optional: Limit number of properties that can be compared
        if ($user->compares()->count() >= 4) {
            return response()->json(['message' => 'Compare list is full (max 4 properties)'], 400);
        }

        if (!$user->compares()->where('property_id', $property->id)->exists()) {
            $user->compares()->attach($property->id);
            return response()->json(['message' => 'Property added to compare list']);
        }

        return response()->json(['message' => 'Property already in compare list'], 400);
    }

    /**
     * Remove property from user's compare list
     */
    public function removeCompare(Property $property)
    {
        $user = Auth::user();

        if ($user->compares()->where('property_id', $property->id)->exists()) {
            $user->compares()->detach($property->id);
            return response()->json(['message' => 'Property removed from compare list']);
        }

        return response()->json(['message' => 'Property not in compare list'], 400);
    }

    /**
     * Get user's compare list
     */
    public function myCompares()
    {
        return Auth::user()->compares()
            ->with(['propertyType', 'propertyStatus', 'city', 'amenities', 'features'])
            ->paginate(12);
    }

    /**
     * Get properties being compared
     */
    public function getComparedProperties()
    {
        return Auth::user()->compares()
            ->with([
                'propertyType',
                'propertyStatus',
                'city',
                'amenities',
                'features',
                'media',
                'floorPlans',
                'user' => function ($query) {
                    $query->select('id', 'name', 'email', 'phone', 'profile_image');
                }
            ])
            ->get();
    }
}
