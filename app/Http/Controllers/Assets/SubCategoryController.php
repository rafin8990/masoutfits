<?php

namespace App\Http\Controllers\Assets;

use App\Http\Controllers\Controller;
use App\Models\SubCategory;
use Illuminate\Http\Request;

class SubCategoryController extends Controller
{
    public function createSubCategory(Request $request)
    {
        try {
            $user = auth()->user();
            if (!$user || $user->role !== 'admin') {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'category_id' => 'required|exists:categories,id',
            ]);

            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();

                $destinationPath = public_path('uploads/subcategory');
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0755, true);
                }

                $image->move($destinationPath, $imageName);

                $validated['image'] = url('public/uploads/subcategory/' . $imageName);
            }

            $subCategory = SubCategory::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'SubCategory created successfully',
                'data' => $subCategory,
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

    public function getAllSubCategories(Request $request)
    {
        try {
            $categoryId = $request->query('category');
            $categoryName = $request->query('name');
            $subCategoryName = $request->query('subCategory');
            $searchTerm = $request->query('searchTerm');

            $query = SubCategory::with('category');

            if ($categoryName) {
                $query->whereHas('category', function ($q) use ($categoryName) {
                    $q->where('name', $categoryName);
                });
            }
            if ($categoryId) {
                $query->where('category_id', $categoryId);
            }


            if ($subCategoryName) {
                $query->where('name', $subCategoryName);
            }

            if ($searchTerm) {
                $query->where('name', 'like', '%' . $searchTerm . '%');
            }

            $subCategories = $query->orderBy('created_at', 'desc')->get();

            return response()->json([
                'success' => true,
                'message' => 'SubCategories fetched successfully',
                'data' => $subCategories,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while fetching the subcategories.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getSubCategoryById($id)
    {
        $subCategory = SubCategory::find($id);
        if ($subCategory) {
            return response()->json([
                'success' => true,
                'message' => 'SubCategory fetched successfully',
                'data' => $subCategory,
            ], 200);
        } else {
            return response()->json(['success' => false, 'message' => 'SubCategory not found'], 404);
        }
    }

    public function updateSubCategory(Request $request, $id)
    {
        try {
            $user = auth()->user();
            if (!$user || $user->role !== 'admin') {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            $subCategory = SubCategory::find($id);
            if (!$subCategory) {
                return response()->json(['success' => false, 'message' => 'SubCategory not found'], 404);
            }

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'category_id' => 'required|exists:categories,id',
            ]);

            if ($request->hasFile('image')) {
                if ($subCategory->image) {
                    $oldImagePath = public_path(parse_url($subCategory->image, PHP_URL_PATH));
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                }

                $image = $request->file('image');
                $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();

                $destinationPath = public_path('uploads/subcategory');
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0755, true);
                }

                $image->move($destinationPath, $imageName);

                $validated['image'] = url('public/uploads/subcategory/' . $imageName);
            }

            $subCategory->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'SubCategory updated successfully',
                'data' => $subCategory,
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
    public function deleteSubCategory($id)
    {
        $user = auth()->user();
        if (!$user || $user->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $subCategory = SubCategory::find($id);
        if (!$subCategory) {
            return response()->json(['success' => false, 'message' => 'SubCategory not found'], 404);
        }

        if ($subCategory->image) {
            $imagePath = public_path(parse_url($subCategory->image, PHP_URL_PATH));
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }

        $subCategory->delete();

        return response()->json(['success' => true, 'message' => 'SubCategory deleted successfully'], 200);
    }
}
