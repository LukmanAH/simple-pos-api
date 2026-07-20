<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProductCategoriesRequest;
use App\Http\Requests\StoreProductCategoryRequest;
use App\Http\Requests\UpdateProductCategoryRequest;
use App\Http\Resources\PaginatedResource;
use App\Http\Resources\ProductCategoryResource;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class ProductCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(ProductCategoriesRequest $request)
    {
        $categories = ProductCategory::search($request['search'])
            ->latest()
            ->paginate($request['limit'] ?? 10);

        return ApiResponse::succes(
            new PaginatedResource($categories, ProductCategoryResource::class),
            'Product Categories'
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductCategoryRequest $request)
    {
        $category = ProductCategory::create($request->validated());

        return ApiResponse::succes(
            new ProductCategoryResource($category),
            'Product Category Created Successfully',
            Response::HTTP_CREATED
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $category = ProductCategory::find($id);

        if (!$category) {
            return ApiResponse::error('Product Category Not Found', Response::HTTP_NOT_FOUND);
        }

        return ApiResponse::succes(
            new ProductCategoryResource($category),
            'Product Category'
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductCategoryRequest $request, string $id)
    {
        $category = ProductCategory::find($id);

        if (!$category) {
            return ApiResponse::error('Product Category Not Found', Response::HTTP_NOT_FOUND);
        }

        $category->update($request->validated());

        return ApiResponse::succes(
            new ProductCategoryResource($category),
            'Product Category Updated Successfully',
            Response::HTTP_OK
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $category = ProductCategory::find($id);

        if (!$category) {
            return ApiResponse::error('Product Category Not Found', Response::HTTP_NOT_FOUND);
        }

        if ($category->image) {
            Storage::disk('public')->delete($category->image);
        }

        $category->delete();

        return ApiResponse::succes(
            null,
            'Product Category Deleted Successfully',
            Response::HTTP_OK
        );
    }
}
