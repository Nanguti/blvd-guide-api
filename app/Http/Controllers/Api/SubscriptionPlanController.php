<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SubscriptionPlanController extends Controller
{
    public function index()
    {
        return SubscriptionPlan::where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }

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

    public function show(SubscriptionPlan $plan)
    {
        return $plan;
    }

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

    public function destroy(SubscriptionPlan $plan)
    {
        $plan->delete();
        return response()->noContent();
    }
}
