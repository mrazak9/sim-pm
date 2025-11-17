<template>
  <MainLayout>
    <div class="rounded-lg border border-gray-200 bg-white shadow dark:border-gray-700 dark:bg-gray-800">
      <!-- Header -->
      <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
        <div class="flex items-center justify-between">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
            {{ isEditMode ? 'Edit Rencana Audit' : 'Tambah Rencana Audit' }}
          </h3>
          <button
            @click="$router.push('/audit/plans')"
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
          <!-- Title -->
          <div>
            <label class="block text-sm font-medium text-gray-900 dark:text-white">
              Judul Rencana Audit <span class="text-red-600">*</span>
            </label>
            <input
              v-model="form.title"
              type="text"
              required
              class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              placeholder="Masukkan judul rencana audit"
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
              placeholder="Masukkan deskripsi rencana audit"
            ></textarea>
          </div>

          <!-- Tahun Akademik & Periode -->
          <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <div>
              <label class="block text-sm font-medium text-gray-900 dark:text-white">
                Tahun Akademik <span class="text-red-600">*</span>
              </label>
              <select
                v-model="form.tahun_akademik_id"
                required
                class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              >
                <option value="">Pilih Tahun Akademik</option>
                <option v-for="ta in tahunAkademiks" :key="ta.id" :value="ta.id">
                  {{ ta.nama }} ({{ ta.tahun_mulai }}/{{ ta.tahun_selesai }})
                </option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-900 dark:text-white">
                Periode <span class="text-red-600">*</span>
              </label>
              <select
                v-model="form.periode"
                required
                class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              >
                <option value="">Pilih Periode</option>
                <option value="semester_1">Semester 1</option>
                <option value="semester_2">Semester 2</option>
                <option value="tahunan">Tahunan</option>
              </select>
            </div>
          </div>

          <!-- Date Range -->
          <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <div>
              <label class="block text-sm font-medium text-gray-900 dark:text-white">
                Tanggal Mulai <span class="text-red-600">*</span>
              </label>
              <input
                v-model="form.start_date"
                type="date"
                required
                class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-900 dark:text-white">
                Tanggal Selesai <span class="text-red-600">*</span>
              </label>
              <input
                v-model="form.end_date"
                type="date"
                required
                class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              />
            </div>
          </div>

          <!-- Objectives -->
          <div>
            <label class="block text-sm font-medium text-gray-900 dark:text-white">
              Tujuan Audit
            </label>
            <textarea
              v-model="form.objectives"
              rows="4"
              class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              placeholder="Masukkan tujuan audit"
            ></textarea>
          </div>

          <!-- Scope -->
          <div>
            <label class="block text-sm font-medium text-gray-900 dark:text-white">
              Lingkup Audit
            </label>
            <textarea
              v-model="form.scope"
              rows="4"
              class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              placeholder="Masukkan lingkup audit"
            ></textarea>
          </div>

          <!-- Methodology -->
          <div>
            <label class="block text-sm font-medium text-gray-900 dark:text-white">
              Metodologi
            </label>
            <textarea
              v-model="form.methodology"
              rows="4"
              class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              placeholder="Masukkan metodologi audit"
            ></textarea>
          </div>
        </div>

        <!-- Actions -->
        <div class="mt-6 flex items-center justify-end gap-3">
          <button
            type="button"
            @click="$router.push('/audit/plans')"
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
            {{ isEditMode ? 'Simpan Perubahan' : 'Buat Rencana Audit' }}
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
import { useAuditApi } from '@/composables/useAuditApi';
import { useMasterDataApi } from '@/composables/useMasterDataApi';

const route = useRoute();
const router = useRouter();

const { getAuditPlan, createAuditPlan, updateAuditPlan, loading } = useAuditApi();
const { getTahunAkademiks } = useMasterDataApi();

const isEditMode = computed(() => !!route.params.id);

const tahunAkademiks = ref([]);

const form = ref({
  title: '',
  description: '',
  tahun_akademik_id: '',
  periode: 'tahunan',
  start_date: '',
  end_date: '',
  objectives: '',
  scope: '',
  methodology: '',
});

const fetchTahunAkademiks = async () => {
  try {
    const response = await getTahunAkademiks({ active_only: true });
    if (response.success) {
      tahunAkademiks.value = response.data;
    }
  } catch (error) {
    console.error('Failed to fetch tahun akademiks:', error);
  }
};

const fetchAuditPlan = async () => {
  try {
    const response = await getAuditPlan(route.params.id);
    if (response.success) {
      const plan = response.data;
      form.value = {
        title: plan.title,
        description: plan.description || '',
        tahun_akademik_id: plan.tahun_akademik_id,
        periode: plan.periode,
        start_date: plan.start_date,
        end_date: plan.end_date,
        objectives: plan.objectives || '',
        scope: plan.scope || '',
        methodology: plan.methodology || '',
      };
    }
  } catch (error) {
    console.error('Failed to fetch audit plan:', error);
    alert('Gagal memuat data rencana audit');
    router.push('/audit/plans');
  }
};

const handleSubmit = async () => {
  try {
    const response = isEditMode.value
      ? await updateAuditPlan(route.params.id, form.value)
      : await createAuditPlan(form.value);

    if (response.success) {
      alert(isEditMode.value ? 'Rencana audit berhasil diperbarui' : 'Rencana audit berhasil dibuat');
      router.push('/audit/plans');
    }
  } catch (error) {
    alert('Gagal menyimpan rencana audit: ' + (error.response?.data?.message || error.message));
  }
};

onMounted(() => {
  fetchTahunAkademiks();
  if (isEditMode.value) {
    fetchAuditPlan();
  }
});
</script>
