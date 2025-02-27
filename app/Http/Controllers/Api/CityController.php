<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\State;
use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CityController extends Controller
{
    public function index(State $state)
    {
        $cities = $state->cities()->withCount(['properties', 'areas'])->get();
        return response()->json($cities);
    }

    public function store(Request $request, State $state)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('cities')->where(function ($query) use ($state) {
                    return $query->where('state_id', $state->id);
                })
            ]
        ]);

        $city = $state->cities()->create($validated);

        return response()->json($city, 201);
    }

    public function getCities()
    {
        $cities = City::all();
        if (!$cities) {
            return response()->json(['message' => 'No cities found'], 404);
        }
        return response()->json($cities);
    }

    public function show(State $state, City $city)
    {
        if ($city->state_id !== $state->id) {
            return response()->json(['message' => 'City not found in specified state'], 404);
        }

        return response()->json($city->load(['areas', 'properties' => function ($query) {
            $query->where('published_status', 'published')->latest()->limit(10);
        }]));
    }

    public function update(Request $request, State $state, City $city)
    {
        if ($city->state_id !== $state->id) {
            return response()->json(['message' => 'City not found in specified state'], 404);
        }

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('cities')->where(function ($query) use ($state) {
                    return $query->where('state_id', $state->id);
                })->ignore($city->id)
            ]
        ]);

        $city->update($validated);

        return response()->json($city);
    }

    public function destroy(State $state, City $city)
    {
        if ($city->state_id !== $state->id) {
            return response()->json(['message' => 'City not found in specified state'], 404);
        }

        if ($city->properties()->exists() || $city->areas()->exists()) {
            return response()->json(['message' => 'Cannot delete city with associated properties or areas'], 422);
        }

        $city->delete();
        return response()->json(null, 204);
    }

    public function getCity($id)
    {
        $city = City::find($id);
        if (!$city) {
            return response()->json(['message' => 'City not found'], 404);
        }
        return response()->json($city);
    }
}
