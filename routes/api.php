<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UnitKerjaController;
use App\Http\Controllers\Api\ProgramStudiController;
use App\Http\Controllers\Api\JabatanController;
use App\Http\Controllers\Api\TahunAkademikController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Master Data Routes
    Route::apiResource('unit-kerja', UnitKerjaController::class);
    Route::apiResource('program-studi', ProgramStudiController::class);
    Route::apiResource('jabatan', JabatanController::class);
    Route::apiResource('tahun-akademik', TahunAkademikController::class);
});
