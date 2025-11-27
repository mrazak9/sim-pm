<template>
  <MainLayout>
    <div class="rounded-lg border border-gray-200 bg-white shadow dark:border-gray-700 dark:bg-gray-800">
      <!-- Header -->
      <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
          {{ isEdit ? 'Edit' : 'Tambah' }} Unit Kerja
        </h3>
      </div>

      <!-- Form -->
      <form @submit.prevent="submitForm" class="p-6">
        <div class="space-y-6">
          <!-- Kode -->
          <div>
            <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
              Kode Unit Kerja <span class="text-red-600">*</span>
            </label>
            <input
              v-model="form.kode"
              type="text"
              placeholder="Contoh: UK-001"
              class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500"
              :class="{ 'border-red-600': errors.kode || errors.kode_unit }"
              required
            />
            <p v-if="errors.kode || errors.kode_unit" class="mt-1 text-sm text-red-600">
              {{ errors.kode?.[0] || errors.kode_unit?.[0] }}
            </p>
          </div>

          <!-- Nama -->
          <div>
            <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
              Nama Unit Kerja <span class="text-red-600">*</span>
            </label>
            <input
              v-model="form.nama"
              type="text"
              placeholder="Nama lengkap unit kerja"
              class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500"
              :class="{ 'border-red-600': errors.nama || errors.nama_unit }"
              required
            />
            <p v-if="errors.nama || errors.nama_unit" class="mt-1 text-sm text-red-600">
              {{ errors.nama?.[0] || errors.nama_unit?.[0] }}
            </p>
          </div>

          <!-- Nama Singkat -->
          <div>
            <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
              Nama Singkat
            </label>
            <input
              v-model="form.nama_singkat"
              type="text"
              placeholder="Singkatan atau akronim"
              class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500"
            />
          </div>

          <!-- Row: Jenis & Parent -->
          <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <div>
              <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
                Jenis Unit Kerja <span class="text-red-600">*</span>
              </label>
              <select
                v-model="form.jenis"
                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:focus:border-blue-500 dark:focus:ring-blue-500"
                :class="{ 'border-red-600': errors.jenis || errors.jenis_unit }"
                required
              >
                <option value="">Pilih Jenis</option>
                <option value="fakultas">Fakultas</option>
                <option value="program_studi">Program Studi</option>
                <option value="lembaga">Lembaga</option>
                <option value="unit_pendukung">Unit Pendukung</option>
              </select>
              <p v-if="errors.jenis || errors.jenis_unit" class="mt-1 text-sm text-red-600">
                {{ errors.jenis?.[0] || errors.jenis_unit?.[0] }}
              </p>
            </div>

            <div>
              <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
                Unit Kerja Parent
              </label>
              <select
                v-model="form.parent_id"
                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:focus:border-blue-500 dark:focus:ring-blue-500"
              >
                <option :value="null">Tidak ada parent</option>
                <option v-for="uk in parentOptions" :key="uk.id" :value="uk.id">
                  {{ uk.nama_unit || uk.nama }}
                </option>
              </select>
              <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                Pilih unit kerja parent jika ada hierarki
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
              placeholder="Deskripsi unit kerja..."
              class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500"
            ></textarea>
          </div>

          <!-- Row: Email & Telepon -->
          <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <div>
              <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
                Email
              </label>
              <input
                v-model="form.email"
                type="email"
                placeholder="email@example.com"
                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500"
                :class="{ 'border-red-600': errors.email }"
              />
              <p v-if="errors.email" class="mt-1 text-sm text-red-600">{{ errors.email[0] }}</p>
            </div>

            <div>
              <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
                Telepon
              </label>
              <input
                v-model="form.telepon"
                type="text"
                placeholder="Nomor telepon"
                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500"
              />
            </div>
          </div>

          <!-- Alamat -->
          <div>
            <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
              Alamat
            </label>
            <textarea
              v-model="form.alamat"
              rows="3"
              placeholder="Alamat lengkap unit kerja..."
              class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500"
            ></textarea>
          </div>

          <!-- Website -->
          <div>
            <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
              Website
            </label>
            <input
              v-model="form.website"
              type="url"
              placeholder="https://example.com"
              class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500"
              :class="{ 'border-red-600': errors.website }"
            />
            <p v-if="errors.website" class="mt-1 text-sm text-red-600">{{ errors.website[0] }}</p>
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
              Unit Kerja Aktif
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
const { loading, getUnitKerja, getUnitKerjas, createUnitKerja, updateUnitKerja } = useMasterDataApi();

const isEdit = ref(false);
const unitKerjaId = ref(null);
const parentOptions = ref([]);

const form = ref({
  kode: '',
  nama: '',
  nama_singkat: '',
  jenis: '',
  parent_id: null,
  deskripsi: '',
  email: '',
  telepon: '',
  alamat: '',
  website: '',
  is_active: true,
});

const errors = ref({});
const errorMessage = ref('');

const fetchUnitKerja = async () => {
  try {
    const response = await getUnitKerja(unitKerjaId.value);
    const data = response.data;

    form.value = {
      kode: data.kode_unit || data.kode,
      nama: data.nama_unit || data.nama,
      nama_singkat: data.nama_singkat || '',
      jenis: data.jenis_unit || data.jenis,
      parent_id: data.parent_id,
      deskripsi: data.deskripsi || '',
      email: data.email || '',
      telepon: data.telepon || '',
      alamat: data.alamat || '',
      website: data.website || '',
      is_active: data.is_active,
    };
    console.log('Unit Kerja data loaded for edit:', form.value);
  } catch (error) {
    console.error('Failed to fetch unit kerja:', error);
    errorMessage.value = 'Gagal memuat data unit kerja';
  }
};

const fetchParentOptions = async () => {
  try {
    const response = await getUnitKerjas({ per_page: 100, is_active: 1 });
    // response is already response.data from composable
    const allUnits = response.data || [];
    // Filter out current item when editing to prevent circular reference
    parentOptions.value = allUnits.filter(uk => uk.id !== unitKerjaId.value);
    console.log('Parent options loaded:', parentOptions.value.length);
  } catch (error) {
    console.error('Failed to fetch parent options:', error);
  }
};

const submitForm = async () => {
  errorMessage.value = '';
  errors.value = {};

  try {
    // Transform form data to match API expectations
    const payload = {
      kode_unit: form.value.kode,
      nama_unit: form.value.nama,
      nama_singkat: form.value.nama_singkat,
      jenis_unit: form.value.jenis,
      parent_id: form.value.parent_id,
      deskripsi: form.value.deskripsi,
      email: form.value.email,
      telepon: form.value.telepon,
      alamat: form.value.alamat,
      website: form.value.website,
      is_active: form.value.is_active,
    };

    if (isEdit.value) {
      await updateUnitKerja(unitKerjaId.value, payload);
    } else {
      await createUnitKerja(payload);
    }

    router.push({ name: 'unit-kerja-list' });
  } catch (error) {
    console.error('Failed to save unit kerja:', error);
    errorMessage.value = error.response?.data?.message || 'Gagal menyimpan unit kerja';

    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors;
    }
  }
};

const cancel = () => {
  router.push({ name: 'unit-kerja-list' });
};

onMounted(() => {
  fetchParentOptions();

  if (route.params.id) {
    isEdit.value = true;
    unitKerjaId.value = route.params.id;
    fetchUnitKerja();
  }
});
</script>
