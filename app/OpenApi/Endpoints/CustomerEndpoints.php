<?php

namespace App\OpenApi\Endpoints;

use OpenApi\Attributes as OA;

final class CustomerEndpoints
{
    #[OA\Get(
        path: '/api/v1/customers',
        summary: 'List all active customers',
        description: 'Returns a paginated list of active customers with optional name search filter',
        tags: ['Customers'],
        parameters: [
            new OA\Parameter(
                name: 'search',
                in: 'query',
                required: false,
                description: 'Filter customers by name (case-insensitive partial match)',
                schema: new OA\Schema(type: 'string'),
                example: 'Ana'
            ),
            new OA\Parameter(
                name: 'page',
                in: 'query',
                required: false,
                description: 'Page number for pagination',
                schema: new OA\Schema(type: 'integer', default: 1),
                example: 1
            ),
            new OA\Parameter(
                name: 'per_page',
                in: 'query',
                required: false,
                description: 'Number of items per page',
                schema: new OA\Schema(type: 'integer', default: 15),
                example: 15
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful operation',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/Customer')
                        ),
                        new OA\Property(
                            property: 'meta',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'total', type: 'integer', example: 100),
                                new OA\Property(property: 'count', type: 'integer', example: 15),
                                new OA\Property(property: 'per_page', type: 'integer', example: 15),
                                new OA\Property(property: 'current_page', type: 'integer', example: 1),
                                new OA\Property(property: 'total_pages', type: 'integer', example: 7)
                            ]
                        )
                    ]
                )
            )
        ]
    )]
    public function index()
    {
    }

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
    public function store()
    {
    }

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
    public function show()
    {
    }

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
    public function update()
    {
    }

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
    public function destroy()
    {
    }

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
    public function restore()
    {
    }

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
    public function forceDelete()
    {
    }
}
