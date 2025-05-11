<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function createProduct(Request $request)
    {
        try {
            $user = auth()->user();
            if (!$user || $user->role !== 'admin') {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }
            $validatedData = $request->validate([
                'name' => 'required|string',
                'description' => 'nullable|string',
                'fit' => 'nullable|string',
                'care' => 'nullable|string',
                'category_id' => 'required|exists:categories,id',
                'sub_category_id' => 'required|exists:sub_categories,id',
                'tag_ids' => 'array',
                'tag_ids.*' => 'exists:tags,id',
                'size_guide_ids' => 'array',
                'size_guide_ids.*' => 'exists:size_guide,id',
            ]);

            $product = Product::create($validatedData);

            if (!empty($validatedData['tag_ids'])) {
                $product->tags()->attach($validatedData['tag_ids']);
            }

            if (!empty($validatedData['size_guide_ids'])) {
                $product->sizeGuide()->attach($validatedData['size_guide_ids']);
            }

            return response()->json([
                'success' => true,
                'message' => 'Product created successfully with tags.',
                'product' => $product
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
                'message' => 'Validation failed.'
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred. Please try again later.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function addProductAvailability(Request $request, $productId)
    {
        $user = auth()->user();
        if (!$user || $user->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'availability' => 'required|array',
            'availability.*.size_id' => 'required|exists:sizes,id',
            'availability.*.color_id' => 'required|exists:colors,id',
            'availability.*.quantity' => 'required|integer|min:0',
        ]);

        $product = Product::findOrFail($productId);

        foreach ($validated['availability'] as $item) {
            $product->availability()->create([
                'size_id' => $item['size_id'],
                'color_id' => $item['color_id'],
                'quantity' => $item['quantity'],
            ]);
        }

        return response()->json(['success' => true, 'message' => 'Availability added successfully', 'data' => $product], 200);
    }

    public function addProductImages(Request $request, $productId)
    {
        $user = auth()->user();
        if (!$user || $user->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'images' => 'required|array',
            'images.*.color_id' => 'required|exists:colors,id',
            'images.*.image' => 'required|array',
            'images.*.image.*' => 'required|file|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $product = Product::findOrFail($productId);

        // dd($validated['images']);
        foreach ($validated['images'] as $index => $imageGroup) {
            $colorId = $imageGroup['color_id'];
            $imageFiles = $request->file("images.$index.image");

            foreach ($imageFiles as $imageFile) {
                $fileName = time() . '_' . uniqid() . '.' . $imageFile->getClientOriginalExtension();
                $destinationPath = public_path('product_images');
                $imageFile->move($destinationPath, $fileName);

                $url = asset('public/product_images/' . $fileName);

                $product->productImages()->create([
                    'color_id' => $colorId,
                    'image' => $url,
                ]);
            }
        }

        return response()->json(['success' => true, 'message' => 'Product images uploaded successfully.', 'data' => $product], 200);
    }


    public function getAllProducts(Request $request)
    {
        $query = Product::with([
            'category',
            'subCategory',
            'tags',
            'productImages',
            'availability',
            'sizeGuide'
        ]);

        if ($request->has('category')) {
            $query->where('category_id', $request->get('category'));
        }
        if ($request->has('sub-category')) {
            $query->where('sub_category_id', $request->get('sub-category'));
        }

        $products = $query->get();
        return response()->json([
            'success' => true,
            'message' => 'Products retrieved successfully.',
            'data' => $products
        ], 200);
    }

    public function getProductById($id)
    {
        $product = Product::with(['category', 'subCategory', 'tags', 'productImages', 'availability', 'sizeGuide'])->findOrFail($id);
        return response()->json([
            'success' => true,
            'message' => 'Product retrieved successfully.',
            'data' => $product
        ], 200);
    }

    public function updateProduct(Request $request, $id)
    {
        $user = auth()->user();
        if (!$user || $user->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validatedData = $request->validate([
            'name' => 'required|string',
            'description' => 'nullable|string',
            'fit' => 'nullable|string',
            'care' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'sub_category_id' => 'required|exists:sub_categories,id',
            'tag_ids' => 'array',
            'tag_ids.*' => 'exists:tags,id',
            'size_guide_ids' => 'array',
            'size_guide_ids.*' => 'exists:size_guide,id',
        ]);

        $product = Product::findOrFail($id);
        $product->update($validatedData);

        if (!empty($validatedData['tag_ids'])) {
            $product->tags()->sync($validatedData['tag_ids']);
        }

        if (!empty($validatedData['size_guide_ids'])) {
            $product->sizeGuide()->sync($validatedData['size_guide_ids']);
        }
        

        return response()->json([
            'success' => true,
            'message' => 'Product updated successfully.',
            'data' => $product
        ], 200);
    }
    public function deleteProduct($id)
    {
        $user = auth()->user();
        if (!$user || $user->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $product = Product::findOrFail($id);
        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully.'
        ], 200);
    }


}
