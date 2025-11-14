<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UnitKerjaController;
use App\Http\Controllers\Api\ProgramStudiController;
use App\Http\Controllers\Api\JabatanController;
use App\Http\Controllers\Api\TahunAkademikController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\IKUController;
use App\Http\Controllers\Api\IKUTargetController;
use App\Http\Controllers\Api\IKUProgressController;
use App\Http\Controllers\Api\PeriodeAkreditasiController;
use App\Http\Controllers\Api\ButirAkreditasiController;
use App\Http\Controllers\Api\PengisianButirController;
use App\Http\Controllers\Api\DokumenAkreditasiController;
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

    // User Management Routes
    Route::apiResource('users', UserController::class);
    Route::post('users/{id}/assign-roles', [UserController::class, 'assignRoles']);
    Route::post('users/{id}/assign-permissions', [UserController::class, 'assignPermissions']);

    // IKU Management Routes
    Route::get('iku/statistics', [IKUController::class, 'statistics']);
    Route::get('iku/categories', [IKUController::class, 'categories']);
    Route::post('iku/{id}/toggle-active', [IKUController::class, 'toggleActive']);
    Route::apiResource('iku', IKUController::class);

    // IKU Target Routes
    Route::get('iku-targets/dashboard-statistics', [IKUTargetController::class, 'dashboardStatistics']);
    Route::get('iku-targets/need-attention', [IKUTargetController::class, 'needAttention']);
    Route::get('iku-targets/by-status', [IKUTargetController::class, 'byStatus']);
    Route::get('iku-targets/{id}/statistics', [IKUTargetController::class, 'statistics']);
    Route::get('iku-targets/{id}/check-risk', [IKUTargetController::class, 'checkRisk']);
    Route::apiResource('iku-targets', IKUTargetController::class);

    // IKU Progress Routes
    Route::get('iku-progress/statistics', [IKUProgressController::class, 'statistics']);
    Route::get('iku-progress/recent', [IKUProgressController::class, 'recent']);
    Route::get('iku-progress/{id}/download', [IKUProgressController::class, 'downloadDocument']);
    Route::get('iku-progress/target/{targetId}/summary', [IKUProgressController::class, 'summaryByTarget']);
    Route::get('iku-progress/target/{targetId}/trend', [IKUProgressController::class, 'trend']);
    Route::apiResource('iku-progress', IKUProgressController::class);

    // Akreditasi Module Routes

    // Periode Akreditasi Routes
    Route::get('periode-akreditasi/{id}/statistics', [PeriodeAkreditasiController::class, 'statistics']);
    Route::apiResource('periode-akreditasi', PeriodeAkreditasiController::class);

    // Butir Akreditasi Routes
    Route::get('butir-akreditasi/by-kategori', [ButirAkreditasiController::class, 'byKategori']);
    Route::get('butir-akreditasi/instrumen', [ButirAkreditasiController::class, 'instrumen']);
    Route::get('butir-akreditasi/kategori', [ButirAkreditasiController::class, 'kategori']);
    Route::apiResource('butir-akreditasi', ButirAkreditasiController::class);

    // Pengisian Butir Routes
    Route::post('pengisian-butir/{id}/submit', [PengisianButirController::class, 'submit']);
    Route::post('pengisian-butir/{id}/approve', [PengisianButirController::class, 'approve']);
    Route::post('pengisian-butir/{id}/revision', [PengisianButirController::class, 'revision']);
    Route::get('pengisian-butir/periode/{periodeId}/summary', [PengisianButirController::class, 'summary']);
    Route::apiResource('pengisian-butir', PengisianButirController::class);

    // Dokumen Akreditasi Routes
    Route::get('dokumen-akreditasi/{id}/download', [DokumenAkreditasiController::class, 'download']);
    Route::post('dokumen-akreditasi/{id}/attach-butir', [DokumenAkreditasiController::class, 'attachToButir']);
    Route::apiResource('dokumen-akreditasi', DokumenAkreditasiController::class);
});
