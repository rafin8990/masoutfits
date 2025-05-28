<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Stripe\Stripe;
use Stripe\Checkout\Session as StripeSession;
use Stripe\Webhook;

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
            'order_status' => 'nullable|string|max:50',
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
            'total_amount' => 'required|numeric',
        ]);

        DB::beginTransaction();
        try {
            $order = Order::create([
                'order_status' => $validated['order_status'],
                'notes' => $validated['notes'] ?? null,
            ]);

            $order->orderUserInfo()->create([
                'user_id' => $validated['user_id'] ?? null,
                'guest_id' => $validated['guest_id'] ?? null,
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

            $lineItems = [];
            foreach ($validated['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);
                $lineItems[] = [
                    'price_data' => [
                        'currency' => 'usd',
                        'product_data' => [
                            'name' => $product->name,
                        ],
                        'unit_amount' => intval($product->price * 100),
                    ],
                    'quantity' => $item['quantity'],
                ];

                $order->orderItems()->create([
                    'product_id' => $item['product_id'],
                    'color_id' => $item['color_id'],
                    'size_id' => $item['size_id'],
                    'quantity' => $item['quantity'],
                ]);
            }

            if ($validated['payment_method'] === 'cod') {
                $order->payment()->create([
                    'payment_method' => 'cod',
                    'transaction_id' => 'COD-' . uniqid(),
                    'is_paid' => false,
                    'payment_date' => null,
                    'total_amount' => $validated['total_amount'],
                ]);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Order placed successfully with Cash on Delivery.',
                    'order_id' => $order->id,
                ], 201);
            }

            if ($validated['payment_method'] === 'stripe') {
                Stripe::setApiKey(config('services.stripe.secret'));
                $session = StripeSession::create([
                    'payment_method_types' => ['card'],
                    'line_items' => $lineItems,
                    'mode' => 'payment',
                    'customer_email' => $validated['email'],
                    'metadata' => [
                        'order_id' => $order->id,
                    ],
                    'success_url' => url('/payment/success?session_id={CHECKOUT_SESSION_ID}'),
                    'cancel_url' => url('/payment/cancel'),
                ]);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Stripe session created successfully.',
                    'session_url' => $session->url,
                ]);
            }

            throw new \Exception('Invalid payment method selected.');

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Order placement failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function handleWebhook(Request $request)
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        $payload = $request->getContent();
        $sig_header = $request->header('Stripe-Signature');
        $endpoint_secret = config('services.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent($payload, $sig_header, $endpoint_secret);

            if ($event->type === 'checkout.session.completed') {
                $session = $event->data->object;
                $orderId = $session->metadata->order_id;
                $order = Order::find($orderId);
                if ($order) {
                    $order->payment()->update([
                        'payment_method' => 'stripe',
                        'transaction_id' => $session->payment_intent,
                        'is_paid' => true,
                        'payment_date' => now(),
                    ]);
                }
            }

            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
