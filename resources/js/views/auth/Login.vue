<template>
  <div class="min-h-screen flex items-center justify-center" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
    <div style="max-width: 28rem; width: 100%; margin: 0 1rem;">
      <!-- Card Container -->
      <div class="bg-white rounded-xl shadow-2xl overflow-hidden">
        <!-- Header -->
        <div class="px-8 py-10 text-center" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
          <div class="bg-white rounded-full mx-auto mb-4 flex items-center justify-center shadow-lg" style="width: 64px; height: 64px;">
            <svg style="width: 32px; height: 32px; color: #667eea;" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
            </svg>
          </div>
          <h1 class="text-3xl font-bold text-white mb-2">SIM Penjaminan Mutu</h1>
          <p class="text-white" style="opacity: 0.9;">Sistem Informasi Manajemen Mutu</p>
        </div>

        <!-- Form Container -->
        <div class="px-8 py-8">
          <form @submit.prevent="handleLogin" style="display: flex; flex-direction: column; gap: 1.5rem;">
            <!-- Error Message -->
            <div v-if="error" class="rounded-lg border p-4" style="background-color: #fef2f2; border-color: #fecaca;">
              <div class="flex items-center">
                <svg style="width: 20px; height: 20px; color: #ef4444; margin-right: 8px; flex-shrink: 0;" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
                <p class="text-sm font-medium" style="color: #991b1b;">{{ error }}</p>
              </div>
            </div>

            <!-- Email Input -->
            <div>
              <label for="email" class="block text-sm font-medium mb-2" style="color: #374151;">Email</label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 flex items-center pointer-events-none" style="padding-left: 0.75rem;">
                  <svg style="width: 20px; height: 20px; color: #9ca3af;" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                  </svg>
                </div>
                <input
                  id="email"
                  v-model="form.email"
                  type="email"
                  required
                  class="block w-full rounded-lg border focus:outline-none transition"
                  style="padding: 0.75rem 0.75rem 0.75rem 2.5rem; border-color: #d1d5db;"
                  placeholder="nama@email.com"
                />
              </div>
            </div>

            <!-- Password Input -->
            <div>
              <label for="password" class="block text-sm font-medium mb-2" style="color: #374151;">Password</label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 flex items-center pointer-events-none" style="padding-left: 0.75rem;">
                  <svg style="width: 20px; height: 20px; color: #9ca3af;" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                  </svg>
                </div>
                <input
                  id="password"
                  v-model="form.password"
                  type="password"
                  required
                  class="block w-full rounded-lg border focus:outline-none transition"
                  style="padding: 0.75rem 0.75rem 0.75rem 2.5rem; border-color: #d1d5db;"
                  placeholder="••••••••"
                />
              </div>
            </div>

            <!-- Submit Button -->
            <button
              type="submit"
              :disabled="loading"
              class="w-full flex justify-center items-center rounded-lg text-white font-medium focus:outline-none transition-all duration-200 shadow-lg hover:shadow-xl"
              style="padding: 0.75rem 1rem; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; cursor: pointer;"
              :style="{ opacity: loading ? 0.5 : 1, cursor: loading ? 'not-allowed' : 'pointer' }"
            >
              <svg v-if="loading" class="animate-spin text-white" style="width: 20px; height: 20px; margin-right: 0.75rem;" fill="none" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <circle style="opacity: 0.25;" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path style="opacity: 0.75;" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <span v-if="loading">Memproses...</span>
              <span v-else>Masuk</span>
            </button>
          </form>

          <!-- Demo Credentials -->
          <div class="mt-6 pt-6 border-t" style="border-color: #e5e7eb;">
            <p class="text-xs text-center mb-2" style="color: #6b7280;">Demo Credentials:</p>
            <div class="text-xs" style="color: #4b5563;">
              <p class="text-center"><strong>Super Admin:</strong> admin@sim-pm.test / password</p>
              <p class="text-center" style="margin-top: 0.25rem;"><strong>Admin:</strong> demo@sim-pm.test / password</p>
              <p class="text-center" style="margin-top: 0.25rem;"><strong>User:</strong> user@sim-pm.test / password</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Footer -->
      <p class="mt-6 text-center text-sm text-white" style="opacity: 0.9;">
        &copy; 2024 SIM Penjaminan Mutu. All rights reserved.
      </p>
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

<style scoped>
@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}

.animate-spin {
  animation: spin 1s linear infinite;
}
</style>
