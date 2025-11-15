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
use App\Http\Controllers\Api\ButirCommentController;
use App\Http\Controllers\Api\DokumenAkreditasiController;
use App\Http\Controllers\Api\NotificationController;
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

    // Unit Kerja Routes
    Route::get('unit-kerja/active', [UnitKerjaController::class, 'active']);
    Route::get('unit-kerja/by-jenis', [UnitKerjaController::class, 'byJenis']);
    Route::get('unit-kerja/roots', [UnitKerjaController::class, 'roots']);
    Route::get('unit-kerja/children/{parentId}', [UnitKerjaController::class, 'children']);
    Route::get('unit-kerja/statistics', [UnitKerjaController::class, 'statistics']);
    Route::post('unit-kerja/{id}/toggle-active', [UnitKerjaController::class, 'toggleActive']);
    Route::apiResource('unit-kerja', UnitKerjaController::class);

    // Program Studi Routes
    Route::get('program-studi/active', [ProgramStudiController::class, 'active']);
    Route::get('program-studi/by-jenjang', [ProgramStudiController::class, 'byJenjang']);
    Route::get('program-studi/by-unit-kerja', [ProgramStudiController::class, 'byUnitKerja']);
    Route::get('program-studi/by-akreditasi', [ProgramStudiController::class, 'byAkreditasi']);
    Route::get('program-studi/statistics', [ProgramStudiController::class, 'statistics']);
    Route::post('program-studi/{id}/toggle-active', [ProgramStudiController::class, 'toggleActive']);
    Route::apiResource('program-studi', ProgramStudiController::class);

    // Jabatan Routes
    Route::get('jabatan/active', [JabatanController::class, 'active']);
    Route::get('jabatan/by-kategori', [JabatanController::class, 'byKategori']);
    Route::get('jabatan/by-level', [JabatanController::class, 'byLevel']);
    Route::get('jabatan/categories', [JabatanController::class, 'categories']);
    Route::get('jabatan/statistics', [JabatanController::class, 'statistics']);
    Route::post('jabatan/{id}/toggle-active', [JabatanController::class, 'toggleActive']);
    Route::apiResource('jabatan', JabatanController::class);

    // Tahun Akademik Routes
    Route::get('tahun-akademik/active', [TahunAkademikController::class, 'active']);
    Route::get('tahun-akademik/current', [TahunAkademikController::class, 'current']);
    Route::get('tahun-akademik/upcoming', [TahunAkademikController::class, 'upcoming']);
    Route::get('tahun-akademik/by-semester', [TahunAkademikController::class, 'bySemester']);
    Route::get('tahun-akademik/statistics', [TahunAkademikController::class, 'statistics']);
    Route::post('tahun-akademik/{id}/toggle-active', [TahunAkademikController::class, 'toggleActive']);
    Route::apiResource('tahun-akademik', TahunAkademikController::class);

    // User Management Routes
    Route::apiResource('users', UserController::class);
    Route::post('users/{id}/assign-roles', [UserController::class, 'assignRoles']);
    Route::post('users/{id}/assign-permissions', [UserController::class, 'assignPermissions']);

    // IKU Management Routes
    Route::get('iku/statistics', [IKUController::class, 'statistics']);
    Route::get('iku/categories', [IKUController::class, 'categories']);
    Route::get('iku/export/excel', [IKUController::class, 'exportExcel']);
    Route::get('iku/export/pdf', [IKUController::class, 'exportPDF']);
    Route::post('iku/{id}/toggle-active', [IKUController::class, 'toggleActive']);
    Route::apiResource('iku', IKUController::class);

    // IKU Target Routes
    Route::get('iku-targets/dashboard-statistics', [IKUTargetController::class, 'dashboardStatistics']);
    Route::get('iku-targets/need-attention', [IKUTargetController::class, 'needAttention']);
    Route::get('iku-targets/by-status', [IKUTargetController::class, 'byStatus']);
    Route::get('iku-targets/export/excel', [IKUTargetController::class, 'exportExcel']);
    Route::get('iku-targets/export/pdf', [IKUTargetController::class, 'exportPDF']);
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
    Route::get('periode-akreditasi/{id}/dashboard', [PeriodeAkreditasiController::class, 'dashboard']);
    Route::get('periode-akreditasi/{id}/statistics', [PeriodeAkreditasiController::class, 'statistics']);
    Route::get('periode-akreditasi/{id}/gap-analysis', [PeriodeAkreditasiController::class, 'gapAnalysis']);
    Route::get('periode-akreditasi/{id}/scoring-simulation', [PeriodeAkreditasiController::class, 'scoringSimulation']);
    Route::get('periode-akreditasi/{id}/export/pdf', [PeriodeAkreditasiController::class, 'exportPDF']);
    Route::get('periode-akreditasi/{id}/export/excel', [PeriodeAkreditasiController::class, 'exportExcel']);
    Route::post('periode-akreditasi/{id}/copy-butir-from-template', [PeriodeAkreditasiController::class, 'copyButirFromTemplate']);
    Route::post('periode-akreditasi/{id}/copy-butir-from-periode', [PeriodeAkreditasiController::class, 'copyButirFromPeriode']);
    Route::get('periode-akreditasi/{id}/butir-count', [PeriodeAkreditasiController::class, 'getButirCount']);
    Route::get('periode-akreditasi/template-count', [PeriodeAkreditasiController::class, 'getTemplateCount']);
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
    Route::get('pengisian-butir/{id}/check-lock-status', [PengisianButirController::class, 'checkLockStatus']);
    Route::post('pengisian-butir/{id}/acquire-lock', [PengisianButirController::class, 'acquireLock']);
    Route::post('pengisian-butir/{id}/release-lock', [PengisianButirController::class, 'releaseLock']);
    Route::post('pengisian-butir/{id}/extend-lock', [PengisianButirController::class, 'extendLock']);
    Route::get('pengisian-butir/{id}/check-edit-lock', [PengisianButirController::class, 'checkEditLock']);
    Route::get('pengisian-butir/periode/{periodeId}/summary', [PengisianButirController::class, 'summary']);
    Route::apiResource('pengisian-butir', PengisianButirController::class);

    // Butir Comments Routes (Collaboration Feature)
    Route::get('pengisian-butir/{pengisianButirId}/comments', [ButirCommentController::class, 'index']);
    Route::post('pengisian-butir/{pengisianButirId}/comments', [ButirCommentController::class, 'store']);
    Route::put('butir-comments/{id}', [ButirCommentController::class, 'update']);
    Route::delete('butir-comments/{id}', [ButirCommentController::class, 'destroy']);
    Route::post('butir-comments/{id}/resolve', [ButirCommentController::class, 'resolve']);
    Route::post('butir-comments/{id}/unresolve', [ButirCommentController::class, 'unresolve']);

    // Dokumen Akreditasi Routes
    Route::get('dokumen-akreditasi/{id}/download', [DokumenAkreditasiController::class, 'download']);
    Route::post('dokumen-akreditasi/{id}/attach-butir', [DokumenAkreditasiController::class, 'attachToButir']);
    Route::apiResource('dokumen-akreditasi', DokumenAkreditasiController::class);

    // Notification Routes
    Route::get('notifications', [NotificationController::class, 'index']);
    Route::get('notifications/unread-count', [NotificationController::class, 'unreadCount']);
    Route::post('notifications/{id}/mark-as-read', [NotificationController::class, 'markAsRead']);
    Route::post('notifications/mark-all-as-read', [NotificationController::class, 'markAllAsRead']);
});
