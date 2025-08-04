<?php

namespace App\Http\Controllers\Shops;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Shop;
use App\Models\Product;
use App\Models\Category;
use App\Models\User;
use App\Models\Purchase;
use App\Models\Rating;
use Illuminate\Support\Facades\Validator;

class ShopController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $shop = Shop::where('user_id', $user->id)->first();
        logger($shop);

        if (!$shop) {
            return response()->json(['error' => 'Shop not found. Please create a shop first.'], 404);
        }

        return response()->json([
            'message' => 'User shop details retrieved successfully',
            'shop' => $shop
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
    public function store(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Check if user already has a shop
        $existingShop = Shop::where('user_id', $user->id)->first();
        if ($existingShop) {
            return response()->json(['error' => 'You already have a shop'], 400);
        }

        $validated = Validator::make($request->all(), [
            'shop_name' => ['required', 'string', 'max:255'],
            'shop_description' => ['nullable', 'string'],
            'shop_address' => ['nullable', 'string', 'max:255'],
            'shop_phone' => ['nullable', 'string', 'max:15'],
            'shop_email' => ['nullable', 'email', 'max:255'],
            'shop_logo' => ['nullable', 'string', 'max:255'],
        ])->validate();

        $shop = Shop::create([
            'id' => \Illuminate\Support\Str::uuid(),
            'user_id' => $user->id,
            'name' => $validated['shop_name'],
            'shop_description' => $validated['shop_description'] ?? null,
            'shop_address' => $validated['shop_address'] ?? null,
            'shop_phone' => $validated['shop_phone'] ?? null,
            'shop_email' => $validated['shop_email'] ?? null,
            'shop_logo' => $validated['shop_logo'] ?? null,
        ]);

        // Update user's has_shop flag
        User::where('id', $user->id)->update(['has_shop' => true]);

        // Create notification for shop creation
        \App\Models\Notification::create([
            'user_id' => $user->id,
            'type' => 'shop_created',
            'data' => [
                'shop_id' => $shop->id,
                'shop_name' => $shop->name
            ]
        ]);

        return response()->json([
            'message' => 'Shop created successfully',
            'shop' => $shop
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($shopId)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $owner = Shop::where('id', $shopId)->first();


        if (!$owner) {
            return response()->json(['message' => 'Shop not found'], 404);
        }

        $userOwnShop = $owner->user_id === $user->id;

        if (!$userOwnShop) {
            return response()->json([
                'message' => 'Not authorized. Viewing other people\'s shop details.',
                'shop' => $owner,
                'access' => 'view-only'
            ], 403);
        }

        if ($userOwnShop) {
            return response()->json([
                'message' => 'Successfully retreived your own shop details',
                'shop' => $owner,
                'access' => 'full'
            ], 200);
        }
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
    public function update(Request $request, string $id, $shopId)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $owner = Shop::where('id', $shopId)
            ->where('user_id', $user->id)
            ->first();


        if (!$owner) {
            return response()->json(['message' => 'Shop not found'], 404);
        }

        if ($owner) {
            $validated = Validator::make($request->all(), [
                'shop_name' => ['required', 'string', 'max:255'],
                'shop_description' => ['nullable', 'string'],
                'shop_address' => ['required', 'string', 'max:255'],
                'shop_phone' => ['nullable', 'string', 'max:15'],
                'shop_email' => ['nullable', 'email', 'max:255'],
                'shop_logo' => ['nullable', 'string', 'max:255'],
            ])->validate();

            $owner->update($validated);

            return response()->json([
                'message' => 'Shop retrieved successfully for editing',
                'shop' => $owner
            ], 200);
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($shopId)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $owner = Shop::where('id', $shopId)
            ->where('user_id', $user->id)
            ->first();

        if (!$owner) {
            return response()->json(['message' => 'Shop not found'], 404);
        }

        if ($owner) {
            $owner->delete();
            return response()->json(['message' => 'Shop deleted successfully'], 200);
        }
    }

    /**
     * Public listing of active shops.
     */
    public function publicIndex()
    {
        $shops = Shop::paginate(20);
        return response()->json([
            'message' => 'Success',
            'shops' => $shops
        ], 200);
    }

    /**
     * Public detail of a single shop.
     */
    public function publicShow($shopId)
    {
        $shop = Shop::find($shopId);
        if (!$shop) {
            return response()->json(['message' => 'Shop not found'], 404);
        }
        return response()->json($shop, 200);
    }

    /**
     * Get shop analytics data.
     */
    public function analytics($shopId, Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $shop = Shop::where('id', $shopId)
            ->where('user_id', $user->id)
            ->first();

        if (!$shop) {
            return response()->json(['message' => 'Shop not found or unauthorized'], 404);
        }

        $period = $request->query('period', 'all');

        // Base query for the shop's products
        $productsQuery = Product::where('shop_id', $shopId);
        $purchasesQuery = Purchase::whereHas('product', function ($query) use ($shopId) {
            $query->where('shop_id', $shopId);
        });

        // Apply date filters based on period
        if ($period !== 'all') {
            $dateFilter = match ($period) {
                'weekly' => now()->subWeek(),
                'monthly' => now()->subMonth(),
                'yearly' => now()->subYear(),
                default => null
            };

            if ($dateFilter) {
                $purchasesQuery->where('created_at', '>=', $dateFilter);
            }
        }

        // Calculate analytics
        $totalProducts = $productsQuery->where('is_active', true)->count();

        // IMPORTANT: Only count revenue from COMPLETED orders (payment_status_id = 4)
        $completedPurchases = clone $purchasesQuery;
        $totalRevenue = $completedPurchases->where('payment_status_id', 4)->sum('total_price');

        // Count all orders regardless of status
        $totalOrders = $purchasesQuery->count();

        // Calculate average rating
        $avgRating = Rating::whereHas('product', function ($query) use ($shopId) {
            $query->where('shop_id', $shopId);
        })->avg('rating') ?? 0;

        // Debug information (can be removed in production)
        $debugInfo = [
            'shop_id' => $shopId,
            'user_id' => $user->id,
            'total_purchases' => $totalOrders,
            'completed_purchases' => $completedPurchases->where('payment_status_id', 4)->count(),
            'to_pay_purchases' => $purchasesQuery->where('payment_status_id', 1)->count(),
            'to_ship_purchases' => $purchasesQuery->where('payment_status_id', 2)->count(),
            'to_receive_purchases' => $purchasesQuery->where('payment_status_id', 3)->count(),
        ];

        return response()->json([
            'message' => 'Analytics retrieved successfully',
            'analytics' => [
                'totalProducts' => $totalProducts,
                'totalRevenue' => number_format($totalRevenue, 2),
                'totalOrders' => $totalOrders,
                'avgRating' => round($avgRating, 1)
            ],
            'debug' => $debugInfo // Remove this in production
        ]);
    }

    /**
     * Search shops and products based on query
     */
    public function search(Request $request)
    {
        $searchQuery = $request->get('q', '');
        $type = $request->get('type', 'all'); // 'shops', 'products', or 'all'

        if (empty($searchQuery)) {
            return response()->json([
                'message' => 'Search query is required',
                'shops' => [],
                'products' => []
            ], 400);
        }

        $results = [
            'shops' => [],
            'products' => []
        ];

        // Search shops
        if ($type === 'all' || $type === 'shops') {
            $shops = Shop::where('name', 'LIKE', '%' . $searchQuery . '%')
                ->orWhere('shop_description', 'LIKE', '%' . $searchQuery . '%')
                ->orWhere('shop_address', 'LIKE', '%' . $searchQuery . '%')
                ->with(['user:id,first_name,last_name'])
                ->withCount(['products' => function ($query) {
                    $query->where('is_active', true);
                }])
                ->limit(20)
                ->get();

            $results['shops'] = $shops->map(function ($shop) {
                return [
                    'id' => $shop->id,
                    'name' => $shop->name,
                    'description' => $shop->shop_description,
                    'address' => $shop->shop_address,
                    'logo' => $shop->shop_logo,
                    'owner' => $shop->user ? $shop->user->first_name . ' ' . $shop->user->last_name : null,
                    'products_count' => $shop->products_count,
                    'type' => 'shop'
                ];
            });
        }

        // Search products
        if ($type === 'all' || $type === 'products') {
            $products = Product::where('is_active', true)
                ->where(function ($query) use ($searchQuery) {
                    $query->where('product_name', 'LIKE', '%' . $searchQuery . '%')
                        ->orWhere('product_description', 'LIKE', '%' . $searchQuery . '%');
                })
                ->orWhereHas('shop', function ($shopQuery) use ($searchQuery) {
                    $shopQuery->where('name', 'LIKE', '%' . $searchQuery . '%');
                })
                ->orWhereHas('category', function ($categoryQuery) use ($searchQuery) {
                    $categoryQuery->where('name', 'LIKE', '%' . $searchQuery . '%');
                })
                ->with(['shop:id,name,shop_logo', 'category:id,name'])
                ->limit(50)
                ->get();

            $results['products'] = $products->map(function ($product) {
                return [
                    'id' => $product->id,
                    'product_name' => $product->product_name,
                    'product_description' => $product->product_description,
                    'price' => $product->price,
                    'image' => $product->image,
                    'discount' => $product->discount,
                    'shop' => [
                        'id' => $product->shop->id,
                        'name' => $product->shop->name,
                        'logo' => $product->shop->shop_logo
                    ],
                    'category' => $product->category,
                    'type' => 'product'
                ];
            });
        }

        return response()->json([
            'message' => 'Search completed successfully',
            'query' => $searchQuery,
            'total_shops' => count($results['shops']),
            'total_products' => count($results['products']),
            'shops' => $results['shops'],
            'products' => $results['products']
        ]);
    }
}
