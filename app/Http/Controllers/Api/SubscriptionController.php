<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubscriptionController extends Controller
{
    public function index()
    {
        return Auth::user()->subscriptions()
            ->with('plan')
            ->latest()
            ->get();
    }

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

    public function show(Subscription $subscription)
    {
        return $subscription->load('plan');
    }

    public function cancel(Subscription $subscription)
    {
        if ($subscription->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $subscription->update(['status' => 'cancelled']);
        return response()->json($subscription->load('plan'));
    }
}
