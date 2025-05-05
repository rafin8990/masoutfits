<?php

namespace App\Http\Controllers\Home;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function createContact(Request $request)
    {
        try {
            $user = auth()->user();
            if (!$user || $user->role !== 'admin') {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            $validated = $request->validate([
                'description' => 'nullable|string',
                'email' => 'nullable|email',
            ]);

            $contact = Contact::create([
                'description' => $validated['description'] ?? null,
                'email' => $validated['email'] ?? null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Contact created successfully',
                'data' => $contact,
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getAllContact()
    {
        try {
            $contact = Contact::orderBy('created_at', 'desc')->get();
            return response()->json([
                'success' => true,
                'message' => 'Contact fetched successfully',
                'data' => $contact,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function getContactById($id)
    {
        try {
            $contact = Contact::find($id);
            if ($contact) {
                return response()->json([
                    'success' => true,
                    'message' => 'Contact fetched successfully',
                    'data' => $contact,
                ], 200);
            } else {
                return response()->json(['success' => false, 'message' => 'Contact not found'], 404);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function updateContact(Request $request, $id)
    {
        try {
            $user = auth()->user();
            if (!$user || $user->role !== 'admin') {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            $contact = Contact::find($id);
            if (!$contact) {
                return response()->json(['success' => false, 'message' => 'Contact not found'], 404);
            }

            $validated = $request->validate([
                'description' => 'nullable|string',
                'email' => 'nullable|email',
            ]);

            $contact->update([
                'description' => $validated['description'] ?? null,
                'email' => $validated['email'] ?? null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Contact updated successfully',
                'data' => $contact,
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function deleteContact($id)
    {
        try {
            $user = auth()->user();
            if (!$user || $user->role !== 'admin') {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            $contact = Contact::find($id);
            if (!$contact) {
                return response()->json(['success' => false, 'message' => 'Contact not found'], 404);
            }

            $contact->delete();

            return response()->json(['success' => true, 'message' => 'Contact deleted successfully'], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
