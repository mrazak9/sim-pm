<template>
  <MainLayout>
    <div class="rounded-lg border border-gray-200 bg-white shadow dark:border-gray-700 dark:bg-gray-800">
      <!-- Header -->
      <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
        <div class="flex items-center justify-between">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
            {{ isEditMode ? 'Edit Indikator' : 'Tambah Indikator' }}
          </h3>
          <button
            @click="$router.push('/spmi/indicators')"
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
          <!-- Standard & Code -->
          <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <div>
              <label class="block text-sm font-medium text-gray-900 dark:text-white">
                Standar SPMI <span class="text-red-600">*</span>
              </label>
              <select
                v-model="form.spmi_standard_id"
                required
                class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              >
                <option value="">Pilih Standar</option>
                <option v-for="standard in standards" :key="standard.id" :value="standard.id">
                  {{ standard.code }} - {{ standard.name }}
                </option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-900 dark:text-white">
                Kode Indikator <span class="text-red-600">*</span>
              </label>
              <input
                v-model="form.code"
                type="text"
                required
                class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                placeholder="Contoh: IND-01"
              />
            </div>
          </div>

          <!-- Name -->
          <div>
            <label class="block text-sm font-medium text-gray-900 dark:text-white">
              Nama Indikator <span class="text-red-600">*</span>
            </label>
            <input
              v-model="form.name"
              type="text"
              required
              class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              placeholder="Masukkan nama indikator"
            />
          </div>

          <!-- Description -->
          <div>
            <label class="block text-sm font-medium text-gray-900 dark:text-white">
              Deskripsi
            </label>
            <textarea
              v-model="form.description"
              rows="3"
              class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              placeholder="Masukkan deskripsi indikator"
            ></textarea>
          </div>

          <!-- Measurement Type & Unit -->
          <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <div>
              <label class="block text-sm font-medium text-gray-900 dark:text-white">
                Tipe Pengukuran <span class="text-red-600">*</span>
              </label>
              <select
                v-model="form.measurement_type"
                required
                class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              >
                <option value="">Pilih Tipe Pengukuran</option>
                <option value="kuantitatif">Kuantitatif</option>
                <option value="kualitatif">Kualitatif</option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-900 dark:text-white">
                Satuan Pengukuran <span class="text-red-600">*</span>
              </label>
              <input
                v-model="form.measurement_unit"
                type="text"
                required
                class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                placeholder="Contoh: %, jumlah, dll"
              />
            </div>
          </div>

          <!-- Formula -->
          <div>
            <label class="block text-sm font-medium text-gray-900 dark:text-white">
              Formula Perhitungan
            </label>
            <textarea
              v-model="form.formula"
              rows="3"
              class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              placeholder="Masukkan formula perhitungan indikator"
            ></textarea>
          </div>

          <!-- Data Source -->
          <div>
            <label class="block text-sm font-medium text-gray-900 dark:text-white">
              Sumber Data
            </label>
            <textarea
              v-model="form.data_source"
              rows="3"
              class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              placeholder="Masukkan sumber data indikator"
            ></textarea>
          </div>

          <!-- Frequency & PIC -->
          <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <div>
              <label class="block text-sm font-medium text-gray-900 dark:text-white">
                Frekuensi Pengukuran <span class="text-red-600">*</span>
              </label>
              <select
                v-model="form.frequency"
                required
                class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              >
                <option value="">Pilih Frekuensi</option>
                <option value="harian">Harian</option>
                <option value="mingguan">Mingguan</option>
                <option value="bulanan">Bulanan</option>
                <option value="semesteran">Semesteran</option>
                <option value="tahunan">Tahunan</option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-900 dark:text-white">
                Penanggung Jawab (PIC)
              </label>
              <UserSelect
                v-model="form.pic_id"
                :multiple="false"
                placeholder="Pilih PIC"
                class="mt-1"
              />
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="mt-6 flex items-center justify-end gap-3">
          <button
            type="button"
            @click="$router.push('/spmi/indicators')"
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
            {{ isEditMode ? 'Simpan Perubahan' : 'Buat Indikator' }}
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
import UserSelect from '@/components/common/UserSelect.vue';
import { useSPMIApi } from '@/composables/useSPMIApi';
import { useToast } from '@/composables/useToast';

const route = useRoute();
const router = useRouter();
const { success, error } = useToast();

const { getSPMIIndicator, createSPMIIndicator, updateSPMIIndicator, getSPMIStandards, loading } = useSPMIApi();

const isEditMode = computed(() => !!route.params.id);

const standards = ref([]);

const form = ref({
  spmi_standard_id: '',
  code: '',
  name: '',
  description: '',
  measurement_unit: '',
  measurement_type: '',
  formula: '',
  data_source: '',
  frequency: '',
  pic_id: '',
});

const fetchStandards = async () => {
  try {
    const response = await getSPMIStandards({ status: 'active', per_page: 100 });
    if (response.success) {
      standards.value = response.data;
    }
  } catch (err) {
    console.error('Failed to fetch standards:', err);
    error('Gagal memuat data standar SPMI');
  }
};

const fetchIndicator = async () => {
  try {
    const response = await getSPMIIndicator(route.params.id);
    if (response.success) {
      const indicator = response.data;
      form.value = {
        spmi_standard_id: indicator.spmi_standard_id,
        code: indicator.code,
        name: indicator.name,
        description: indicator.description || '',
        measurement_unit: indicator.measurement_unit,
        measurement_type: indicator.measurement_type,
        formula: indicator.formula || '',
        data_source: indicator.data_source || '',
        frequency: indicator.frequency,
        pic_id: indicator.pic_id || '',
      };
    }
  } catch (err) {
    console.error('Failed to fetch indicator:', err);
    error('Gagal memuat data indikator');
    router.push('/spmi/indicators');
  }
};

const handleSubmit = async () => {
  try {
    const response = isEditMode.value
      ? await updateSPMIIndicator(route.params.id, form.value)
      : await createSPMIIndicator(form.value);

    if (response.success) {
      success(isEditMode.value ? 'Indikator berhasil diperbarui' : 'Indikator berhasil dibuat');
      router.push('/spmi/indicators');
    }
  } catch (err) {
    console.error('Failed to save indicator:', err);
    error('Gagal menyimpan indikator: ' + (err.response?.data?.message || err.message));
  }
};

onMounted(() => {
  fetchStandards();
  if (isEditMode.value) {
    fetchIndicator();
  }
});
</script>
