<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Reviews",
 *     description="API Endpoints for managing property reviews"
 * )
 */
class ReviewController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/properties/{property}/reviews",
     *     summary="Get list of property reviews",
     *     tags={"Reviews"},
     *     @OA\Parameter(
     *         name="property",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of property reviews",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Review")),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     )
     * )
     */
    public function index(Property $property)
    {
        $reviews = $property->reviews()
            ->with('user')
            ->where('status', 'approved')
            ->latest()
            ->paginate(10);
        return response()->json($reviews);
    }

    /**
     * @OA\Post(
     *     path="/api/properties/{property}/reviews",
     *     summary="Create a new property review",
     *     tags={"Reviews"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="property",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"rating", "comment"},
     *             @OA\Property(property="rating", type="integer", minimum=1, maximum=5),
     *             @OA\Property(property="comment", type="string", minLength=10)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Review created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Review")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function store(Request $request, Property $property)
    {
        $validated = $request->validate([
            'rating' => 'required|numeric|min:1|max:5',
            'comment' => 'required|string|min:10',
        ]);

        $review = $property->reviews()->create([
            'user_id' => Auth::id(),
            'rating' => $validated['rating'],
            'comment' => $validated['comment'],
            'status' => 'pending'
        ]);

        return response()->json($review->load('user'), 201);
    }

    /**
     * @OA\Get(
     *     path="/api/properties/{property}/reviews/{review}",
     *     summary="Get property review details",
     *     tags={"Reviews"},
     *     @OA\Parameter(
     *         name="property",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="review",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Property review details",
     *         @OA\JsonContent(ref="#/components/schemas/Review")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Review not found"
     *     )
     * )
     */
    public function show(Property $property, Review $review)
    {
        // ... existing code ...
    }

    /**
     * @OA\Put(
     *     path="/api/properties/{property}/reviews/{review}",
     *     summary="Update property review",
     *     tags={"Reviews"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="property",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="review",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"rating", "comment"},
     *             @OA\Property(property="rating", type="integer", minimum=1, maximum=5),
     *             @OA\Property(property="comment", type="string", minLength=10)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Review updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Review")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized to update this review"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function update(Request $request, Property $property, Review $review)
    {
        $validated = $request->validate([
            'rating' => 'sometimes|numeric|min:1|max:5',
            'comment' => 'sometimes|string|min:10',
        ]);

        $review->update($validated);

        return response()->json($review->load('user'));
    }

    /**
     * @OA\Delete(
     *     path="/api/properties/{property}/reviews/{review}",
     *     summary="Delete a property review",
     *     tags={"Reviews"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="property",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="review",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Review deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized to delete this review"
     *     )
     * )
     */
    public function destroy(Property $property, Review $review)
    {
        $review->delete();
        return response()->json(null, 204);
    }

    public function updateStatus(Request $request, Property $property, Review $review)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,approved,rejected'
        ]);

        $review->update($validated);

        return response()->json($review->load('user'));
    }
}
