<?php

namespace App\Http\Controllers\Home;

use App\Http\Controllers\Controller;
use App\Models\SectionTwo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class SectionTwoController extends Controller
{
    public function createSectionTwo(Request $request)
    {
        try {
            $user = auth()->user();
            if (!$user || $user->role !== 'admin') {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }


            $validator = Validator::make($request->all(), [
                'title' => 'nullable|string',
                'description' => 'nullable|string',
                'sections' => 'required|array',
                'sections.*.title' => 'required|string',
                'sections.*.short_des' => 'required|string',
                'sections.*.image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $validated = $validator->validated();


            foreach ($validated['sections'] as $key => $section) {
                $image = $section['image'];
                $imageName = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();

                $destinationPath = public_path('uploads/sections');
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0755, true);
                }

                $image->move($destinationPath, $imageName);

                $validated['sections'][$key]['image'] = url('public/uploads/sections/' . $imageName);
            }

            $sectionTwo = SectionTwo::create([
                'title' => $validated['title'] ?? null,
                'description' => $validated['description'] ?? null,
                'sections' => $validated['sections'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Section Two created successfully',
                'data' => $sectionTwo,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getAllSectionTwo()
    {
        try {
            $sectionTwo = SectionTwo::orderBy('created_at', 'desc')->get();
            return response()->json([
                'success' => true,
                'message' => 'Section Two fetched successfully',
                'data' => $sectionTwo,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function getSectionTwoById($id)
    {
        try {
            $sectionTwo = SectionTwo::find($id);
            if ($sectionTwo) {
                return response()->json([
                    'success' => true,
                    'message' => 'Section Two fetched successfully',
                    'data' => $sectionTwo,
                ], 200);
            } else {
                return response()->json(['success' => false, 'message' => 'Section Two not found'], 404);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function updateSectionTwo(Request $request, $id)
    {
        try {
            $user = auth()->user();
            if (!$user || $user->role !== 'admin') {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }
    
            $sectionTwo = SectionTwo::find($id);
            if (!$sectionTwo) {
                return response()->json(['success' => false, 'message' => 'Section Two not found'], 404);
            }
    
            $validator = Validator::make($request->all(), [
                'title' => 'nullable|string',
                'description' => 'nullable|string',
                'sections' => 'nullable|array',
                'sections.*.title' => 'nullable|string',
                'sections.*.short_des' => 'nullable|string',
                'sections.*.image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);
    
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }
    
            $validated = $validator->validated();
    
            if (isset($validated['sections']) && is_array($validated['sections'])) {
                foreach ($validated['sections'] as $key => $section) {
                    if (isset($section['image'])) {
                        $image = $section['image'];
                        $imageName = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
    
                        $destinationPath = public_path('uploads/sections');
                        if (!file_exists($destinationPath)) {
                            mkdir($destinationPath, 0755, true);
                        }
    
                        $image->move($destinationPath, $imageName);
    
                        $validated['sections'][$key]['image'] = url('uploads/sections/' . $imageName);
                    }
                }
            }
    
            $sectionTwo->update([
                'title' => $validated['title'] ?? $sectionTwo->title,
                'description' => $validated['description'] ?? $sectionTwo->description,
                'sections' => $validated['sections'] ?? $sectionTwo->sections,
            ]);
    
            return response()->json([
                'success' => true,
                'message' => 'Section Two updated successfully',
                'data' => $sectionTwo,
            ], 200);
    
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.',
                'errors' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $user = auth()->user();
            if (!$user || $user->role !== 'admin') {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            $section = SectionTwo::findOrFail($id);
            $section->delete();

            return response()->json([
                'success' => true,
                'message' => 'Section Two deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


}
