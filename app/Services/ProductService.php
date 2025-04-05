<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ProductService
{
    /**
     * Create a new product.
     *
     * @param array $data
     * @param UploadedFile|null $image
     * @return Product
     */
    public function createProduct(array $data, ?UploadedFile $image = null): Product
    {
        $product = Product::create($data);

        if ($image) {
            $product->image = $this->storeImage($image);
            $product->save();
        } else {
            $product->image = 'product-placeholder.jpg';
            $product->save();
        }

        return $product;
    }

    /**
     * Update an existing product.
     *
     * @param Product $product
     * @param array $data
     * @param UploadedFile|null $image
     * @return Product
     */
    public function updateProduct(Product $product, array $data, ?UploadedFile $image = null): Product
    {
        $oldPrice = $product->price;
        $product->update($data);

        if ($image) {
            $product->image = $this->storeImage($image);
            $product->save();
        }

        return $product;
    }

    /**
     * Store the uploaded image.
     *
     * @param UploadedFile $image
     * @return string
     */
    private function storeImage(UploadedFile $image): string
    {
        $filename = time() . '_' . $image->getClientOriginalName();
        $path = $image->storeAs('uploads', $filename, 'public');
        return 'storage/' . $path;
    }
} 