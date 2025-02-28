<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\ScheduleCreated;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Schedules",
 *     description="API Endpoints for managing property viewing schedules"
 * )
 */
class ScheduleController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/properties/{property}/schedules",
     *     summary="Get list of property viewing schedules",
     *     tags={"Schedules"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="property",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of property schedules",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Schedule")),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     )
     * )
     */
    public function index(Property $property)
    {

        $schedules = $property->schedules()
            ->with('user')
            ->latest()
            ->paginate(20);
        return response()->json($schedules);
    }

    /**
     * @OA\Post(
     *     path="/api/properties/{property}/schedules",
     *     summary="Create a new viewing schedule",
     *     tags={"Schedules"},
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
     *             required={"date", "time", "message"},
     *             @OA\Property(property="date", type="string", format="date"),
     *             @OA\Property(property="time", type="string", format="time"),
     *             @OA\Property(property="message", type="string", minLength=10)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Schedule created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Schedule")
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
            'date' => 'required|date|after:today',
            'time' => 'required|date_format:H:i',
            'message' => 'nullable|string'
        ]);

        $schedule = $property->schedules()->create([
            'user_id' => Auth::id(),
            'date' => $validated['date'],
            'time' => $validated['time'],
            'message' => $validated['message'],
            'status' => 'pending'
        ]);
        //send email to admin and property owner
        $admin = User::where('role', 'admin')->first();
        $propertyOwner = $property->user;

        Mail::to($admin->email)->send(new ScheduleCreated($schedule));
        Mail::to($propertyOwner->email)->send(new ScheduleCreated($schedule));

        return response()->json($schedule->load('user'), 201);
    }

    /**
     * @OA\Get(
     *     path="/api/properties/{property}/schedules/{schedule}",
     *     summary="Get viewing schedule details",
     *     tags={"Schedules"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="property",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="schedule",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Schedule details",
     *         @OA\JsonContent(ref="#/components/schemas/Schedule")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Schedule not found"
     *     )
     * )
     */
    public function show(Property $property, Schedule $schedule)
    {

        if ($schedule->property_id !== $property->id) {
            return response()->json(['message' => 'Schedule not found for this property'], 404);
        }

        return response()->json($schedule->load('user'));
    }

    /**
     * @OA\Put(
     *     path="/api/properties/{property}/schedules/{schedule}/status",
     *     summary="Update viewing schedule status",
     *     tags={"Schedules"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="property",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="schedule",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"status"},
     *             @OA\Property(property="status", type="string", enum={"pending", "approved", "rejected", "completed"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Schedule status updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Schedule")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized to update this schedule"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function updateStatus(Request $request, Property $property, Schedule $schedule)
    {

        if ($schedule->property_id !== $property->id) {
            return response()->json(['message' => 'Schedule not found for this property'], 404);
        }

        $validated = $request->validate([
            'status' => 'required|in:pending,approved,rejected,completed,cancelled'
        ]);

        $schedule->update($validated);

        return response()->json($schedule->load('user'));
    }

    /**
     * @OA\Delete(
     *     path="/api/properties/{property}/schedules/{schedule}",
     *     summary="Delete a viewing schedule",
     *     tags={"Schedules"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="property",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="schedule",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Schedule deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized to delete this schedule"
     *     )
     * )
     */
    public function destroy(Property $property, Schedule $schedule)
    {

        if ($schedule->property_id !== $property->id) {
            return response()->json(['message' => 'Schedule not found for this property'], 404);
        }

        $schedule->delete();
        return response()->json(null, 204);
    }
}
