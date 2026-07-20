<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\CustomersRequest;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Http\Resources\CustomerResource;
use App\Http\Resources\PaginatedResource;
use App\Models\Customer;
use Illuminate\Http\Response;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(CustomersRequest $request)
    {
        $customers = Customer::search($request['search'])
            ->latest()
            ->paginate($request['limit'] ?? 10);

        return ApiResponse::succes(
            new PaginatedResource($customers, CustomerResource::class),
            'Customers'
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCustomerRequest $request)
    {
        $customer = Customer::create($request->validated());

        return ApiResponse::succes(
            new CustomerResource($customer),
            'Customer Created Successfully',
            Response::HTTP_CREATED
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $customer = Customer::find($id);

        if (!$customer) {
            return ApiResponse::error('Customer Not Found', Response::HTTP_NOT_FOUND);
        }

        return ApiResponse::succes(
            new CustomerResource($customer),
            'Customer'
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCustomerRequest $request, string $id)
    {
        $customer = Customer::find($id);

        if (!$customer) {
            return ApiResponse::error('Customer Not Found', Response::HTTP_NOT_FOUND);
        }

        $customer->update($request->validated());

        return ApiResponse::succes(
            new CustomerResource($customer),
            'Customer Updated Successfully',
            Response::HTTP_OK
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $customer = Customer::find($id);

        if (!$customer) {
            return ApiResponse::error('Customer Not Found', Response::HTTP_NOT_FOUND);
        }

        $customer->delete();

        return ApiResponse::succes(
            null,
            'Customer Deleted Successfully',
            Response::HTTP_OK
        );
    }
}
