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
