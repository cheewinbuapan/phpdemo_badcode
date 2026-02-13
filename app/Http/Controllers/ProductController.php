<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\View\View;

class ProductController extends Controller
{
    /**
     * Display a listing of all products
     */
    public function index(): View
    {
        $products = Product::orderBy('product_name')->get();
        
        return view('products.index', compact('products'));
    }
}
