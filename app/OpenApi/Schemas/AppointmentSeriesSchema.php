<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'AppointmentSeries',
    required: ['id', 'customer_id', 'title', 'starts_on'],
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'customer_id', type: 'integer', example: 1),
        new OA\Property(property: 'title', type: 'string', maxLength: 150, example: 'Weekly Therapy Sessions'),
        new OA\Property(property: 'notes', type: 'string', nullable: true, example: 'Recurring appointment series'),
        new OA\Property(property: 'timezone', type: 'string', maxLength: 64, example: 'America/Sao_Paulo'),
        new OA\Property(property: 'starts_on', type: 'string', format: 'date', example: '2026-02-01'),
        new OA\Property(property: 'ends_on', type: 'string', format: 'date', nullable: true, example: '2026-12-31'),
        new OA\Property(property: 'is_active', type: 'boolean', example: true),
        new OA\Property(property: 'deleted_at', type: 'string', format: 'date-time', nullable: true, example: null),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2026-02-01T10:00:00Z'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', example: '2026-02-01T10:00:00Z'),
        new OA\Property(
            property: 'weekdays',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/AppointmentSeriesWeekday'),
            description: 'Weekday time slots for this series'
        )
    ]
)]
final class AppointmentSeriesSchema
{
}
