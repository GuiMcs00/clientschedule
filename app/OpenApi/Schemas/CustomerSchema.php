<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

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
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
    ]
)]
final class CustomerSchema
{
}
