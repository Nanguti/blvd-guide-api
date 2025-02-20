<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PropertyStatus;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PropertyStatusController extends Controller
{
    public function index()
    {
        $statuses = PropertyStatus::withCount('properties')->get();
        return response()->json($statuses);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:property_statuses',
            'description' => 'nullable|string'
        ]);

        $status = PropertyStatus::create($validated);

        return response()->json($status, 201);
    }

    public function show(PropertyStatus $propertyStatus)
    {
        return response()->json($propertyStatus->load(['properties' => function ($query) {
            $query->where('published_status', 'published')
                ->latest()
                ->limit(10);
        }]));
    }

    public function update(Request $request, PropertyStatus $propertyStatus)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('property_statuses')->ignore($propertyStatus->id)],
            'description' => 'nullable|string'
        ]);

        $propertyStatus->update($validated);

        return response()->json($propertyStatus);
    }

    public function destroy(PropertyStatus $propertyStatus)
    {
        if ($propertyStatus->properties()->exists()) {
            return response()->json(['message' => 'Cannot delete property status with associated properties'], 422);
        }

        $propertyStatus->delete();
        return response()->json(null, 204);
    }
}
