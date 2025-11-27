<template>
  <MainLayout>
    <div class="rounded-lg border border-gray-200 bg-white shadow dark:border-gray-700 dark:bg-gray-800">
      <!-- Header -->
      <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
          {{ isEdit ? 'Edit' : 'Tambah' }} Program Studi
        </h3>
      </div>

      <!-- Form -->
      <form @submit.prevent="submitForm" class="p-6">
        <div class="space-y-6">
          <!-- Row: Kode & Nama Singkat -->
          <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <div>
              <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
                Kode Program Studi <span class="text-red-600">*</span>
              </label>
              <input
                v-model="form.kode_prodi"
                type="text"
                placeholder="Contoh: PRODI-001"
                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                :class="{ 'border-red-600': errors.kode_prodi }"
                required
              />
              <p v-if="errors.kode_prodi" class="mt-1 text-sm text-red-600">{{ errors.kode_prodi[0] }}</p>
            </div>
            <div>
              <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
                Nama Singkat
              </label>
              <input
                v-model="form.nama_singkat"
                type="text"
                placeholder="Singkatan prodi"
                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              />
            </div>
          </div>

          <!-- Nama Prodi -->
          <div>
            <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
              Nama Program Studi <span class="text-red-600">*</span>
            </label>
            <input
              v-model="form.nama_prodi"
              type="text"
              placeholder="Nama lengkap program studi"
              class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              :class="{ 'border-red-600': errors.nama_prodi }"
              required
            />
            <p v-if="errors.nama_prodi" class="mt-1 text-sm text-red-600">{{ errors.nama_prodi[0] }}</p>
          </div>

          <!-- Row: Jenjang & Unit Kerja -->
          <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <div>
              <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
                Jenjang <span class="text-red-600">*</span>
              </label>
              <select
                v-model="form.jenjang"
                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                :class="{ 'border-red-600': errors.jenjang }"
                required
              >
                <option value="">Pilih Jenjang</option>
                <option value="D3">D3</option>
                <option value="D4">D4</option>
                <option value="S1">S1</option>
                <option value="S2">S2</option>
                <option value="S3">S3</option>
                <option value="Profesi">Profesi</option>
                <option value="Spesialis">Spesialis</option>
              </select>
              <p v-if="errors.jenjang" class="mt-1 text-sm text-red-600">{{ errors.jenjang[0] }}</p>
            </div>
            <div>
              <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
                Unit Kerja <span class="text-red-600">*</span>
              </label>
              <select
                v-model="form.unit_kerja_id"
                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                :class="{ 'border-red-600': errors.unit_kerja_id }"
                required
              >
                <option :value="null">Pilih Unit Kerja</option>
                <option v-for="uk in unitKerjaOptions" :key="uk.id" :value="uk.id">
                  {{ uk.nama_unit }}
                </option>
              </select>
              <p v-if="errors.unit_kerja_id" class="mt-1 text-sm text-red-600">{{ errors.unit_kerja_id[0] }}</p>
            </div>
          </div>

          <!-- Row: Akreditasi & Tanggal Akreditasi -->
          <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <div>
              <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
                Akreditasi
              </label>
              <select
                v-model="form.akreditasi"
                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                :class="{ 'border-red-600': errors.akreditasi }"
              >
                <option value="">Pilih Akreditasi</option>
                <option value="Unggul">Unggul</option>
                <option value="Baik Sekali">Baik Sekali</option>
                <option value="Baik">Baik</option>
                <option value="Tidak Terakreditasi">Tidak Terakreditasi</option>
              </select>
              <p v-if="errors.akreditasi" class="mt-1 text-sm text-red-600">{{ errors.akreditasi[0] }}</p>
            </div>
            <div>
              <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
                Tanggal Akreditasi
              </label>
              <input
                v-model="form.tanggal_akreditasi"
                type="date"
                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              />
            </div>
          </div>

          <!-- Row: SK Pendirian & Tanggal SK -->
          <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <div>
              <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
                Nomor SK Pendirian
              </label>
              <input
                v-model="form.sk_pendirian"
                type="text"
                placeholder="Nomor SK pendirian program studi"
                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              />
            </div>
            <div>
              <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
                Tanggal SK Pendirian
              </label>
              <input
                v-model="form.tanggal_sk"
                type="date"
                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              />
            </div>
          </div>

          <!-- Row: Gelar & Deskripsi -->
          <div>
            <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
              Gelar Lulusan
            </label>
            <input
              v-model="form.gelar"
              type="text"
              placeholder="Contoh: S.Kom., S.T., M.Kom."
              class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
            />
          </div>

          <div>
            <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
              Deskripsi
            </label>
            <textarea
              v-model="form.deskripsi"
              rows="4"
              placeholder="Deskripsi program studi..."
              class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
            ></textarea>
          </div>

          <!-- Status Aktif -->
          <div class="flex items-center">
            <input
              v-model="form.is_active"
              type="checkbox"
              id="is_active"
              class="h-4 w-4 rounded border-gray-300 bg-gray-100 text-blue-600 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700"
            />
            <label for="is_active" class="ml-2 text-sm font-medium text-gray-900 dark:text-white">
              Program Studi Aktif
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
              class="rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-100 focus:outline-none focus:ring-4 focus:ring-gray-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400"
            >
              Batal
            </button>
            <button
              type="submit"
              :disabled="loading"
              class="inline-flex items-center rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-300 disabled:opacity-50"
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
const { loading, getProgramStudi, createProgramStudi, updateProgramStudi, getUnitKerjas } = useMasterDataApi();

