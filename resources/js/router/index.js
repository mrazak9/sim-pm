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
