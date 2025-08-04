<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\UserController;
use App\Http\Controllers\Shops\ShopController;
use App\Http\Controllers\Shops\PurchasesController;
use App\Http\Controllers\Shops\ProductsController;
use App\Http\Middleware\AuthJwt;
use App\Http\Controllers\Auth\ProfileController;
use App\Http\Controllers\Auth\NotificationController;
use App\Http\Controllers\Philosophy\PhilosophyController;
use App\Http\Controllers\PaymentMethodController;
use App\Http\Controllers\UserPurchasesController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\Shops\PaymentStatusController;
use App\Http\Controllers\CartController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\FeaturedProducts\FeaturedController;

Route::prefix('/v1')->group(function () {

    Route::get('/featured', [FeaturedController::class,'index']);
    
    Route::prefix('/auth')->group(function () {
        Route::post('/login', [UserController::class, 'login']);
        Route::post('/register', [UserController::class, 'register']);
        Route::post('/refresh', [UserController::class, 'refresh']);
        Route::delete('/delete', [UserController::class, 'deleteAccount']);
        Route::middleware(AuthJwt::class)->group(callback: function () {
            Route::post('/logout', [UserController::class, 'logout']);
            Route::get('/me', [ProfileController::class, 'show']);
            Route::put('/profile', [ProfileController::class, 'update']);
            Route::post('/profile', [ProfileController::class, 'update']); // For file uploads
            Route::put('/profile/password', [ProfileController::class, 'changePassword']);
            Route::post('/profile/change-password', [ProfileController::class, 'changePassword']);
            Route::post('/profile/upload-avatar', [ProfileController::class, 'uploadProfilePicture']);
            Route::apiResource('notifications', NotificationController::class);
            Route::get('/purchases', [UserPurchasesController::class, 'index']);
            Route::get('/purchases/{purchaseId}', [UserPurchasesController::class, 'show']);
            Route::put('/purchases/{purchaseId}/cancel', [UserPurchasesController::class, 'cancel']);
            Route::put('/purchases/{purchaseId}/refund', [UserPurchasesController::class, 'refund']);
            Route::put('/purchases/{purchaseId}/received', [UserPurchasesController::class, 'markReceived']);
            Route::post('/cart/checkout', [CartController::class, 'checkout']);
        });
    });

    Route::middleware(AuthJwt::class)->prefix('shops')->group(function () {

        Route::apiResource('manage', ShopController::class);
        Route::get('manage/{shopId}/analytics', [ShopController::class, 'analytics']);

        // Shop product management routes
        Route::get('manage/{shopId}/products', [ProductsController::class, 'manageIndex']);
        Route::post('manage/{shopId}/products', [ProductsController::class, 'store']);
        Route::get('manage/{shopId}/products/{productId}', [ProductsController::class, 'show']);
        Route::put('manage/{shopId}/products/{productId}', [ProductsController::class, 'update']);
        Route::post('manage/{shopId}/products/{productId}', [ProductsController::class, 'update']); // For file uploads
        Route::delete('manage/{shopId}/products/{productId}', [ProductsController::class, 'destroy']);

        Route::apiResource('products', ProductsController::class);
        Route::get('{shopId}/products', [ProductsController::class, 'getProductsByShop']);
        Route::get('{shopId}/products/{productId}', [ProductsController::class, 'show']);
        Route::get('{shopId}/products/{productId}/users/{userId}', [PurchasesController::class, 'showPaymentStatus']);
        Route::get('{shopId}/payment-status', [PaymentStatusController::class, 'showPaymentStatus']);
        Route::put('{shopId}/purchases/{purchaseId}/payment-status', [PaymentStatusController::class, 'updatePaymentStatus']);
        Route::apiResource('{shopId}/purchases', PurchasesController::class);

        // Debug route (remove in production)
        Route::get('{shopId}/debug/purchases', function ($shopId) {
            $user = Auth::user();
            $shop = \App\Models\Shop::where('id', $shopId)->where('user_id', $user->id)->first();

            if (!$shop) {
                return response()->json(['error' => 'Shop not found or unauthorized'], 404);
            }

            $allPurchases = \App\Models\Purchase::whereHas('product', function ($query) use ($shopId) {
                $query->where('shop_id', $shopId);
            })->with(['user', 'product', 'paymentStatus', 'paymentMethod'])->get();

            return response()->json([
                'shop_id' => $shopId,
                'user_id' => $user->id,
                'shop_owner_id' => $shop->user_id,
                'is_owner' => $shop->user_id === $user->id,
                'purchases_count' => $allPurchases->count(),
                'purchases' => $allPurchases
            ]);
        });

        // Search route
        Route::get('search', [ShopController::class, 'search']);
    });

    Route::prefix('/public')->group(function () {
        Route::get('/philosophy', [PhilosophyController::class, 'index']);
        Route::get('/products', [ProductsController::class, 'publicIndex']);
        Route::get('/products/{productId}', [ProductsController::class, 'publicShow']);
        Route::get('/shops', [ShopController::class, 'publicIndex']);
        Route::get('/shops/{shopId}', [ShopController::class, 'publicShow']);
    });

    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/categories/{id}', [CategoryController::class, 'show']);
    Route::get('/payment-statuses', [PaymentStatusController::class, 'getPaymentStatuses']);

    Route::get('/payment-methods', [PaymentMethodController::class, 'index']);
    Route::middleware(AuthJwt::class)->group(function () {
        Route::apiResource('payment-methods', PaymentMethodController::class)->except(['index']);

        // Profile routes
        Route::get('/profile', [ProfileController::class, 'show']);
        Route::post('/profile', [ProfileController::class, 'update']);
        Route::post('/profile/change-password', [ProfileController::class, 'changePassword']);
        Route::post('/profile/upload-avatar', [ProfileController::class, 'uploadProfilePicture']);
    });
});

