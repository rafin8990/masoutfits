<?php

namespace App\Http\Controllers\Assets;

use App\Http\Controllers\Controller;
use App\Models\SizeGuide;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SizeGuideController extends Controller
{
    public function createSizeGuide(Request $request)
    {
        try {
            $user = auth()->user();
            if (!$user || $user->role !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'category_id' => 'required|exists:categories,id',
                'sub_category_id' => 'required|exists:sub_categories,id',
                'chest' => 'required|string|max:255',
                'body' => 'required|string|max:255',
            ]);

            $sizeGuide = SizeGuide::create($validatedData);

            return response()->json([
                'success' => true,
                'data' => $sizeGuide,
                'message' => 'Size guide created successfully'
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
                'message' => 'Validation failed'
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred. Please try again later.'
            ], 500);
        }
    }

    public function getAllSizeGuide()
    {
        try {
            $sizeGuide = SizeGuide::all();
            return response()->json([
                'success' => true,
                'data' => $sizeGuide,
                'message' => 'Size guides retrieved successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred. Please try again later.'
            ], 500);
        }
    }

    public function getSizeGuideById($id)
    {
        try {
            $sizeGuide = SizeGuide::find($id);
            if (!$sizeGuide) {
                return response()->json([
                    'success' => false,
                    'message' => 'Size guide not found'
                ], 404);
            }
            return response()->json([
                'success' => true,
                'data' => $sizeGuide,
                'message' => 'Size guide retrieved successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred. Please try again later.'
            ], 500);
        }
    }

    public function updateSizeGuide($id, Request $request)
    {
        try {

            $user = auth()->user();
            if (!$user || $user->role !== 'admin') {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }
            $sizeGuide = SizeGuide::find($id);

            if (!$sizeGuide) {
                return response()->json([
                    'success' => false,
                    'message' => 'Size guide not found.'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|string|max:255',
                'category_id' => 'sometimes|exists:categories,id',
                'sub_category_id' => 'sometimes|exists:sub_categories,id',
                'chest' => 'sometimes|string|max:255',
                'body' => 'sometimes|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            $sizeGuide->update($validator->validated());

            return response()->json([
                'success' => true,
                'message' => 'Size guide updated successfully.',
                'data' => $sizeGuide
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating the size guide.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function deleteSizeGuide($id)
    {
        try {

            $user = auth()->user();
            if (!$user || $user->role !== 'admin') {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }
            $sizeGuide = SizeGuide::find($id);

            if (!$sizeGuide) {
                return response()->json([
                    'success' => false,
                    'message' => 'Size guide not found.'
                ], 404);
            }

            $sizeGuide->delete();

            return response()->json([
                'success' => true,
                'message' => 'Size guide deleted successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting the size guide.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

}
