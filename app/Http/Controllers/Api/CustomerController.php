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

#[OA\Info(title: 'ClientSchedule API', version: '1.0.0')]
#[OA\Server(url: 'http://127.0.0.1:8000')]
class CustomerController extends Controller
{
    /**
     * List all active customers.
     *
     * @param Request $request The HTTP request
     * @return JsonResponse Collection of active customers
     */
    #[OA\Get(
        path: '/api/v1/customers',
        summary: 'List all active customers',
        tags: ['Customers'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful operation',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/Customer')
                )
            )
        ]
    )]
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
    #[OA\Post(
        path: '/api/v1/customers',
        summary: 'Create a new customer',
        description: 'Creates a new customer. If a soft deleted customer with the same email exists, it will be reactivated and updated.',
        tags: ['Customers'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'email'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', maxLength: 150, example: 'Ana Souza'),
                    new OA\Property(property: 'email', type: 'string', format: 'email', maxLength: 255, example: 'ana@email.com'),
                    new OA\Property(property: 'phone', type: 'string', maxLength: 30, nullable: true, example: '+55 61 99999-9999')
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Customer created successfully',
                content: new OA\JsonContent(ref: '#/components/schemas/Customer')
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error'
            )
        ]
    )]
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
    #[OA\Get(
        path: '/api/v1/customers/{id}',
        summary: 'Get customer details',
        tags: ['Customers'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
                example: 1
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful operation',
                content: new OA\JsonContent(ref: '#/components/schemas/Customer')
            ),
            new OA\Response(
                response: 404,
                description: 'Customer not found'
            )
        ]
    )]
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
    #[OA\Patch(
        path: '/api/v1/customers/{id}',
        summary: 'Update a customer',
        description: 'Updates a customer. All fields are optional (partial update supported).',
        tags: ['Customers'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
                example: 1
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'name', type: 'string', maxLength: 150, example: 'Ana Souza'),
                    new OA\Property(property: 'email', type: 'string', format: 'email', maxLength: 255, example: 'ana@email.com'),
                    new OA\Property(property: 'phone', type: 'string', maxLength: 30, nullable: true, example: '+55 61 99999-9999')
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Customer updated successfully',
                content: new OA\JsonContent(ref: '#/components/schemas/Customer')
            ),
            new OA\Response(
                response: 404,
                description: 'Customer not found'
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error'
            )
        ]
    )]
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
    #[OA\Delete(
        path: '/api/v1/customers/{id}',
        summary: 'Soft delete a customer',
        description: 'Performs a soft delete on the customer (sets deleted_at timestamp).',
        tags: ['Customers'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
                example: 1
            )
        ],
        responses: [
            new OA\Response(
                response: 204,
                description: 'Customer soft deleted successfully'
            ),
            new OA\Response(
                response: 404,
                description: 'Customer not found'
            )
        ]
    )]
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
    #[OA\Post(
        path: '/api/v1/customers/{id}/restore',
        summary: 'Restore a soft deleted customer',
        description: 'Reactivates a soft deleted customer by removing the deleted_at timestamp.',
        tags: ['Customers'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
                example: 1
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Customer restored successfully',
                content: new OA\JsonContent(ref: '#/components/schemas/Customer')
            ),
            new OA\Response(
                response: 404,
                description: 'Customer not found'
            )
        ]
    )]
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
    #[OA\Delete(
        path: '/api/v1/customers/{id}/force',
        summary: 'Permanently delete a customer',
        description: 'Permanently removes the customer from the database (cannot be undone).',
        tags: ['Customers'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
                example: 1
            )
        ],
        responses: [
            new OA\Response(
                response: 204,
                description: 'Customer permanently deleted successfully'
            ),
            new OA\Response(
                response: 404,
                description: 'Customer not found'
            )
        ]
    )]
    public function forceDelete(int $id): JsonResponse
    {
        $customer = Customer::withTrashed()->findOrFail($id);
        $customer->forceDelete();

        return response()->json(null, 204);
    }
}

#[OA\Schema(
    schema: 'Customer',
    required: ['id', 'name', 'email'],
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'name', type: 'string', maxLength: 150, example: 'Ana Souza'),
        new OA\Property(property: 'email', type: 'string', format: 'email', maxLength: 255, example: 'ana@email.com'),
        new OA\Property(property: 'phone', type: 'string', maxLength: 30, nullable: true, example: '+55 61 99999-9999'),
        new OA\Property(property: 'deleted_at', type: 'string', format: 'date-time', nullable: true),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time')
    ]
)]
class CustomerSchema
{
}