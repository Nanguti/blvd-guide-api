<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\PropertyMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Property Media",
 *     description="API Endpoints for managing property media files"
 * )
 */
class PropertyMediaController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/properties/{property}/media",
     *     summary="Get list of property media",
     *     tags={"Property Media"},
     *     @OA\Parameter(
     *         name="property",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of property media files",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/PropertyMedia"))
     *     )
     * )
     */
    public function index(Property $property)
    {
        return response()->json($property->media()->orderBy('sort_order')->get());
    }

    /**
     * @OA\Post(
     *     path="/api/properties/{property}/media",
     *     summary="Upload property media files",
     *     tags={"Property Media"},
     *     @OA\Parameter(
     *         name="property",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"type", "images"},
     *             @OA\Property(property="type", type="string", enum={"image", "video", "virtual_tour"}),
     *             @OA\Property(property="images", type="array", @OA\Items(type="string", format="binary"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Media files uploaded successfully",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/PropertyMedia"))
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
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

    /**
     * @OA\Put(
     *     path="/api/properties/{property}/media/{media}",
     *     summary="Update property media details",
     *     tags={"Property Media"},
     *     @OA\Parameter(
     *         name="property",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="media",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="is_featured", type="boolean"),
     *             @OA\Property(property="sort_order", type="integer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Media details updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/PropertyMedia")
     *     )
     * )
     */
    public function update(Request $request, Property $property, PropertyMedia $media)
    {

        $validated = $request->validate([
            'is_featured' => 'boolean',
            'sort_order' => 'integer'
        ]);

        $media->update($validated);

        return response()->json($media);
    }

    /**
     * @OA\Delete(
     *     path="/api/properties/{property}/media/{media}",
     *     summary="Delete property media",
     *     tags={"Property Media"},
     *     @OA\Parameter(
     *         name="property",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="media",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Media deleted successfully"
     *     )
     * )
     */
    public function destroy(Property $property, PropertyMedia $media)
    {

        // Delete the file from storage
        if (Storage::disk('public')->exists($media->url)) {
            Storage::disk('public')->delete($media->url);
        }

        $media->delete();
        return response()->json(null, 204);
    }

    /**
     * @OA\Put(
     *     path="/api/properties/{property}/media/reorder",
     *     summary="Reorder property media files",
     *     tags={"Property Media"},
     *     @OA\Parameter(
     *         name="property",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"media"},
     *             @OA\Property(
     *                 property="media",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     required={"id", "sort_order"},
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="sort_order", type="integer")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Media files reordered successfully",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/PropertyMedia"))
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
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
