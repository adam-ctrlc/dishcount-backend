<?php

namespace App\Http\Controllers\Shops;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Purchase;
use App\Models\User;
use App\Models\Shop;
use App\Models\PaymentStatus;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PaymentStatusController extends Controller
{
    public function showPaymentStatus($shopId)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'message' => 'Not authorized'
            ], 401);
        }

        $isShopOwner = Shop::where('id', $shopId)
            ->where('user_id', $user->id)
            ->exists();

        if ($isShopOwner) {
            $purchases = Purchase::whereHas('product', function ($query) use ($shopId): void {
                $query->where('shop_id', $shopId);
            })
                ->with(['paymentStatus', 'user:id,first_name,last_name,email', 'product:id,product_name'])
                ->orderBy('created_at', 'desc')
                ->paginate(20);

            return response()->json([
                'is_shop_owner' => true,
                'purchases' => $purchases
            ]);
        }

        $purchases = Purchase::whereHas('product', function ($query) use ($shopId) {
            $query->where('shop_id', $shopId);
        })
            ->where('user_id', $user->id)
            ->with(['paymentStatus', 'product:id,product_name'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'is_shop_owner' => false,
            'purchases' => $purchases
        ]);
    }

    public function updatePaymentStatus(Request $request, $shopId, $purchaseId)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $isShopOwner = Shop::where('id', $shopId)
            ->where('user_id', $user->id)
            ->exists();

        if (!$isShopOwner) {
            return response()->json(['message' => 'You are not authorized to update this purchase'], 403);
        }

        $validator = Validator::make($request->all(), [
            'payment_status_id' => ['required', 'integer', 'exists:payment_statuses,id']
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $purchase = Purchase::whereHas('product', function ($query) use ($shopId) {
            $query->where('shop_id', $shopId);
        })
            ->where('id', $purchaseId)
            ->first();

        if (!$purchase) {
            return response()->json(['message' => 'Purchase not found'], 404);
        }

        $purchase->payment_status_id = $request->payment_status_id;
        $purchase->save();

        // Create notification for customer about payment status update
        $paymentStatus = \App\Models\PaymentStatus::find($request->payment_status_id);
        \App\Models\Notification::create([
            'user_id' => $purchase->user_id,
            'type' => 'order_updated',
            'data' => [
                'purchase_id' => $purchase->id,
                'payment_status' => $paymentStatus->status ?? 'Unknown',
                'shop_id' => $shopId
            ]
        ]);

        return response()->json([
            'message' => 'Payment status updated successfully',
            'purchase' => $purchase->load(['paymentStatus', 'user:id,first_name,last_name,email', 'product:id,product_name'])
        ]);
    }

    public function getPaymentStatuses()
    {
        $paymentStatuses = PaymentStatus::all();
        return response()->json($paymentStatuses);
    }
}
