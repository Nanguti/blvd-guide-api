<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\State;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StateController extends Controller
{
    public function index(Country $country)
    {
        $states = $country->states()->withCount('cities')->get();
        return response()->json($states);
    }

    public function store(Request $request, Country $country)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('states')->where(function ($query) use ($country) {
                    return $query->where('country_id', $country->id);
                })
            ]
        ]);

        $state = $country->states()->create($validated);

        return response()->json($state, 201);
    }

    public function show(Country $country, State $state)
    {
        if ($state->country_id !== $country->id) {
            return response()->json(['message' => 'State not found in specified country'], 404);
        }

        return response()->json($state->load('cities'));
    }

    public function update(Request $request, Country $country, State $state)
    {
        if ($state->country_id !== $country->id) {
            return response()->json(['message' => 'State not found in specified country'], 404);
        }

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('states')->where(function ($query) use ($country) {
                    return $query->where('country_id', $country->id);
                })->ignore($state->id)
            ]
        ]);

        $state->update($validated);

        return response()->json($state);
    }

    public function destroy(Country $country, State $state)
    {
        if ($state->country_id !== $country->id) {
            return response()->json(['message' => 'State not found in specified country'], 404);
        }

        if ($state->cities()->exists()) {
            return response()->json(['message' => 'Cannot delete state with associated cities'], 422);
        }

        $state->delete();
        return response()->json(null, 204);
    }

    public function getState($id)
    {
        $state = State::find($id);
        return response()->json($state);
    }
}
