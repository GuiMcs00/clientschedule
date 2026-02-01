<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Http\Resources\CustomerResource;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $customers = Customer::query()->whereNull('deleted_at')->get();

        return response()->json(CustomerResource::collection($customers));
    }

    public function store(StoreCustomerRequest $request): JsonResponse
    {
        $customer = Customer::create($request->validated());

        return response()->json(new CustomerResource($customer), 201);
    }

    public function show(Customer $customer): JsonResponse
    {
        return response()->json(new CustomerResource($customer));
    }

    public function update(UpdateCustomerRequest $request, Customer $customer): JsonResponse
    {
        $customer->update($request->validated());

        return response()->json(new CustomerResource($customer));
    }

    public function destroy(Customer $customer): JsonResponse
    {
        $customer->delete();

        return response()->json(null, 204);
    }

    public function restore(int $id): JsonResponse
    {
        $customer = Customer::withTrashed()->findOrFail($id);

        if (!$customer->trashed()) {
            return response()->json([
                'message' => 'Cliente ja ativo.',
                'customer' => new CustomerResource($customer),
            ], 200);
        }
        $customer->restore();

        return response()->json(new CustomerResource($customer));
    }

    public function forceDelete(int $id): JsonResponse
    {
        $customer = Customer::withTrashed()->findOrFail($id);
        $customer->forceDelete();

        return response()->json(null, 204);
    }
}