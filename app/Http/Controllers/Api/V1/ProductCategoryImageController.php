<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\UploadProductCategoryImageRequest;
use App\Http\Resources\ProductCategoryResource;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class ProductCategoryImageController extends Controller
{
    public function store(UploadProductCategoryImageRequest $request, string $productCategoryId)
    {
        $productCategory = ProductCategory::find($productCategoryId);

        if (!$productCategory) {
            return ApiResponse::error('Product Category Not Found', Response::HTTP_NOT_FOUND);
        }

        if ($productCategory->image) {
            Storage::delete($productCategory->image);
        }

        $path = $request->file('image')->store('product_categories', 'public');
        $productCategory->update([
            'image' => $path,
        ]);

        return ApiResponse::succes(
            new ProductCategoryResource($productCategory),
            'Product Category Image Uploaded Successfully',
            Response::HTTP_OK
        );
    }
}
