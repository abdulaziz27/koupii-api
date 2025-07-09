<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

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
 *     url="http://localhost:8000",
 *     description="Local server"
 * )
 */
class SwaggerTestController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/test",
     *     summary="Test endpoint",
     *     tags={"Test"},
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="API is working!")
     *         )
     *     )
     * )
     */
    public function test(Request $request)
    {
        return response()->json(['message' => 'API is working!']);
    }
} 