<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Appointment',
    required: ['id', 'customer_id', 'title', 'starts_at', 'ends_at'],
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'customer_id', type: 'integer', example: 1),
        new OA\Property(property: 'series_id', type: 'integer', nullable: true, example: null),
        new OA\Property(property: 'title', type: 'string', maxLength: 150, example: 'Team Meeting'),
        new OA\Property(property: 'notes', type: 'string', nullable: true, example: 'Quarterly review discussion'),
        new OA\Property(property: 'starts_at', type: 'string', format: 'date-time', example: '2026-02-15T10:00:00Z'),
        new OA\Property(property: 'ends_at', type: 'string', format: 'date-time', example: '2026-02-15T11:00:00Z'),
        new OA\Property(property: 'deleted_at', type: 'string', format: 'date-time', nullable: true, example: null),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2026-02-01T10:00:00Z'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', example: '2026-02-01T10:00:00Z'),
        new OA\Property(
            property: 'series',
            ref: '#/components/schemas/AppointmentSeries',
            nullable: true,
            description: 'Included only when requested via include_series parameter'
        )
    ]
)]
final class AppointmentSchema
{
}
