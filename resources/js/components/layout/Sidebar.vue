<template>
  <aside
    :class="[
      'fixed left-0 top-0 z-9999 flex h-screen flex-col overflow-y-hidden border-r border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 transition-all duration-300',
      sidebarExpanded ? 'w-[290px]' : 'lg:w-[90px]',
      sidebarMobileOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'
    ]"
  >
    <!-- Sidebar Header -->
    <div class="flex items-center justify-between gap-2 px-5 py-8">
      <router-link to="/" class="flex items-center">
        <!-- Logo Text -->
        <div v-if="sidebarExpanded || sidebarMobileOpen" class="text-xl font-bold text-gray-800 dark:text-white">
          SIM-PM
        </div>
        <div v-else class="text-xl font-bold text-gray-800 dark:text-white">
          S
        </div>
      </router-link>

      <!-- Toggle button (desktop only) -->
      <button
        @click="toggleSidebar"
        class="hidden lg:block p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
        </svg>
      </button>
    </div>

    <!-- Sidebar Menu -->
    <nav class="flex-1 overflow-y-auto no-scrollbar px-5">
      <!-- Menu Group: Main -->
      <div class="mb-6">
        <h3 class="mb-4 text-xs uppercase text-gray-400 font-semibold">
          <span v-if="sidebarExpanded || sidebarMobileOpen">MENU</span>
          <span v-else class="flex justify-center">•••</span>
        </h3>

        <ul class="space-y-2">
          <!-- Dashboard -->
          <li>
            <router-link
              to="/"
              @click="closeMobileSidebar"
              :class="[
                'flex items-center gap-3 px-4 py-3 rounded-lg transition-colors',
                $route.path === '/'
                  ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 font-medium'
                  : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800'
              ]"
            >
              <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
              </svg>
              <span v-if="sidebarExpanded || sidebarMobileOpen">Dashboard</span>
            </router-link>
          </li>
        </ul>
      </div>

      <!-- Menu Group: Master Data -->
      <div class="mb-6">
        <h3 class="mb-4 text-xs uppercase text-gray-400 font-semibold">
          <span v-if="sidebarExpanded || sidebarMobileOpen">MASTER DATA</span>
          <span v-else class="flex justify-center">•••</span>
        </h3>

        <ul class="space-y-2">
          <!-- Unit Kerja -->
          <li>
            <router-link
              to="/master-data/unit-kerja"
              @click="closeMobileSidebar"
              :class="[
                'flex items-center gap-3 px-4 py-3 rounded-lg transition-colors',
                $route.path.includes('/unit-kerja')
                  ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 font-medium'
                  : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800'
              ]"
            >
              <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
              </svg>
              <span v-if="sidebarExpanded || sidebarMobileOpen">Unit Kerja</span>
            </router-link>
          </li>

          <!-- Program Studi -->
          <li>
            <router-link
              to="/master-data/program-studi"
              @click="closeMobileSidebar"
              :class="[
                'flex items-center gap-3 px-4 py-3 rounded-lg transition-colors',
                $route.path.includes('/program-studi')
                  ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 font-medium'
                  : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800'
              ]"
            >
              <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
              </svg>
              <span v-if="sidebarExpanded || sidebarMobileOpen">Program Studi</span>
            </router-link>
          </li>

          <!-- Jabatan -->
          <li>
            <router-link
              to="/master-data/jabatan"
              @click="closeMobileSidebar"
              :class="[
                'flex items-center gap-3 px-4 py-3 rounded-lg transition-colors',
                $route.path.includes('/jabatan')
                  ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 font-medium'
                  : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800'
              ]"
            >
              <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
              </svg>
              <span v-if="sidebarExpanded || sidebarMobileOpen">Jabatan</span>
            </router-link>
          </li>

          <!-- Tahun Akademik -->
          <li>
            <router-link
              to="/master-data/tahun-akademik"
              @click="closeMobileSidebar"
              :class="[
                'flex items-center gap-3 px-4 py-3 rounded-lg transition-colors',
                $route.path.includes('/tahun-akademik')
                  ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 font-medium'
                  : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800'
              ]"
            >
              <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
              <span v-if="sidebarExpanded || sidebarMobileOpen">Tahun Akademik</span>
            </router-link>
          </li>

          <!-- User Management -->
          <li>
            <router-link
              to="/users"
              @click="closeMobileSidebar"
              :class="[
                'flex items-center gap-3 px-4 py-3 rounded-lg transition-colors',
                $route.path.startsWith('/users')
                  ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 font-medium'
                  : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800'
              ]"
            >
              <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
              </svg>
              <span v-if="sidebarExpanded || sidebarMobileOpen">User Management</span>
            </router-link>
          </li>
        </ul>
      </div>

      <!-- Menu Group: Dokumen -->
      <div class="mb-6">
        <h3 class="mb-4 text-xs uppercase text-gray-400 font-semibold">
          <span v-if="sidebarExpanded || sidebarMobileOpen">DOKUMEN</span>
          <span v-else class="flex justify-center">•••</span>
        </h3>

        <ul class="space-y-2">
          <li>
            <router-link
              to="/dokumen"
              @click="closeMobileSidebar"
              :class="[
                'flex items-center gap-3 px-4 py-3 rounded-lg transition-colors',
                $route.path.startsWith('/dokumen')
                  ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 font-medium'
                  : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800'
              ]"
            >
              <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
              <span v-if="sidebarExpanded || sidebarMobileOpen">Dokumen Mutu</span>
            </router-link>
          </li>
        </ul>
      </div>

      <!-- Menu Group: Audit -->
      <div class="mb-6">
        <h3 class="mb-4 text-xs uppercase text-gray-400 font-semibold">
          <span v-if="sidebarExpanded || sidebarMobileOpen">AUDIT</span>
          <span v-else class="flex justify-center">•••</span>
        </h3>

        <ul class="space-y-2">
          <li>
            <router-link
              to="/audit"
              @click="closeMobileSidebar"
              :class="[
                'flex items-center gap-3 px-4 py-3 rounded-lg transition-colors',
                $route.path.startsWith('/audit')
                  ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 font-medium'
                  : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800'
              ]"
            >
              <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
              </svg>
              <span v-if="sidebarExpanded || sidebarMobileOpen">Audit Internal</span>
            </router-link>
          </li>
        </ul>
      </div>

      <!-- Menu Group: IKU -->
      <div class="mb-6">
        <h3 class="mb-4 text-xs uppercase text-gray-400 font-semibold">
          <span v-if="sidebarExpanded || sidebarMobileOpen">IKU</span>
          <span v-else class="flex justify-center">•••</span>
        </h3>

        <ul class="space-y-2">
          <!-- IKU Dashboard -->
          <li>
            <router-link
              to="/iku"
              @click="closeMobileSidebar"
              :class="[
                'flex items-center gap-3 px-4 py-3 rounded-lg transition-colors',
                $route.path === '/iku'
                  ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 font-medium'
                  : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800'
              ]"
            >
              <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
              </svg>
              <span v-if="sidebarExpanded || sidebarMobileOpen">Dashboard IKU</span>
            </router-link>
          </li>

          <!-- IKU Management -->
          <li>
            <router-link
              to="/iku/list"
              @click="closeMobileSidebar"
              :class="[
                'flex items-center gap-3 px-4 py-3 rounded-lg transition-colors',
                $route.path.startsWith('/iku/') && !$route.path.includes('/target') && !$route.path.includes('/progress')
                  ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 font-medium'
                  : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800'
              ]"
            >
              <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
              </svg>
              <span v-if="sidebarExpanded || sidebarMobileOpen">Kelola IKU</span>
            </router-link>
          </li>
        </ul>
      </div>

      <!-- Menu Group: Akreditasi -->
      <div class="mb-6">
        <h3 class="mb-4 text-xs uppercase text-gray-400 font-semibold">
          <span v-if="sidebarExpanded || sidebarMobileOpen">AKREDITASI</span>
          <span v-else class="flex justify-center">•••</span>
        </h3>

        <ul class="space-y-2">
          <!-- Akreditasi Dashboard -->
          <li>
            <router-link
              to="/akreditasi"
              @click="closeMobileSidebar"
              :class="[
                'flex items-center gap-3 px-4 py-3 rounded-lg transition-colors',
                $route.path === '/akreditasi'
                  ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 font-medium'
                  : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800'
              ]"
            >
              <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
              </svg>
              <span v-if="sidebarExpanded || sidebarMobileOpen">Dashboard</span>
            </router-link>
          </li>

          <!-- Periode Akreditasi -->
          <li>
            <router-link
              to="/akreditasi/periode"
              @click="closeMobileSidebar"
              :class="[
                'flex items-center gap-3 px-4 py-3 rounded-lg transition-colors',
                $route.path.startsWith('/akreditasi/periode')
                  ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 font-medium'
                  : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800'
              ]"
            >
              <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
              <span v-if="sidebarExpanded || sidebarMobileOpen">Periode Akreditasi</span>
            </router-link>
          </li>
        </ul>
      </div>
    </nav>

    <!-- User Profile (at bottom) -->
    <div class="border-t border-gray-200 dark:border-gray-800 px-5 py-4">
      <div class="flex items-center gap-3 px-4 py-3 rounded-lg bg-gray-50 dark:bg-gray-800">
        <div class="w-10 h-10 rounded-full bg-blue-600 flex items-center justify-center text-white font-semibold flex-shrink-0">
          {{ userInitials }}
        </div>
        <div v-if="sidebarExpanded || sidebarMobileOpen" class="flex-1 min-w-0">
          <p class="text-sm font-medium text-gray-800 dark:text-white truncate">{{ userName }}</p>
          <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ userRole }}</p>
        </div>
      </div>
    </div>
  </aside>
</template>

<script setup>
import { computed } from 'vue';
import { useSidebar } from '@/composables/useSidebar';
import { useAuthStore } from '@/stores/auth';

const { sidebarExpanded, sidebarMobileOpen, toggleSidebar, closeMobileSidebar } = useSidebar();
const authStore = useAuthStore();

const userName = computed(() => authStore.user?.name || 'User');
const userRole = computed(() => authStore.user?.roles?.[0]?.name || 'User');
const userInitials = computed(() => {
  const name = authStore.user?.name || 'U';
  return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
});
</script>
