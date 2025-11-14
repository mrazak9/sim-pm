<template>
  <MainLayout>
    <div class="rounded-lg border border-gray-200 bg-white shadow dark:border-gray-700 dark:bg-gray-800">
      <!-- Header -->
      <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
          {{ isEdit ? 'Edit' : 'Tambah' }} Jabatan
        </h3>
      </div>

      <!-- Form -->
      <form @submit.prevent="submitForm" class="p-6">
        <div class="space-y-6">
          <!-- Kode Jabatan -->
          <div>
            <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
              Kode Jabatan <span class="text-red-600">*</span>
            </label>
            <input
              v-model="form.kode_jabatan"
              type="text"
              placeholder="Contoh: JBT-001"
              class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500"
              :class="{ 'border-red-600': errors.kode_jabatan }"
              required
            />
            <p v-if="errors.kode_jabatan" class="mt-1 text-sm text-red-600">{{ errors.kode_jabatan[0] }}</p>
          </div>

          <!-- Nama Jabatan -->
          <div>
            <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
              Nama Jabatan <span class="text-red-600">*</span>
            </label>
            <input
              v-model="form.nama_jabatan"
              type="text"
              placeholder="Nama lengkap jabatan"
              class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500"
              :class="{ 'border-red-600': errors.nama_jabatan }"
              required
            />
            <p v-if="errors.nama_jabatan" class="mt-1 text-sm text-red-600">{{ errors.nama_jabatan[0] }}</p>
          </div>

          <!-- Row: Kategori & Level -->
          <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <div>
              <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
                Kategori <span class="text-red-600">*</span>
              </label>
              <select
                v-model="form.kategori"
                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:focus:border-blue-500 dark:focus:ring-blue-500"
                :class="{ 'border-red-600': errors.kategori }"
                required
              >
                <option value="">Pilih Kategori</option>
                <option value="struktural">Struktural</option>
                <option value="fungsional">Fungsional</option>
                <option value="teknis">Teknis</option>
              </select>
              <p v-if="errors.kategori" class="mt-1 text-sm text-red-600">{{ errors.kategori[0] }}</p>
            </div>

            <div>
              <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
                Level <span class="text-red-600">*</span>
              </label>
              <select
                v-model="form.level"
                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:focus:border-blue-500 dark:focus:ring-blue-500"
                :class="{ 'border-red-600': errors.level }"
                required
              >
                <option value="">Pilih Level</option>
                <option value="1">Level 1</option>
                <option value="2">Level 2</option>
                <option value="3">Level 3</option>
                <option value="4">Level 4</option>
                <option value="5">Level 5</option>
              </select>
              <p v-if="errors.level" class="mt-1 text-sm text-red-600">{{ errors.level[0] }}</p>
              <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                Level 1 = tertinggi, Level 5 = terendah
              </p>
            </div>
          </div>

          <!-- Deskripsi -->
          <div>
            <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
              Deskripsi
            </label>
            <textarea
              v-model="form.deskripsi"
              rows="4"
              placeholder="Deskripsi jabatan, tugas, dan tanggung jawab..."
              class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500"
            ></textarea>
          </div>

          <!-- Status Aktif -->
          <div class="flex items-center">
            <input
              v-model="form.is_active"
              type="checkbox"
              id="is_active"
              class="h-4 w-4 rounded border-gray-300 bg-gray-100 text-blue-600 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:ring-offset-gray-800 dark:focus:ring-blue-600"
            />
            <label for="is_active" class="ml-2 text-sm font-medium text-gray-900 dark:text-white">
              Jabatan Aktif
            </label>
          </div>

          <!-- Error Alert -->
          <div v-if="errorMessage" class="rounded-lg border border-red-300 bg-red-50 p-4 dark:border-red-800 dark:bg-gray-800">
            <p class="text-sm text-red-800 dark:text-red-400">{{ errorMessage }}</p>
          </div>

          <!-- Buttons -->
          <div class="flex justify-end gap-3">
            <button
              @click="cancel"
              type="button"
              class="rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-100 focus:outline-none focus:ring-4 focus:ring-gray-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white dark:focus:ring-gray-700"
            >
              Batal
            </button>
            <button
              type="submit"
              :disabled="loading"
              class="inline-flex items-center rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-300 disabled:opacity-50 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800"
            >
              <svg v-if="loading" class="mr-2 h-5 w-5 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              {{ loading ? 'Menyimpan...' : 'Simpan' }}
            </button>
          </div>
        </div>
      </form>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import MainLayout from '@/layouts/MainLayout.vue';
import { useMasterDataApi } from '@/composables/useMasterDataApi';

const router = useRouter();
const route = useRoute();
const { loading, getJabatan, createJabatan, updateJabatan } = useMasterDataApi();

const isEdit = ref(false);
const jabatanId = ref(null);

const form = ref({
  kode_jabatan: '',
  nama_jabatan: '',
  kategori: '',
  level: '',
  deskripsi: '',
  is_active: true,
});

const errors = ref({});
const errorMessage = ref('');

const fetchJabatan = async () => {
  try {
    const response = await getJabatan(jabatanId.value);
    const data = response.data;

    form.value = {
      kode_jabatan: data.kode_jabatan,
      nama_jabatan: data.nama_jabatan,
      kategori: data.kategori,
      level: data.level,
      deskripsi: data.deskripsi || '',
      is_active: data.is_active,
    };
  } catch (error) {
    console.error('Failed to fetch jabatan:', error);
    errorMessage.value = 'Gagal memuat data jabatan';
  }
};

const submitForm = async () => {
  errorMessage.value = '';
  errors.value = {};

  try {
    if (isEdit.value) {
      await updateJabatan(jabatanId.value, form.value);
    } else {
      await createJabatan(form.value);
    }

    router.push({ name: 'jabatan-list' });
  } catch (error) {
    console.error('Failed to save jabatan:', error);
    errorMessage.value = error.response?.data?.message || 'Gagal menyimpan jabatan';

    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors;
    }
  }
};

const cancel = () => {
  router.push({ name: 'jabatan-list' });
};

onMounted(() => {
  if (route.params.id) {
    isEdit.value = true;
    jabatanId.value = route.params.id;
    fetchJabatan();
  }
});
</script>
