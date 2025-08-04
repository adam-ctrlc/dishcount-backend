<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\Purchase;
use App\Models\Product;
use App\Models\Shop;
use App\Models\Notification;

class CartController extends Controller
{
  public function checkout(Request $request)
  {
    $user = Auth::user();

    if (!$user) {
      return response()->json(['error' => 'Unauthorized'], 401);
    }

    // Handle FormData vs JSON
    $items = $request->input('items');
    if (is_string($items)) {
      $items = json_decode($items, true);
    }

    $validator = Validator::make(array_merge($request->all(), ['items' => $items]), [
      'items' => ['required', 'array', 'min:1'],
      'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
      'items.*.quantity' => ['required', 'integer', 'min:1'],
      'items.*.total_amount' => ['required', 'numeric', 'min:0.01'],
      'payment_method_id' => ['required', 'integer', 'exists:payment_methods,id'],
      'receipt' => ['nullable', 'image', 'max:5120'], // 5MB max
    ]);

    if ($validator->fails()) {
      return response()->json([
        'message' => 'Validation failed',
        'errors' => $validator->errors()
      ], 422);
    }

    $validated = $validator->validated();

    // Handle receipt upload
    $receiptPath = null;
    if ($request->hasFile('receipt')) {
      $receiptPath = $request->file('receipt')->store('receipts', 'public');
    }

    // All orders start as pending for store owner verification
    $paymentStatusId = 1; // Pending - store owner needs to verify receipt and update status

    $purchases = [];

    try {
      foreach ($validated['items'] as $item) {
        // Get product to verify shop and price
        $product = Product::with('shop')->find($item['product_id']);

        if (!$product) {
          return response()->json([
            'message' => 'Product not found: ' . $item['product_id']
          ], 404);
        }

        // Verify the total amount matches product price * quantity
        $expectedTotal = $product->price * $item['quantity'];
        if (abs($expectedTotal - $item['total_amount']) > 0.01) {
          return response()->json([
            'message' => 'Invalid total amount for product: ' . $product->product_name
          ], 400);
        }

        $purchaseData = [
          'user_id' => $user->id,
          'product_id' => $item['product_id'],
          'quantity' => $item['quantity'],
          'total_price' => $item['total_amount'],
          'payment_method_id' => $validated['payment_method_id'],
          'payment_status_id' => $paymentStatusId,
          'reference_number' => 'REF-' . time() . '-' . rand(1000, 9999),
          'is_active' => true,
          'receipt' => $receiptPath,
        ];

        $purchase = Purchase::create($purchaseData);

        // Load relationships for response
        $purchase->load(['product', 'paymentStatus', 'paymentMethod']);
        $purchases[] = $purchase;

        // Notify shop owner of new order
        if ($product->shop) {
          Notification::create([
            'user_id' => $product->shop->user_id,
            'type' => 'new_order',
            'data' => [
              'purchase_id' => $purchase->id,
              'shop_id' => $product->shop->id,
              'product_name' => $product->product_name,
              'customer_name' => $user->first_name . ' ' . $user->last_name
            ]
          ]);
        }
      }

      return response()->json([
        'message' => 'Checkout completed successfully',
        'purchases' => $purchases,
        'total_orders' => count($purchases)
      ], 201);
    } catch (\Exception $e) {
      return response()->json([
        'message' => 'Checkout failed',
        'error' => $e->getMessage()
      ], 500);
    }
  }
}
