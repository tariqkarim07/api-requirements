<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductResource;
use App\Service\ProductService;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    private $product;
    public function __construct(ProductService $productService)
    {
        $this->product = $productService;
    }

    public function getProducts():JsonResponse
    {
        $products = $this->product->getProducts();
        return response()->json(ProductResource::collection($products));
    }
}
