<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Property;
use App\Models\PropertyInquiry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\PropertyInquiryCreated;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Property Inquiries",
 *     description="API Endpoints for managing property inquiries"
 * )
 */
class PropertyInquiryController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/properties/{property}/inquiries",
     *     summary="Get list of property inquiries",
     *     tags={"Property Inquiries"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="property",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of property inquiries",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/PropertyInquiry")),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     )
     * )
     */
    public function index(Property $property)
    {
        $inquiries = $property->inquiries()
            ->with('user')
            ->latest()
            ->paginate(20);
        return response()->json($inquiries);
    }

    /**
     * @OA\Post(
     *     path="/api/properties/{property}/inquiries",
     *     summary="Create a new property inquiry",
     *     tags={"Property Inquiries"},
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
     *             required={"message"},
     *             @OA\Property(property="message", type="string", minLength=10)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Inquiry created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/PropertyInquiry")
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
            'message' => 'required|string|min:10'
        ]);

        $inquiry = $property->inquiries()->create([
            'user_id' => Auth::id(),
            'message' => $validated['message'],
            'status' => 'new'
        ]);

        //when inquiry is created, send email to admin and property owner
        $admin = User::where('role', 'admin')->first();
        $propertyOwner = $property->user;

        Mail::to($admin->email)->send(new PropertyInquiryCreated($inquiry));
        Mail::to($propertyOwner->email)->send(new PropertyInquiryCreated($inquiry));

        return response()->json($inquiry->load('user'), 201);
    }

    /**
     * @OA\Get(
     *     path="/api/properties/{property}/inquiries/{inquiry}",
     *     summary="Get property inquiry details",
     *     tags={"Property Inquiries"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="property",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="inquiry",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Property inquiry details",
     *         @OA\JsonContent(ref="#/components/schemas/PropertyInquiry")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Inquiry not found for this property"
     *     )
     * )
     */
    public function show(Property $property, PropertyInquiry $inquiry)
    {

        if ($inquiry->property_id !== $property->id) {
            return response()->json(['message' => 'Inquiry not found for this property'], 404);
        }

        return response()->json($inquiry->load('user'));
    }

    /**
     * @OA\Put(
     *     path="/api/properties/{property}/inquiries/{inquiry}/status",
     *     summary="Update property inquiry status",
     *     tags={"Property Inquiries"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="property",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="inquiry",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"status"},
     *             @OA\Property(property="status", type="string", enum={"new", "read", "replied"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Inquiry status updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/PropertyInquiry")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Inquiry not found for this property"
     *     )
     * )
     */
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

    /**
     * @OA\Delete(
     *     path="/api/properties/{property}/inquiries/{inquiry}",
     *     summary="Delete a property inquiry",
     *     tags={"Property Inquiries"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="property",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="inquiry",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Inquiry deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Inquiry not found for this property"
     *     )
     * )
     */
    public function destroy(Property $property, PropertyInquiry $inquiry)
    {

        if ($inquiry->property_id !== $property->id) {
            return response()->json(['message' => 'Inquiry not found for this property'], 404);
        }

        $inquiry->delete();
        return response()->json(null, 204);
    }
}
