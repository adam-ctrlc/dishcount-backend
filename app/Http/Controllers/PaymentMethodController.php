<?php

namespace App\Http\Controllers;

use App\Models\PaymentMethod;
use Illuminate\Http\Request;

class PaymentMethodController extends Controller
{
    public function index()
    {
        try {
            $paymentMethods = PaymentMethod::all();
            return response()->json($paymentMethods);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch payment methods',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'nullable|string',
            'number' => 'nullable|string|max:255'
        ]);

        try {
            $paymentMethod = PaymentMethod::create($request->all());
            return response()->json($paymentMethod, 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create payment method',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $paymentMethod = PaymentMethod::findOrFail($id);
            return response()->json($paymentMethod);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Payment method not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'image' => 'nullable|string',
            'number' => 'nullable|string|max:255'
        ]);

        try {
            $paymentMethod = PaymentMethod::findOrFail($id);
            $paymentMethod->update($request->all());
            return response()->json($paymentMethod);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update payment method',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $paymentMethod = PaymentMethod::findOrFail($id);
            $paymentMethod->delete();
            return response()->json([
                'message' => 'Payment method deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete payment method',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}