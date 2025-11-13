<template>
  <div class="relative bg-white dark:bg-gray-900 min-h-screen">
    <!-- Container -->
    <div class="flex flex-col lg:flex-row min-h-screen">
      <!-- Left Side: Form -->
      <div class="flex-1 flex flex-col">
        <!-- Back button -->
        <div class="max-w-md mx-auto w-full pt-10 px-6">
          <router-link to="/" class="inline-flex items-center text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali
          </router-link>
        </div>

        <!-- Main Form -->
        <div class="flex-1 flex items-center justify-center px-6">
          <div class="w-full max-w-md">
            <!-- Title -->
            <div class="mb-8">
              <h1 class="text-title-md font-semibold text-gray-800 dark:text-white mb-2">
                Sign In
              </h1>
              <p class="text-sm text-gray-500">
                Masukkan email dan password untuk masuk!
              </p>
            </div>

            <!-- Login Form -->
            <form @submit.prevent="handleLogin" class="space-y-6">
              <!-- Email Input -->
              <div>
                <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Email
                </label>
                <input
                  id="email"
                  v-model="form.email"
                  type="email"
                  required
                  class="w-full px-4 py-3 border border-gray-300 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-800 dark:text-white transition-colors"
                  placeholder="nama@email.com"
                />
              </div>

              <!-- Password Input -->
              <div>
                <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Password
                </label>
                <input
                  id="password"
                  v-model="form.password"
                  type="password"
                  required
                  class="w-full px-4 py-3 border border-gray-300 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-800 dark:text-white transition-colors"
                  placeholder="••••••••"
                />
              </div>

              <!-- Error Message -->
              <div v-if="error" class="rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 p-4">
                <p class="text-sm text-red-800 dark:text-red-200">{{ error }}</p>
              </div>

              <!-- Submit Button -->
              <button
                type="submit"
                :disabled="loading"
                class="w-full py-3 px-4 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <span v-if="loading">Memproses...</span>
                <span v-else>Sign In</span>
              </button>
            </form>

            <!-- Demo Credentials -->
            <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-800">
              <p class="text-xs text-gray-500 text-center mb-2">Demo Credentials:</p>
              <div class="text-xs text-gray-600 dark:text-gray-400 space-y-1">
                <p class="text-center"><strong>Super Admin:</strong> admin@sim-pm.test / password</p>
                <p class="text-center"><strong>Admin:</strong> demo@sim-pm.test / password</p>
                <p class="text-center"><strong>User:</strong> user@sim-pm.test / password</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Side: Pattern/Image -->
      <div class="hidden lg:flex lg:flex-1 bg-blue-600 dark:bg-gray-800 items-center justify-center p-12">
        <!-- Decorative pattern or image -->
        <div class="text-center text-white">
          <div class="mb-8">
            <svg class="w-24 h-24 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
            </svg>
          </div>
          <h2 class="text-4xl font-bold mb-4">SIM Penjaminan Mutu</h2>
          <p class="text-lg opacity-90">Sistem Informasi Manajemen Penjaminan Mutu</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const router = useRouter();
const authStore = useAuthStore();

const form = reactive({
  email: '',
  password: '',
});

const loading = ref(false);
const error = ref('');

const handleLogin = async () => {
  loading.value = true;
  error.value = '';

  try {
    const result = await authStore.login(form);

    if (result.success) {
      router.push('/');
    } else {
      error.value = result.message || 'Login gagal. Periksa email dan password Anda.';
    }
  } catch (err) {
    error.value = 'Terjadi kesalahan saat login';
    console.error('Login error:', err);
  } finally {
    loading.value = false;
  }
};
</script>
