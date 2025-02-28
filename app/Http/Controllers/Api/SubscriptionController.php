<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Subscriptions",
 *     description="API Endpoints for managing user subscriptions"
 * )
 */
class SubscriptionController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/subscriptions",
     *     summary="Get list of user subscriptions",
     *     tags={"Subscriptions"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of user subscriptions",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Subscription")),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     )
     * )
     */
    public function index()
    {
        return Auth::user()->subscriptions()
            ->with('plan')
            ->latest()
            ->get();
    }

    /**
     * @OA\Post(
     *     path="/api/subscriptions",
     *     summary="Create a new subscription",
     *     tags={"Subscriptions"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"plan_id", "payment_method"},
     *             @OA\Property(property="plan_id", type="integer"),
     *             @OA\Property(property="payment_method", type="string", enum={"stripe", "paypal"}),
     *             @OA\Property(property="payment_token", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Subscription created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Subscription")
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
            'subscription_plan_id' => 'required|exists:subscription_plans,id',
            'payment_method' => 'required|string',
        ]);

        $plan = SubscriptionPlan::findOrFail($validated['subscription_plan_id']);

        $subscription = Auth::user()->subscriptions()->create([
            'subscription_plan_id' => $plan->id,
            'starts_at' => now(),
            'ends_at' => now()->addDays($plan->duration_in_days),
            'status' => 'active',
            'payment_status' => 'pending',
            'payment_method' => $validated['payment_method']
        ]);

        return response()->json($subscription->load('plan'), 201);
    }

    /**
     * @OA\Get(
     *     path="/api/subscriptions/{subscription}",
     *     summary="Get subscription details",
     *     tags={"Subscriptions"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="subscription",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Subscription details",
     *         @OA\JsonContent(ref="#/components/schemas/Subscription")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Subscription not found"
     *     )
     * )
     */
    public function show(Subscription $subscription)
    {
        return $subscription->load('plan');
    }

    /**
     * @OA\Put(
     *     path="/api/subscriptions/{subscription}/cancel",
     *     summary="Cancel a subscription",
     *     tags={"Subscriptions"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="subscription",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Subscription cancelled successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Subscription")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized to cancel this subscription"
     *     )
     * )
     */
    public function cancel(Subscription $subscription)
    {
        if ($subscription->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $subscription->update(['status' => 'cancelled']);
        return response()->json($subscription->load('plan'));
    }

    /**
     * @OA\Put(
     *     path="/api/subscriptions/{subscription}/resume",
     *     summary="Resume a cancelled subscription",
     *     tags={"Subscriptions"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="subscription",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Subscription resumed successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Subscription")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized to resume this subscription"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Cannot resume this subscription"
     *     )
     * )
     */
    public function resume(Subscription $subscription)
    {
        // ... existing code ...
    }
}
