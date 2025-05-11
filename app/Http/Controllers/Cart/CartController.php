<?php

namespace App\Http\Controllers\Cart;

use App\Http\Controllers\Controller;
use App\Models\Availability;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function addToCart(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'color_id' => 'required|exists:colors,id',
            'size_id' => 'required|exists:sizes,id',
            'quantity' => 'required|integer|min:1',
        ]);
        $userId = Auth::id();
        $productId = $request->product_id;
        $colorId = $request->color_id;
        $sizeId = $request->size_id;
        $changeQty = $request->quantity;

        $availability = Availability::where([
            'product_id' => $request->product_id,
            'color_id' => $request->color_id,
            'size_id' => $request->size_id,
        ])->first();



        if (!$availability) {
            return response()->json(['success' => false, 'message' => 'Product Not Available'], 400);
        }

        $existingCartItem = CartItem::where([
            'user_id' => $userId,
            'product_id' => $productId,
            'color_id' => $colorId,
            'size_id' => $sizeId,
        ])->first();

        if ($existingCartItem) {
            $newQuantity = $existingCartItem->quantity + $changeQty;

            if ($newQuantity <= 0) {
                $existingCartItem->delete();
                return response()->json(['message' => 'Item removed from cart']);
            }

            $existingCartItem->update(['quantity' => $newQuantity]);
            return response()->json(['message' => 'Cart updated', 'item' => $existingCartItem]);
        }

        $product = Product::findOrFail($productId);

        $cartItem = CartItem::create([
            'user_id' => $userId,
            'product_id' => $productId,
            'color_id' => $colorId,
            'size_id' => $sizeId,
            'quantity' => $changeQty,
            'price' => $product->price,
        ]);

        return response()->json(['success' => true, 'message' => 'Added to cart', 'item' => $cartItem]);
    }

    public function removeFromCart($id)
    {
        $userId = Auth::id();
        $cartItem = CartItem::where('user_id', $userId)->findOrFail($id);
        $cartItem->delete();

        return response()->json(['success' => true, 'message' => 'Item removed from cart']);
    }
    public function getCartItems()
    {
        $userId = Auth::id();
        $cartItems = CartItem::where('user_id', $userId)->with(['product', 'color', 'size'])->get();

        return response()->json(['success' => true, 'data' => $cartItems]);
    }
    public function clearCart()
    {
        $userId = Auth::id();
        CartItem::where('user_id', $userId)->delete();

        return response()->json(['success' => true, 'message' => 'Cart cleared']);
    }
    public function updateCartItem(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $userId = Auth::id();
        $cartItem = CartItem::where('user_id', $userId)->findOrFail($id);
        $cartItem->update(['quantity' => $request->quantity]);

        return response()->json(['success' => true, 'message' => 'Cart item updated', 'item' => $cartItem]);
    }
    public function getCartItemCount()
    {
        $userId = Auth::id();
        $cartItemCount = CartItem::where('user_id', $userId)->count();

        return response()->json(['success' => true, 'data' => ['cart_item_count' => $cartItemCount]]);
    }
}
