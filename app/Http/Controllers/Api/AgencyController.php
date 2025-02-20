<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Agency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AgencyController extends Controller
{
    public function index()
    {
        $agencies = Agency::withCount('agents')->paginate(10);
        return response()->json($agencies);
    }

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

    public function show(Agency $agency)
    {
        return response()->json($agency->load(['agents' => function ($query) {
            $query->withCount('properties');
        }]));
    }

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

    public function destroy(Agency $agency)
    {
        $agency->delete();
        return response()->json(null, 204);
    }

    public function agents(Agency $agency)
    {
        $agents = $agency->agents()->withCount('properties')->paginate(10);
        return response()->json($agents);
    }
}
