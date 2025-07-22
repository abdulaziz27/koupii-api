<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VocabularyCategoryController;
use App\Http\Controllers\VocabularyController;
use App\Http\Controllers\ClassController;
use App\Http\Controllers\ClassEnrollmentController;
use App\Http\Controllers\ClassInvitationController;

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

Route::prefix('profile')->middleware('auth:sanctum')->group(function () {
    Route::patch('/update', [UserController::class, 'update']);
    Route::delete('/destroy', [UserController::class, 'destroy']);
});

Route::middleware(['auth:sanctum', 'role:admin,teacher'])
    ->prefix('vocab')
    ->group(function () {
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

Route::middleware('auth:sanctum')
    ->prefix('classes')
    ->group(function () {
        Route::get('/', [ClassController::class, 'index']);
        Route::get('/{id}', [ClassController::class, 'show']);
        Route::middleware(['role:admin,teacher'])->group(function () {
            Route::post('/create', [ClassController::class, 'store']);
            Route::patch('/update/{id}', [ClassController::class, 'update']);
            Route::delete('/delete/{id}', [ClassController::class, 'destroy']);
        });
    });

Route::middleware('auth:sanctum')
    ->prefix('enrollments')
    ->group(function () {
        Route::get('/', [ClassEnrollmentController::class, 'index']);
        Route::get('/{id}', [ClassEnrollmentController::class, 'show']);
        Route::post('/create', [ClassEnrollmentController::class, 'store'])->middleware(['role:student']);
        Route::middleware(['role:admin,teacher'])->group(function () {
            Route::patch('/update/{id}', [ClassEnrollmentController::class, 'update']);
            Route::delete('/delete/{id}', [ClassEnrollmentController::class, 'destroy']);
        });
    });

Route::middleware(['auth:sanctum'])
    ->prefix('invitations')
    ->group(function () {
        Route::get('/', [ClassInvitationController::class, 'index']);
        Route::patch('/update/{id}', [ClassInvitationController::class, 'update'])->middleware('role:student');
        Route::middleware(['role:admin,teacher'])->group(function () {
            Route::post('/create', [ClassInvitationController::class, 'store'])->middleware('role:admin,teacher');
            Route::delete('/delete/{id}', [ClassInvitationController::class, 'destroy'])->middleware('role:admin,teacher');
        });
    });
