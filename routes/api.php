<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UnitKerjaController;
use App\Http\Controllers\Api\ProgramStudiController;
use App\Http\Controllers\Api\JabatanController;
use App\Http\Controllers\Api\TahunAkademikController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\IKUController;
use App\Http\Controllers\Api\IKUTargetController;
use App\Http\Controllers\Api\IKUProgressController;
use App\Http\Controllers\Api\PeriodeAkreditasiController;
use App\Http\Controllers\Api\ButirAkreditasiController;
use App\Http\Controllers\Api\PengisianButirController;
use App\Http\Controllers\Api\ButirCommentController;
use App\Http\Controllers\Api\DokumenAkreditasiController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\DocumentController;
use App\Http\Controllers\Api\DocumentCategoryController;
use App\Http\Controllers\Api\AuditPlanController;
use App\Http\Controllers\Api\AuditScheduleController;
use App\Http\Controllers\Api\AuditFindingController;
use App\Http\Controllers\Api\RTLController;
use App\Http\Controllers\Api\SpmiStandardController;
use App\Http\Controllers\Api\SpmiIndicatorController;
use App\Http\Controllers\Api\SpmiMonitoringController;
use App\Http\Controllers\Api\RTMController;
use App\Http\Controllers\Api\RTMActionItemController;
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

    // Role & Permission Routes
    Route::get('roles', [RoleController::class, 'index']);
    Route::get('roles/{id}', [RoleController::class, 'show']);
    Route::get('permissions', [PermissionController::class, 'index']);
    Route::get('permissions/{id}', [PermissionController::class, 'show']);

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

    // Document Management Routes

    // Document Category Routes
    Route::get('document-categories/tree', [DocumentCategoryController::class, 'tree']);
    Route::apiResource('document-categories', DocumentCategoryController::class);

    // Document Routes
    Route::get('documents/my-documents', [DocumentController::class, 'myDocuments']);
    Route::get('documents/shared-with-me', [DocumentController::class, 'sharedWithMe']);
    Route::post('documents/{id}/upload-version', [DocumentController::class, 'uploadVersion']);
    Route::post('documents/{documentId}/restore-version/{versionNumber}', [DocumentController::class, 'restoreVersion']);
    Route::post('documents/{id}/update-status', [DocumentController::class, 'updateStatus']);
    Route::post('documents/{id}/share', [DocumentController::class, 'share']);
    Route::delete('document-shares/{shareId}', [DocumentController::class, 'revokeShare']);
    Route::delete('documents/{id}/force', [DocumentController::class, 'forceDestroy']);
    Route::apiResource('documents', DocumentController::class);

    // Audit Module Routes

    // Audit Plan Routes
    Route::get('audit-plans/active', [AuditPlanController::class, 'active']);
    Route::get('audit-plans/statistics', [AuditPlanController::class, 'statistics']);
    Route::post('audit-plans/{id}/approve', [AuditPlanController::class, 'approve']);
    Route::post('audit-plans/{id}/start', [AuditPlanController::class, 'start']);
    Route::post('audit-plans/{id}/complete', [AuditPlanController::class, 'complete']);
    Route::apiResource('audit-plans', AuditPlanController::class);

    // Audit Schedule Routes
    Route::get('audit-schedules/upcoming', [AuditScheduleController::class, 'upcoming']);
    Route::get('audit-schedules/calendar', [AuditScheduleController::class, 'calendar']);
    Route::get('audit-schedules/statistics', [AuditScheduleController::class, 'statistics']);
    Route::post('audit-schedules/{id}/start', [AuditScheduleController::class, 'start']);
    Route::post('audit-schedules/{id}/complete', [AuditScheduleController::class, 'complete']);
    Route::post('audit-schedules/{id}/reschedule', [AuditScheduleController::class, 'reschedule']);
    Route::apiResource('audit-schedules', AuditScheduleController::class);

    // Audit Finding Routes
    Route::get('audit-findings/overdue', [AuditFindingController::class, 'overdue']);
    Route::get('audit-findings/needing-attention', [AuditFindingController::class, 'needingAttention']);
    Route::get('audit-findings/statistics', [AuditFindingController::class, 'statistics']);
    Route::get('audit-findings/statistics-by-category', [AuditFindingController::class, 'statisticsByCategory']);
    Route::post('audit-findings/{id}/resolve', [AuditFindingController::class, 'resolve']);
    Route::post('audit-findings/{id}/verify', [AuditFindingController::class, 'verify']);
    Route::post('audit-findings/{id}/close', [AuditFindingController::class, 'close']);
    Route::post('audit-findings/{id}/reopen', [AuditFindingController::class, 'reopen']);
    Route::apiResource('audit-findings', AuditFindingController::class);

    // RTL Routes
    Route::get('rtls/overdue', [RTLController::class, 'overdue']);
    Route::get('rtls/due-soon', [RTLController::class, 'dueSoon']);
    Route::get('rtls/pending-verification', [RTLController::class, 'pendingVerification']);
    Route::get('rtls/statistics', [RTLController::class, 'statistics']);
    Route::get('rtls/dashboard-statistics', [RTLController::class, 'dashboardStatistics']);
    Route::get('rtls/statistics-by-unit-kerja', [RTLController::class, 'statisticsByUnitKerja']);
    Route::post('rtls/{id}/start', [RTLController::class, 'start']);
    Route::post('rtls/{id}/complete', [RTLController::class, 'complete']);
    Route::post('rtls/{id}/progress', [RTLController::class, 'addProgress']);
    Route::post('rtls/{id}/verify', [RTLController::class, 'verify']);
    Route::apiResource('rtls', RTLController::class);

    // SPMI Module Routes

    // SPMI Standard Routes
    Route::get('spmi-standards/statistics', [SpmiStandardController::class, 'statistics']);
    Route::get('spmi-standards/due-for-review', [SpmiStandardController::class, 'getDueForReview']);
    Route::post('spmi-standards/{id}/approve', [SpmiStandardController::class, 'approve']);
    Route::post('spmi-standards/{id}/revise', [SpmiStandardController::class, 'revise']);
    Route::post('spmi-standards/{id}/archive', [SpmiStandardController::class, 'archive']);
    Route::apiResource('spmi-standards', SpmiStandardController::class);

    // SPMI Indicator Routes
    Route::get('spmi-indicators/statistics', [SpmiIndicatorController::class, 'statistics']);
    Route::post('spmi-indicators/{id}/activate', [SpmiIndicatorController::class, 'activate']);
    Route::post('spmi-indicators/{id}/deactivate', [SpmiIndicatorController::class, 'deactivate']);
    Route::post('spmi-indicators/{id}/targets', [SpmiIndicatorController::class, 'createTarget']);
    Route::put('spmi-indicators/{id}/targets/{targetId}', [SpmiIndicatorController::class, 'updateTarget']);
    Route::post('spmi-indicators/{id}/achievements', [SpmiIndicatorController::class, 'recordAchievement']);
    Route::apiResource('spmi-indicators', SpmiIndicatorController::class);

    // SPMI Monitoring Routes
    Route::get('spmi-monitorings/statistics', [SpmiMonitoringController::class, 'statistics']);
    Route::get('spmi-monitorings/dashboard-data', [SpmiMonitoringController::class, 'dashboardData']);
    Route::post('spmi-monitorings/{id}/start', [SpmiMonitoringController::class, 'start']);
    Route::post('spmi-monitorings/{id}/complete', [SpmiMonitoringController::class, 'complete']);
    Route::post('spmi-monitorings/{id}/upload-report', [SpmiMonitoringController::class, 'uploadReport']);
    Route::apiResource('spmi-monitorings', SpmiMonitoringController::class);

    // RTM Routes
    Route::get('rtms/statistics', [RTMController::class, 'statistics']);
    Route::get('rtms/upcoming', [RTMController::class, 'upcoming']);
    Route::post('rtms/{id}/start', [RTMController::class, 'start']);
    Route::post('rtms/{id}/complete', [RTMController::class, 'complete']);
    Route::post('rtms/{id}/cancel', [RTMController::class, 'cancel']);
    Route::post('rtms/{id}/participants', [RTMController::class, 'addParticipant']);
    Route::delete('rtms/{id}/participants', [RTMController::class, 'removeParticipant']);
    Route::post('rtms/{id}/attendance', [RTMController::class, 'markAttendance']);
    Route::post('rtms/{id}/upload-minutes', [RTMController::class, 'uploadMinutes']);
    Route::post('rtms/{id}/upload-attendance', [RTMController::class, 'uploadAttendance']);
    Route::apiResource('rtms', RTMController::class);

    // RTM Action Item Routes
    Route::get('rtm-action-items/statistics', [RTMActionItemController::class, 'statistics']);
    Route::get('rtm-action-items/dashboard-statistics', [RTMActionItemController::class, 'dashboardStatistics']);
    Route::get('rtm-action-items/overdue', [RTMActionItemController::class, 'overdue']);
    Route::get('rtm-action-items/due-soon', [RTMActionItemController::class, 'dueSoon']);
    Route::post('rtm-action-items/{id}/start', [RTMActionItemController::class, 'start']);
    Route::post('rtm-action-items/{id}/complete', [RTMActionItemController::class, 'complete']);
    Route::post('rtm-action-items/{id}/cancel', [RTMActionItemController::class, 'cancel']);
    Route::post('rtm-action-items/{id}/progress', [RTMActionItemController::class, 'addProgress']);
    Route::post('rtm-action-items/{id}/extend', [RTMActionItemController::class, 'extend']);
    Route::apiResource('rtm-action-items', RTMActionItemController::class);
});
