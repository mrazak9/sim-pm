<template>
  <MainLayout>
    <div class="rounded-lg border border-gray-200 bg-white shadow dark:border-gray-700 dark:bg-gray-800">
      <!-- Header -->
      <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
          {{ isEdit ? 'Edit' : 'Tambah' }} Tahun Akademik
        </h3>
      </div>

      <!-- Form -->
      <form @submit.prevent="submitForm" class="p-6">
        <div class="space-y-6">
          <!-- Tahun -->
          <div>
            <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
              Tahun Akademik <span class="text-red-600">*</span>
            </label>
            <input
              v-model="form.tahun"
              type="text"
              placeholder="Contoh: 2024/2025"
              class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500"
              :class="{ 'border-red-600': errors.tahun }"
              required
            />
            <p v-if="errors.tahun" class="mt-1 text-sm text-red-600">{{ errors.tahun[0] }}</p>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
              Format: YYYY/YYYY (contoh: 2024/2025)
            </p>
          </div>

          <!-- Semester -->
          <div>
            <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
              Semester <span class="text-red-600">*</span>
            </label>
            <select
              v-model="form.semester"
              class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:focus:border-blue-500 dark:focus:ring-blue-500"
              :class="{ 'border-red-600': errors.semester }"
              required
            >
              <option value="">Pilih Semester</option>
              <option value="ganjil">Ganjil</option>
              <option value="genap">Genap</option>
              <option value="pendek">Pendek</option>
            </select>
            <p v-if="errors.semester" class="mt-1 text-sm text-red-600">{{ errors.semester[0] }}</p>
          </div>

          <!-- Row: Tanggal Mulai & Tanggal Selesai -->
          <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <div>
              <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
                Tanggal Mulai <span class="text-red-600">*</span>
              </label>
              <input
                v-model="form.tanggal_mulai"
                type="date"
                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:focus:border-blue-500 dark:focus:ring-blue-500"
                :class="{ 'border-red-600': errors.tanggal_mulai }"
                required
                @change="validateDates"
              />
              <p v-if="errors.tanggal_mulai" class="mt-1 text-sm text-red-600">{{ errors.tanggal_mulai[0] }}</p>
            </div>

            <div>
              <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
                Tanggal Selesai <span class="text-red-600">*</span>
              </label>
              <input
                v-model="form.tanggal_selesai"
                type="date"
                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:focus:border-blue-500 dark:focus:ring-blue-500"
                :class="{ 'border-red-600': errors.tanggal_selesai || validationError }"
                required
                @change="validateDates"
              />
              <p v-if="errors.tanggal_selesai" class="mt-1 text-sm text-red-600">{{ errors.tanggal_selesai[0] }}</p>
              <p v-if="validationError" class="mt-1 text-sm text-red-600">{{ validationError }}</p>
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
              placeholder="Deskripsi tahun akademik..."
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
              Tahun Akademik Aktif
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
              :disabled="loading || !!validationError"
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
const { loading, getTahunAkademik, createTahunAkademik, updateTahunAkademik } = useMasterDataApi();

const isEdit = ref(false);
const tahunAkademikId = ref(null);

const form = ref({
  tahun: '',
  semester: '',
  tanggal_mulai: '',
  tanggal_selesai: '',
  deskripsi: '',
  is_active: true,
});

const errors = ref({});
const errorMessage = ref('');
const validationError = ref('');

const fetchTahunAkademik = async () => {
  try {
    const response = await getTahunAkademik(tahunAkademikId.value);
    const data = response.data;

    form.value = {
      tahun: data.nama_tahun || data.tahun,
      semester: data.semester,
      tanggal_mulai: data.tanggal_mulai,
      tanggal_selesai: data.tanggal_selesai,
      deskripsi: data.deskripsi || '',
      is_active: data.is_active,
    };
    console.log('Tahun Akademik data loaded for edit:', form.value);
  } catch (error) {
    console.error('Failed to fetch tahun akademik:', error);
    errorMessage.value = 'Gagal memuat data tahun akademik';
  }
};

const validateDates = () => {
  validationError.value = '';

  if (form.value.tanggal_mulai && form.value.tanggal_selesai) {
    const startDate = new Date(form.value.tanggal_mulai);
    const endDate = new Date(form.value.tanggal_selesai);

    if (endDate <= startDate) {
      validationError.value = 'Tanggal selesai harus setelah tanggal mulai';
    }
  }
};

const submitForm = async () => {
  errorMessage.value = '';
  errors.value = {};

  // Validate dates before submitting
  validateDates();
  if (validationError.value) {
    return;
  }

  try {
    if (isEdit.value) {
      await updateTahunAkademik(tahunAkademikId.value, form.value);
    } else {
      await createTahunAkademik(form.value);
    }

    router.push({ name: 'tahun-akademik-list' });
  } catch (error) {
    console.error('Failed to save tahun akademik:', error);
    errorMessage.value = error.response?.data?.message || 'Gagal menyimpan tahun akademik';

    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors;
    }
  }
};

const cancel = () => {
  router.push({ name: 'tahun-akademik-list' });
};

onMounted(() => {
  if (route.params.id) {
    isEdit.value = true;
    tahunAkademikId.value = route.params.id;
    fetchTahunAkademik();
  }
});
</script>
