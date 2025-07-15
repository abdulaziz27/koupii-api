<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\VocabularyCategoryController;
use App\Http\Controllers\VocabularyController;

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
Route::get('/test', [\App\Http\Controllers\SwaggerTestController::class, 'test']);

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
    });
});

Route::middleware(['auth:sanctum', 'role:admin,teacher'])->prefix('vocab')->group(function () {
    Route::prefix('categories')->group(function () {
        Route::get('/', [VocabularyCategoryController::class, 'index']);
        Route::get('/{id}', [VocabularyCategoryController::class, 'show']);
        Route::post('/create', [VocabularyCategoryController::class, 'store']);
        Route::patch('/update/{id}', [VocabularyCategoryController::class, 'update']);
        Route::delete('/delete/{id}', [VocabularyCategoryController::class, 'destroy']);
    });

    Route::get('/vocabularies', [VocabularyController::class, 'index']);
    Route::get('/{id}', [VocabularyController::class, 'show']);
    Route::post('/create', [VocabularyController::class, 'store']);
    Route::patch('/update/{id}', [VocabularyController::class, 'update']);
    Route::delete('/delete/{id}', [VocabularyController::class, 'destroy']);
});