const isEdit = ref(false);
const programStudiId = ref(null);
const unitKerjaOptions = ref([]);

const form = ref({
  kode_prodi: '',
  nama_prodi: '',
  nama_singkat: '',
  jenjang: '',
  unit_kerja_id: null,
  akreditasi: '',
  tanggal_akreditasi: '',
  sk_pendirian: '',
  tanggal_sk: '',
  gelar: '',
  deskripsi: '',
  is_active: true,
});

const errors = ref({});
const errorMessage = ref('');

const fetchProgramStudi = async () => {
  try {
    const response = await getProgramStudi(programStudiId.value);
    const data = response.data;

    form.value = {
      kode_prodi: data.kode_prodi,
      nama_prodi: data.nama_prodi,
      nama_singkat: data.nama_singkat || '',
      jenjang: data.jenjang,
      unit_kerja_id: data.unit_kerja_id,
      akreditasi: data.akreditasi || '',
      tanggal_akreditasi: data.tanggal_akreditasi || '',
      sk_pendirian: data.sk_pendirian || '',
      tanggal_sk: data.tanggal_sk || '',
      gelar: data.gelar || '',
      deskripsi: data.deskripsi || '',
      is_active: data.is_active,
    };
  } catch (error) {
    console.error('Failed to fetch program studi:', error);
    errorMessage.value = 'Gagal memuat data program studi';
  }
};

const fetchUnitKerjaOptions = async () => {
  try {
    const response = await getUnitKerjas({ per_page: 100, is_active: 1 });
    // response is already response.data from composable
    unitKerjaOptions.value = response.data || [];
    console.log('Unit Kerja options loaded:', unitKerjaOptions.value.length);
  } catch (error) {
    console.error('Failed to fetch unit kerja options:', error);
  }
};

const submitForm = async () => {
  errorMessage.value = '';
  errors.value = {};

  try {
    if (isEdit.value) {
      await updateProgramStudi(programStudiId.value, form.value);
    } else {
      await createProgramStudi(form.value);
    }

    router.push({ name: 'program-studi-list' });
  } catch (error) {
    console.error('Failed to save program studi:', error);
    errorMessage.value = error.response?.data?.message || 'Gagal menyimpan program studi';

    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors;
    }
  }
};

const cancel = () => {
  router.push({ name: 'program-studi-list' });
};

onMounted(() => {
  fetchUnitKerjaOptions();

  if (route.params.id) {
    isEdit.value = true;
    programStudiId.value = route.params.id;
    fetchProgramStudi();
  }
});
</script>
