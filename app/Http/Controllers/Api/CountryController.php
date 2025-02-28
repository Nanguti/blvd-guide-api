<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Countries",
 *     description="API Endpoints for managing countries"
 * )
 */
class CountryController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/countries",
     *     summary="Get list of countries",
     *     tags={"Countries"},
     *     @OA\Response(
     *         response=200,
     *         description="List of countries with state count",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Country"))
     *     )
     * )
     */
    public function index()
    {
        $countries = Country::withCount('states')->get();
        return response()->json($countries);
    }

    /**
     * @OA\Post(
     *     path="/api/countries",
     *     summary="Create a new country",
     *     tags={"Countries"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "code"},
     *             @OA\Property(property="name", type="string", maxLength=255),
     *             @OA\Property(property="code", type="string", description="2-letter country code", maxLength=2)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Country created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Country")
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
            'name' => 'required|string|max:255|unique:countries',
            'code' => 'required|string|size:2|unique:countries'
        ]);

        $country = Country::create($validated);

        return response()->json($country, 201);
    }

    /**
     * @OA\Get(
     *     path="/api/countries/{country}",
     *     summary="Get country details",
     *     tags={"Countries"},
     *     @OA\Parameter(
     *         name="country",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Country details with states",
     *         @OA\JsonContent(ref="#/components/schemas/Country")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Country not found"
     *     )
     * )
     */
    public function show(Country $country)
    {
        return response()->json($country->load('states'));
    }

    /**
     * @OA\Put(
     *     path="/api/countries/{country}",
     *     summary="Update country details",
     *     tags={"Countries"},
     *     @OA\Parameter(
     *         name="country",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "code"},
     *             @OA\Property(property="name", type="string", maxLength=255),
     *             @OA\Property(property="code", type="string", description="2-letter country code", maxLength=2)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Country updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Country")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function update(Request $request, Country $country)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('countries')->ignore($country->id)],
            'code' => ['required', 'string', 'size:2', Rule::unique('countries')->ignore($country->id)]
        ]);

        $country->update($validated);

        return response()->json($country);
    }

    /**
     * @OA\Delete(
     *     path="/api/countries/{country}",
     *     summary="Delete a country",
     *     tags={"Countries"},
     *     @OA\Parameter(
     *         name="country",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Country deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Cannot delete country with associated states"
     *     )
     * )
     */
    public function destroy(Country $country)
    {
        if ($country->states()->exists()) {
            return response()->json(['message' => 'Cannot delete country with associated states'], 422);
        }

        $country->delete();
        return response()->json(null, 204);
    }
}
