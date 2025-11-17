import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const routes = [
  {
    path: '/',
    name: 'home',
    component: () => import('@/views/Home.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/login',
    name: 'login',
    component: () => import('@/views/auth/Login.vue'),
    meta: { guest: true },
  },
  // User Management Routes
  {
    path: '/users',
    name: 'user-list',
    component: () => import('@/views/users/UserList.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/users/create',
    name: 'user-create',
    component: () => import('@/views/users/UserForm.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/users/:id/edit',
    name: 'user-edit',
    component: () => import('@/views/users/UserForm.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/users/roles',
    name: 'role-management',
    component: () => import('@/views/users/RoleManagement.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/users/permissions',
    name: 'permission-management',
    component: () => import('@/views/users/PermissionManagement.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/profile',
    name: 'user-profile',
    component: () => import('@/views/users/UserProfile.vue'),
    meta: { requiresAuth: true },
  },
  // IKU Routes
  {
    path: '/iku',
    name: 'iku-dashboard',
    component: () => import('@/views/iku/IKUDashboard.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/iku/list',
    name: 'iku-list',
    component: () => import('@/views/iku/IKUList.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/iku/create',
    name: 'iku-create',
    component: () => import('@/views/iku/IKUForm.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/iku/edit/:id',
    name: 'iku-edit',
    component: () => import('@/views/iku/IKUForm.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/iku/detail/:id',
    name: 'iku-detail',
    component: () => import('@/views/iku/IKUList.vue'),
    meta: { requiresAuth: true },
  },
  // Master Data Routes
  // Unit Kerja
  {
    path: '/master-data/unit-kerja',
    name: 'unit-kerja-list',
    component: () => import('@/views/master-data/UnitKerjaList.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/master-data/unit-kerja/create',
    name: 'unit-kerja-create',
    component: () => import('@/views/master-data/UnitKerjaForm.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/master-data/unit-kerja/:id/edit',
    name: 'unit-kerja-edit',
    component: () => import('@/views/master-data/UnitKerjaForm.vue'),
    meta: { requiresAuth: true },
  },
  // Program Studi
  {
    path: '/master-data/program-studi',
    name: 'program-studi-list',
    component: () => import('@/views/master-data/ProgramStudiList.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/master-data/program-studi/create',
    name: 'program-studi-create',
    component: () => import('@/views/master-data/ProgramStudiForm.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/master-data/program-studi/:id/edit',
    name: 'program-studi-edit',
    component: () => import('@/views/master-data/ProgramStudiForm.vue'),
    meta: { requiresAuth: true },
  },
  // Jabatan
  {
    path: '/master-data/jabatan',
    name: 'jabatan-list',
    component: () => import('@/views/master-data/JabatanList.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/master-data/jabatan/create',
    name: 'jabatan-create',
    component: () => import('@/views/master-data/JabatanForm.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/master-data/jabatan/:id/edit',
    name: 'jabatan-edit',
    component: () => import('@/views/master-data/JabatanForm.vue'),
    meta: { requiresAuth: true },
  },
  // Tahun Akademik
  {
    path: '/master-data/tahun-akademik',
    name: 'tahun-akademik-list',
    component: () => import('@/views/master-data/TahunAkademikList.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/master-data/tahun-akademik/create',
    name: 'tahun-akademik-create',
    component: () => import('@/views/master-data/TahunAkademikForm.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/master-data/tahun-akademik/:id/edit',
    name: 'tahun-akademik-edit',
    component: () => import('@/views/master-data/TahunAkademikForm.vue'),
    meta: { requiresAuth: true },
  },
  // Akreditasi Routes
  {
    path: '/akreditasi',
    name: 'akreditasi-dashboard',
    component: () => import('@/views/akreditasi/AkreditasiDashboard.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/akreditasi/periode',
    name: 'periode-akreditasi-list',
    component: () => import('@/views/akreditasi/PeriodeAkreditasiList.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/akreditasi/periode/create',
    name: 'periode-akreditasi-create',
    component: () => import('@/views/akreditasi/PeriodeAkreditasiForm.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/akreditasi/periode/:id/edit',
    name: 'periode-akreditasi-edit',
    component: () => import('@/views/akreditasi/PeriodeAkreditasiForm.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/akreditasi/periode/:id',
    name: 'periode-akreditasi-detail',
    component: () => import('@/views/akreditasi/PeriodeAkreditasiDetail.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/akreditasi/periode/:periodeId/pengisian',
    name: 'pengisian-butir-list-by-periode',
    component: () => import('@/views/akreditasi/PengisianButirList.vue'),
    meta: { requiresAuth: true },
  },
  // Butir Akreditasi Routes
  {
    path: '/akreditasi/butir',
    name: 'butir-akreditasi-list',
    component: () => import('@/views/akreditasi/ButirAkreditasiList.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/akreditasi/butir/create',
    name: 'butir-akreditasi-create',
    component: () => import('@/views/akreditasi/ButirAkreditasiForm.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/akreditasi/butir/:id/edit',
    name: 'butir-akreditasi-edit',
    component: () => import('@/views/akreditasi/ButirAkreditasiForm.vue'),
    meta: { requiresAuth: true },
  },
  // Butir Template Management Routes
  {
    path: '/akreditasi/butir-templates',
    name: 'butir-template-management',
    component: () => import('@/views/akreditasi/ButirTemplateManagement.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/akreditasi/butir-templates/:id',
    name: 'butir-template-builder',
    component: () => import('@/views/akreditasi/ButirTemplateBuilder.vue'),
    meta: { requiresAuth: true },
  },
  // Pengisian Butir Routes
  {
    path: '/akreditasi/pengisian',
    name: 'pengisian-butir-list',
    component: () => import('@/views/akreditasi/PengisianButirForm.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/akreditasi/pengisian/create',
    name: 'pengisian-butir-create',
    component: () => import('@/views/akreditasi/PengisianButirForm.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/akreditasi/pengisian/:id/edit',
    name: 'pengisian-butir-edit',
    component: () => import('@/views/akreditasi/PengisianButirForm.vue'),
    meta: { requiresAuth: true },
  },
  // Dokumen Akreditasi Routes
  {
    path: '/akreditasi/dokumen',
    name: 'dokumen-akreditasi-list',
    component: () => import('@/views/akreditasi/DokumenAkreditasiList.vue'),
    meta: { requiresAuth: true },
  },
  // Scoring Simulation Route
  {
    path: '/akreditasi/scoring-simulation',
    name: 'scoring-simulation',
    component: () => import('@/views/akreditasi/ScoringSimulation.vue'),
    meta: { requiresAuth: true },
  },
  // Document Management Routes
  {
    path: '/dokumen/dashboard',
    name: 'document-dashboard',
    component: () => import('@/views/dokumen/DocumentDashboard.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/dokumen',
    name: 'document-list',
    component: () => import('@/views/dokumen/DocumentList.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/dokumen/categories',
    name: 'document-categories',
    component: () => import('@/views/dokumen/CategoryManagement.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/dokumen/:id',
    name: 'document-detail',
    component: () => import('@/views/dokumen/DocumentDetail.vue'),
    meta: { requiresAuth: true },
  },
  // User Activity Dashboard
  {
    path: '/activity',
    name: 'user-activity',
    component: () => import('@/views/UserActivityDashboard.vue'),
    meta: { requiresAuth: true },
  },
  // Audit Module Routes
  // Audit Plans
  {
    path: '/audit/plans',
    name: 'audit-plan-list',
    component: () => import('@/views/audit/AuditPlanList.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/audit/plans/create',
    name: 'audit-plan-create',
    component: () => import('@/views/audit/AuditPlanForm.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/audit/plans/:id/edit',
    name: 'audit-plan-edit',
    component: () => import('@/views/audit/AuditPlanForm.vue'),
    meta: { requiresAuth: true },
  },
  // Audit Schedules
  {
    path: '/audit/schedules',
    name: 'audit-schedule-list',
    component: () => import('@/views/audit/AuditScheduleList.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/audit/schedules/create',
    name: 'audit-schedule-create',
    component: () => import('@/views/audit/AuditScheduleForm.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/audit/schedules/:id/edit',
    name: 'audit-schedule-edit',
    component: () => import('@/views/audit/AuditScheduleForm.vue'),
    meta: { requiresAuth: true },
  },
  // Audit Findings
  {
    path: '/audit/findings',
    name: 'audit-finding-list',
    component: () => import('@/views/audit/AuditFindingList.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/audit/findings/create',
    name: 'audit-finding-create',
    component: () => import('@/views/audit/AuditFindingForm.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/audit/findings/:id/edit',
    name: 'audit-finding-edit',
    component: () => import('@/views/audit/AuditFindingForm.vue'),
    meta: { requiresAuth: true },
  },
  // RTL (Rencana Tindak Lanjut)
  {
    path: '/audit/rtl',
    name: 'rtl-list',
    component: () => import('@/views/audit/RTLList.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/audit/rtl/create',
    name: 'rtl-create',
    component: () => import('@/views/audit/RTLForm.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/audit/rtl/:id/edit',
    name: 'rtl-edit',
    component: () => import('@/views/audit/RTLForm.vue'),
    meta: { requiresAuth: true },
  },
  // SPMI Module Routes
  // SPMI Standards
  {
    path: '/spmi/standards',
    name: 'spmi-standard-list',
    component: () => import('@/views/spmi/SpmiStandardList.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/spmi/standards/create',
    name: 'spmi-standard-create',
    component: () => import('@/views/spmi/SpmiStandardForm.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/spmi/standards/:id/edit',
    name: 'spmi-standard-edit',
    component: () => import('@/views/spmi/SpmiStandardForm.vue'),
    meta: { requiresAuth: true },
  },
  // SPMI Indicators
  {
    path: '/spmi/indicators',
    name: 'spmi-indicator-list',
    component: () => import('@/views/spmi/SpmiIndicatorList.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/spmi/indicators/create',
    name: 'spmi-indicator-create',
    component: () => import('@/views/spmi/SpmiIndicatorForm.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/spmi/indicators/:id/edit',
    name: 'spmi-indicator-edit',
    component: () => import('@/views/spmi/SpmiIndicatorForm.vue'),
    meta: { requiresAuth: true },
  },
  // SPMI Monitoring
  {
    path: '/spmi/monitorings',
    name: 'spmi-monitoring-list',
    component: () => import('@/views/spmi/SpmiMonitoringList.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/spmi/monitorings/create',
    name: 'spmi-monitoring-create',
    component: () => import('@/views/spmi/SpmiMonitoringForm.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/spmi/monitorings/:id/edit',
    name: 'spmi-monitoring-edit',
    component: () => import('@/views/spmi/SpmiMonitoringForm.vue'),
    meta: { requiresAuth: true },
  },
  // RTM (Rapat Tinjauan Manajemen)
  {
    path: '/spmi/rtm',
    name: 'rtm-list',
    component: () => import('@/views/spmi/RTMList.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/spmi/rtm/create',
    name: 'rtm-create',
    component: () => import('@/views/spmi/RTMForm.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/spmi/rtm/:id/edit',
    name: 'rtm-edit',
    component: () => import('@/views/spmi/RTMForm.vue'),
    meta: { requiresAuth: true },
  },
  // RTM Action Items
  {
    path: '/spmi/rtm-actions',
    name: 'rtm-action-list',
    component: () => import('@/views/spmi/RTMActionItemList.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/spmi/rtm-actions/create',
    name: 'rtm-action-create',
    component: () => import('@/views/spmi/RTMActionItemForm.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/spmi/rtm-actions/:id/edit',
    name: 'rtm-action-edit',
    component: () => import('@/views/spmi/RTMActionItemForm.vue'),
    meta: { requiresAuth: true },
  },
  // Survey (Kuesioner) Module Routes
  {
    path: '/surveys',
    name: 'survey-list',
    component: () => import('@/views/survey/SurveyList.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/surveys/create',
    name: 'survey-create',
    component: () => import('@/views/survey/SurveyForm.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/surveys/:id/edit',
    name: 'survey-edit',
    component: () => import('@/views/survey/SurveyForm.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/surveys/:id/builder',
    name: 'survey-builder',
    component: () => import('@/views/survey/SurveyBuilder.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/surveys/:id/analytics',
    name: 'survey-analytics',
    component: () => import('@/views/survey/SurveyAnalytics.vue'),
    meta: { requiresAuth: true },
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

// Navigation guards
router.beforeEach((to, from, next) => {
  const authStore = useAuthStore();
  const isAuthenticated = authStore.isLoggedIn;

  // Check if route requires authentication
  if (to.meta.requiresAuth && !isAuthenticated) {
    next({ name: 'login' });
  }
  // Check if route is for guests only
  else if (to.meta.guest && isAuthenticated) {
    next({ name: 'home' });
  }
  // Allow navigation
  else {
    next();
  }
});

export default router;
