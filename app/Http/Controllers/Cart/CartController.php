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
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'color_id' => 'required|exists:colors,id',
            'size_id' => 'required|exists:sizes,id',
            'quantity' => 'required|integer',
            'guest_id' => 'nullable|string',
        ]);

        $available = Availability::where([
            'product_id' => $validated['product_id'],
            'color_id' => $validated['color_id'],
            'size_id' => $validated['size_id'],
        ])->exists();

        if (!$available) {
            return response()->json([
                'success' => false,
                'message' => 'Product not available',
            ], 404);
        }

        $userId = Auth::id();
        $guestId = $userId ? null : $validated['guest_id'];

        $product = Product::findOrFail($validated['product_id']);
        $cartItem = CartItem::where([
            'user_id' => $userId,
            'guest_id' => $guestId,
            'product_id' => $validated['product_id'],
            'color_id' => $validated['color_id'],
            'size_id' => $validated['size_id'],
        ])->first();

        if ($cartItem) {
            $cartItem->quantity += $validated['quantity'];
            if ($cartItem->quantity < 1) {
                $cartItem->delete();
            } else {
                $cartItem->save();
            }
        } else {
            if ($validated['quantity'] > 0) {
                CartItem::create([
                    'user_id' => $userId,
                    'guest_id' => $guestId,
                    'product_id' => $validated['product_id'],
                    'color_id' => $validated['color_id'],
                    'size_id' => $validated['size_id'],
                    'quantity' => $validated['quantity'],
                    'price' => $product->price,


                ]);
            }
        }

        return response()->json(['success' => true, 'message' => 'Cart updated successfully']);
    }

    public function getCartItems(Request $request)
    {
        $userId = $request->query('user_id');
        $guestId = $request->query('guest_id');

        $cartItems = CartItem::with(['product', 'color', 'size'])
            ->when($userId, function ($query) use ($userId) {
                return $query->where('user_id', $userId);
            })
            ->when(!$userId && $guestId, function ($query) use ($guestId) {
                return $query->where('guest_id', $guestId);
            })
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Cart items retrieved successfully.',
            'data' => $cartItems
        ]);
    }
    public function removeCartItem(Request $request, $id)
    {
        $userId = Auth::id();
        $guestId = $request->cookie('guest_id');

        $item = CartItem::where('id', $id)
            ->when($userId, fn($q) => $q->where('user_id', $userId))
            ->when(!$userId && $guestId, fn($q) => $q->where('guest_id', $guestId))
            ->first();

        if ($item) {
            $item->delete();
            return response()->json(['success' => true, 'message' => 'Item removed']);
        }

        return response()->json(['success' => false, 'message' => 'Item not found'], 404);
    }

    public function clearCart(Request $request)
    {
        $userId = Auth::id();
        $guestId = $request->cookie('guest_id');

        CartItem::when($userId, fn($q) => $q->where('user_id', $userId))
            ->when(!$userId && $guestId, fn($q) => $q->where('guest_id', $guestId))
            ->delete();

        return response()->json(['success' => true, 'message' => 'Cart cleared']);
    }

    public function getCartCount(Request $request)
    {
        $userId = Auth::id();
        $guestId = $request->cookie('guest_id');

        $count = CartItem::when($userId, fn($q) => $q->where('user_id', $userId))
            ->when(!$userId && $guestId, fn($q) => $q->where('guest_id', $guestId))
            ->count();

        return response()->json(['count' => $count]);
    }

    public function updateCart(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer',
        ]);

        $userId = Auth::id();
        $guestId = $request->cookie('guest_id');

        $item = CartItem::where('id', $id)
            ->when($userId, fn($q) => $q->where('user_id', $userId))
            ->when(!$userId && $guestId, fn($q) => $q->where('guest_id', $guestId))
            ->first();

        if (!$item) {
            return response()->json(['success' => false, 'message' => 'Item not found'], 404);
        }

        if ($request->quantity < 1) {
            $item->delete();
            return response()->json(['success' => true, 'message' => 'Item removed']);
        }

        $item->quantity = $request->quantity;
        $item->save();

        return response()->json(['success' => true, 'message' => 'Quantity updated']);
    }
}