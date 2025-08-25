<?php

namespace App\Swagger;

use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="Koupii LMS API",
 *     description="API documentation for English course LMS",
 *     @OA\Contact(
 *         email="support@koupii.com"
 *     )
 * )
 *
 * @OA\Server(
 *     url="https://api-koupii.magercoding.com",
 *     description="Production server"
 * )
 */
class OpenApiSpec
{
    // This class is intentionally empty. It only holds OpenAPI annotations.
}


