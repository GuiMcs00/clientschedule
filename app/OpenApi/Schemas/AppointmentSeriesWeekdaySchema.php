<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'AppointmentSeriesWeekday',
    required: ['id', 'series_id', 'weekday', 'start_time', 'end_time'],
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'series_id', type: 'integer', example: 1),
        new OA\Property(
            property: 'weekday',
            type: 'integer',
            minimum: 0,
            maximum: 6,
            description: '0=Sunday, 1=Monday, 2=Tuesday, 3=Wednesday, 4=Thursday, 5=Friday, 6=Saturday',
            example: 1
        ),
        new OA\Property(property: 'start_time', type: 'string', format: 'time', example: '09:00'),
        new OA\Property(property: 'end_time', type: 'string', format: 'time', example: '10:00'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2026-02-01T10:00:00Z'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', example: '2026-02-01T10:00:00Z')
    ]
)]
final class AppointmentSeriesWeekdaySchema
{
}
