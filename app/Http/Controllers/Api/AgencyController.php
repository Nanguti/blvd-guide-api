<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Agency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Agencies",
 *     description="API Endpoints for real estate agencies"
 * )
 */
class AgencyController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/agencies",
     *     summary="Get list of agencies",
     *     tags={"Agencies"},
     *     @OA\Response(
     *         response=200,
     *         description="List of agencies with agent count",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Agency")),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     )
     * )
     */
    public function index()
    {
        $agencies = Agency::withCount('agents')->paginate(10);
        return response()->json($agencies);
    }

    /**
     * @OA\Post(
     *     path="/api/agencies",
     *     summary="Create a new agency",
     *     tags={"Agencies"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", maxLength=255),
     *             @OA\Property(property="description", type="string", nullable=true),
     *             @OA\Property(property="logo", type="string", nullable=true),
     *             @OA\Property(property="address", type="string", nullable=true),
     *             @OA\Property(property="phone", type="string", maxLength=20, nullable=true),
     *             @OA\Property(property="email", type="string", format="email", nullable=true),
     *             @OA\Property(property="website", type="string", format="url", nullable=true),
     *             @OA\Property(property="social_media_links", type="object", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Agency created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Agency")
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
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'logo' => 'nullable|string',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|url|max:255',
            'social_media_links' => 'nullable|array'
        ]);

        $agency = Agency::create($validated);

        return response()->json($agency, 201);
    }

    /**
     * @OA\Get(
     *     path="/api/agencies/{agency}",
     *     summary="Get agency details",
     *     tags={"Agencies"},
     *     @OA\Parameter(
     *         name="agency",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Agency details with agents",
     *         @OA\JsonContent(ref="#/components/schemas/Agency")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Agency not found"
     *     )
     * )
     */
    public function show(Agency $agency)
    {
        return response()->json($agency->load(['agents' => function ($query) {
            $query->withCount('properties');
        }]));
    }

    /**
     * @OA\Put(
     *     path="/api/agencies/{agency}",
     *     summary="Update agency details",
     *     tags={"Agencies"},
     *     @OA\Parameter(
     *         name="agency",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", maxLength=255),
     *             @OA\Property(property="description", type="string", nullable=true),
     *             @OA\Property(property="logo", type="string", nullable=true),
     *             @OA\Property(property="address", type="string", nullable=true),
     *             @OA\Property(property="phone", type="string", maxLength=20, nullable=true),
     *             @OA\Property(property="email", type="string", format="email", nullable=true),
     *             @OA\Property(property="website", type="string", format="url", nullable=true),
     *             @OA\Property(property="social_media_links", type="object", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Agency updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Agency")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function update(Request $request, Agency $agency)
    {
        Log::info($request->all());
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'logo' => 'nullable|string',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|url|max:255',
            'social_media_links' => 'nullable|array'
        ]);

        $agency->update($validated);

        return response()->json($agency);
    }

    /**
     * @OA\Delete(
     *     path="/api/agencies/{agency}",
     *     summary="Delete an agency",
     *     tags={"Agencies"},
     *     @OA\Parameter(
     *         name="agency",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Agency deleted successfully"
     *     )
     * )
     */
    public function destroy(Agency $agency)
    {
        $agency->delete();
        return response()->json(null, 204);
    }

    /**
     * @OA\Get(
     *     path="/api/agencies/{agency}/agents",
     *     summary="Get agency agents",
     *     tags={"Agencies"},
     *     @OA\Parameter(
     *         name="agency",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of agency agents",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/User")),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     )
     * )
     */
    public function agents(Agency $agency)
    {
        $agents = $agency->agents()->withCount('properties')->paginate(10);
        return response()->json($agents);
    }
}
