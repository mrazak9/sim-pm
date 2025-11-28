<template>
  <MainLayout>
    <div class="rounded-lg border border-gray-200 bg-white shadow dark:border-gray-700 dark:bg-gray-800">
      <!-- Header -->
      <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
        <div class="flex items-center justify-between">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
            {{ isEditMode ? 'Edit Standar SPMI' : 'Tambah Standar SPMI' }}
          </h3>
          <button
            @click="$router.push('/spmi/standards')"
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
          <!-- Code -->
          <div>
            <label class="block text-sm font-medium text-gray-900 dark:text-white">
              Kode Standar <span class="text-red-600">*</span>
            </label>
            <input
              v-model="form.code"
              type="text"
              required
              class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              placeholder="Contoh: SP-01-2024"
            />
          </div>

          <!-- Name -->
          <div>
            <label class="block text-sm font-medium text-gray-900 dark:text-white">
              Nama Standar <span class="text-red-600">*</span>
            </label>
            <input
              v-model="form.name"
              type="text"
              required
              class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              placeholder="Masukkan nama standar"
            />
          </div>

          <!-- Category -->
          <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <div>
              <label class="block text-sm font-medium text-gray-900 dark:text-white">
                Kategori <span class="text-red-600">*</span>
              </label>
              <select
                v-model="form.category"
                required
                class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              >
                <option value="">Pilih Kategori</option>
                <option value="pendidikan">Pendidikan</option>
                <option value="penelitian">Penelitian</option>
                <option value="pengabdian">Pengabdian</option>
                <option value="pengelolaan">Pengelolaan</option>
              </select>
            </div>

            <!-- Unit Kerja -->
            <div>
              <label class="block text-sm font-medium text-gray-900 dark:text-white">
                Unit Kerja <span class="text-red-600">*</span>
              </label>
              <select
                v-model="form.unit_kerja_id"
                required
                class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              >
                <option value="">Pilih Unit Kerja</option>
                <option v-for="unit in unitKerjas" :key="unit.id" :value="unit.id">
                  {{ unit.nama_unit || unit.nama }}
                </option>
              </select>
            </div>
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
              placeholder="Masukkan deskripsi standar"
            ></textarea>
          </div>

          <!-- Objective -->
          <div>
            <label class="block text-sm font-medium text-gray-900 dark:text-white">
              Tujuan Standar
            </label>
            <textarea
              v-model="form.objective"
              rows="3"
              class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              placeholder="Masukkan tujuan standar"
            ></textarea>
          </div>

          <!-- Scope -->
          <div>
            <label class="block text-sm font-medium text-gray-900 dark:text-white">
              Lingkup Standar
            </label>
            <textarea
              v-model="form.scope"
              rows="3"
              class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              placeholder="Masukkan lingkup standar"
            ></textarea>
          </div>

          <!-- Reference -->
          <div>
            <label class="block text-sm font-medium text-gray-900 dark:text-white">
              Referensi
            </label>
            <textarea
              v-model="form.reference"
              rows="3"
              class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              placeholder="Masukkan referensi standar"
            ></textarea>
          </div>

          <!-- Version & Dates -->
          <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
            <div>
              <label class="block text-sm font-medium text-gray-900 dark:text-white">
                Versi <span class="text-red-600">*</span>
              </label>
              <input
                v-model="form.version"
                type="text"
                required
                class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                placeholder="1.0"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-900 dark:text-white">
                Tanggal Berlaku <span class="text-red-600">*</span>
              </label>
              <input
                v-model="form.effective_date"
                type="date"
                required
                class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-900 dark:text-white">
                Tanggal Review <span class="text-red-600">*</span>
              </label>
              <input
                v-model="form.review_date"
                type="date"
                required
                class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              />
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="mt-6 flex items-center justify-end gap-3">
          <button
            type="button"
            @click="$router.push('/spmi/standards')"
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
            {{ isEditMode ? 'Simpan Perubahan' : 'Buat Standar' }}
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
import { useSPMIApi } from '@/composables/useSPMIApi';
import { useMasterDataApi } from '@/composables/useMasterDataApi';
import { useToast } from '@/composables/useToast';

const route = useRoute();
const router = useRouter();
const { success, error } = useToast();

const { getSPMIStandard, createSPMIStandard, updateSPMIStandard, loading } = useSPMIApi();
const { getUnitKerjas } = useMasterDataApi();

const isEditMode = computed(() => !!route.params.id);

const unitKerjas = ref([]);

const form = ref({
  code: '',
  name: '',
  category: '',
  description: '',
  objective: '',
  scope: '',
  reference: '',
  effective_date: '',
  review_date: '',
  unit_kerja_id: '',
  version: '1.0',
});

const fetchUnitKerjas = async () => {
  try {
    const response = await getUnitKerjas({ active_only: true });
    if (response.success) {
      unitKerjas.value = response.data;
    }
  } catch (err) {
    console.error('Failed to fetch unit kerjas:', err);
    error('Gagal memuat data unit kerja');
  }
};

const fetchStandard = async () => {
  try {
    const response = await getSPMIStandard(route.params.id);
    if (response.success) {
      const standard = response.data;
      form.value = {
        code: standard.code,
        name: standard.name,
        category: standard.category,
        description: standard.description || '',
        objective: standard.objective || '',
        scope: standard.scope || '',
        reference: standard.reference || '',
        effective_date: standard.effective_date,
        review_date: standard.review_date,
        unit_kerja_id: standard.unit_kerja_id,
        version: standard.version,
      };
    }
  } catch (err) {
    console.error('Failed to fetch standard:', err);
    error('Gagal memuat data standar');
    router.push('/spmi/standards');
  }
};

const handleSubmit = async () => {
  try {
    const response = isEditMode.value
      ? await updateSPMIStandard(route.params.id, form.value)
      : await createSPMIStandard(form.value);

    if (response.success) {
      success(isEditMode.value ? 'Standar berhasil diperbarui' : 'Standar berhasil dibuat');
      router.push('/spmi/standards');
    }
  } catch (err) {
    console.error('Failed to save standard:', err);
    error('Gagal menyimpan standar: ' + (err.response?.data?.message || err.message));
  }
};

onMounted(() => {
  fetchUnitKerjas();
  if (isEditMode.value) {
    fetchStandard();
  }
});
</script>
