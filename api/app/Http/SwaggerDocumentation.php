<?php

namespace App\Http;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    description: 'API documentation for Sentiment Analyzer',
    title: 'Sentiment Analyzer API',
    contact: new OA\Contact(
        name: 'Денис Зямаев',
        email: 'code4un@yandex.ru'
    )
)]
#[OA\Server(
    url: 'http://localhost:8000',
    description: 'Localhost'
)]
#[OA\SecurityScheme(
    securityScheme: 'sanctum',
    type: 'http',
    description: 'Enter your token: Bearer {token} (for mobile clients). For SPA (Nuxt.js) auth is via HttpOnly cookies automatically.',
    bearerFormat: 'JWT',
    scheme: 'bearer'
)]
#[OA\Tag(
    name: 'Authentication',
    description: 'Authentication routes'
)]
final readonly class SwaggerDocumentation
{
}
