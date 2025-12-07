<?php

namespace App\Http\Controllers\MediaCoverage;

use App\Http\Controllers\Controller;
use App\Models\MediaCoverage;
use Illuminate\Http\Request;

class MediaCoverageController extends Controller
{
    public function createMediaCoverage(Request $request)
    {
        try {
            $user = auth()->user();
            if (!$user || $user->role !== 'admin') {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            $validated = $request->validate([
                'title' => 'nullable|string',
                'description' => 'nullable|string',
            ]);

            $mediaCoverage = MediaCoverage::create([
                'title' => $validated['title'] ?? null,
                'description' => $validated['description'] ?? null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Media coverage created successfully',
                'data' => $mediaCoverage,
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

    public function getAllMediaCoverage()
    {
        try {
            $mediaCoverage = MediaCoverage::orderBy('created_at', 'desc')->get();
            return response()->json([
                'success' => true,
                'message' => 'Media coverage fetched successfully',
                'data' => $mediaCoverage,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getMediaCoverageById($id)
    {
        try {
            $mediaCoverage = MediaCoverage::find($id);
            if ($mediaCoverage) {
                return response()->json([
                    'success' => true,
                    'message' => 'Media coverage fetched successfully',
                    'data' => $mediaCoverage,
                ], 200);
            } else {
                return response()->json(['success' => false, 'message' => 'Media coverage not found'], 404);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function updateMediaCoverage(Request $request, $id)
    {
        try {
            $user = auth()->user();
            if (!$user || $user->role !== 'admin') {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            $mediaCoverage = MediaCoverage::find($id);
            if (!$mediaCoverage) {
                return response()->json(['success' => false, 'message' => 'Media coverage not found'], 404);
            }

            $validated = $request->validate([
                'title' => 'nullable|string',
                'description' => 'nullable|string',
            ]);

            $mediaCoverage->update([
                'title' => $validated['title'] ?? null,
                'description' => $validated['description'] ?? null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Media coverage updated successfully',
                'data' => $mediaCoverage,
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

    public function deleteMediaCoverage($id)
    {
        try {
            $user = auth()->user();
            if (!$user || $user->role !== 'admin') {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            $mediaCoverage = MediaCoverage::find($id);
            if (!$mediaCoverage) {
                return response()->json(['success' => false, 'message' => 'Media coverage not found'], 404);
            }

            $mediaCoverage->delete();

            return response()->json(['success' => true, 'message' => 'Media coverage deleted successfully'], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

