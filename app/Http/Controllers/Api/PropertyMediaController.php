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
        $this->authorize('update', $property);

        $validated = $request->validate([
            'type' => 'required|in:image,video',
            'file' => 'required|file|mimes:jpg,jpeg,png,mp4|max:10240', // 10MB max
            'is_featured' => 'boolean',
            'sort_order' => 'integer'
        ]);

        $path = $request->file('file')->store('property-media', 'public');

        $media = $property->media()->create([
            'type' => $validated['type'],
            'url' => $path,
            'is_featured' => $validated['is_featured'] ?? false,
            'sort_order' => $validated['sort_order'] ?? 0
        ]);

        return response()->json($media, 201);
    }

    public function update(Request $request, Property $property, PropertyMedia $media)
    {
        $this->authorize('update', $property);

        $validated = $request->validate([
            'is_featured' => 'boolean',
            'sort_order' => 'integer'
        ]);

        $media->update($validated);

        return response()->json($media);
    }

    public function destroy(Property $property, PropertyMedia $media)
    {
        $this->authorize('update', $property);

        // Delete the file from storage
        if (Storage::disk('public')->exists($media->url)) {
            Storage::disk('public')->delete($media->url);
        }

        $media->delete();
        return response()->json(null, 204);
    }

    public function reorder(Request $request, Property $property)
    {
        $this->authorize('update', $property);

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
