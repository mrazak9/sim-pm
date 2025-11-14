<template>
  <MainLayout>
    <div class="space-y-6">
      <!-- Page Header with Action Button -->
      <div class="flex items-center justify-between">
      <div>
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
          Dashboard IKU
        </h2>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
          Ringkasan dan statistik Indikator Kinerja Utama
        </p>
      </div>
      <router-link
        :to="{ name: 'iku-create' }"
        class="inline-flex items-center justify-center rounded-md bg-blue-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800"
      >
        <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Tambah IKU
      </router-link>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
      <!-- Total IKU -->
      <div class="rounded-lg border border-gray-200 bg-white p-6 shadow dark:border-gray-700 dark:bg-gray-800">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total IKU</p>
            <h4 class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">
              {{ stats.total_iku }}
            </h4>
          </div>
          <div class="flex h-12 w-12 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900">
            <svg class="h-6 w-6 text-blue-600 dark:text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
          </div>
        </div>
      </div>

      <!-- IKU Aktif -->
      <div class="rounded-lg border border-gray-200 bg-white p-6 shadow dark:border-gray-700 dark:bg-gray-800">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">IKU Aktif</p>
            <h4 class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">
              {{ stats.iku_aktif }}
            </h4>
          </div>
          <div class="flex h-12 w-12 items-center justify-center rounded-full bg-green-100 dark:bg-green-900">
            <svg class="h-6 w-6 text-green-600 dark:text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
        </div>
      </div>

      <!-- Total Target -->
      <div class="rounded-lg border border-gray-200 bg-white p-6 shadow dark:border-gray-700 dark:bg-gray-800">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Target</p>
            <h4 class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">
              {{ stats.total_targets }}
            </h4>
          </div>
          <div class="flex h-12 w-12 items-center justify-center rounded-full bg-yellow-100 dark:bg-yellow-900">
            <svg class="h-6 w-6 text-yellow-600 dark:text-yellow-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
            </svg>
          </div>
        </div>
      </div>

      <!-- Kategori -->
      <div class="rounded-lg border border-gray-200 bg-white p-6 shadow dark:border-gray-700 dark:bg-gray-800">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Kategori</p>
            <h4 class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">
              {{ stats.total_categories }}
            </h4>
          </div>
          <div class="flex h-12 w-12 items-center justify-center rounded-full bg-purple-100 dark:bg-purple-900">
            <svg class="h-6 w-6 text-purple-600 dark:text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
            </svg>
          </div>
        </div>
      </div>
    </div>

    <!-- Recent IKUs -->
    <div class="rounded-lg border border-gray-200 bg-white shadow dark:border-gray-700 dark:bg-gray-800">
      <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
          IKU Terbaru
        </h3>
      </div>
      <div class="p-6">
        <div v-if="loading" class="flex justify-center py-8">
          <svg class="h-8 w-8 animate-spin text-blue-600" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
        </div>
        <div v-else-if="recentIKUs.length === 0" class="py-8 text-center text-gray-500 dark:text-gray-400">
          Belum ada IKU
        </div>
        <div v-else class="space-y-3">
          <div
            v-for="iku in recentIKUs"
            :key="iku.id"
            class="flex items-center justify-between rounded-lg border border-gray-200 p-4 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-700"
          >
            <div class="flex-1">
              <h4 class="font-medium text-gray-900 dark:text-white">
                {{ iku.kode_iku }} - {{ iku.nama_iku }}
              </h4>
              <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                {{ iku.kategori || 'Tidak ada kategori' }} • {{ getSatuanLabel(iku.satuan) }}
              </p>
            </div>
            <div class="flex items-center gap-2">
              <span :class="[
                'inline-flex rounded-full px-3 py-1 text-xs font-medium',
                iku.is_active
                  ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300'
                  : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300'
              ]">
                {{ iku.is_active ? 'Aktif' : 'Tidak Aktif' }}
              </span>
              <router-link
                :to="{ name: 'iku-edit', params: { id: iku.id } }"
                class="inline-flex items-center justify-center rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-300"
              >
                Edit
              </router-link>
            </div>
          </div>
        </div>
        <div class="mt-6 text-center">
          <router-link
            :to="{ name: 'iku-list' }"
            class="inline-flex items-center text-sm font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400"
          >
            Lihat Semua IKU
            <svg class="ml-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </router-link>
        </div>
      </div>
    </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import MainLayout from '@/layouts/MainLayout.vue';
import { useIKUApi } from '@/composables/useIKUApi';

const { loading, getIKUs, getCategories } = useIKUApi();

const stats = ref({
  total_iku: 0,
  iku_aktif: 0,
  total_targets: 0,
  total_categories: 0,
});

const recentIKUs = ref([]);

const fetchDashboardData = async () => {
  try {
    // Fetch IKUs
    const ikuResponse = await getIKUs({ per_page: 5 });
    recentIKUs.value = ikuResponse.data.data;
    stats.value.total_iku = ikuResponse.data.total;

    // Count active IKUs
    const activeResponse = await getIKUs({ is_active: 1 });
    stats.value.iku_aktif = activeResponse.data.total;

    // Get categories
    const categoriesResponse = await getCategories();
    stats.value.total_categories = categoriesResponse.data.length;

    // Count targets (sum from all IKUs)
    stats.value.total_targets = recentIKUs.value.reduce((sum, iku) => {
      return sum + (iku.targets?.length || 0);
    }, 0);
  } catch (error) {
    console.error('Failed to fetch dashboard data:', error);
  }
};

const getSatuanLabel = (satuan) => {
  const labels = {
    persen: 'Persen (%)',
    jumlah: 'Jumlah',
    skor: 'Skor',
    nilai: 'Nilai',
  };
  return labels[satuan] || satuan;
};

onMounted(() => {
  fetchDashboardData();
});
</script>
