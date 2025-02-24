<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\PropertyInquiry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PropertyInquiryController extends Controller
{
    public function index(Property $property)
    {
        $inquiries = $property->inquiries()
            ->with('user')
            ->latest()
            ->paginate(20);
        return response()->json($inquiries);
    }

    public function store(Request $request, Property $property)
    {
        $validated = $request->validate([
            'message' => 'required|string|min:10'
        ]);

        $inquiry = $property->inquiries()->create([
            'user_id' => Auth::id(),
            'message' => $validated['message'],
            'status' => 'new'
        ]);

        return response()->json($inquiry->load('user'), 201);
    }

    public function show(Property $property, PropertyInquiry $inquiry)
    {

        if ($inquiry->property_id !== $property->id) {
            return response()->json(['message' => 'Inquiry not found for this property'], 404);
        }

        return response()->json($inquiry->load('user'));
    }

    public function updateStatus(Request $request, Property $property, PropertyInquiry $inquiry)
    {

        if ($inquiry->property_id !== $property->id) {
            return response()->json(['message' => 'Inquiry not found for this property'], 404);
        }

        $validated = $request->validate([
            'status' => 'required|in:new,read,replied'
        ]);

        $inquiry->update($validated);

        return response()->json($inquiry->load('user'));
    }

    public function destroy(Property $property, PropertyInquiry $inquiry)
    {

        if ($inquiry->property_id !== $property->id) {
            return response()->json(['message' => 'Inquiry not found for this property'], 404);
        }

        $inquiry->delete();
        return response()->json(null, 204);
    }
}
