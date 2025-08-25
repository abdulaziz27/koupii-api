<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

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
