<template>
  <MainLayout>
    <div class="rounded-lg border border-gray-200 bg-white shadow dark:border-gray-700 dark:bg-gray-800">
      <!-- Header -->
      <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
        <div class="flex items-center justify-between">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
            {{ isEditMode ? 'Edit Pengguna' : 'Tambah Pengguna' }}
          </h3>
          <button
            @click="$router.push('/users')"
            class="text-gray-600 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300"
          >
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </div>

      <!-- Form -->
      <form @submit.prevent="handleSubmit" class="p-6">
        <div class="space-y-6">
          <!-- Name & Email -->
          <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <div>
              <label class="block text-sm font-medium text-gray-900 dark:text-white">
                Nama Lengkap <span class="text-red-600">*</span>
              </label>
              <input
                v-model="form.name"
                type="text"
                required
                class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                :class="{ 'border-red-500': errors.name }"
                placeholder="Masukkan nama lengkap"
              />
              <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name[0] }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-900 dark:text-white">
                Email <span class="text-red-600">*</span>
              </label>
              <input
                v-model="form.email"
                type="email"
                required
                class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                :class="{ 'border-red-500': errors.email }"
                placeholder="Masukkan email"
              />
              <p v-if="errors.email" class="mt-1 text-sm text-red-600">{{ errors.email[0] }}</p>
            </div>
          </div>

          <!-- Password (only on create) -->
          <div v-if="!isEditMode">
            <label class="block text-sm font-medium text-gray-900 dark:text-white">
              Password <span class="text-red-600">*</span>
            </label>
            <input
              v-model="form.password"
              type="password"
              :required="!isEditMode"
              class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              :class="{ 'border-red-500': errors.password }"
              placeholder="Masukkan password"
            />
            <p v-if="errors.password" class="mt-1 text-sm text-red-600">{{ errors.password[0] }}</p>
          </div>

          <!-- Password (optional on edit) -->
          <div v-if="isEditMode">
            <label class="block text-sm font-medium text-gray-900 dark:text-white">
              Password Baru
            </label>
            <input
              v-model="form.password"
              type="password"
              class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              :class="{ 'border-red-500': errors.password }"
              placeholder="Kosongkan jika tidak ingin mengubah password"
            />
            <p v-if="errors.password" class="mt-1 text-sm text-red-600">{{ errors.password[0] }}</p>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Kosongkan jika tidak ingin mengubah password</p>
          </div>

          <!-- NIP & NIDN -->
          <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <div>
              <label class="block text-sm font-medium text-gray-900 dark:text-white">
                NIP
              </label>
              <input
                v-model="form.nip"
                type="text"
                class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                :class="{ 'border-red-500': errors.nip }"
                placeholder="Masukkan NIP"
              />
              <p v-if="errors.nip" class="mt-1 text-sm text-red-600">{{ errors.nip[0] }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-900 dark:text-white">
                NIDN
              </label>
              <input
                v-model="form.nidn"
                type="text"
                class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                :class="{ 'border-red-500': errors.nidn }"
                placeholder="Masukkan NIDN"
              />
              <p v-if="errors.nidn" class="mt-1 text-sm text-red-600">{{ errors.nidn[0] }}</p>
            </div>
          </div>

          <!-- Phone -->
          <div>
            <label class="block text-sm font-medium text-gray-900 dark:text-white">
              Telepon
            </label>
            <input
              v-model="form.phone"
              type="text"
              class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              :class="{ 'border-red-500': errors.phone }"
              placeholder="Masukkan nomor telepon"
            />
            <p v-if="errors.phone" class="mt-1 text-sm text-red-600">{{ errors.phone[0] }}</p>
          </div>

          <!-- Address -->
          <div>
            <label class="block text-sm font-medium text-gray-900 dark:text-white">
              Alamat
            </label>
            <textarea
              v-model="form.address"
              rows="3"
              class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              :class="{ 'border-red-500': errors.address }"
              placeholder="Masukkan alamat lengkap"
            ></textarea>
            <p v-if="errors.address" class="mt-1 text-sm text-red-600">{{ errors.address[0] }}</p>
          </div>

          <!-- Unit Kerja & Jabatan -->
          <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <div>
              <label class="block text-sm font-medium text-gray-900 dark:text-white">
                Unit Kerja
              </label>
              <select
                v-model="form.unit_kerja_id"
                class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                :class="{ 'border-red-500': errors.unit_kerja_id }"
              >
                <option value="">Pilih Unit Kerja</option>
                <option v-for="uk in unitKerjas" :key="uk.id" :value="uk.id">
                  {{ uk.nama }}
                </option>
              </select>
              <p v-if="errors.unit_kerja_id" class="mt-1 text-sm text-red-600">{{ errors.unit_kerja_id[0] }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-900 dark:text-white">
                Jabatan
              </label>
              <select
                v-model="form.jabatan_id"
                class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                :class="{ 'border-red-500': errors.jabatan_id }"
              >
                <option value="">Pilih Jabatan</option>
                <option v-for="jab in jabatans" :key="jab.id" :value="jab.id">
                  {{ jab.nama }}
                </option>
              </select>
              <p v-if="errors.jabatan_id" class="mt-1 text-sm text-red-600">{{ errors.jabatan_id[0] }}</p>
            </div>
          </div>

          <!-- Status Active -->
          <div>
            <label class="flex items-center">
              <input
                v-model="form.is_active"
                type="checkbox"
                class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700"
              />
              <span class="ml-2 text-sm text-gray-900 dark:text-white">Pengguna Aktif</span>
            </label>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
              Centang jika pengguna dapat login dan menggunakan sistem
            </p>
          </div>

          <!-- Roles -->
          <div>
            <label class="block text-sm font-medium text-gray-900 dark:text-white">
              Role
            </label>
            <div class="mt-2 space-y-2">
              <label v-for="role in roles" :key="role.id" class="flex items-center">
                <input
                  type="checkbox"
                  :value="role.name"
                  v-model="form.roles"
                  class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700"
                />
                <span class="ml-2 text-sm text-gray-900 dark:text-white">{{ role.name }}</span>
              </label>
            </div>
            <p v-if="errors.roles" class="mt-1 text-sm text-red-600">{{ errors.roles[0] }}</p>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
              Pilih satu atau lebih role untuk pengguna
            </p>
          </div>

          <!-- Validation Errors Summary -->
          <div v-if="Object.keys(errors).length > 0" class="rounded-lg bg-red-50 p-4 dark:bg-red-900/20">
            <div class="flex">
              <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
              </div>
              <div class="ml-3">
                <h3 class="text-sm font-medium text-red-800 dark:text-red-400">
                  Terdapat {{ Object.keys(errors).length }} kesalahan pada form
                </h3>
              </div>
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="mt-6 flex items-center justify-end gap-3">
          <button
            type="button"
            @click="$router.push('/users')"
            class="rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-4 focus:ring-gray-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700"
          >
            Batal
          </button>
          <button
            type="submit"
            :disabled="loading"
            class="inline-flex items-center rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-300 disabled:cursor-not-allowed disabled:opacity-50"
          >
            <svg v-if="loading" class="mr-2 h-5 w-5 animate-spin" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            {{ isEditMode ? 'Simpan Perubahan' : 'Tambah Pengguna' }}
          </button>
        </div>
      </form>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import MainLayout from '@/layouts/MainLayout.vue';
import { useUserApi } from '@/composables/useUserApi';
import { useMasterDataApi } from '@/composables/useMasterDataApi';

const route = useRoute();
const router = useRouter();

const { getUser, createUser, updateUser, getRoles, loading } = useUserApi();
const { getActiveUnitKerjas, getActiveJabatans } = useMasterDataApi();

const isEditMode = computed(() => !!route.params.id);

const unitKerjas = ref([]);
const jabatans = ref([]);
const roles = ref([]);
const errors = ref({});

const form = ref({
  name: '',
  email: '',
  password: '',
  nip: '',
  nidn: '',
  phone: '',
  address: '',
  unit_kerja_id: '',
  jabatan_id: '',
  is_active: true,
  roles: [],
});

const fetchUnitKerjas = async () => {
  try {
    const response = await getActiveUnitKerjas();
    if (response.success) {
      unitKerjas.value = response.data;
    }
  } catch (error) {
    console.error('Failed to fetch unit kerja:', error);
  }
};

const fetchJabatans = async () => {
  try {
    const response = await getActiveJabatans();
    if (response.success) {
      jabatans.value = response.data;
    }
  } catch (error) {
    console.error('Failed to fetch jabatan:', error);
  }
};

const fetchRoles = async () => {
  try {
    const response = await getRoles();
    if (response.success) {
      roles.value = response.data;
    }
  } catch (error) {
    console.error('Failed to fetch roles:', error);
  }
};

const loadUser = async () => {
  if (!isEditMode.value) return;

  try {
    const response = await getUser(route.params.id);
    if (response.success) {
      const user = response.data;
      form.value = {
        name: user.name || '',
        email: user.email || '',
        password: '',
        nip: user.nip || '',
        nidn: user.nidn || '',
        phone: user.phone || '',
        address: user.address || '',
        unit_kerja_id: user.unit_kerja_id || '',
        jabatan_id: user.jabatan_id || '',
        is_active: user.is_active || false,
        roles: user.roles ? user.roles.map(r => r.name) : [],
      };
    }
  } catch (error) {
    console.error('Failed to load user:', error);
    alert('Gagal memuat data pengguna: ' + (error.response?.data?.message || error.message));
    router.push('/users');
  }
};

const handleSubmit = async () => {
  errors.value = {};

  try {
    const payload = { ...form.value };

    // Convert empty strings to null for optional fields
    if (!payload.nip) payload.nip = null;
    if (!payload.nidn) payload.nidn = null;
    if (!payload.phone) payload.phone = null;
    if (!payload.address) payload.address = null;
    if (!payload.unit_kerja_id) payload.unit_kerja_id = null;
    if (!payload.jabatan_id) payload.jabatan_id = null;

    // Remove password if empty on edit mode
    if (isEditMode.value && !payload.password) {
      delete payload.password;
    }

    let response;
    if (isEditMode.value) {
      response = await updateUser(route.params.id, payload);
    } else {
      response = await createUser(payload);
    }

    if (response.success) {
      alert(isEditMode.value ? 'Pengguna berhasil diperbarui' : 'Pengguna berhasil ditambahkan');
      router.push('/users');
    }
  } catch (error) {
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors;
    } else {
      alert('Gagal menyimpan pengguna: ' + (error.response?.data?.message || error.message));
    }
  }
};

onMounted(() => {
  fetchUnitKerjas();
  fetchJabatans();
  fetchRoles();
  loadUser();
});
</script>
