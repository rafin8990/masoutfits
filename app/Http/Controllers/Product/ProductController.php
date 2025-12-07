<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\SizeGuide;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    public function createProduct(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user || $user->role !== 'admin') {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }
            $validatedData = $request->validate([
                'name' => 'required|string',
                'description' => 'nullable|string',
                'fit' => 'nullable|string',
                'care' => 'nullable|string',
                'category_ids' => 'required|array',
                'category_ids.*' => 'exists:categories,id',
                'sub_category_ids' => 'required|array',
                'sub_category_ids.*' => 'exists:sub_categories,id',
                'price' => 'nullable|string',
                'isNew' => 'required|boolean',
                'discount_price' => 'nullable|string',
                'discount_amount' => 'nullable|string',
                'tag_ids' => 'array',
                'tag_ids.*' => 'exists:tags,id',
                'size_guide_ids' => 'array',
                'size_guide_ids.*' => 'exists:size_guide,id',
            ]);

            $product = Product::create(collect($validatedData)->only([
                'name',
                'description',
                'fit',
                'care',
                'price',
                'isNew',
                'discount_price',
                'discount_amount'
            ])->toArray());

            // Attach multiple categories and subcategories
            if (!empty($validatedData['category_ids'])) {
                $product->categories()->attach($validatedData['category_ids']);
            }

            if (!empty($validatedData['sub_category_ids'])) {
                $product->subCategories()->attach($validatedData['sub_category_ids']);
            }

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
        $user = Auth::user();
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
        $user = Auth::user();
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
            'categories:id,name,image',
            'subCategories:id,name,image,category_id',
            'tags',
            'productImages',
            'availability',
            'sizeGuide'
        ]);

        // Apply optional filters if present
        if ($request->filled('category')) {
            $categoryId = $request->input('category');
            $query->whereHas('categories', function ($q) use ($categoryId) {
                $q->where('categories.id', $categoryId);
            });
        }

        if ($request->filled('sub-category')) {
            $subCategoryId = $request->input('sub-category');
            $query->whereHas('subCategories', function ($q) use ($subCategoryId) {
                $q->where('sub_categories.id', $subCategoryId);
            });
        }

        $query->orderBy('created_at', 'desc');
        $products = $query->get();

        return response()->json([
            'success' => true,
            'message' => 'Products retrieved successfully.',
            'data' => $products
        ]);
    }


//   public function getProductsImages()
// {
//     $products = Product::select('id', 'name', 'category_id')
//         ->with(['productImages:id,product_id,image,color_id'])
//         ->get();

//     return response()->json([
//         'success' => true,
//         'message' => 'Product images retrieved successfully.',
//         'data' => $products
//     ], 200);
// }


