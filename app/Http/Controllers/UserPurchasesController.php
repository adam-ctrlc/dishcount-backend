<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Purchase;

class UserPurchasesController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $purchases = Purchase::where('user_id', $user->id)
            ->with(['product.shop', 'paymentStatus', 'paymentMethod'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'message' => 'User purchases retrieved successfully',
            'data' => $purchases
        ], 200);
    }

    public function show($purchaseId)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $purchase = Purchase::where('id', $purchaseId)
            ->where('user_id', $user->id)
            ->with(['product.shop', 'paymentStatus', 'paymentMethod'])
            ->first();

        if (!$purchase) {
            return response()->json(['error' => 'Purchase not found'], 404);
        }

        return response()->json([
            'message' => 'Purchase retrieved successfully',
            'data' => $purchase
        ], 200);
    }

    public function cancel($purchaseId)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $purchase = Purchase::where('id', $purchaseId)
            ->where('user_id', $user->id)
            ->first();

        if (!$purchase) {
            return response()->json(['error' => 'Purchase not found'], 404);
        }

        // Check if the purchase can be cancelled (only pending orders)
        if ($purchase->payment_status_id !== 1) { // 1 is Pending
            return response()->json(['error' => 'This order cannot be cancelled'], 400);
        }

        // Update payment status to Cancelled (4 is Cancelled)
        $purchase->payment_status_id = 4;
        $purchase->save();

        return response()->json([
            'message' => 'Order cancelled successfully',
            'data' => $purchase->load(['product.shop', 'paymentStatus', 'paymentMethod'])
        ], 200);
    }

    public function refund(Request $request, $purchaseId)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $purchase = Purchase::where('id', $purchaseId)
            ->where('user_id', $user->id)
            ->first();

        if (!$purchase) {
            return response()->json(['error' => 'Purchase not found'], 404);
        }

        // Check if the purchase can be refunded (completed orders)
        if ($purchase->payment_status_id !== 4) { // 4 is Completed
            return response()->json(['error' => 'This order cannot be refunded'], 400);
        }

        // Update payment status to Refunded (3 is Refunded & Returned)
        $purchase->payment_status_id = 3;
        $purchase->refund_reason = $request->input('reason', '');
        $purchase->save();

        return response()->json([
            'message' => 'Refund request submitted successfully',
            'data' => $purchase->load(['product.shop', 'paymentStatus', 'paymentMethod'])
        ], 200);
    }

    public function markReceived($purchaseId)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $purchase = Purchase::where('id', $purchaseId)
            ->where('user_id', $user->id)
            ->first();

        if (!$purchase) {
            return response()->json(['error' => 'Purchase not found'], 404);
        }

        // Mark as received (you might want to add a received_at timestamp)
        $purchase->received_at = now();
        $purchase->save();

        return response()->json([
            'message' => 'Order marked as received successfully',
            'data' => $purchase->load(['product.shop', 'paymentStatus', 'paymentMethod'])
        ], 200);
    }
}
