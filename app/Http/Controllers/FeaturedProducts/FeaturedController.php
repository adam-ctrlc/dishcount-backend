<?php

namespace App\Http\Controllers\FeaturedProducts;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FeaturedController extends Controller
{
    public function index()
    {
        $featuredProducts = Product::where('is_featured', true)->get();
    }
}
