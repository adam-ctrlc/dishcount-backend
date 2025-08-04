<?php

namespace App\Http\Controllers\Shops;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\Validator;
use App\Models\Shop;
use App\Models\User;
use App\Models\Category;
use App\Models\Purchase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use function Laravel\Prompts\select;
use Illuminate\Support\Facades\Storage;

class ProductsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $totalCustomer = User::count();

        if ($totalCustomer == 0) {
            $totalCustomer = 1;
        }

        $totalProduct = Product::count();

        if ($totalProduct == 0) {
            $totalProduct = 1;
        }

        $totalPurchase = Purchase::count();

        if ($totalPurchase == 0) {
            $totalPurchase = 1;
        }

        $trending = Purchase::select(
            'product_id',
            'products.product_name',
            'products.image',
            'products.price',
            'products.stock',
            'products.discount',
            DB::raw('SUM(quantity) as total_quantity')
        )
            ->join('products', 'purchases.product_id', '=', 'products.id')
            ->groupBy(
                'product_id',
                'products.product_name',
                'products.image',
                'products.price',
                'products.stock',
                'products.discount'
            )
            ->orderByDesc('total_quantity')
            ->limit(10)
            ->get();

        // Get all products with category and shop, then group and limit to top 5 per category
        $allProducts = Product::with(['category', 'shop'])->get();
        $groupedProducts = $allProducts->groupBy('category.name');

        // Limit each category to top 5 products
        $products = $groupedProducts->map(function ($categoryProducts) {
            return $categoryProducts->take(5);
        });

        if ($products->isEmpty()) {
            return response()->json(['message' => 'No products found'], 200);
        }

        return response()->json([
            'message' => 'Success',
            'trending' => $trending,
            'products' => $products,
            'total' => [
                'customer' => $totalCustomer,
                'product' => $totalProduct,
                'purchase' => $totalPurchase
            ]
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
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $shop = Shop::where('id', $shopId)
            ->where('user_id', $user->id)
            ->first();

        if (!$shop) {
            return response()->json(['message' => 'Unauthorized to create products for this shop'], 403);
        }

        $validator = Validator::make($request->all(), [
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'product_name' => ['required', 'string', 'max:255'],
            'product_description' => ['required', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            'stock' => ['required', 'integer', 'min:0'],
            'discount' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
        }

        $product = Product::create([
            'shop_id' => $shopId,
            'category_id' => $validated['category_id'],
            'product_name' => $validated['product_name'],
            'product_description' => $validated['product_description'],
            'price' => $validated['price'],
            'image' => $imagePath,
            'stock' => $validated['stock'],
            'discount' => $validated['discount'] ?? 0,
            'is_active' => isset($validated['is_active']) ? (bool)$validated['is_active'] : true,
        ]);

        $product->load(['category', 'shop']);

        // Create notification for shop owner
        \App\Models\Notification::create([
            'user_id' => $user->id,
            'type' => 'product_created',
            'data' => [
                'product_id' => $product->id,
                'product_name' => $product->product_name,
                'shop_id' => $shopId
            ]
        ]);

        return response()->json([
            'message' => 'Product created successfully',
            'product' => $product
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($shopId, $productId)
    {
        $shop = Shop::find($shopId);

        if (!$shop) {
            return response()->json(['message' => 'Shop not found'], 404);
        }

        $product = Product::with(['category', 'shop'])
            ->where('id', $productId)
            ->where('shop_id', $shopId)
            ->first();

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        return response()->json([
            'message' => 'Product retrieved successfully',
            'product' => $product
        ]);
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
    public function update(Request $request, $shopId, $productId)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Check if user owns the shop
        $shop = Shop::where('id', $shopId)
            ->where('user_id', $user->id)
            ->first();

        if (!$shop) {
            return response()->json(['message' => 'Unauthorized to update products for this shop'], 403);
        }

        $product = Product::where('id', $productId)
            ->where('shop_id', $shopId)
            ->first();

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'product_name' => ['required', 'string', 'max:255'],
            'product_description' => ['required', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            'stock' => ['required', 'integer', 'min:0'],
            'discount' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($product->image && Storage::disk('public')->exists($product->image)) {
                Storage::disk('public')->delete($product->image);
            }
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        // Handle is_active field properly
        if (isset($validated['is_active'])) {
            $validated['is_active'] = (bool)$validated['is_active'];
        }

        $product->update($validated);
        $product->load(['category', 'shop']);

        return response()->json([
            'message' => 'Product updated successfully',
            'product' => $product
        ]);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($shopId, $productId)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Check if user owns the shop
        $shop = Shop::where('id', $shopId)
            ->where('user_id', $user->id)
            ->first();

        if (!$shop) {
            return response()->json(['message' => 'Unauthorized to delete products for this shop'], 403);
        }

        $product = Product::where('shop_id', $shopId)
            ->where('id', $productId)
            ->first();

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $product->update(['is_active' => false]);

        return response()->json(['message' => 'Product deleted successfully']);
    }


    public function publicIndex()
    {

        $totalCustomer = User::count();

        if ($totalCustomer == 0) {
            $totalCustomer = 1;
        }

        $totalProduct = Product::count();

        if ($totalProduct == 0) {
            $totalProduct = 1;
        }

        $totalPurchase = Purchase::count();

        if ($totalPurchase == 0) {
            $totalPurchase = 1;
        }

        $trending = Purchase::select(
            'product_id',
            'products.product_name',
            'products.image',
            'products.price',
            'products.stock',
            'products.discount',
            DB::raw('SUM(quantity) as total_quantity')
        )
            ->join('products', 'purchases.product_id', '=', 'products.id')
            ->groupBy(
                'product_id',
                'products.product_name',
                'products.image',
                'products.price',
                'products.stock',
                'products.discount'
            )
            ->orderByDesc('total_quantity')
            ->limit(10)
            ->get();


        $products = Product::with('category')->paginate(20);
        $products = $products->groupBy('category.name');

        if ($products->isEmpty()) {
            return response()->json(['message' => 'No products found'], 200);
        }

        return response()->json([
            'message' => 'Success',
            'trending' => $trending,
            'products' => $products,
            'total' => [
                'customer' => $totalCustomer,
                'product' => $totalProduct,
                'purchase' => $totalPurchase
            ]
        ], 200);
    }

    public function publicShow($productId)
    {


        $product = Product::with('category')->find($productId);
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }
        return response()->json([
            'message' => 'Success',
            'product' => $product
        ], 200);
    }

    public function getProductsByShop($shopId)
    {
        $shop = Shop::find($shopId);

        if (!$shop) {
            return response()->json(['message' => 'Shop not found'], 404);
        }

        $products = Product::with(['category', 'shop'])
            ->where('shop_id', $shopId)
            ->where('is_active', true)
            ->get();

        return response()->json([
            'message' => 'Products retrieved successfully',
            'products' => $products
        ], 200);
    }

    public function manageIndex($shopId)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Check if user owns the shop
        $shop = Shop::where('id', $shopId)
            ->where('user_id', $user->id)
            ->first();

        if (!$shop) {
            return response()->json(['message' => 'Unauthorized to manage products for this shop'], 403);
        }

        $products = Product::with(['category', 'shop'])
            ->where('shop_id', $shopId)
            ->get();

        return response()->json([
            'message' => 'Products retrieved successfully',
            'products' => $products
        ], 200);
    }
}
