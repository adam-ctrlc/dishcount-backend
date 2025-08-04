<?php

namespace App\Http\Controllers\Shops;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\Purchase;
use App\Models\User;
use App\Models\Shop;
use App\Models\Notification;

class PurchasesController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function index($shopId)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $isShopOwner = Shop::where('id', $shopId)
            ->where('user_id', $user->id)
            ->exists();

        if (!$isShopOwner) {
            return response()->json(['message' => 'You are not authorized to view purchases for this shop'], 403);
        }

        $purchases = Purchase::whereHas('product', function ($query) use ($shopId) {
            $query->where('shop_id', $shopId);
        })
            ->with(['user', 'product', 'paymentStatus', 'paymentMethod'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'message' => 'Purchases retrieved successfully',
            'purchases' => $purchases
        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, $shopId)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $validator = Validator::make($request->all(), [
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'total_price' => ['required', 'numeric', 'min:0.01'],
            'payment_method_id' => ['required', 'integer', 'exists:payment_methods,id'],
            'receipt' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:5120'], // 5MB max
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();

        $paymentStatusId = 1; // To Pay

        $purchaseData = [
            'user_id' => $user->id,
            'product_id' => $validated['product_id'],
            'quantity' => $validated['quantity'],
            'total_price' => $validated['total_price'],
            'payment_method_id' => $validated['payment_method_id'],
            'payment_status_id' => $paymentStatusId,
            'reference_number' => 'REF-' . time() . '-' . rand(1000, 9999),
            'is_active' => true,
        ];

        // Handle receipt upload
        if ($request->hasFile('receipt')) {
            $receiptPath = $request->file('receipt')->store('receipts', 'public');
            $purchaseData['receipt'] = $receiptPath;
        }

        $purchase = Purchase::create($purchaseData);

        // Notify shop owner of new order
        $shop = Shop::find($shopId);
        if ($shop) {
            Notification::create([
                'user_id' => $shop->user_id,
                'type' => 'new_order',
                'data' => ['purchase_id' => $purchase->id, 'shop_id' => $shopId]
            ]);
        }

        return response()->json([
            'message' => 'Purchase created successfully',
            'purchase' => $purchase->load(['product', 'paymentStatus', 'paymentMethod'])
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($shopId, $purchaseId)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $isShopOwner = Shop::where('id', $shopId)
            ->where('user_id', $user->id)
            ->exists();

        if (!$isShopOwner) {
            return response()->json(['message' => 'You are not authorized to view this purchase'], 403);
        }

        $purchase = Purchase::whereHas('product', function ($query) use ($shopId) {
            $query->where('shop_id', $shopId);
        })
            ->where('id', $purchaseId)
            ->with(['user', 'product', 'paymentStatus', 'paymentMethod'])
            ->first();

        if (!$purchase) {
            return response()->json(['message' => 'Purchase not found'], 404);
        }

        return response()->json([
            'message' => 'Purchase retrieved successfully',
            'purchase' => $purchase
        ], 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $shopId, $purchaseId)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $isShopOwner = Shop::where('id', $shopId)
            ->where('user_id', $user->id)
            ->exists();

        if (!$isShopOwner) {
            return response()->json(['message' => 'You are not authorized to update this purchase'], 403);
        }

        $purchase = Purchase::whereHas('product', function ($query) use ($shopId) {
            $query->where('shop_id', $shopId);
        })
            ->where('id', $purchaseId)
            ->first();

        if (!$purchase) {
            return response()->json(['message' => 'Purchase not found'], 404);
        }

        $validated = Validator::make($request->all(), [
            'product_id' => ['sometimes', 'integer', 'exists:products,id'],
            'quantity' => ['sometimes', 'integer', 'min:1'],
            'total_price' => ['sometimes', 'numeric', 'min:0.01'],
            'payment_status_id' => ['sometimes', 'integer', 'exists:payment_statuses,id'],
        ])->validate();

        $purchase->update($validated);

        // Notify customer of purchase status update
        Notification::create([
            'user_id' => $purchase->user_id,
            'type' => 'order_updated',
            'data' => ['purchase_id' => $purchase->id]
        ]);

        return response()->json([
            'message' => 'Purchase updated successfully',
            'purchase' => $purchase->load(['product', 'paymentStatus', 'paymentMethod'])
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($shopId, $purchaseId)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $isShopOwner = Shop::where('id', $shopId)
            ->where('user_id', $user->id)
            ->exists();

        if (!$isShopOwner) {
            return response()->json(['message' => 'You are not authorized to delete this purchase'], 403);
        }

        $purchase = Purchase::whereHas('product', function ($query) use ($shopId) {
            $query->where('shop_id', $shopId);
        })
            ->where('id', $purchaseId)
            ->first();

        if (!$purchase) {
            return response()->json(['message' => 'Purchase not found'], 404);
        }

        $purchase->update(['is_active' => false]);

        return response()->json([
            'message' => 'Purchase deleted successfully'
        ], 200);
    }
}
