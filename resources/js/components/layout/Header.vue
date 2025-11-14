<template>
  <header class="sticky top-0 z-999 flex w-full bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-800">
    <div class="flex flex-grow items-center justify-between px-4 py-4 shadow-sm md:px-6">
      <!-- Left: Mobile menu button + Page Title -->
      <div class="flex items-center gap-4">
        <!-- Mobile menu button -->
        <button
          @click="toggleMobileSidebar"
          class="lg:hidden p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800"
        >
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
          </svg>
        </button>

        <!-- Page Title -->
        <h1 class="text-xl font-semibold text-gray-800 dark:text-white">
          {{ pageTitle }}
        </h1>
      </div>

      <!-- Right: Dark mode toggle + Notifications + User menu -->
      <div class="flex items-center gap-3">
        <!-- Dark Mode Toggle -->
        <button
          @click="toggleDarkMode"
          class="relative flex h-10 w-10 items-center justify-center rounded-full hover:bg-gray-100 dark:hover:bg-gray-800"
        >
          <!-- Sun icon (show in dark mode) -->
          <svg v-if="darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
          </svg>
          <!-- Moon icon (show in light mode) -->
          <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
          </svg>
        </button>

        <!-- Notifications Bell -->
        <NotificationBell />

        <!-- User Dropdown -->
        <div class="relative" v-click-outside="closeUserMenu">
          <button
            @click="userMenuOpen = !userMenuOpen"
            class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800"
          >
            <div class="w-9 h-9 rounded-full bg-blue-600 flex items-center justify-center text-white font-semibold text-sm">
              {{ userInitials }}
            </div>
            <div class="hidden md:block text-left">
              <p class="text-sm font-medium text-gray-800 dark:text-white">{{ userName }}</p>
              <p class="text-xs text-gray-500 dark:text-gray-400">{{ userRole }}</p>
            </div>
            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </button>

          <!-- Dropdown -->
          <transition
            enter-active-class="transition ease-out duration-100"
            enter-from-class="transform opacity-0 scale-95"
            enter-to-class="transform opacity-100 scale-100"
            leave-active-class="transition ease-in duration-75"
            leave-from-class="transform opacity-100 scale-100"
            leave-to-class="transform opacity-0 scale-95"
          >
            <div
              v-if="userMenuOpen"
              class="absolute right-0 mt-2 w-56 rounded-lg border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-lg"
            >
              <div class="p-3 border-b border-gray-200 dark:border-gray-800">
                <p class="text-sm font-medium text-gray-800 dark:text-white">{{ userName }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ userEmail }}</p>
              </div>

              <ul class="p-2">
                <li>
                  <router-link
                    to="/profile"
                    class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    My Profile
                  </router-link>
                </li>
                <li>
                  <router-link
                    to="/settings"
                    class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Settings
                  </router-link>
                </li>
              </ul>

              <div class="p-2 border-t border-gray-200 dark:border-gray-800">
                <button
                  @click="handleLogout"
                  class="flex w-full items-center gap-3 px-3 py-2 rounded-lg text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                  </svg>
                  Log Out
                </button>
              </div>
            </div>
          </transition>
        </div>
      </div>
    </div>
  </header>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useSidebar } from '@/composables/useSidebar';
import { useDarkMode } from '@/composables/useDarkMode';
import { useAuthStore } from '@/stores/auth';
import NotificationBell from '@/components/layout/NotificationBell.vue';

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();
const { toggleMobileSidebar } = useSidebar();
const { darkMode, toggleDarkMode } = useDarkMode();

const userMenuOpen = ref(false);

const userName = computed(() => authStore.user?.name || 'User');
const userEmail = computed(() => authStore.user?.email || '');
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
    '/audit': 'Audit Internal',
    '/profile': 'Profile',
    '/settings': 'Settings',
  };
  return titles[route.path] || 'Dashboard';
});

const closeUserMenu = () => {
  userMenuOpen.value = false;
};

const handleLogout = async () => {
  await authStore.logout();
  router.push('/login');
};
</script>
