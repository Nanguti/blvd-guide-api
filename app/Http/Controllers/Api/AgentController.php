<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Agents",
 *     description="API Endpoints for agent management"
 * )
 */
class AgentController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/agents",
     *     summary="Get list of agents",
     *     tags={"Agents"},
     *     @OA\Response(
     *         response=200,
     *         description="List of agents",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Agent"))
     *         )
     *     )
     * )
     */
    public function index()
    {
        return Agent::with(['user', 'agency', 'properties'])->paginate(12);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/agents",
     *     summary="Create a new agent",
     *     tags={"Agents"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Agent")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Agent created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Agent")
     *     )
     * )
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'agency_id' => 'required|exists:agencies,id',
            'license_number' => 'nullable|string|unique:agents,license_number',
            'experience_years' => 'nullable|integer|min:0',
            'specialties' => 'nullable|array',
            'bio' => 'nullable|string'
        ]);

        // Check if agency exists
        $agency = \App\Models\Agency::findOrFail($data['agency_id']);

        $agent = Agent::create($data);

        // Load relationships explicitly
        $agent->load(['user', 'agency']);

        return response()->json($agent, 201);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/agents/{agent}",
     *     summary="Get agent details",
     *     tags={"Agents"},
     *     @OA\Parameter(
     *         name="agent",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Agent details",
     *         @OA\JsonContent(ref="#/components/schemas/Agent")
     *     )
     * )
     */
    public function show(Agent $agent)
    {
        return $agent->load(['user', 'agency', 'properties']);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/agents/{agent}",
     *     summary="Update agent details",
     *     tags={"Agents"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="agent",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Agent")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Agent updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Agent")
     *     )
     * )
     */
    public function update(Request $request, Agent $agent)
    {
        $data = $request->validate([
            'agency_id' => 'exists:agencies,id',
            'license_number' => 'string|unique:agents,license_number,' . $agent->id,
            'experience_years' => 'integer|min:0',
            'specialties' => 'nullable|array',
            'bio' => 'nullable|string'
        ]);

        $agent->update($data);

        return response()->json($agent->load(['user', 'agency']));
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/agents/{agent}",
     *     summary="Delete an agent",
     *     tags={"Agents"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="agent",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Agent deleted successfully"
     *     )
     * )
     */
    public function destroy(Agent $agent)
    {
        $agent->delete();
        return response()->json(null, 204);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/agents/{agent}/properties",
     *     summary="Get agent's properties",
     *     tags={"Agents"},
     *     @OA\Parameter(
     *         name="agent",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of agent's properties",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Property"))
     *         )
     *     )
     * )
     */
    public function properties(Agent $agent)
    {
        return $agent->properties()->with(['propertyType', 'propertyStatus', 'city'])->paginate(12);
    }
}
