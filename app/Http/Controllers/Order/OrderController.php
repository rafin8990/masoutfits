<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function placeOrder(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'guest_id' => 'nullable|string',
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone_number' => 'required|string|max:15',
            'order_status' => 'required|string|max:50',
            'notes' => 'nullable|string',
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.color_id' => 'required|exists:colors,id',
            'items.*.size_id' => 'required|exists:sizes,id',
            'items.*.quantity' => 'required|integer|min:1',
            'country' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'town' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'police_station' => 'nullable|string|max:100',
            'post_code' => 'nullable|string|max:20',
            'area_details' => 'nullable|string|max:255',
            'address_type' => 'nullable|string|max:50',
            'apartment_address' => 'nullable|string',
            'payment_method' => 'required|string|max:50',
            'payment_status' => 'required|string|max:50',
            'transaction_id' => 'nullable|string|max:100',
            'is_paid' => 'nullable|boolean',
            'payment_date' => 'nullable|date',
            'total_amount' => 'required|string|max:50',
        ]);

        DB::beginTransaction();
        try {

            $orderData = collect($validated)->only([
                'order_status',
                'notes',
            ])->toArray();

            $order = Order::create($orderData);

            $order->orderUserInfo()->create([
                'user_id' => $validated['user_id'],
                'guest_id' => $validated['guest_id'],
                'full_name' => $validated['full_name'],
                'email' => $validated['email'],
                'phone_number' => $validated['phone_number'],
            ]);
            $order->address()->create([
                'country' => $validated['country'],
                'city' => $validated['city'],
                'town' => $validated['town'],
                'state' => $validated['state'],
                'police_station' => $validated['police_station'],
                'post_code' => $validated['post_code'],
                'area_details' => $validated['area_details'],
                'address_type' => $validated['address_type'],
                'apartment_address' => $validated['apartment_address'],
            ]);

            if (!empty($validated['items'])) {
                foreach ($validated['items'] as $item) {
                    $order->orderItems()->create([
                        'product_id' => $item['product_id'],
                        'color_id' => $item['color_id'],
                        'size_id' => $item['size_id'],
                        'quantity' => $item['quantity'],
                    ]);
                }
            }
            $paymentType = $request->input('payment_method');

            if ($paymentType == 'cod') {
                $order->payment()->create([
                    'payment_method' => $validated['payment_method'],
                    'payment_status' => $validated['payment_status'],
                    'transaction_id' => $validated['transaction_id'],
                    'is_paid' => $validated['is_paid'],
                    'payment_date' => $validated['payment_date'],
                    'total_amount' => $validated['total_amount'],
                ]);
            }

            //   $productData = collect($validatedData)->only([
            //     'name',
            //     'description',
            //     'fit',
            //     'care',
            //     'category_id',
            //     'sub_category_id',
            //     'price'
            // ])->toArray();

            // $product = Product::create($productData);

            // // Create tags
            // if (!empty($validatedData['tags'])) {
            //     foreach ($validatedData['tags'] as $tag) {
            //         $product->tags()->create($tag);
            //     }
            // }



        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to place order',
                'details' => $e->getMessage()
            ], 500);
        }

    }
}
