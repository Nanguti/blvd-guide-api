<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(Property $property)
    {
        $reviews = $property->reviews()
            ->with('user')
            ->where('status', 'approved')
            ->latest()
            ->paginate(10);
        return response()->json($reviews);
    }

    public function store(Request $request, Property $property)
    {
        $validated = $request->validate([
            'rating' => 'required|numeric|min:1|max:5',
            'comment' => 'required|string|min:10',
        ]);

        $review = $property->reviews()->create([
            'user_id' => auth()->id(),
            'rating' => $validated['rating'],
            'comment' => $validated['comment'],
            'status' => 'pending'
        ]);

        return response()->json($review->load('user'), 201);
    }

    public function update(Request $request, Property $property, Review $review)
    {
        $this->authorize('update', $review);

        $validated = $request->validate([
            'rating' => 'sometimes|numeric|min:1|max:5',
            'comment' => 'sometimes|string|min:10',
        ]);

        $review->update($validated);

        return response()->json($review->load('user'));
    }

    public function destroy(Property $property, Review $review)
    {
        $this->authorize('delete', $review);

        $review->delete();
        return response()->json(null, 204);
    }

    public function updateStatus(Request $request, Property $property, Review $review)
    {
        $this->authorize('updateStatus', $review);

        $validated = $request->validate([
            'status' => 'required|in:pending,approved,rejected'
        ]);

        $review->update($validated);

        return response()->json($review->load('user'));
    }
}
