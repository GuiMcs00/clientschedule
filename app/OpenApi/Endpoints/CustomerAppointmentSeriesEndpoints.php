<?php

namespace App\OpenApi\Endpoints;

use OpenApi\Attributes as OA;

final class CustomerAppointmentSeriesEndpoints
{
    #[OA\Get(
        path: '/api/v1/customers/{customer}/series',
        summary: 'List all appointment series for a customer',
        description: 'Returns a list of recurring appointment series for a specific customer with optional filtering by status and weekday slot inclusion.',
        tags: ['Customer Appointment Series'],
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
                name: 'status',
                in: 'query',
                required: false,
                description: 'Filter by series status',
                schema: new OA\Schema(type: 'string', enum: ['active', 'inactive', 'all'], default: 'active'),
                example: 'active'
            ),
            new OA\Parameter(
                name: 'include_weekdays',
                in: 'query',
                required: false,
                description: 'Include weekday time slots in the response',
                schema: new OA\Schema(type: 'boolean', default: true),
                example: true
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful operation',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/AppointmentSeries')
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
        path: '/api/v1/customers/{customer}/series',
        summary: 'Create a new appointment series',
        description: 'Creates a new recurring appointment series with weekday time slots. Optionally generates individual appointment occurrences immediately. Returns 409 if generated appointments conflict with existing ones.',
        tags: ['Customer Appointment Series'],
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
                name: 'generate',
                in: 'query',
                required: false,
                description: 'Whether to generate appointment occurrences immediately',
                schema: new OA\Schema(type: 'boolean', default: false),
                example: false
            ),
            new OA\Parameter(
                name: 'weeks',
                in: 'query',
                required: false,
                description: 'Number of weeks to generate occurrences for (only used if generate=true)',
                schema: new OA\Schema(type: 'integer', default: 12, minimum: 1, maximum: 52),
                example: 12
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['title', 'starts_on', 'weekdays'],
                properties: [
                    new OA\Property(
                        property: 'title',
                        type: 'string',
                        description: 'Title for the appointment series',
                        example: 'Weekly Team Meeting'
                    ),
                    new OA\Property(
                        property: 'notes',
                        type: 'string',
                        description: 'Additional notes for the series',
                        example: 'Recurring meeting every Monday and Wednesday',
                        nullable: true
                    ),
                    new OA\Property(
                        property: 'starts_on',
                        type: 'string',
                        format: 'date',
                        description: 'Start date for the series',
                        example: '2026-02-01'
                    ),
                    new OA\Property(
                        property: 'ends_on',
                        type: 'string',
                        format: 'date',
                        description: 'End date for the series (null means no end)',
                        example: '2026-12-31',
                        nullable: true
                    ),
                    new OA\Property(
                        property: 'timezone',
                        type: 'string',
                        description: 'Timezone for the series',
                        example: 'America/Sao_Paulo',
                        nullable: true
                    ),
                    new OA\Property(
                        property: 'is_active',
                        type: 'boolean',
                        description: 'Whether the series is active',
                        example: true,
                        default: true
                    ),
                    new OA\Property(
                        property: 'weekdays',
                        type: 'array',
                        description: 'Array of weekday time slots (0=Sunday, 6=Saturday)',
                        items: new OA\Items(
                            required: ['weekday', 'start_time', 'end_time'],
                            properties: [
                                new OA\Property(
                                    property: 'weekday',
                                    type: 'integer',
                                    description: 'Day of week (0=Sunday, 1=Monday, ..., 6=Saturday)',
                                    example: 1,
                                    minimum: 0,
                                    maximum: 6
                                ),
                                new OA\Property(
                                    property: 'start_time',
                                    type: 'string',
                                    format: 'time',
                                    description: 'Start time for the slot',
                                    example: '09:00'
                                ),
                                new OA\Property(
                                    property: 'end_time',
                                    type: 'string',
                                    format: 'time',
                                    description: 'End time for the slot',
                                    example: '10:00'
                                )
                            ],
                            type: 'object'
                        ),
                        example: [
                            ['weekday' => 1, 'start_time' => '09:00', 'end_time' => '10:00'],
                            ['weekday' => 3, 'start_time' => '14:00', 'end_time' => '15:00']
                        ]
                    )
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Appointment series created successfully',
                content: new OA\JsonContent(ref: '#/components/schemas/AppointmentSeries')
            ),
            new OA\Response(
                response: 404,
                description: 'Customer not found'
            ),
            new OA\Response(
                response: 409,
                description: 'Conflict - Generated occurrences overlap with existing appointments',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Conflito de horário: a série gerou ocorrências que colidem com marcações existentes.'
                        )
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
        path: '/api/v1/customers/{customer}/series/{series}',
        summary: 'Get a specific appointment series',
        description: 'Returns details of a specific appointment series including weekday time slots.',
        tags: ['Customer Appointment Series'],
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
                name: 'series',
                in: 'path',
                required: true,
                description: 'Appointment Series ID',
                schema: new OA\Schema(type: 'integer'),
                example: 1
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful operation',
                content: new OA\JsonContent(ref: '#/components/schemas/AppointmentSeries')
            ),
            new OA\Response(
                response: 404,
                description: 'Customer or series not found, or series does not belong to customer'
            )
        ]
    )]
    public function show()
    {
    }

    #[OA\Put(
        path: '/api/v1/customers/{customer}/series/{series}',
        summary: 'Update an appointment series',
        description: 'Updates an existing appointment series. Supports partial updates. Can replace weekday slots if provided. Optionally regenerates future appointment occurrences.',
        tags: ['Customer Appointment Series'],
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
                name: 'series',
                in: 'path',
                required: true,
                description: 'Appointment Series ID',
                schema: new OA\Schema(type: 'integer'),
                example: 1
            ),
            new OA\Parameter(
                name: 'regenerate',
                in: 'query',
                required: false,
                description: 'Whether to delete future occurrences and regenerate them based on updated configuration',
                schema: new OA\Schema(type: 'boolean', default: false),
                example: false
            ),
            new OA\Parameter(
                name: 'weeks',
                in: 'query',
                required: false,
                description: 'Number of weeks to generate occurrences for (only used if regenerate=true)',
                schema: new OA\Schema(type: 'integer', default: 12, minimum: 1, maximum: 52),
                example: 12
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'title',
                        type: 'string',
                        description: 'Title for the appointment series',
                        example: 'Weekly Team Meeting',
                        nullable: true
                    ),
                    new OA\Property(
                        property: 'notes',
                        type: 'string',
                        description: 'Additional notes for the series',
                        example: 'Updated recurring meeting schedule',
                        nullable: true
                    ),
                    new OA\Property(
                        property: 'starts_on',
                        type: 'string',
                        format: 'date',
                        description: 'Start date for the series',
                        example: '2026-02-01',
                        nullable: true
                    ),
                    new OA\Property(
                        property: 'ends_on',
                        type: 'string',
                        format: 'date',
                        description: 'End date for the series (null means no end)',
                        example: '2026-12-31',
                        nullable: true
                    ),
                    new OA\Property(
                        property: 'timezone',
                        type: 'string',
                        description: 'Timezone for the series',
                        example: 'America/Sao_Paulo',
                        nullable: true
                    ),
                    new OA\Property(
                        property: 'is_active',
                        type: 'boolean',
                        description: 'Whether the series is active',
                        example: true,
                        nullable: true
                    ),
                    new OA\Property(
                        property: 'weekdays',
                        type: 'array',
                        description: 'Array of weekday time slots to replace existing slots (0=Sunday, 6=Saturday)',
                        items: new OA\Items(
                            required: ['weekday', 'start_time', 'end_time'],
                            properties: [
                                new OA\Property(
                                    property: 'weekday',
                                    type: 'integer',
                                    description: 'Day of week (0=Sunday, 1=Monday, ..., 6=Saturday)',
                                    example: 1,
                                    minimum: 0,
                                    maximum: 6
                                ),
                                new OA\Property(
                                    property: 'start_time',
                                    type: 'string',
                                    format: 'time',
                                    description: 'Start time for the slot',
                                    example: '09:00'
                                ),
                                new OA\Property(
                                    property: 'end_time',
                                    type: 'string',
                                    format: 'time',
                                    description: 'End time for the slot',
                                    example: '10:00'
                                )
                            ],
                            type: 'object'
                        ),
                        nullable: true,
                        example: [
                            ['weekday' => 1, 'start_time' => '09:00', 'end_time' => '10:00'],
                            ['weekday' => 3, 'start_time' => '14:00', 'end_time' => '15:00']
                        ]
                    )
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Appointment series updated successfully',
                content: new OA\JsonContent(ref: '#/components/schemas/AppointmentSeries')
            ),
            new OA\Response(
                response: 404,
                description: 'Customer or series not found, or series does not belong to customer'
            ),
            new OA\Response(
                response: 409,
                description: 'Conflict - Regenerated occurrences overlap with existing appointments',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Conflito de horário: a atualização gerou ocorrências que colidem com marcações existentes.'
                        )
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
        path: '/api/v1/customers/{customer}/series/{series}',
        summary: 'Deactivate an appointment series',
        description: 'Deactivates an appointment series by setting is_active=false. Optionally deletes all future appointment occurrences created from this series.',
        tags: ['Customer Appointment Series'],
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
                name: 'series',
                in: 'path',
                required: true,
                description: 'Appointment Series ID',
                schema: new OA\Schema(type: 'integer'),
                example: 1
            ),
            new OA\Parameter(
                name: 'delete_future',
                in: 'query',
                required: false,
                description: 'Whether to soft delete all future appointment occurrences created from this series',
                schema: new OA\Schema(type: 'boolean', default: false),
                example: false
            )
        ],
        responses: [
            new OA\Response(
                response: 204,
                description: 'Appointment series deactivated successfully'
            ),
            new OA\Response(
                response: 404,
                description: 'Customer or series not found, or series does not belong to customer'
            )
        ]
    )]
    public function destroy()
    {
    }
}
