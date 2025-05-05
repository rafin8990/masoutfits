<?php

namespace App\Http\Controllers\Home;

use App\Http\Controllers\Controller;
use App\Models\SectionOne;
use Illuminate\Http\Request;

class SectionOneController extends Controller
{
    public function createSectionOne(Request $request)
    {
        $user = auth()->user();
        if (!$user->role == 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }
        $validated = $request->validate([
            'title' => 'nullable|string',
            'description' => 'nullable|string',
            'sections' => 'nullable|array',
            'sections.*.title' => 'required|string',
            'sections.*.short_des' => 'required|string',
        ]);

        $sectionOne = SectionOne::create([
            'title' => $validated['title'] ?? null,
            'description' => $validated['description'] ?? null,
            'sections' => $validated['sections'] ?? [],
        ]);

        return response()->json(['success' => true, 'message' => 'Section One created successfully', 'data' => $sectionOne], 201);
    }

    public function getAllSectionOne()
    {
        $sectionOne = SectionOne::orderBy('created_at', 'desc')->get();
        return response()->json(['success' => true, 'message' => 'Section One fetched successfully', 'data' => $sectionOne], 200);
    }

    public function getSectionOneById($id)
    {
        $sectionOne = SectionOne::find($id);
        if ($sectionOne) {
            return response()->json(['success' => true, 'message' => 'Section One fetched successfully', 'data' => $sectionOne], 200);
        } else {
            return response()->json(['success' => false, 'message' => 'Section One not found'], 404);
        }
    }
    public function updateSectionOne(Request $request, $id)
    {
        $user = auth()->user();
        if (!$user->role == 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }
        $validated = $request->validate([
            'title' => 'nullable|string',
            'description' => 'nullable|string',
            'sections' => 'nullable|array',
            'sections.*.title' => 'required|string',
            'sections.*.short_des' => 'required|string',
        ]);

        $sectionOne = SectionOne::find($id);
        if (!$sectionOne) {
            return response()->json(['success' => false, 'message' => 'Section One not found'], 404);
        }

        $sectionOne->update([
            'title' => $validated['title'] ?? null,
            'description' => $validated['description'] ?? null,
            'sections' => $validated['sections'] ?? [],
        ]);

        return response()->json(['success' => true, 'message' => 'Section One updated successfully', 'data' => $sectionOne], 200);
    }
    public function deleteSectionOne($id)
    {
        $user = auth()->user();
        if (!$user->role == 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }
        $sectionOne = SectionOne::find($id);
        if (!$sectionOne) {
            return response()->json(['success' => false, 'message' => 'Section One not found'], 404);
        }

        $sectionOne->delete();

        return response()->json(['success' => true, 'message' => 'Section One deleted successfully'], 200);
    }
    public function deleteAllSectionOne()
    {
        $user = auth()->user();
        if (!$user->role == 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }
        SectionOne::truncate();

        return response()->json(['success' => true, 'message' => 'All Section One deleted successfully'], 200);
    }

}
