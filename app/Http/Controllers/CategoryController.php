<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
  public function index()
  {
    try {
      $categories = Category::all();
      return response()->json($categories);
    } catch (\Exception $e) {
      return response()->json([
        'message' => 'Failed to fetch categories',
        'error' => $e->getMessage()
      ], 500);
    }
  }

  public function show($id)
  {
    try {
      $category = Category::findOrFail($id);
      return response()->json($category);
    } catch (\Exception $e) {
      return response()->json([
        'message' => 'Category not found',
        'error' => $e->getMessage()
      ], 404);
    }
  }
}
