<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Http\Resources\CustomerResource;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class CustomerController extends Controller
{
    /**
     * List all active customers.
     *
     * @param Request $request The HTTP request
     * @return JsonResponse Collection of active customers
     */
    public function index(Request $request): JsonResponse
    {
        $customers = Customer::query()->whereNull('deleted_at')->get();

        return response()->json(CustomerResource::collection($customers));
    }

    /**
     * Create a new customer.
     *
     * @param StoreCustomerRequest $request The validated request
     * @return JsonResponse The created customer
     */
    public function store(StoreCustomerRequest $request): JsonResponse
    {
        $customer = Customer::create($request->validated());

        return response()->json(new CustomerResource($customer), 201);
    }

    /**
     * Get a specific customer.
     *
     * @param Customer $customer The customer model instance
     * @return JsonResponse The customer details
     */
    public function show(Customer $customer): JsonResponse
    {
        return response()->json(new CustomerResource($customer));
    }

    /**
     * Update a customer.
     *
     * @param UpdateCustomerRequest $request The validated request
     * @param Customer $customer The customer model instance
     * @return JsonResponse The updated customer
     */
    public function update(UpdateCustomerRequest $request, Customer $customer): JsonResponse
    {
        $customer->update($request->validated());

        return response()->json(new CustomerResource($customer));
    }

    /**
     * Soft delete a customer.
     *
     * @param Customer $customer The customer model instance
     * @return JsonResponse Empty response with 204 status
     */
    public function destroy(Customer $customer): JsonResponse
    {
        $customer->delete();

        return response()->json(null, 204);
    }

    /**
     * Restore a soft deleted customer.
     *
     * @param int $id The customer ID
     * @return JsonResponse The restored customer
     */
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

    /**
     * Permanently delete a customer.
     *
     * @param int $id The customer ID
     * @return JsonResponse Empty response with 204 status
     */
    public function forceDelete(int $id): JsonResponse
    {
        $customer = Customer::withTrashed()->findOrFail($id);
        $customer->forceDelete();

        return response()->json(null, 204);
    }
}
