<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Jobs\SendPriceChangeNotification;

class AdminController extends Controller
{
    /**
     * @var ProductService
     */
    private $productService;

    /**
     * Create a new controller instance.
     *
     * @param ProductService $productService
     */
    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    /**
     * Show the login page.
     *
     * @return \Illuminate\View\View
     */
    public function loginPage()
    {
        return view('login');
    }

    /**
     * Handle the login request.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function login(Request $request)
    {
        if (Auth::attempt($request->only('email', 'password'))) {
            return redirect()->intended(route('admin.products'));
        }

        return redirect()->back()
            ->withInput()
            ->withErrors(['email' => 'Invalid login credentials']);
    }

    /**
     * Handle the logout request.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout()
    {
        Auth::logout();
        return redirect()->route('login');
    }

    /**
     * Display a listing of the products.
     *
     * @return \Illuminate\View\View
     */
    public function products()
    {
        $products = Product::latest()->get();
        return view('admin.products', compact('products'));
    }

    /**
     * Show the form for editing the specified product.
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function editProduct($id)
    {
        $product = Product::findOrFail($id);
        return view('admin.edit_product', compact('product'));
    }

    /**
     * Update the specified product.
     *
     * @param ProductRequest $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateProduct(ProductRequest $request, $id)
    {
        $product = Product::findOrFail($id);
        $oldPrice = $product->price;

        $product = $this->productService->updateProduct(
            $product,
            $request->validated(),
            $request->file('image')
        );

        if ($oldPrice != $product->price) {
            $this->dispatchPriceChangeNotification($product, $oldPrice);
        }

        return redirect()->route('admin.products')
            ->with('success', 'Product updated successfully');
    }

    /**
     * Remove the specified product.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteProduct($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return redirect()->route('admin.products')
            ->with('success', 'Product deleted successfully');
    }

    /**
     * Show the form for creating a new product.
     *
     * @return \Illuminate\View\View
     */
    public function addProductForm()
    {
        return view('admin.add_product');
    }

    /**
     * Store a newly created product.
     *
     * @param ProductRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function addProduct(ProductRequest $request)
    {
        $this->productService->createProduct(
            $request->validated(),
            $request->file('image')
        );

        return redirect()->route('admin.products')
            ->with('success', 'Product added successfully');
    }

    /**
     * Dispatch the price change notification job.
     *
     * @param Product $product
     * @param float $oldPrice
     * @return void
     */
    private function dispatchPriceChangeNotification(Product $product, float $oldPrice): void
    {
        try {
            SendPriceChangeNotification::dispatch(
                $product,
                $oldPrice,
                $product->price,
                config('app.price_notification_email', 'admin@example.com')
            );
        } catch (\Exception $e) {
            Log::error('Failed to dispatch price change notification: ' . $e->getMessage());
        }
    }
}
