<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ScheduleController extends Controller
{
    public function index(Property $property)
    {

        $schedules = $property->schedules()
            ->with('user')
            ->latest()
            ->paginate(20);
        return response()->json($schedules);
    }

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

        return response()->json($schedule->load('user'), 201);
    }

    public function show(Property $property, Schedule $schedule)
    {

        if ($schedule->property_id !== $property->id) {
            return response()->json(['message' => 'Schedule not found for this property'], 404);
        }

        return response()->json($schedule->load('user'));
    }

    public function update(Request $request, Property $property, Schedule $schedule)
    {
        //if field is not passed, use the current value
        $validated = [];
        if ($request->has('date')) {
            $validated['date'] = $request->date;
        }
        if ($request->has('time')) {
            $validated['time'] = $request->time;
        }
        if ($request->has('message')) {
            $validated['message'] = $request->message;
        }


        $schedule->update($validated);

        return response()->json($schedule->load('user'));
    }

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

    public function destroy(Property $property, Schedule $schedule)
    {

        if ($schedule->property_id !== $property->id) {
            return response()->json(['message' => 'Schedule not found for this property'], 404);
        }

        $schedule->delete();
        return response()->json(null, 204);
    }
}
