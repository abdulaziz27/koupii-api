<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\VocabularyCategoryController;
use App\Http\Controllers\VocabularyController;
use App\Http\Controllers\ClassController;
use App\Http\Controllers\ClassEnrollmentController;
use App\Http\Controllers\ClassInvitationController;

Route::get('/health', fn() => response()->json(['ok' => true, 'time' => time()]));

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
    });
});

Route::prefix('profile')->middleware('auth:sanctum')->group(function () {
    Route::get('/{id}', [UserController::class, 'show']);
    Route::patch('/update', [UserController::class, 'update']);
    Route::delete('/destroy', [UserController::class, 'destroy']);
});

Route::prefix('password')->middleware('auth:sanctum')->group(function () {
    Route::patch('/change-password', [PasswordController::class, 'changePassword']);
});

Route::middleware('auth:sanctum')
    ->prefix('vocab')
    ->group(function () {
        Route::middleware('role:admin,teacher')->prefix('categories')->group(function () {
            Route::get('/', [VocabularyCategoryController::class, 'index']);
            Route::get('/{id}', [VocabularyCategoryController::class, 'show']);
            Route::post('/create', [VocabularyCategoryController::class, 'store']);
            Route::patch('/update/{id}', [VocabularyCategoryController::class, 'update']);
            Route::delete('/delete/{id}', [VocabularyCategoryController::class, 'destroy']);
        });

        Route::middleware('role:admin,teacher,student')->group(function () {
            Route::get('/vocabularies', [VocabularyController::class, 'index']);
            Route::post('/{id}/bookmark', [VocabularyController::class, 'toggleBookmark']);
        });

        Route::middleware('role:admin,teacher')->group(function () {
            Route::get('/{id}', [VocabularyController::class, 'show']);
            Route::post('/create', [VocabularyController::class, 'store']);
            Route::patch('/update/{id}', [VocabularyController::class, 'update']);
            Route::delete('/delete/{id}', [VocabularyController::class, 'destroy']);
        });
    });

Route::middleware('auth:sanctum')
    ->prefix('classes')
    ->group(function () {
        Route::get('/', [ClassController::class, 'index']);
        Route::get('/{id}', [ClassController::class, 'show']);
        Route::get('/{id}/students', [ClassController::class, 'students']);
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