public function getProductsImages()
{
    $products = Product::with([
        'productImages:id,product_id,image,color_id',
        'categories:id,name,image',
        'subCategories:id,name,image,category_id'
    ])->get();

    return response()->json([
        'success' => true,
        'message' => 'Full product details with images and category retrieved successfully.',
        'data' => $products
    ], 200);
}




    public function getProductById($id)
    {
        $product = Product::with([
            'categories:id,name,image',
            'subCategories:id,name,image,category_id',
            'tags',
            'productImages.color',
            'availability',
            'sizeGuide'
        ])->findOrFail($id);
        return response()->json([
            'success' => true,
            'message' => 'Product retrieved successfully.',
            'data' => $product
        ], 200);
    }

    public function updateProduct(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validatedData = $request->validate([
            'name' => 'required|string',
            'description' => 'nullable|string',
            'fit' => 'nullable|string',
            'care' => 'nullable|string',
            'category_ids' => 'required|array',
            'category_ids.*' => 'exists:categories,id',
            'sub_category_ids' => 'required|array',
            'sub_category_ids.*' => 'exists:sub_categories,id',
            'price' => 'nullable|string',
                'isNew' => 'required|boolean',
            'discount_price' => 'nullable|string',
            'discount_amount' => 'nullable|string',
            'tag_ids' => 'array',
            'tag_ids.*' => 'exists:tags,id',
            'size_guide_ids' => 'array',
            'size_guide_ids.*' => 'exists:size_guide,id',
        ]);

        $product = Product::findOrFail($id);
        $product->update(collect($validatedData)->only([
            'name',
            'description',
            'fit',
            'care',
            'price',
            'isNew',
            'discount_price',
            'discount_amount'
        ])->toArray());

        // Sync multiple categories and subcategories
        if (!empty($validatedData['category_ids'])) {
            $product->categories()->sync($validatedData['category_ids']);
        }

        if (!empty($validatedData['sub_category_ids'])) {
            $product->subCategories()->sync($validatedData['sub_category_ids']);
        }

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

    public function updateProductAvailability(Request $request, $productId)
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'availability' => 'required|array',
            'availability.*.id' => 'required|exists:availabilities,id',
            'availability.*.size_id' => 'required|exists:sizes,id',
            'availability.*.color_id' => 'required|exists:colors,id',
            'availability.*.quantity' => 'required|integer|min:0',
        ]);

        $product = Product::findOrFail($productId);

        foreach ($validated['availability'] as $item) {
            $availability = $product->availability()->findOrFail($item['id']);
            $availability->update([
                'size_id' => $item['size_id'],
                'color_id' => $item['color_id'],
                'quantity' => $item['quantity'],
            ]);
        }

        return response()->json(['success' => true, 'message' => 'Availability updated successfully', 'data' => $product], 200);
    }

    // public function updateProductImages(Request $request, $productId)
    // {
       

    //     $validated = $request->validate([
    //         'images' => 'required|array',
    //         'images.*.id' => 'required|exists:product_images,id',
    //         'images.*.color_id' => 'required|exists:colors,id',
    //         'images.*.image' => 'required|array',
    //         'images.*.image.*' => 'required|file|mimes:jpg,jpeg,png,webp|max:2048',
    //     ]);

    //     $product = Product::findOrFail($productId);

    //     foreach ($validated['images'] as $index => $imageGroup) {
    //         $colorId = $imageGroup['color_id'];
    //         $imageFiles = $request->file("images.$index.image");

    //         foreach ($imageFiles as $imageFile) {
    //             $fileName = time() . '_' . uniqid() . '.' . $imageFile->getClientOriginalExtension();
    //             $destinationPath = public_path('product_images');
    //             $imageFile->move($destinationPath, $fileName);

    //             $url = asset('public/product_images/' . $fileName);

    //             $productImage = $product->productImages()->findOrFail($imageGroup['id']);
    //             $productImage->update([
    //                 'color_id' => $colorId,
    //                 'image' => $url,
    //             ]);
    //         }
    //     }

    //     return response()->json(['success' => true, 'message' => 'Product images updated successfully.', 'data' => $product], 200);
    // }
    
    
    public function updateProductImages(Request $request, $productId)
{
    $validated = $request->validate([
        'images' => 'required|array',
        'images.*.color_id' => 'required|exists:colors,id',
        'images.*.id' => 'nullable|exists:product_images,id',
        'images.*.image' => 'required|array',
        'images.*.image.*' => 'required|file|mimes:jpg,jpeg,png,webp|max:2048',
    ]);

    $product = Product::findOrFail($productId);

    foreach ($validated['images'] as $index => $imageGroup) {
        $colorId = $imageGroup['color_id'];
        $imageFiles = $request->file("images.$index.image");

        foreach ($imageFiles as $imageFile) {
            $fileName = time() . '_' . uniqid() . '.' . $imageFile->getClientOriginalExtension();
            $destinationPath = public_path('product_images');
            $imageFile->move($destinationPath, $fileName);

            $url = asset('public/product_images/' . $fileName);

            // If id exists → update existing product image
            if (!empty($imageGroup['id'])) {
                $productImage = $product->productImages()->find($imageGroup['id']);
                if ($productImage) {
                    $productImage->update([
                        'color_id' => $colorId,
                        'image' => $url,
                    ]);
                } else {
                    // If id not found, create a new one
                    $product->productImages()->create([
                        'color_id' => $colorId,
                        'image' => $url,
                    ]);
                }
            } else {
                // No id provided → create a new product image
                $product->productImages()->create([
                    'color_id' => $colorId,
                    'image' => $url,
                ]);
            }
        }
    }

    // optionally reload product with images
    $product->load('productImages');

    return response()->json([
        'success' => true,
        'message' => 'Product images updated or created successfully.',
        'data' => $product
    ], 200);
}

    
    
    public function deleteProductImages(Request $request, $productId)
    {

        $validated = $request->validate([
            'image_ids' => 'required|array',
            'image_ids.*' => 'required|exists:product_images,id',
        ]);

        $product = Product::findOrFail($productId);

        foreach ($validated['image_ids'] as $imageId) {
            $productImage = $product->productImages()->findOrFail($imageId);
            
            // Delete the physical file from storage
            $imagePath = public_path('product_images/' . basename($productImage->image));
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
            
            // Delete the database record
            $productImage->delete();
        }

        return response()->json(['success' => true, 'message' => 'Product images deleted successfully.', 'data' => $product], 200);
    }
    
    
    public function deleteProduct($id)
    {
        $user = Auth::user();
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


    public function createProductWithAvailability(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user || $user->role !== 'admin') {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            $validatedData = $request->validate([
                'name' => 'required|string',
                'description' => 'nullable|string',
                'fit' => 'nullable|string',
                'care' => 'nullable|string',
            'category_ids' => 'required|array',
            'category_ids.*' => 'exists:categories,id',
            'sub_category_ids' => 'required|array',
            'sub_category_ids.*' => 'exists:sub_categories,id',
                'price' => 'nullable|string',
                'isNew' => 'required|boolean',
                'discount_price' => 'nullable|string',
                'discount_amount' => 'nullable|string',
                'tags' => 'array',
                'tags.*.name' => 'required|string',
                'tags.*.description' => 'nullable|string',
                'size_guides' => 'array',
                'size_guides.*.name' => 'required|string',
                'size_guides.*.chest' => 'required|string',
                'size_guides.*.body' => 'required|string',
                'availability' => 'required|array',
                'availability.*.size_id' => 'required|exists:sizes,id',
                'availability.*.color_id' => 'required|exists:colors,id',
                'availability.*.quantity' => 'required|integer|min:0',
            ]);

            $productData = collect($validatedData)->only([
                'name',
                'description',
                'fit',
                'care',
                'price',
                'isNew',
                'discount_price',
                'discount_amount'
            ])->toArray();

            $product = Product::create($productData);

            // Create tags
            if (!empty($validatedData['tags'])) {
                foreach ($validatedData['tags'] as $tag) {
                    $product->tags()->create($tag);
                }
            }

            // Create size guides
            if (!empty($validatedData['size_guides'])) {
                foreach ($validatedData['size_guides'] as $sizeGuide) {
                    $product->sizeGuide()->create($sizeGuide);
                }
            }

            // Create availability records
            foreach ($validatedData['availability'] as $item) {
                $product->availability()->create([
                    'size_id' => $item['size_id'],
                    'color_id' => $item['color_id'],
                    'quantity' => $item['quantity'],
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Product and availability created successfully.',
                'product' => $product->load(['availability.color', 'availability.size', 'tags', 'sizeGuide']),
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

    public function updateProductWithAvailability(Request $request, $productId)
    {
        try {
            $user = Auth::user();
            if (!$user || $user->role !== 'admin') {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            $validatedData = $request->validate([
                'name' => 'nullable|string',
                'description' => 'nullable|string',
                'fit' => 'nullable|string',
                'care' => 'nullable|string',
                'category_id' => 'required|exists:categories,id',
                'sub_category_id' => 'required|exists:sub_categories,id',
                'price' => 'nullable|string',
                'isNew' => 'required|boolean',
                'discount_price' => 'nullable|string',
                'discount_amount' => 'nullable|string',
                'tags' => 'array',
                'tags.*.name' => 'required|string',
                'tags.*.description' => 'nullable|string',
                'size_guides' => 'array',
                'size_guides.*.name' => 'required|string',
                'size_guides.*.chest' => 'required|string',
                'size_guides.*.body' => 'required|string',
                'availability' => 'required|array',
                'availability.*.size_id' => 'required|exists:sizes,id',
                'availability.*.color_id' => 'required|exists:colors,id',
                'availability.*.quantity' => 'required|integer|min:0',
            ]);

            $product = Product::findOrFail($productId);

            $productData = collect($validatedData)->only([
                'name',
                'description',
                'fit',
                'care',
                'price',
                'isNew',
                'discount_price',
                'discount_amount'
            ])->toArray();

            $product->update($productData);

            // Sync tags
            $product->tags()->delete();
            if (!empty($validatedData['tags'])) {
                foreach ($validatedData['tags'] as $tag) {
                    $product->tags()->create($tag);
                }
            }

            // Sync size guides
            $product->sizeGuide()->delete();
            if (!empty($validatedData['size_guides'])) {
                foreach ($validatedData['size_guides'] as $sizeGuide) {
                    $product->sizeGuide()->create($sizeGuide);
                }
            }

            // Sync availability
            $product->availability()->delete();
            foreach ($validatedData['availability'] as $item) {
                $product->availability()->create([
                    'size_id' => $item['size_id'],
                    'color_id' => $item['color_id'],
                    'quantity' => $item['quantity'],
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Product and availability updated successfully.',
                'product' => $product->load(['availability.color', 'availability.size', 'tags', 'sizeGuide']),
            ], 200);

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



}
