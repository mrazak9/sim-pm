<template>
  <MainLayout>
    <div class="space-y-6">
      <!-- Profile Information -->
      <div class="rounded-lg border border-gray-200 bg-white shadow dark:border-gray-700 dark:bg-gray-800">
        <!-- Header -->
        <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
            Profil Pengguna
          </h3>
          <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            Informasi akun dan data pribadi Anda
          </p>
        </div>

        <!-- Profile Form -->
        <form @submit.prevent="handleUpdateProfile" class="p-6">
          <div class="space-y-6">
            <!-- Name & Email -->
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
              <div>
                <label class="block text-sm font-medium text-gray-900 dark:text-white">
                  Nama Lengkap <span class="text-red-600">*</span>
                </label>
                <input
                  v-model="profileForm.name"
                  type="text"
                  required
                  class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                  :class="{ 'border-red-500': profileErrors.name }"
                />
                <p v-if="profileErrors.name" class="mt-1 text-sm text-red-600">{{ profileErrors.name[0] }}</p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-900 dark:text-white">
                  Email <span class="text-red-600">*</span>
                </label>
                <input
                  v-model="profileForm.email"
                  type="email"
                  required
                  class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                  :class="{ 'border-red-500': profileErrors.email }"
                />
                <p v-if="profileErrors.email" class="mt-1 text-sm text-red-600">{{ profileErrors.email[0] }}</p>
              </div>
            </div>

            <!-- Phone -->
            <div>
              <label class="block text-sm font-medium text-gray-900 dark:text-white">
                Telepon
              </label>
              <input
                v-model="profileForm.phone"
                type="text"
                class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                :class="{ 'border-red-500': profileErrors.phone }"
              />
              <p v-if="profileErrors.phone" class="mt-1 text-sm text-red-600">{{ profileErrors.phone[0] }}</p>
            </div>

            <!-- Address -->
            <div>
              <label class="block text-sm font-medium text-gray-900 dark:text-white">
                Alamat
              </label>
              <textarea
                v-model="profileForm.address"
                rows="3"
                class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                :class="{ 'border-red-500': profileErrors.address }"
              ></textarea>
              <p v-if="profileErrors.address" class="mt-1 text-sm text-red-600">{{ profileErrors.address[0] }}</p>
            </div>

            <!-- Read-only fields -->
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
              <div>
                <label class="block text-sm font-medium text-gray-900 dark:text-white">
                  NIP
                </label>
                <input
                  :value="user?.nip || '-'"
                  type="text"
                  disabled
                  class="mt-1 block w-full rounded-lg border border-gray-300 bg-gray-100 px-4 py-2 text-gray-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-400"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-900 dark:text-white">
                  NIDN
                </label>
                <input
                  :value="user?.nidn || '-'"
                  type="text"
                  disabled
                  class="mt-1 block w-full rounded-lg border border-gray-300 bg-gray-100 px-4 py-2 text-gray-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-400"
                />
              </div>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
              <div>
                <label class="block text-sm font-medium text-gray-900 dark:text-white">
                  Unit Kerja
                </label>
                <input
                  :value="user?.unit_kerja?.nama || '-'"
                  type="text"
                  disabled
                  class="mt-1 block w-full rounded-lg border border-gray-300 bg-gray-100 px-4 py-2 text-gray-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-400"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-900 dark:text-white">
                  Jabatan
                </label>
                <input
                  :value="user?.jabatan?.nama || '-'"
                  type="text"
                  disabled
                  class="mt-1 block w-full rounded-lg border border-gray-300 bg-gray-100 px-4 py-2 text-gray-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-400"
                />
              </div>
            </div>
          </div>

          <!-- Actions -->
          <div class="mt-6 flex justify-end">
            <button
              type="submit"
              :disabled="profileLoading"
              class="inline-flex items-center rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-300 disabled:cursor-not-allowed disabled:opacity-50"
            >
              <svg v-if="profileLoading" class="mr-2 h-5 w-5 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              Simpan Profil
            </button>
          </div>
        </form>
      </div>

      <!-- Roles & Permissions -->
      <div class="rounded-lg border border-gray-200 bg-white shadow dark:border-gray-700 dark:bg-gray-800">
        <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
            Role & Permissions
          </h3>
          <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            Hak akses yang Anda miliki dalam sistem
          </p>
        </div>

        <div class="p-6">
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-900 dark:text-white">Role</label>
              <div class="mt-2 flex flex-wrap gap-2">
                <span
                  v-for="role in user?.roles"
                  :key="role.id"
                  class="inline-flex rounded-full bg-blue-100 px-3 py-1 text-sm font-medium text-blue-800 dark:bg-blue-900 dark:text-blue-300"
                >
                  {{ role.name }}
                </span>
                <span v-if="!user?.roles || user.roles.length === 0" class="text-gray-500 dark:text-gray-400">Tidak ada role</span>
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-900 dark:text-white">Permissions</label>
              <div class="mt-2 grid grid-cols-1 gap-2 md:grid-cols-2 lg:grid-cols-3">
                <div
                  v-for="permission in user?.permissions"
                  :key="permission.id"
                  class="flex items-center rounded-lg border border-gray-200 bg-gray-50 p-2 dark:border-gray-700 dark:bg-gray-900"
                >
                  <svg class="mr-2 h-4 w-4 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                  </svg>
                  <span class="text-xs text-gray-700 dark:text-gray-300">{{ permission.name }}</span>
                </div>
                <span v-if="!user?.permissions || user.permissions.length === 0" class="text-gray-500 dark:text-gray-400">Tidak ada permissions</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Change Password -->
      <div class="rounded-lg border border-gray-200 bg-white shadow dark:border-gray-700 dark:bg-gray-800">
        <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
            Ubah Password
          </h3>
          <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            Perbarui password akun Anda
          </p>
        </div>

        <form @submit.prevent="handleChangePassword" class="p-6">
          <div class="space-y-6">
            <div>
              <label class="block text-sm font-medium text-gray-900 dark:text-white">
                Password Saat Ini <span class="text-red-600">*</span>
              </label>
              <input
                v-model="passwordForm.current_password"
                type="password"
                required
                class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                :class="{ 'border-red-500': passwordErrors.current_password }"
              />
              <p v-if="passwordErrors.current_password" class="mt-1 text-sm text-red-600">{{ passwordErrors.current_password[0] }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-900 dark:text-white">
                Password Baru <span class="text-red-600">*</span>
              </label>
              <input
                v-model="passwordForm.password"
                type="password"
                required
                class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                :class="{ 'border-red-500': passwordErrors.password }"
              />
              <p v-if="passwordErrors.password" class="mt-1 text-sm text-red-600">{{ passwordErrors.password[0] }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-900 dark:text-white">
                Konfirmasi Password Baru <span class="text-red-600">*</span>
              </label>
              <input
                v-model="passwordForm.password_confirmation"
                type="password"
                required
                class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                :class="{ 'border-red-500': passwordErrors.password_confirmation }"
              />
              <p v-if="passwordErrors.password_confirmation" class="mt-1 text-sm text-red-600">{{ passwordErrors.password_confirmation[0] }}</p>
            </div>
          </div>

          <div class="mt-6 flex justify-end">
            <button
              type="submit"
              :disabled="passwordLoading"
              class="inline-flex items-center rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-300 disabled:cursor-not-allowed disabled:opacity-50"
            >
              <svg v-if="passwordLoading" class="mr-2 h-5 w-5 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              Ubah Password
            </button>
          </div>
        </form>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import MainLayout from '@/layouts/MainLayout.vue';
import { useUserApi } from '@/composables/useUserApi';

const router = useRouter();

const { getProfile, updateProfile, changePassword } = useUserApi();

const user = ref(null);
const profileLoading = ref(false);
const passwordLoading = ref(false);
const profileErrors = ref({});
const passwordErrors = ref({});

const profileForm = ref({
  name: '',
  email: '',
  phone: '',
  address: '',
});

const passwordForm = ref({
  current_password: '',
  password: '',
  password_confirmation: '',
});

const loadProfile = async () => {
  try {
    const response = await getProfile();
    if (response.success) {
      user.value = response.data;
      profileForm.value = {
        name: user.value.name || '',
        email: user.value.email || '',
        phone: user.value.phone || '',
        address: user.value.address || '',
      };
    }
  } catch (error) {
    console.error('Failed to load profile:', error);
    alert('Gagal memuat profil: ' + (error.response?.data?.message || error.message));
  }
};

const handleUpdateProfile = async () => {
  profileErrors.value = {};
  profileLoading.value = true;

  try {
    const response = await updateProfile(profileForm.value);
    if (response.success) {
      alert('Profil berhasil diperbarui');
      loadProfile();
    }
  } catch (error) {
    if (error.response?.data?.errors) {
      profileErrors.value = error.response.data.errors;
    } else {
      alert('Gagal memperbarui profil: ' + (error.response?.data?.message || error.message));
    }
  } finally {
    profileLoading.value = false;
  }
};

const handleChangePassword = async () => {
  passwordErrors.value = {};
  passwordLoading.value = true;

  try {
    const response = await changePassword(passwordForm.value);
    if (response.success) {
      alert('Password berhasil diubah');
      passwordForm.value = {
        current_password: '',
        password: '',
        password_confirmation: '',
      };
    }
  } catch (error) {
    if (error.response?.data?.errors) {
      passwordErrors.value = error.response.data.errors;
    } else {
      alert('Gagal mengubah password: ' + (error.response?.data?.message || error.message));
    }
  } finally {
    passwordLoading.value = false;
  }
};

onMounted(() => {
  loadProfile();
});
</script>
