<template>
  <div class="min-h-screen" style="background-color: #f9fafb;">
    <!-- Sidebar -->
    <div
      :class="['fixed inset-y-0 left-0 z-50 transform transition-transform duration-300 ease-in-out', sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0']"
      style="width: 16rem; background: linear-gradient(180deg, #667eea 0%, #764ba2 100%);"
    >
      <div class="flex flex-col h-full">
        <!-- Logo -->
        <div class="flex items-center justify-center px-4" style="height: 4rem; background: rgba(0,0,0,0.1);">
          <div class="flex items-center" style="gap: 0.75rem;">
            <div class="bg-white rounded-lg flex items-center justify-center shadow-lg" style="width: 40px; height: 40px;">
              <svg style="width: 24px; height: 24px; color: #667eea;" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
              </svg>
            </div>
            <span class="text-white font-bold text-lg">SIM PM</span>
          </div>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 px-4 py-6 overflow-y-auto" style="display: flex; flex-direction: column; gap: 0.5rem;">
          <router-link
            to="/"
            class="flex items-center px-4 py-3 text-white rounded-lg transition-colors"
            active-class="font-semibold"
            style="background: rgba(255,255,255,0.1);"
          >
            <svg style="width: 20px; height: 20px; margin-right: 0.75rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            Dashboard
          </router-link>

          <!-- Master Data Section -->
          <div style="padding-top: 1rem;">
            <p class="px-4 text-xs font-semibold uppercase mb-2" style="color: rgba(255,255,255,0.7); letter-spacing: 0.05em;">Master Data</p>
            <router-link
              to="/unit-kerja"
              class="flex items-center px-4 py-3 text-white rounded-lg transition-colors hover-bg"
              active-class="font-semibold active-link"
            >
              <svg style="width: 20px; height: 20px; margin-right: 0.75rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
              </svg>
              Unit Kerja
            </router-link>
            <router-link
              to="/program-studi"
              class="flex items-center px-4 py-3 text-white rounded-lg transition-colors hover-bg"
              active-class="font-semibold active-link"
            >
              <svg style="width: 20px; height: 20px; margin-right: 0.75rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
              </svg>
              Program Studi
            </router-link>
            <router-link
              to="/users"
              class="flex items-center px-4 py-3 text-white rounded-lg transition-colors hover-bg"
              active-class="font-semibold active-link"
            >
              <svg style="width: 20px; height: 20px; margin-right: 0.75rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
              </svg>
              User Management
            </router-link>
          </div>

          <!-- Dokumen Section -->
          <div style="padding-top: 1rem;">
            <p class="px-4 text-xs font-semibold uppercase mb-2" style="color: rgba(255,255,255,0.7); letter-spacing: 0.05em;">Dokumen</p>
            <router-link
              to="/dokumen"
              class="flex items-center px-4 py-3 text-white rounded-lg transition-colors hover-bg"
              active-class="font-semibold active-link"
            >
              <svg style="width: 20px; height: 20px; margin-right: 0.75rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
              Dokumen Mutu
            </router-link>
          </div>

          <!-- Audit Section -->
          <div style="padding-top: 1rem;">
            <p class="px-4 text-xs font-semibold uppercase mb-2" style="color: rgba(255,255,255,0.7); letter-spacing: 0.05em;">Audit</p>
            <router-link
              to="/audit"
              class="flex items-center px-4 py-3 text-white rounded-lg transition-colors hover-bg"
              active-class="font-semibold active-link"
            >
              <svg style="width: 20px; height: 20px; margin-right: 0.75rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
              </svg>
              Audit Internal
            </router-link>
          </div>
        </nav>

        <!-- User Profile -->
        <div class="p-4" style="border-top: 1px solid rgba(255,255,255,0.1);">
          <div class="flex items-center px-4 py-3 rounded-lg" style="gap: 0.75rem; background: rgba(255,255,255,0.1);">
            <div class="rounded-full flex items-center justify-center text-white font-bold" style="width: 40px; height: 40px; background-color: rgba(99, 102, 241, 0.8);">
              {{ userInitials }}
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-medium text-white">{{ userName }}</p>
              <p class="text-xs text-white" style="opacity: 0.8;">{{ userRole }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Mobile menu button -->
    <button
      @click="sidebarOpen = !sidebarOpen"
      class="lg:hidden fixed top-4 left-4 z-50 p-2 rounded-lg text-white shadow-lg"
      style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);"
    >
      <svg style="width: 24px; height: 24px;" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
      </svg>
    </button>

    <!-- Main Content -->
    <div class="lg:pl-64">
      <!-- Top Bar -->
      <div class="sticky top-0 z-40 bg-white" style="border-bottom: 1px solid #e5e7eb;">
        <div class="flex items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
          <h2 class="text-2xl font-bold" style="color: #1f2937;">{{ pageTitle }}</h2>
          <div class="flex items-center" style="gap: 1rem;">
            <button @click="handleLogout" class="flex items-center px-4 py-2 text-sm font-medium text-white rounded-lg transition-colors shadow-sm" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
              <svg style="width: 16px; height: 16px; margin-right: 0.5rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
              </svg>
              Logout
            </button>
          </div>
        </div>
      </div>

      <!-- Page Content -->
      <main class="p-4 sm:p-6 lg:p-8">
        <slot />
      </main>
    </div>

    <!-- Mobile overlay -->
    <div
      v-if="sidebarOpen"
      @click="sidebarOpen = false"
      class="lg:hidden fixed inset-0 z-40"
      style="background-color: rgba(0, 0, 0, 0.5);"
    ></div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();

const sidebarOpen = ref(false);

const userName = computed(() => authStore.user?.name || 'User');
const userRole = computed(() => authStore.user?.roles?.[0]?.name || 'User');
const userInitials = computed(() => {
  const name = authStore.user?.name || 'U';
  return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
});

const pageTitle = computed(() => {
  const titles = {
    '/': 'Dashboard',
    '/unit-kerja': 'Unit Kerja',
    '/program-studi': 'Program Studi',
    '/users': 'User Management',
    '/dokumen': 'Dokumen Mutu',
    '/audit': 'Audit Internal'
  };
  return titles[route.path] || 'Dashboard';
});

const handleLogout = async () => {
  await authStore.logout();
  router.push('/login');
};
</script>

<style scoped>
.hover-bg:hover {
  background: rgba(255, 255, 255, 0.1);
}

.active-link {
  background: rgba(255, 255, 255, 0.2);
}
</style>
