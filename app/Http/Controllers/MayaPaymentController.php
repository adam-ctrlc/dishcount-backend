<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class MayaPaymentController extends Controller
{
    public function createCheckout(Request $request)
    {
        $secretKey = env('PAYMAYA_SECRET_KEY'); // Make sure this is in your .env file
        $url = 'https://pg-sandbox.paymaya.com/checkout/v1/checkouts';

        // Get data from the request, with default values if not provided
        $value = $request->input('totalAmount.value', 100);
        $currency = $request->input('totalAmount.currency', 'PHP');
        $tax = $request->input('totalAmount.details.tax', '0.00');
        $subtotal = $request->input('totalAmount.details.subtotal', '100.00');

        $firstName = $request->input('buyer.firstName', 'John');
        $lastName = $request->input('buyer.lastName', 'Doe');
        $email = $request->input('buyer.contact.email', 'test@example.com');
        $phone = $request->input('buyer.contact.phone', '');

        $successUrl = $request->input('redirectUrl.success', url('/checkout/success'));
        $failureUrl = $request->input('redirectUrl.failure', url('/checkout/failure'));
        $cancelUrl = $request->input('redirectUrl.cancel', url('/checkout/cancel'));

        $requestReferenceNumber = $request->input('requestReferenceNumber', uniqid("order_"));

        $items = [];
        $itemsData = $request->input('items', []);
        foreach ($itemsData as $itemData) {
            $items[] = [
                'name' => $itemData['name'] ?? '',
                'amount' => [
                    'currency' => $itemData['amount']['currency'] ?? 'PHP',
                    'value' => $itemData['amount']['value'] ?? 0,
                    'details' => [
                        'tax' => $itemData['amount']['details']['tax'] ?? '0.00',
                        'subtotal' => $itemData['amount']['details']['subtotal'] ?? '0.00'
                    ]
                ],
                'totalAmount' => [
                    'currency' => $itemData['totalAmount']['currency'] ?? 'PHP',
                    'value' => $itemData['totalAmount']['value'] ?? 0,
                    'details' => [
                        'tax' => $itemData['totalAmount']['details']['tax'] ?? '0.00',
                        'subtotal' => $itemData['totalAmount']['details']['subtotal'] ?? '0.00'
                    ]
                ]
            ];
        }

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Basic ' . base64_encode($secretKey . ':'),
        ])->withOptions(['verify' => false])->post($url, [
            'totalAmount' => [
                'value' => $value,
                'currency' => $currency,
                'details' => [
                    'tax' => $tax,
                    'subtotal' => $subtotal,
                ],
            ],
            'buyer' => [
                'firstName' => $firstName,
                'lastName' => $lastName,
                'contact' => [
                    'email' => $email,
                    'phone' => $phone,
                ],
            ],
            'items' => $items,
            'redirectUrl' => [
                'success' => $successUrl,
                'failure' => $failureUrl,
                'cancel' => $cancelUrl,
            ],
            'requestReferenceNumber' => $requestReferenceNumber,
        ]);

        if ($response->successful()) {
            $responseData = $response->json();
            if (isset($responseData['redirectUrl'])) {
                return redirect()->to($responseData['redirectUrl']);
            } else {
                return response()->json([
                    'message' => 'Checkout creation successful, but no redirect URL found',
                    'details' => $responseData
                ], 200);
            }
        } else {
            return response()->json([
                'message' => 'Checkout creation failed',
                'details' => $response->json(),
            ], $response->status());
        }
    }

    public function checkoutSuccess(Request $request)
    {
        // Handle successful payment
        $paymentId = $request->get('paymentId');
        return "<h1>Payment Successful! 🎉</h1><p>Payment ID: " . $paymentId . "</p>";
    }

    public function checkoutFailure(Request $request)
    {
        // Handle failed payment
        return "<h1>Payment Failed! 😔</h1><p>Please try again.</p>";
    }

    public function checkoutCancel(Request $request)
    {
        // Handle cancelled payment
        return "<h1>Payment Cancelled. 🙅‍♀️</h1><p>You can go back to shopping.</p>";
    }
}
