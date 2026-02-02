<?php

namespace App\OpenApi\Endpoints;

use OpenApi\Attributes as OA;

final class CustomerAppointmentEndpoints
{
    #[OA\Get(
        path: '/api/v1/customers/{customer}/appointments',
        summary: 'List all appointments for a customer',
        description: 'Returns a paginated list of appointments for a specific customer with optional filtering by date range, status, and series inclusion.',
        tags: ['Customer Appointments'],
        parameters: [
            new OA\Parameter(
                name: 'customer',
                in: 'path',
                required: true,
                description: 'Customer ID',
                schema: new OA\Schema(type: 'integer'),
                example: 1
            ),
            new OA\Parameter(
                name: 'from',
                in: 'query',
                required: false,
                description: 'Filter appointments starting from this date/time',
                schema: new OA\Schema(type: 'string', format: 'date-time'),
                example: '2026-02-01T09:00:00Z'
            ),
            new OA\Parameter(
                name: 'to',
                in: 'query',
                required: false,
                description: 'Filter appointments up to this date/time',
                schema: new OA\Schema(type: 'string', format: 'date-time'),
                example: '2026-02-28T18:00:00Z'
            ),
            new OA\Parameter(
                name: 'status',
                in: 'query',
                required: false,
                description: 'Filter by appointment status',
                schema: new OA\Schema(type: 'string', enum: ['active', 'trashed', 'all'], default: 'active'),
                example: 'active'
            ),
            new OA\Parameter(
                name: 'include_series',
                in: 'query',
                required: false,
                description: 'Include series data with weekday slots',
                schema: new OA\Schema(type: 'boolean', default: false),
                example: false
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
                description: 'Number of items per page (max 100)',
                schema: new OA\Schema(type: 'integer', default: 15, maximum: 100),
                example: 15
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful operation',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/Appointment')
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Customer not found'
            )
        ]
    )]
    public function index()
    {
    }

    #[OA\Post(
        path: '/api/v1/customers/{customer}/appointments',
        summary: 'Create a new appointment',
        description: 'Creates a new appointment for a customer. Returns 409 if the time slot conflicts with an existing appointment.',
        tags: ['Customer Appointments'],
        parameters: [
            new OA\Parameter(
                name: 'customer',
                in: 'path',
                required: true,
                description: 'Customer ID',
                schema: new OA\Schema(type: 'integer'),
                example: 1
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['title', 'starts_at', 'ends_at'],
                properties: [
                    new OA\Property(property: 'title', type: 'string', maxLength: 150, example: 'Team Meeting'),
                    new OA\Property(property: 'notes', type: 'string', nullable: true, example: 'Quarterly review discussion'),
                    new OA\Property(property: 'starts_at', type: 'string', format: 'date-time', example: '2026-02-15T10:00:00Z'),
                    new OA\Property(property: 'ends_at', type: 'string', format: 'date-time', example: '2026-02-15T11:00:00Z'),
                    new OA\Property(property: 'series_id', type: 'integer', nullable: true, example: null)
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Appointment created successfully',
                content: new OA\JsonContent(ref: '#/components/schemas/Appointment')
            ),
            new OA\Response(
                response: 404,
                description: 'Customer not found'
            ),
            new OA\Response(
                response: 409,
                description: 'Time conflict - overlapping appointment exists',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Conflito de horário: já existe uma marcação nesse intervalo.')
                    ]
                )
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
        path: '/api/v1/customers/{customer}/appointments/{appointment}',
        summary: 'Get appointment details',
        description: 'Returns details of a specific appointment for a customer.',
        tags: ['Customer Appointments'],
        parameters: [
            new OA\Parameter(
                name: 'customer',
                in: 'path',
                required: true,
                description: 'Customer ID',
                schema: new OA\Schema(type: 'integer'),
                example: 1
            ),
            new OA\Parameter(
                name: 'appointment',
                in: 'path',
                required: true,
                description: 'Appointment ID',
                schema: new OA\Schema(type: 'integer'),
                example: 1
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful operation',
                content: new OA\JsonContent(ref: '#/components/schemas/Appointment')
            ),
            new OA\Response(
                response: 404,
                description: 'Customer or appointment not found'
            )
        ]
    )]
    public function show()
    {
    }

    #[OA\Patch(
        path: '/api/v1/customers/{customer}/appointments/{appointment}',
        summary: 'Update an appointment',
        description: 'Updates an existing appointment. Returns 409 if the updated time conflicts with another appointment. All fields are optional for partial updates.',
        tags: ['Customer Appointments'],
        parameters: [
            new OA\Parameter(
                name: 'customer',
                in: 'path',
                required: true,
                description: 'Customer ID',
                schema: new OA\Schema(type: 'integer'),
                example: 1
            ),
            new OA\Parameter(
                name: 'appointment',
                in: 'path',
                required: true,
                description: 'Appointment ID',
                schema: new OA\Schema(type: 'integer'),
                example: 1
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'title', type: 'string', maxLength: 150, example: 'Updated Meeting Title'),
                    new OA\Property(property: 'notes', type: 'string', nullable: true, example: 'Updated notes'),
                    new OA\Property(property: 'starts_at', type: 'string', format: 'date-time', example: '2026-02-15T14:00:00Z'),
                    new OA\Property(property: 'ends_at', type: 'string', format: 'date-time', example: '2026-02-15T15:00:00Z')
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Appointment updated successfully',
                content: new OA\JsonContent(ref: '#/components/schemas/Appointment')
            ),
            new OA\Response(
                response: 404,
                description: 'Customer or appointment not found'
            ),
            new OA\Response(
                response: 409,
                description: 'Time conflict - overlapping appointment exists',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Conflito de horário: já existe uma marcação nesse intervalo.')
                    ]
                )
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
        path: '/api/v1/customers/{customer}/appointments/{appointment}',
        summary: 'Soft delete an appointment',
        description: 'Performs a soft delete on the appointment (sets deleted_at timestamp).',
        tags: ['Customer Appointments'],
        parameters: [
            new OA\Parameter(
                name: 'customer',
                in: 'path',
                required: true,
                description: 'Customer ID',
                schema: new OA\Schema(type: 'integer'),
                example: 1
            ),
            new OA\Parameter(
                name: 'appointment',
                in: 'path',
                required: true,
                description: 'Appointment ID',
                schema: new OA\Schema(type: 'integer'),
                example: 1
            )
        ],
        responses: [
            new OA\Response(
                response: 204,
                description: 'Appointment soft deleted successfully'
            ),
            new OA\Response(
                response: 404,
                description: 'Customer or appointment not found'
            )
        ]
    )]
    public function destroy()
    {
    }
}
