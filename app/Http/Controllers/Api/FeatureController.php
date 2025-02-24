<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Feature;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FeatureController extends Controller
{
    public function index()
    {
        return Feature::all();
    }

    public function show(Feature $feature)
    {
        return $feature;
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:255',
        ]);

        $slug = Str::slug($request->name);
        if (Feature::where('slug', $slug)->exists()) {
            return response()->json(['message' => 'Feature already exists'], 400);
        }
        $data['slug'] = $slug;

        return Feature::create($data);
    }

    public function update(Request $request, Feature $feature)
    {
        $data = $request->validate([
            'name' => 'string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:255',
        ]);

        if ($request->has('name')) {
            $slug = Str::slug($request->name);
            if (Feature::where('slug', $slug)
                ->where('id', '!=', $feature->id)->exists()
            ) {
                return response()->json(['message' => 'Feature with this name already exists'], 400);
            }
            $data['slug'] = $slug;
        }

        $feature->update($data);
        return $feature;
    }

    public function destroy(Feature $feature)
    {
        $feature->delete();
        return response()->noContent();
    }
}
