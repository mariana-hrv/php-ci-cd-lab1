<?php

declare(strict_types=1);

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'Real Estate Sales API',
    description: 'REST API системи продажу нерухомості. Лабораторна робота №8.'
)]
#[OA\Server(
    url: 'http://127.0.0.1:8000/api/v1',
    description: 'Локальний сервер Laravel'
)]
#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    scheme: 'bearer',
    description: 'Токен автентифікації Laravel Sanctum'
)]
class ApiDocumentation {}
