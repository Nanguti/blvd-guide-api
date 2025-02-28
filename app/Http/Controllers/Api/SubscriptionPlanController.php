<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Subscription Plans",
 *     description="API Endpoints for managing subscription plans"
 * )
 */
class SubscriptionPlanController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/subscription-plans",
     *     summary="Get list of subscription plans",
     *     tags={"Subscription Plans"},
     *     @OA\Response(
     *         response=200,
     *         description="List of subscription plans",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/SubscriptionPlan"))
     *     )
     * )
     */
    public function index()
    {
        return SubscriptionPlan::where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * @OA\Post(
     *     path="/api/subscription-plans",
     *     summary="Create a new subscription plan",
     *     tags={"Subscription Plans"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "price", "duration", "features"},
     *             @OA\Property(property="name", type="string", maxLength=255),
     *             @OA\Property(property="description", type="string", nullable=true),
     *             @OA\Property(property="price", type="number", format="float"),
     *             @OA\Property(property="duration", type="integer", description="Duration in days"),
     *             @OA\Property(
     *                 property="features",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="name", type="string"),
     *                     @OA\Property(property="value", type="string")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Subscription plan created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/SubscriptionPlan")
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
            'price' => 'required|numeric|min:0',
            'duration_in_days' => 'required|integer|min:1',
            'features' => 'nullable|array',
            'property_limit' => 'required|integer|min:0',
            'is_active' => 'boolean',
            'sort_order' => 'integer'
        ]);

        //create a slug from the name
        $validated['slug'] = Str::slug($request->name);
        return SubscriptionPlan::create($validated);
    }

    /**
     * @OA\Get(
     *     path="/api/subscription-plans/{plan}",
     *     summary="Get subscription plan details",
     *     tags={"Subscription Plans"},
     *     @OA\Parameter(
     *         name="plan",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Subscription plan details",
     *         @OA\JsonContent(ref="#/components/schemas/SubscriptionPlan")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Subscription plan not found"
     *     )
     * )
     */
    public function show(SubscriptionPlan $plan)
    {
        return $plan;
    }

    /**
     * @OA\Put(
     *     path="/api/subscription-plans/{plan}",
     *     summary="Update subscription plan details",
     *     tags={"Subscription Plans"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="plan",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "price", "duration", "features"},
     *             @OA\Property(property="name", type="string", maxLength=255),
     *             @OA\Property(property="description", type="string", nullable=true),
     *             @OA\Property(property="price", type="number", format="float"),
     *             @OA\Property(property="duration", type="integer", description="Duration in days"),
     *             @OA\Property(
     *                 property="features",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="name", type="string"),
     *                     @OA\Property(property="value", type="string")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Subscription plan updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/SubscriptionPlan")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function update(Request $request, SubscriptionPlan $subscriptionPlan)
    {
        $validated = $request->validate([
            'name' => 'string|max:255',
            'description' => 'nullable|string',
            'price' => 'numeric|min:0',
            'duration_in_days' => 'integer|min:1',
            'features' => 'nullable|array',
            'property_limit' => 'integer|min:0',
            'is_active' => 'boolean',
            'sort_order' => 'integer'
        ]);

        if ($request->has('name')) {
            $validated['slug'] = Str::slug($request->name);
        }

        $subscriptionPlan->update($validated);
        return $subscriptionPlan;
    }

    /**
     * @OA\Delete(
     *     path="/api/subscription-plans/{plan}",
     *     summary="Delete a subscription plan",
     *     tags={"Subscription Plans"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="plan",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Subscription plan deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Cannot delete plan with active subscriptions"
     *     )
     * )
     */
    public function destroy(SubscriptionPlan $plan)
    {
        $plan->delete();
        return response()->noContent();
    }
}
