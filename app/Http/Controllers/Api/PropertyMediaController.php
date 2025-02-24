<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\PropertyMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PropertyMediaController extends Controller
{
    public function index(Property $property)
    {
        return response()->json($property->media()->orderBy('sort_order')->get());
    }

    public function store(Request $request, Property $property)
    {
        $request->validate([
            'images.*' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'type' => 'required|in:image,video,virtual_tour'
        ]);

        $mediaItems = [];

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('properties/' . $property->id . '/media', 'public');

                $mediaItems[] = $property->media()->create([
                    'url' => $path,
                    'type' => $request->type,
                    'sort_order' => $property->media()->count() + 1
                ]);
            }
        }

        return response()->json($mediaItems, 201);
    }

    public function update(Request $request, Property $property, PropertyMedia $media)
    {

        $validated = $request->validate([
            'is_featured' => 'boolean',
            'sort_order' => 'integer'
        ]);

        $media->update($validated);

        return response()->json($media);
    }

    public function destroy(Property $property, PropertyMedia $media)
    {

        // Delete the file from storage
        if (Storage::disk('public')->exists($media->url)) {
            Storage::disk('public')->delete($media->url);
        }

        $media->delete();
        return response()->json(null, 204);
    }

    public function reorder(Request $request, Property $property)
    {

        $validated = $request->validate([
            'media' => 'required|array',
            'media.*.id' => 'required|exists:property_media,id',
            'media.*.sort_order' => 'required|integer'
        ]);

        foreach ($validated['media'] as $item) {
            PropertyMedia::where('id', $item['id'])
                ->where('property_id', $property->id)
                ->update(['sort_order' => $item['sort_order']]);
        }

        return response()->json($property->media()->orderBy('sort_order')->get());
    }
}
