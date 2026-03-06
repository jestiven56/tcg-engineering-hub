<?php

use App\Http\Controllers\Api\V1\ArtifactController;
use App\Http\Controllers\Api\V1\AuditEventController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ModuleController;
use App\Http\Controllers\Api\V1\ProjectController;
use Illuminate\Support\Facades\Route;

// Auth
Route::prefix('v1')->group(function () {

    Route::post('login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {

        // Auth
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);

        // Projects
        Route::get('projects', [ProjectController::class, 'index']);
        Route::post('projects', [ProjectController::class, 'store']);
        Route::get('projects/{project}', [ProjectController::class, 'show']);
        Route::put('projects/{project}', [ProjectController::class, 'update']);
        Route::patch('projects/{project}/status', [ProjectController::class, 'updateStatus']);
        Route::delete('projects/{project}', [ProjectController::class, 'destroy']);

        // Artifacts
        Route::get('projects/{project}/artifacts', [ArtifactController::class, 'index']);
        Route::get('projects/{project}/artifacts/{artifact}', [ArtifactController::class, 'show']);
        Route::put('projects/{project}/artifacts/{artifact}', [ArtifactController::class, 'update']);
        Route::patch('projects/{project}/artifacts/{artifact}/status', [ArtifactController::class, 'updateStatus']);

        // Modules
        Route::get('projects/{project}/modules', [ModuleController::class, 'index']);
        Route::post('projects/{project}/modules', [ModuleController::class, 'store']);
        Route::get('projects/{project}/modules/{module}', [ModuleController::class, 'show']);
        Route::put('projects/{project}/modules/{module}', [ModuleController::class, 'update']);
        Route::delete('projects/{project}/modules/{module}', [ModuleController::class, 'destroy']);
        Route::patch('projects/{project}/modules/{module}/validate', [ModuleController::class, 'validates']);

        // Audit
        Route::get('projects/{project}/audit', [AuditEventController::class, 'index']);

        // Users (solo para testing, eliminar en producción)
        Route::get('users', function () {
            return \App\Http\Responses\ApiResponse::success(\App\Models\User::select('id', 'name', 'role')->get());
        });

    });
});