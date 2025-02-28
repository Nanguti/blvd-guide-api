<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\State;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="States",
 *     description="API Endpoints for managing states/provinces"
 * )
 */
class StateController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/states",
     *     summary="Get list of states",
     *     tags={"States"},
     *     @OA\Parameter(
     *         name="country_id",
     *         in="query",
     *         required=false,
     *         description="Filter states by country ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of states",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/State"))
     *     )
     * )
     */
    public function index(Country $country)
    {
        $states = $country->states()->withCount('cities')->get();
        return response()->json($states);
    }

    /**
     * @OA\Post(
     *     path="/api/states",
     *     summary="Create a new state",
     *     tags={"States"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "country_id"},
     *             @OA\Property(property="name", type="string", maxLength=255),
     *             @OA\Property(property="country_id", type="integer"),
     *             @OA\Property(property="latitude", type="number", format="float", nullable=true),
     *             @OA\Property(property="longitude", type="number", format="float", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="State created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/State")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
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

    /**
     * @OA\Get(
     *     path="/api/states/{state}",
     *     summary="Get state details",
     *     tags={"States"},
     *     @OA\Parameter(
     *         name="state",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="State details",
     *         @OA\JsonContent(ref="#/components/schemas/State")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="State not found"
     *     )
     * )
     */
    public function show(Country $country, State $state)
    {
        if ($state->country_id !== $country->id) {
            return response()->json(['message' => 'State not found in specified country'], 404);
        }

        return response()->json($state->load('cities'));
    }

    /**
     * @OA\Put(
     *     path="/api/states/{state}",
     *     summary="Update state details",
     *     tags={"States"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="state",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "country_id"},
     *             @OA\Property(property="name", type="string", maxLength=255),
     *             @OA\Property(property="country_id", type="integer"),
     *             @OA\Property(property="latitude", type="number", format="float", nullable=true),
     *             @OA\Property(property="longitude", type="number", format="float", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="State updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/State")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
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

    /**
     * @OA\Delete(
     *     path="/api/states/{state}",
     *     summary="Delete a state",
     *     tags={"States"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="state",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="State deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Cannot delete state with associated cities"
     *     )
     * )
     */
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
