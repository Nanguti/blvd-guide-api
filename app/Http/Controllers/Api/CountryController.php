<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CountryController extends Controller
{
    public function index()
    {
        $countries = Country::withCount('states')->get();
        return response()->json($countries);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:countries',
            'code' => 'required|string|size:2|unique:countries'
        ]);

        $country = Country::create($validated);

        return response()->json($country, 201);
    }

    public function show(Country $country)
    {
        return response()->json($country->load('states'));
    }

    public function update(Request $request, Country $country)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('countries')->ignore($country->id)],
            'code' => ['required', 'string', 'size:2', Rule::unique('countries')->ignore($country->id)]
        ]);

        $country->update($validated);

        return response()->json($country);
    }

    public function destroy(Country $country)
    {
        if ($country->states()->exists()) {
            return response()->json(['message' => 'Cannot delete country with associated states'], 422);
        }

        $country->delete();
        return response()->json(null, 204);
    }
}
