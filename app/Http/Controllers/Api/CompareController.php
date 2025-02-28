<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Compare;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Compare",
 *     description="API Endpoints for property comparison functionality"
 * )
 */
class CompareController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/compare",
     *     summary="Get user's property comparison list",
     *     tags={"Compare"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of properties being compared",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Compare"))
     *     )
     * )
     */
    public function index()
    {
        return Compare::where('user_id', Auth::id())->with('property')->get();
    }

    /**
     * @OA\Post(
     *     path="/api/compare",
     *     summary="Add property to comparison list",
     *     tags={"Compare"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"property_id"},
     *             @OA\Property(property="property_id", type="integer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Property added to comparison list",
     *         @OA\JsonContent(ref="#/components/schemas/Compare")
     *     )
     * )
     */
    public function store(Request $request)
    {
        return Compare::create([
            'user_id' => Auth::id(),
            'property_id' => $request->property_id
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/compare/{compare}",
     *     summary="Remove property from comparison list",
     *     tags={"Compare"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="compare",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Property removed from comparison list"
     *     )
     * )
     */
    public function destroy(Compare $compare)
    {
        $compare->delete();
        return response()->noContent();
    }
}
