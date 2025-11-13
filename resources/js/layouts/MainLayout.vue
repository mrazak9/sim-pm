<template>
  <div class="min-h-screen bg-gray-100">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
          <div class="flex">
            <!-- Logo -->
            <div class="flex-shrink-0 flex items-center">
              <h1 class="text-xl font-bold text-gray-900">SIM Penjaminan Mutu</h1>
            </div>
          </div>

          <!-- User menu -->
          <div class="flex items-center space-x-4">
            <span v-if="user" class="text-sm text-gray-700">
              {{ user.name }}
            </span>
            <button
              v-if="isAuthenticated"
              @click="handleLogout"
              class="text-gray-500 hover:text-gray-700"
            >
              Logout
            </button>
          </div>
        </div>
      </div>
    </nav>

    <!-- Page Content -->
    <main>
      <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <slot />
      </div>
    </main>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const router = useRouter();
const authStore = useAuthStore();

const user = computed(() => authStore.currentUser);
const isAuthenticated = computed(() => authStore.isLoggedIn);

const handleLogout = async () => {
  await authStore.logout();
  router.push('/login');
};
</script>
