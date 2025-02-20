<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

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

    public function favorites(User $user)
    {
        $favorites = $user->favorites()->with('property')->paginate(10);
        return response()->json($favorites);
    }
}
