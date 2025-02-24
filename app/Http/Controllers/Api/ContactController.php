<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Mail\NewContactMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        // $this->authorize('viewAny', Contact::class);

        $contacts = Contact::latest()->paginate(20);
        return response()->json($contacts);
    }

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

    public function show(Contact $contact)
    {
        $this->authorize('view', $contact);
        return response()->json($contact);
    }

    public function updateStatus(Request $request, Contact $contact)
    {
        $this->authorize('update', $contact);

        $validated = $request->validate([
            'status' => 'required|in:new,read,replied'
        ]);

        $contact->update($validated);

        return response()->json($contact);
    }

    public function destroy(Contact $contact)
    {
        $this->authorize('delete', $contact);

        $contact->delete();
        return response()->json(null, 204);
    }
}
