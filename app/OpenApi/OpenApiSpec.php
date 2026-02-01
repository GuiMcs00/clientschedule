<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Info(title: 'ClientSchedule API', version: '1.0.0')]
#[OA\Server(url: 'http://127.0.0.1:8000')]
final class OpenApiSpec
{
}
