<template>
  <MainLayout>
    <div class="rounded-lg border border-gray-200 bg-white shadow dark:border-gray-700 dark:bg-gray-800">
      <!-- Header -->
      <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
          {{ isEditMode ? 'Edit Kuesioner' : 'Buat Kuesioner Baru' }}
        </h3>
      </div>

      <!-- Form -->
      <form @submit.prevent="handleSubmit" class="p-6">
        <div class="space-y-6">
          <!-- Title -->
          <div>
            <label for="title" class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
              Judul Kuesioner <span class="text-red-500">*</span>
            </label>
            <input
              type="text"
              id="title"
              v-model="formData.title"
              class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400"
              :class="{ 'border-red-500': validationErrors.title }"
              placeholder="Masukkan judul kuesioner"
              required
            />
            <p v-if="validationErrors.title" class="mt-1 text-sm text-red-600 dark:text-red-400">
              {{ validationErrors.title[0] }}
            </p>
          </div>

          <!-- Description -->
          <div>
            <label for="description" class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
              Deskripsi
            </label>
            <textarea
              id="description"
              v-model="formData.description"
              rows="4"
              class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400"
              :class="{ 'border-red-500': validationErrors.description }"
              placeholder="Masukkan deskripsi kuesioner (opsional)"
            ></textarea>
            <p v-if="validationErrors.description" class="mt-1 text-sm text-red-600 dark:text-red-400">
              {{ validationErrors.description[0] }}
            </p>
          </div>

          <!-- Type and Unit Kerja -->
          <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
              <label for="type" class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
                Tipe <span class="text-red-500">*</span>
              </label>
              <select
                id="type"
                v-model="formData.type"
                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                :class="{ 'border-red-500': validationErrors.type }"
                required
              >
                <option value="">Pilih Tipe</option>
                <option value="internal">Internal</option>
                <option value="external">Eksternal</option>
                <option value="public">Publik</option>
              </select>
              <p v-if="validationErrors.type" class="mt-1 text-sm text-red-600 dark:text-red-400">
                {{ validationErrors.type[0] }}
              </p>
            </div>

            <div>
              <label for="unit_kerja_id" class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
                Unit Kerja
              </label>
              <select
                id="unit_kerja_id"
                v-model="formData.unit_kerja_id"
                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                :class="{ 'border-red-500': validationErrors.unit_kerja_id }"
              >
                <option value="">Pilih Unit Kerja (Opsional)</option>
                <option v-for="uk in unitKerjas" :key="uk.id" :value="uk.id">{{ uk.nama }}</option>
              </select>
              <p v-if="validationErrors.unit_kerja_id" class="mt-1 text-sm text-red-600 dark:text-red-400">
                {{ validationErrors.unit_kerja_id[0] }}
              </p>
            </div>
          </div>

          <!-- Start Date and End Date -->
          <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
              <label for="start_date" class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
                Tanggal Mulai
              </label>
              <input
                type="date"
                id="start_date"
                v-model="formData.start_date"
                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                :class="{ 'border-red-500': validationErrors.start_date }"
              />
              <p v-if="validationErrors.start_date" class="mt-1 text-sm text-red-600 dark:text-red-400">
                {{ validationErrors.start_date[0] }}
              </p>
            </div>

            <div>
              <label for="end_date" class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
                Tanggal Selesai
              </label>
              <input
                type="date"
                id="end_date"
                v-model="formData.end_date"
                :min="formData.start_date"
                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                :class="{ 'border-red-500': validationErrors.end_date }"
              />
              <p v-if="validationErrors.end_date" class="mt-1 text-sm text-red-600 dark:text-red-400">
                {{ validationErrors.end_date[0] }}
              </p>
            </div>
          </div>

          <!-- Settings -->
          <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
            <h4 class="mb-4 text-sm font-semibold text-gray-900 dark:text-white">Pengaturan</h4>

            <div class="space-y-3">
              <div class="flex items-center">
                <input
                  type="checkbox"
                  id="is_anonymous"
                  v-model="formData.is_anonymous"
                  class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700"
                />
                <label for="is_anonymous" class="ml-2 text-sm text-gray-900 dark:text-white">
                  Anonim (Responden tidak perlu login/identitas)
                </label>
              </div>

              <div class="flex items-center">
                <input
                  type="checkbox"
                  id="allow_multiple_responses"
                  v-model="formData.allow_multiple_responses"
                  class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700"
                />
                <label for="allow_multiple_responses" class="ml-2 text-sm text-gray-900 dark:text-white">
                  Izinkan Responden Mengisi Lebih dari Sekali
                </label>
              </div>

              <div class="flex items-center">
                <input
                  type="checkbox"
                  id="require_login"
                  v-model="formData.require_login"
                  class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700"
                />
                <label for="require_login" class="ml-2 text-sm text-gray-900 dark:text-white">
                  Memerlukan Login
                </label>
              </div>

              <div class="flex items-center">
                <input
                  type="checkbox"
                  id="show_results"
                  v-model="formData.show_results"
                  class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700"
                />
                <label for="show_results" class="ml-2 text-sm text-gray-900 dark:text-white">
                  Tampilkan Hasil kepada Responden
                </label>
              </div>

              <div>
                <label for="max_responses" class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
                  Maksimal Respons (Opsional)
                </label>
                <input
                  type="number"
                  id="max_responses"
                  v-model="formData.max_responses"
                  min="1"
                  class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white md:w-1/2"
                  :class="{ 'border-red-500': validationErrors.max_responses }"
                  placeholder="Kosongkan jika tidak ada batas"
                />
                <p v-if="validationErrors.max_responses" class="mt-1 text-sm text-red-600 dark:text-red-400">
                  {{ validationErrors.max_responses[0] }}
                </p>
              </div>
            </div>
          </div>

          <!-- Validation Errors Summary -->
          <div v-if="Object.keys(validationErrors).length > 0" class="rounded-lg bg-red-50 p-4 dark:bg-red-900/20">
            <div class="flex">
              <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
              </div>
              <div class="ml-3">
                <h3 class="text-sm font-medium text-red-800 dark:text-red-400">
                  Terdapat {{ Object.keys(validationErrors).length }} kesalahan validasi
                </h3>
              </div>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="flex justify-end gap-3 border-t border-gray-200 pt-6 dark:border-gray-700">
            <button
              type="button"
              @click="$router.push('/surveys')"
              class="rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-4 focus:ring-gray-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700"
            >
              Batal
            </button>
            <button
              type="submit"
              :disabled="loading"
              class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-300 disabled:cursor-not-allowed disabled:opacity-50"
            >
              <span v-if="loading" class="flex items-center">
                <svg class="mr-2 h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Menyimpan...
              </span>
              <span v-else>{{ isEditMode ? 'Update' : 'Simpan' }}</span>
            </button>
          </div>
        </div>
      </form>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import MainLayout from '@/layouts/MainLayout.vue';
