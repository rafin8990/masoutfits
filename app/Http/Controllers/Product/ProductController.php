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
                'category_id' => 'required|exists:categories,id',
                'sub_category_id' => 'required|exists:sub_categories,id',
                'price' => 'nullable|string',
                'isNew' => 'required|boolean',
                'discount_price' => 'nullable|string',
                'discount_amount' => 'nullable|string',
                'size_column_name_one' => 'nullable|string',
                'size_column_name_two' => 'nullable|string',
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
                'discount_amount',
                'size_column_name_one',
                'size_column_name_two'
            ])->toArray());

            // Attach single category and subcategory
            $product->categories()->sync([$validatedData['category_id']]);
            $product->subCategories()->sync([$validatedData['sub_category_id']]);

            if (!empty($validatedData['tag_ids'])) {
                $product->tags()->attach($validatedData['tag_ids']);
            }

            if (!empty($validatedData['size_guide_ids'])) {
                $product->sizeGuide()->attach($validatedData['size_guide_ids']);
            }

            $product->load(['categories', 'subCategories']);
            
            // Transform categories and subCategories from arrays to single objects
            if ($product->categories && $product->categories->count() > 0) {
                $product->category = $product->categories->first();
                unset($product->categories);
            }
            if ($product->subCategories && $product->subCategories->count() > 0) {
                $product->subCategory = $product->subCategories->first();
                unset($product->subCategories);
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
        $query = Product::select('products.*');

        // Apply optional filters if present (before eager loading for better performance)
        if ($request->has('category') && $request->input('category') !== null && $request->input('category') !== 'all') {
            $categoryId = (int) $request->input('category');
            // Check both pivot table and direct foreign key for backward compatibility
            $query->where(function ($q) use ($categoryId) {
                $q->whereHas('categories', function ($subQ) use ($categoryId) {
                    $subQ->where('categories.id', $categoryId);
                })->orWhere('products.category_id', $categoryId);
            });
        }

        if ($request->has('sub-category') && $request->input('sub-category') !== null) {
            $subCategoryId = (int) $request->input('sub-category');
            // Check both pivot table and direct foreign key for backward compatibility
            $query->where(function ($q) use ($subCategoryId) {
                $q->whereHas('subCategories', function ($subQ) use ($subCategoryId) {
                    $subQ->where('sub_categories.id', $subCategoryId);
                })->orWhere('products.sub_category_id', $subCategoryId);
            });
        }

        $query->orderBy('products.created_at', 'desc');
        
        // Eager load relationships after filtering
        $products = $query->with([
            'categories:id,name,image',
            'subCategories:id,name,image',
            'tags',
            'productImages',
            'availability',
            'sizeGuide'
        ])->get();
        
        // Debug: Log query and count for troubleshooting
        // \Log::info('Product Query', ['sql' => $query->toSql(), 'bindings' => $query->getBindings(), 'count' => $products->count()]);

        // Transform categories and subCategories from arrays to single objects
        // Also sync from direct foreign keys to pivot tables if needed
        $products->transform(function ($product) {
            // Handle categories
            if ($product->categories && $product->categories->count() > 0) {
                $product->category = $product->categories->first();
                unset($product->categories);
            } elseif ($product->category_id) {
                // If pivot table is empty but direct foreign key exists, load from direct key
                $category = \App\Models\Category::find($product->category_id);
                if ($category) {
                    $product->category = $category;
                    // Sync to pivot table for future queries
                    $product->categories()->syncWithoutDetaching([$product->category_id]);
                }
            }
            
            // Handle subCategories
            if ($product->subCategories && $product->subCategories->count() > 0) {
                $product->subCategory = $product->subCategories->first();
                unset($product->subCategories);
            } elseif ($product->sub_category_id) {
                // If pivot table is empty but direct foreign key exists, load from direct key
                $subCategory = \App\Models\SubCategory::find($product->sub_category_id);
                if ($subCategory) {
                    $product->subCategory = $subCategory;
                    // Sync to pivot table for future queries
                    $product->subCategories()->syncWithoutDetaching([$product->sub_category_id]);
                }
            }
            
            return $product;
        });

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
        'subCategories:id,name,image'
    ])->get();

    // Transform categories and subCategories from arrays to single objects
    $products->transform(function ($product) {
        if ($product->categories && $product->categories->count() > 0) {
            $product->category = $product->categories->first();
            unset($product->categories);
        }
        if ($product->subCategories && $product->subCategories->count() > 0) {
            $product->subCategory = $product->subCategories->first();
            unset($product->subCategories);
        }
        return $product;
    });

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
            'subCategories:id,name,image',
            'tags',
            'productImages.color',
            'availability',
            'sizeGuide'
        ])->findOrFail($id);

        // Transform categories and subCategories from arrays to single objects
        if ($product->categories && $product->categories->count() > 0) {
            $product->category = $product->categories->first();
            unset($product->categories);
        }
        if ($product->subCategories && $product->subCategories->count() > 0) {
            $product->subCategory = $product->subCategories->first();
            unset($product->subCategories);
        }

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
            'category_id' => 'required|exists:categories,id',
            'sub_category_id' => 'required|exists:sub_categories,id',
            'price' => 'nullable|string',
                'isNew' => 'required|boolean',
            'discount_price' => 'nullable|string',
            'discount_amount' => 'nullable|string',
            'size_column_name_one' => 'nullable|string',
            'size_column_name_two' => 'nullable|string',
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
            'discount_amount',
            'size_column_name_one',
            'size_column_name_two'
        ])->toArray());

        // Sync single category and subcategory
        $product->categories()->sync([$validatedData['category_id']]);
        $product->subCategories()->sync([$validatedData['sub_category_id']]);

        if (!empty($validatedData['tag_ids'])) {
            $product->tags()->sync($validatedData['tag_ids']);
        }

        if (!empty($validatedData['size_guide_ids'])) {
            $product->sizeGuide()->sync($validatedData['size_guide_ids']);
        }

        $product->load(['categories', 'subCategories']);
        
        // Transform categories and subCategories from arrays to single objects
        if ($product->categories && $product->categories->count() > 0) {
            $product->category = $product->categories->first();
            unset($product->categories);
        }
        if ($product->subCategories && $product->subCategories->count() > 0) {
            $product->subCategory = $product->subCategories->first();
            unset($product->subCategories);
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
                'category_id' => 'required|exists:categories,id',
                'sub_category_id' => 'required|exists:sub_categories,id',
                'price' => 'nullable|string',
                'isNew' => 'required|boolean',
                'discount_price' => 'nullable|string',
                'discount_amount' => 'nullable|string',
                'size_column_name_one' => 'nullable|string',
                'size_column_name_two' => 'nullable|string',
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
                'discount_amount',
                'size_column_name_one',
                'size_column_name_two'
            ])->toArray();

            $product = Product::create($productData);

            // Attach single category and subcategory
            $product->categories()->sync([$validatedData['category_id']]);
            $product->subCategories()->sync([$validatedData['sub_category_id']]);

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

            $product->load(['categories', 'subCategories', 'availability.color', 'availability.size', 'tags', 'sizeGuide']);
            
            // Transform categories and subCategories from arrays to single objects
            if ($product->categories && $product->categories->count() > 0) {
                $product->category = $product->categories->first();
                unset($product->categories);
            }
            if ($product->subCategories && $product->subCategories->count() > 0) {
                $product->subCategory = $product->subCategories->first();
                unset($product->subCategories);
            }

            return response()->json([
                'success' => true,
                'message' => 'Product and availability created successfully.',
                'product' => $product,
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
                'size_column_name_one' => 'nullable|string',
                'size_column_name_two' => 'nullable|string',
                'tags' => 'nullable|array',
                'tags.*.name' => 'required_with:tags|string',
                'tags.*.description' => 'nullable|string',
                'size_guides' => 'nullable|array',
                'size_guides.*.name' => 'required_with:size_guides|string',
                'size_guides.*.chest' => 'required_with:size_guides|string',
                'size_guides.*.body' => 'required_with:size_guides|string',
                'availability' => 'required|array',
                'availability.*.size_id' => 'required|exists:sizes,id',
                'availability.*.color_id' => 'required|exists:colors,id',
                'availability.*.quantity' => 'required|integer|min:0',
            ]);
            
            // Convert isNew string to boolean if needed
            if (isset($validatedData['isNew']) && !is_bool($validatedData['isNew'])) {
                $validatedData['isNew'] = filter_var($validatedData['isNew'], FILTER_VALIDATE_BOOLEAN);
            }

            $product = Product::findOrFail($productId);

            $productData = collect($validatedData)->only([
                'name',
                'description',
                'fit',
                'care',
                'price',
                'isNew',
                'discount_price',
                'discount_amount',
                'size_column_name_one',
                'size_column_name_two'
            ])->toArray();

            $product->update($productData);

            // Sync single category and subcategory
            $product->categories()->sync([$validatedData['category_id']]);
            $product->subCategories()->sync([$validatedData['sub_category_id']]);

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

            $product->load(['categories', 'subCategories', 'availability.color', 'availability.size', 'tags', 'sizeGuide']);
            
            // Transform categories and subCategories from arrays to single objects
            if ($product->categories && $product->categories->count() > 0) {
                $product->category = $product->categories->first();
                unset($product->categories);
            }
            if ($product->subCategories && $product->subCategories->count() > 0) {
                $product->subCategory = $product->subCategories->first();
                unset($product->subCategories);
            }

            return response()->json([
                'success' => true,
                'message' => 'Product and availability updated successfully.',
                'product' => $product,
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
