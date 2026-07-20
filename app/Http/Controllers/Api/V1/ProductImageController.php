<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\UploadProductImageRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class ProductImageController extends Controller
{
    public function store(UploadProductImageRequest $request, string $productId)
    {
        $product = Product::find($productId);

        if (!$product) {
            return ApiResponse::error('Product Not Found', Response::HTTP_NOT_FOUND);
        }

        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $path = $request->file('image')->store('products', 'public');
        $product->update([
            'image' => $path,
        ]);

        $product->load('category');

        return ApiResponse::succes(
            new ProductResource($product),
            'Product Image Uploaded Successfully',
            Response::HTTP_OK
        );
    }
}
