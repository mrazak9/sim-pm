<template>
  <MainLayout>
    <div class="rounded-lg border border-gray-200 bg-white shadow dark:border-gray-700 dark:bg-gray-800">
      <!-- Header -->
      <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
      <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
        {{ isEdit ? 'Edit' : 'Tambah' }} Indikator Kinerja Utama (IKU)
      </h3>
    </div>

    <!-- Form -->
    <form @submit.prevent="submitForm" class="p-6">
      <div class="space-y-6">
        <!-- Kode IKU -->
        <div>
          <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
            Kode IKU <span class="text-red-600">*</span>
          </label>
          <input
            v-model="form.kode_iku"
            type="text"
            placeholder="Contoh: IKU-001"
            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500"
            :class="{ 'border-red-600': errors.kode_iku }"
            required
          />
          <p v-if="errors.kode_iku" class="mt-1 text-sm text-red-600">{{ errors.kode_iku }}</p>
        </div>

        <!-- Nama IKU -->
        <div>
          <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
            Nama IKU <span class="text-red-600">*</span>
          </label>
          <input
            v-model="form.nama_iku"
            type="text"
            placeholder="Nama lengkap IKU"
            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500"
            :class="{ 'border-red-600': errors.nama_iku }"
            required
          />
          <p v-if="errors.nama_iku" class="mt-1 text-sm text-red-600">{{ errors.nama_iku }}</p>
        </div>

        <!-- Deskripsi -->
        <div>
          <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
            Deskripsi
          </label>
          <textarea
            v-model="form.deskripsi"
            rows="4"
            placeholder="Deskripsi IKU..."
            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500"
          ></textarea>
        </div>

        <!-- Row: Satuan & Target Type -->
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
          <div>
            <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
              Satuan <span class="text-red-600">*</span>
            </label>
            <select
              v-model="form.satuan"
              class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:focus:border-blue-500 dark:focus:ring-blue-500"
              :class="{ 'border-red-600': errors.satuan }"
              required
            >
              <option value="">Pilih Satuan</option>
              <option value="persen">Persen (%)</option>
              <option value="jumlah">Jumlah</option>
              <option value="skor">Skor</option>
              <option value="nilai">Nilai</option>
            </select>
            <p v-if="errors.satuan" class="mt-1 text-sm text-red-600">{{ errors.satuan }}</p>
          </div>

          <div>
            <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
              Tipe Target <span class="text-red-600">*</span>
            </label>
            <select
              v-model="form.target_type"
              class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:focus:border-blue-500 dark:focus:ring-blue-500"
              :class="{ 'border-red-600': errors.target_type }"
              required
            >
              <option value="">Pilih Tipe Target</option>
              <option value="increase">Meningkat (semakin besar semakin baik)</option>
              <option value="decrease">Menurun (semakin kecil semakin baik)</option>
            </select>
            <p v-if="errors.target_type" class="mt-1 text-sm text-red-600">{{ errors.target_type }}</p>
          </div>
        </div>

        <!-- Row: Kategori & Bobot -->
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
          <div>
            <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
              Kategori
            </label>
            <input
              v-model="form.kategori"
              type="text"
              placeholder="Contoh: SDM, Akademik, Penelitian"
              class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500"
            />
          </div>

          <div>
            <label class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
              Bobot (0-100)
            </label>
            <input
              v-model.number="form.bobot"
              type="number"
              min="0"
              max="100"
              placeholder="Bobot IKU"
              class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500"
              :class="{ 'border-red-600': errors.bobot }"
            />
            <p v-if="errors.bobot" class="mt-1 text-sm text-red-600">{{ errors.bobot }}</p>
          </div>
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
            IKU Aktif
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
import { useIKUApi } from '@/composables/useIKUApi';

const router = useRouter();
const route = useRoute();
const { loading, getIKU, createIKU, updateIKU } = useIKUApi();

const isEdit = ref(false);
const ikuId = ref(null);

const form = ref({
  kode_iku: '',
  nama_iku: '',
  deskripsi: '',
  satuan: '',
  target_type: '',
  kategori: '',
  bobot: null,
  is_active: true,
});

const errors = ref({});
const errorMessage = ref('');

const fetchIKU = async () => {
  try {
    const response = await getIKU(ikuId.value);
    const data = response.data;

    form.value = {
      kode_iku: data.kode_iku,
      nama_iku: data.nama_iku,
      deskripsi: data.deskripsi || '',
      satuan: data.satuan,
      target_type: data.target_type,
      kategori: data.kategori || '',
      bobot: data.bobot,
      is_active: data.is_active,
    };
  } catch (error) {
    console.error('Failed to fetch IKU:', error);
    errorMessage.value = 'Gagal memuat data IKU';
  }
};

const validateForm = () => {
  errors.value = {};
  let isValid = true;

  if (!form.value.kode_iku.trim()) {
    errors.value.kode_iku = 'Kode IKU wajib diisi';
    isValid = false;
  }

  if (!form.value.nama_iku.trim()) {
    errors.value.nama_iku = 'Nama IKU wajib diisi';
    isValid = false;
  }

  if (!form.value.satuan) {
    errors.value.satuan = 'Satuan wajib dipilih';
    isValid = false;
  }

  if (!form.value.target_type) {
    errors.value.target_type = 'Tipe target wajib dipilih';
    isValid = false;
  }

  if (form.value.bobot !== null && (form.value.bobot < 0 || form.value.bobot > 100)) {
    errors.value.bobot = 'Bobot harus antara 0-100';
    isValid = false;
  }

  return isValid;
};

const submitForm = async () => {
  errorMessage.value = '';

  if (!validateForm()) {
    return;
  }

  try {
    if (isEdit.value) {
      await updateIKU(ikuId.value, form.value);
    } else {
      await createIKU(form.value);
    }

    router.push({ name: 'iku-list' });
  } catch (error) {
    console.error('Failed to save IKU:', error);
    errorMessage.value = error.response?.data?.message || 'Gagal menyimpan IKU';

    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors;
    }
  }
};

const cancel = () => {
  router.push({ name: 'iku-list' });
};

onMounted(() => {
  if (route.params.id) {
    isEdit.value = true;
    ikuId.value = route.params.id;
    fetchIKU();
  }
});
</script>
