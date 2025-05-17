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
    public function getOwnerId()
    {
        return Auth::id() ?? session()->getId();
    }

    public function addToCart(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'color_id' => 'required|exists:colors,id',
            'size_id' => 'required|exists:sizes,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $ownerId = $this->getOwnerId();
        $isGuest = !Auth::check();

        $productId = $request->product_id;
        $colorId = $request->color_id;
        $sizeId = $request->size_id;
        $quantity = $request->quantity;

        $availability = Availability::where([
            'product_id' => $productId,
            'color_id' => $colorId,
            'size_id' => $sizeId,
        ])->first();

        if (!$availability) {
            return response()->json(['success' => false, 'message' => 'Product Not Available'], 400);
        }

        $query = CartItem::where([
            'product_id' => $productId,
            'color_id' => $colorId,
            'size_id' => $sizeId,
        ]);

        $query->where($isGuest ? 'session_id' : 'user_id', $ownerId);
        $existingCartItem = $query->first();

        if ($existingCartItem) {
            $newQuantity = $existingCartItem->quantity + $quantity;

            if ($newQuantity <= 0) {
                $existingCartItem->delete();
                return response()->json(['message' => 'Item removed from cart']);
            }

            $existingCartItem->update(['quantity' => $newQuantity]);
            return response()->json(['message' => 'Cart updated', 'item' => $existingCartItem]);
        }

        $product = Product::findOrFail($productId);

        $cartItem = CartItem::create([
            'user_id' => $isGuest ? null : $ownerId,
            'session_id' => $isGuest ? $ownerId : null,
            'product_id' => $productId,
            'color_id' => $colorId,
            'size_id' => $sizeId,
            'quantity' => $quantity,
            'price' => $product->price,
        ]);

        return response()->json(['success' => true, 'message' => 'Added to cart', 'item' => $cartItem]);
    }

    public function getCartItems()
    {
        $ownerId = $this->getOwnerId();
        $isGuest = !Auth::check();

        $cartItems = CartItem::with(['product', 'color', 'size'])
            ->where($isGuest ? 'session_id' : 'user_id', $ownerId)
            ->get();

        return response()->json(['success' => true, 'data' => $cartItems]);
    }

    public function removeFromCart($id)
    {
        $ownerId = $this->getOwnerId();
        $isGuest = !Auth::check();

        $cartItem = CartItem::where('id', $id)
            ->where($isGuest ? 'session_id' : 'user_id', $ownerId)
            ->firstOrFail();

        $cartItem->delete();

        return response()->json(['success' => true, 'message' => 'Item removed from cart']);
    }

    public function clearCart()
    {
        $ownerId = $this->getOwnerId();
        $isGuest = !Auth::check();

        CartItem::where($isGuest ? 'session_id' : 'user_id', $ownerId)->delete();

        return response()->json(['success' => true, 'message' => 'Cart cleared']);
    }

    public function updateCartItem(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $ownerId = $this->getOwnerId();
        $isGuest = !Auth::check();

        $cartItem = CartItem::where('id', $id)
            ->where($isGuest ? 'session_id' : 'user_id', $ownerId)
            ->firstOrFail();

        $cartItem->update(['quantity' => $request->quantity]);

        return response()->json(['success' => true, 'message' => 'Cart item updated', 'item' => $cartItem]);
    }

    public function getCartItemCount()
    {
        $ownerId = $this->getOwnerId();
        $isGuest = !Auth::check();
        $count = CartItem::where($isGuest ? 'session_id' : 'user_id', $ownerId)->count();
        return response()->json(['success' => true, 'data' => ['cart_item_count' => $count]]);
    }
}