import { useSurveyApi } from '@/composables/useSurveyApi';
import { useMasterDataApi } from '@/composables/useMasterDataApi';

const router = useRouter();
const route = useRoute();

const { getSurvey, createSurvey, updateSurvey, loading } = useSurveyApi();
const { getActiveUnitKerjas } = useMasterDataApi();

const isEditMode = computed(() => !!route.params.id);

const formData = ref({
  title: '',
  description: '',
  type: '',
  start_date: '',
  end_date: '',
  unit_kerja_id: '',
  is_anonymous: false,
  allow_multiple_responses: false,
  require_login: false,
  show_results: false,
  max_responses: null,
});

const validationErrors = ref({});
const unitKerjas = ref([]);

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

const fetchSurvey = async () => {
  try {
    const response = await getSurvey(route.params.id);
    if (response.success) {
      const survey = response.data;
      formData.value = {
        title: survey.title || '',
        description: survey.description || '',
        type: survey.type || '',
        start_date: survey.start_date || '',
        end_date: survey.end_date || '',
        unit_kerja_id: survey.unit_kerja_id || '',
        is_anonymous: survey.is_anonymous || false,
        allow_multiple_responses: survey.allow_multiple_responses || false,
        require_login: survey.require_login || false,
        show_results: survey.show_results || false,
        max_responses: survey.max_responses || null,
      };
    }
  } catch (error) {
    console.error('Failed to fetch survey:', error);
    alert('Gagal memuat data kuesioner: ' + (error.response?.data?.message || error.message));
    router.push('/surveys');
  }
};

const handleSubmit = async () => {
  validationErrors.value = {};

  // Client-side validation
  if (!formData.value.title) {
    validationErrors.value.title = ['Judul kuesioner wajib diisi'];
    return;
  }

  if (!formData.value.type) {
    validationErrors.value.type = ['Tipe kuesioner wajib dipilih'];
    return;
  }

  if (formData.value.end_date && formData.value.start_date) {
    if (new Date(formData.value.end_date) < new Date(formData.value.start_date)) {
      validationErrors.value.end_date = ['Tanggal selesai harus setelah tanggal mulai'];
      return;
    }
  }

  try {
    const submitData = { ...formData.value };

    // Clean up empty values
    if (!submitData.unit_kerja_id) delete submitData.unit_kerja_id;
    if (!submitData.start_date) delete submitData.start_date;
    if (!submitData.end_date) delete submitData.end_date;
    if (!submitData.max_responses) delete submitData.max_responses;

    let response;
    if (isEditMode.value) {
      response = await updateSurvey(route.params.id, submitData);
    } else {
      response = await createSurvey(submitData);
    }

    if (response.success) {
      alert(isEditMode.value ? 'Kuesioner berhasil diupdate' : 'Kuesioner berhasil dibuat');
      router.push('/surveys');
    }
  } catch (error) {
    console.error('Failed to save survey:', error);

    if (error.response?.data?.errors) {
      validationErrors.value = error.response.data.errors;
    } else {
      alert('Gagal menyimpan kuesioner: ' + (error.response?.data?.message || error.message));
    }
  }
};

onMounted(() => {
  fetchUnitKerjas();
  if (isEditMode.value) {
    fetchSurvey();
  }
});
</script>
