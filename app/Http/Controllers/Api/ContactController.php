<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Mail\NewContactMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Contacts",
 *     description="API Endpoints for managing contact messages"
 * )
 */
class ContactController extends Controller
{
    use AuthorizesRequests;

    /**
     * @OA\Get(
     *     path="/api/contacts",
     *     summary="Get list of contact messages",
     *     tags={"Contacts"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of contact messages",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Contact")),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     )
     * )
     */
    public function index()
    {
        // $this->authorize('viewAny', Contact::class);

        $contacts = Contact::latest()->paginate(20);
        return response()->json($contacts);
    }

    /**
     * @OA\Post(
     *     path="/api/contacts",
     *     summary="Create a new contact message",
     *     tags={"Contacts"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "email", "message"},
     *             @OA\Property(property="name", type="string", maxLength=255),
     *             @OA\Property(property="email", type="string", format="email"),
     *             @OA\Property(property="phone", type="string", maxLength=20, nullable=true),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Contact message created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Contact")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to process email"
     *     )
     * )
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'message' => 'required|string'
        ]);

        $contact = Contact::create($validated + ['status' => 'new']);

        try {
            Mail::to('info@blvdguide.com')
                ->queue(new NewContactMail($contact));
        } catch (\Exception $e) {
            Log::error('Failed to queue email: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to process email'], 500);
        }

        return response()->json($contact, 201);
    }

    /**
     * @OA\Get(
     *     path="/api/contacts/{contact}",
     *     summary="Get contact message details",
     *     tags={"Contacts"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="contact",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Contact message details",
     *         @OA\JsonContent(ref="#/components/schemas/Contact")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized action"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Contact message not found"
     *     )
     * )
     */
    public function show(Contact $contact)
    {
        $this->authorize('view', $contact);
        return response()->json($contact);
    }

    /**
     * @OA\Put(
     *     path="/api/contacts/{contact}/status",
     *     summary="Update contact message status",
     *     tags={"Contacts"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="contact",
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
     *         description="Contact message status updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Contact")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized action"
     *     )
     * )
     */
    public function updateStatus(Request $request, Contact $contact)
    {
        $this->authorize('update', $contact);

        $validated = $request->validate([
            'status' => 'required|in:new,read,replied'
        ]);

        $contact->update($validated);

        return response()->json($contact);
    }

    /**
     * @OA\Delete(
     *     path="/api/contacts/{contact}",
     *     summary="Delete a contact message",
     *     tags={"Contacts"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="contact",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Contact message deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized action"
     *     )
     * )
     */
    public function destroy(Contact $contact)
    {
        $this->authorize('delete', $contact);

        $contact->delete();
        return response()->json(null, 204);
    }
}
