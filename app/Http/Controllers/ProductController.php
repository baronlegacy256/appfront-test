<?php

namespace App\Http\Controllers;

use App\Services\ExchangeRateService;
use App\Models\Product;

class ProductController extends Controller
{
    /**
     * @var ExchangeRateService
     */
    private $exchangeRateService;

    /**
     * Create a new controller instance.
     *
     * @param ExchangeRateService $exchangeRateService
     */
    public function __construct(ExchangeRateService $exchangeRateService)
    {
        $this->exchangeRateService = $exchangeRateService;
    }

    /**
     * Display a listing of the products.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $products = Product::latest()->get();
        $exchangeRate = $this->exchangeRateService->getExchangeRate();

        return view('products.list', compact('products', 'exchangeRate'));
    }

    /**
     * Display the specified product.
     *
     * @param int $product_id
     * @return \Illuminate\View\View
     */
    public function show($product_id)
    {
        $product = Product::findOrFail($product_id);
        $exchangeRate = $this->exchangeRateService->getExchangeRate();

        return view('products.show', compact('product', 'exchangeRate'));
    }
}
