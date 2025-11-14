<template>
  <div class="rounded-sm border border-stroke bg-white shadow-default dark:border-strokedark dark:bg-boxdark">
    <!-- Header -->
    <div class="border-b border-stroke px-7 py-4 dark:border-strokedark">
      <h3 class="font-medium text-black dark:text-white">
        {{ isEdit ? 'Edit' : 'Tambah' }} Indikator Kinerja Utama (IKU)
      </h3>
    </div>

    <!-- Form -->
    <form @submit.prevent="submitForm" class="p-7">
      <div class="mb-5.5 space-y-5.5">
        <!-- Kode IKU -->
        <div>
          <label class="mb-3 block text-sm font-medium text-black dark:text-white">
            Kode IKU <span class="text-danger">*</span>
          </label>
          <input
            v-model="form.kode_iku"
            type="text"
            placeholder="Contoh: IKU-001"
            class="w-full rounded border border-stroke bg-gray px-4.5 py-3 text-black focus:border-primary focus-visible:outline-none dark:border-strokedark dark:bg-meta-4 dark:text-white dark:focus:border-primary"
            :class="{ 'border-danger': errors.kode_iku }"
            required
          />
          <p v-if="errors.kode_iku" class="mt-1 text-sm text-danger">{{ errors.kode_iku }}</p>
        </div>

        <!-- Nama IKU -->
        <div>
          <label class="mb-3 block text-sm font-medium text-black dark:text-white">
            Nama IKU <span class="text-danger">*</span>
          </label>
          <input
            v-model="form.nama_iku"
            type="text"
            placeholder="Nama lengkap IKU"
            class="w-full rounded border border-stroke bg-gray px-4.5 py-3 text-black focus:border-primary focus-visible:outline-none dark:border-strokedark dark:bg-meta-4 dark:text-white dark:focus:border-primary"
            :class="{ 'border-danger': errors.nama_iku }"
            required
          />
          <p v-if="errors.nama_iku" class="mt-1 text-sm text-danger">{{ errors.nama_iku }}</p>
        </div>

        <!-- Deskripsi -->
        <div>
          <label class="mb-3 block text-sm font-medium text-black dark:text-white">
            Deskripsi
          </label>
          <textarea
            v-model="form.deskripsi"
            rows="4"
            placeholder="Deskripsi IKU..."
            class="w-full rounded border border-stroke bg-gray px-4.5 py-3 text-black focus:border-primary focus-visible:outline-none dark:border-strokedark dark:bg-meta-4 dark:text-white dark:focus:border-primary"
          ></textarea>
        </div>

        <!-- Row: Satuan & Target Type -->
        <div class="grid grid-cols-1 gap-5.5 md:grid-cols-2">
          <div>
            <label class="mb-3 block text-sm font-medium text-black dark:text-white">
              Satuan <span class="text-danger">*</span>
            </label>
            <select
              v-model="form.satuan"
              class="w-full rounded border border-stroke bg-gray px-4.5 py-3 text-black focus:border-primary focus-visible:outline-none dark:border-strokedark dark:bg-meta-4 dark:text-white dark:focus:border-primary"
              :class="{ 'border-danger': errors.satuan }"
              required
            >
              <option value="">Pilih Satuan</option>
              <option value="persen">Persen (%)</option>
              <option value="jumlah">Jumlah</option>
              <option value="skor">Skor</option>
              <option value="nilai">Nilai</option>
            </select>
            <p v-if="errors.satuan" class="mt-1 text-sm text-danger">{{ errors.satuan }}</p>
          </div>

          <div>
            <label class="mb-3 block text-sm font-medium text-black dark:text-white">
              Tipe Target <span class="text-danger">*</span>
            </label>
            <select
              v-model="form.target_type"
              class="w-full rounded border border-stroke bg-gray px-4.5 py-3 text-black focus:border-primary focus-visible:outline-none dark:border-strokedark dark:bg-meta-4 dark:text-white dark:focus:border-primary"
              :class="{ 'border-danger': errors.target_type }"
              required
            >
              <option value="">Pilih Tipe Target</option>
              <option value="increase">Meningkat (semakin besar semakin baik)</option>
              <option value="decrease">Menurun (semakin kecil semakin baik)</option>
            </select>
            <p v-if="errors.target_type" class="mt-1 text-sm text-danger">{{ errors.target_type }}</p>
          </div>
        </div>

        <!-- Row: Kategori & Bobot -->
        <div class="grid grid-cols-1 gap-5.5 md:grid-cols-2">
          <div>
            <label class="mb-3 block text-sm font-medium text-black dark:text-white">
              Kategori
            </label>
            <input
              v-model="form.kategori"
              type="text"
              placeholder="Contoh: SDM, Akademik, Penelitian"
              class="w-full rounded border border-stroke bg-gray px-4.5 py-3 text-black focus:border-primary focus-visible:outline-none dark:border-strokedark dark:bg-meta-4 dark:text-white dark:focus:border-primary"
            />
          </div>

          <div>
            <label class="mb-3 block text-sm font-medium text-black dark:text-white">
              Bobot (0-100)
            </label>
            <input
              v-model.number="form.bobot"
              type="number"
              min="0"
              max="100"
              placeholder="Bobot IKU"
              class="w-full rounded border border-stroke bg-gray px-4.5 py-3 text-black focus:border-primary focus-visible:outline-none dark:border-strokedark dark:bg-meta-4 dark:text-white dark:focus:border-primary"
              :class="{ 'border-danger': errors.bobot }"
            />
            <p v-if="errors.bobot" class="mt-1 text-sm text-danger">{{ errors.bobot }}</p>
          </div>
        </div>

        <!-- Status Aktif -->
        <div>
          <label class="mb-3 flex items-center">
            <input
              v-model="form.is_active"
              type="checkbox"
              class="mr-2 h-5 w-5 rounded border-stroke"
            />
            <span class="text-sm font-medium text-black dark:text-white">IKU Aktif</span>
          </label>
        </div>

        <!-- Error Alert -->
        <div v-if="errorMessage" class="rounded-sm border border-danger bg-danger bg-opacity-10 px-4 py-3">
          <p class="text-sm text-danger">{{ errorMessage }}</p>
        </div>

        <!-- Buttons -->
        <div class="flex justify-end gap-4.5">
          <button
            @click="cancel"
            type="button"
            class="flex justify-center rounded border border-stroke px-6 py-2 font-medium text-black hover:shadow-1 dark:border-strokedark dark:text-white"
          >
            Batal
          </button>
          <button
            type="submit"
            :disabled="loading"
            class="flex justify-center rounded bg-primary px-6 py-2 font-medium text-gray hover:bg-opacity-90 disabled:opacity-50"
          >
            <span v-if="loading" class="mr-2">
              <svg class="h-5 w-5 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
            </span>
            {{ loading ? 'Menyimpan...' : 'Simpan' }}
          </button>
        </div>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
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
