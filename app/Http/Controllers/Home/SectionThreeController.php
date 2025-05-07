<?php

namespace App\Http\Controllers\Home;

use App\Http\Controllers\Controller;
use App\Models\SectionThree;
use Illuminate\Http\Request;

class SectionThreeController extends Controller
{
    public function createSectionThree(Request $request)
    {
        try {
            $user = auth()->user();
            if (!$user || $user->role !== 'admin') {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            $validatedData = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string|max:1000',
                'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);


            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();

                $destinationPath = public_path('uploads/sections');
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0755, true);
                }

                $image->move($destinationPath, $imageName);
                $fullImageUrl = url('public/uploads/sections/' . $imageName);
                $validatedData['image'] = $fullImageUrl;
            }


            $section = SectionThree::create($validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Section Three created successfully',
                'data' => $section,
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

    public function getAllSectionThree()
    {
        $sections = SectionThree::orderBy('created_at', 'desc')->get();
        return response()->json(['success' => true, 'message' => 'Sections fetched successfully', 'data' => $sections], 200);
    }
    public function getSectionThreeById($id)
    {
        $section = SectionThree::find($id);
        if ($section) {
            return response()->json(['success' => true, 'message' => 'Section fetched successfully', 'data' => $section], 200);
        } else {
            return response()->json(['success' => false, 'message' => 'Section not found'], 404);
        }
    }
    public function updateSectionThree(Request $request, $id)
    {
        try {
            $user = auth()->user();
            if (!$user || $user->role !== 'admin') {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            $section = SectionThree::find($id);
            if (!$section) {
                return response()->json(['success' => false, 'message' => 'Section not found'], 404);
            }

            $validatedData = $request->validate([
                'title' => 'nullable|string|max:255',
                'description' => 'nullable|string|max:1000',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();

                $destinationPath = public_path('uploads/sections');
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0755, true);
                }

                $image->move($destinationPath, $imageName);
                $fullImageUrl = url('public/uploads/sections/' . $imageName);
                $validatedData['image'] = $fullImageUrl;
            }

            $section->update($validatedData);

            return response()->json(['success' => true, 'message' => 'Section updated successfully', 'data' => $section], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Something went wrong.', 'error' => $e->getMessage()], 500);
        }
    }
    public function deleteSectionThree($id)
    {
        $user = auth()->user();
        if (!$user || $user->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $section = SectionThree::find($id);
        if (!$section) {
            return response()->json(['success' => false, 'message' => 'Section not found'], 404);
        }

        $section->delete();

        return response()->json(['success' => true, 'message' => 'Section deleted successfully'], 200);
    }
}
