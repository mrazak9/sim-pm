<template>
  <div class="space-y-6">
    <!-- Page Header -->
    <div>
      <h2 class="text-title-md2 font-semibold text-black dark:text-white">
        Dashboard IKU
      </h2>
      <p class="mt-2 text-sm text-body">
        Ringkasan dan statistik Indikator Kinerja Utama
      </p>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
      <div class="rounded-sm border border-stroke bg-white px-7.5 py-6 shadow-default dark:border-strokedark dark:bg-boxdark">
        <div class="flex items-center justify-between">
          <div>
            <h4 class="text-title-md font-bold text-black dark:text-white">
              {{ stats.total_iku }}
            </h4>
            <span class="text-sm font-medium">Total IKU</span>
          </div>
          <div class="flex h-11.5 w-11.5 items-center justify-center rounded-full bg-meta-2 dark:bg-meta-4">
            <svg class="fill-primary dark:fill-white" width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M21.1063 18.0469L19.3875 3.23126C19.2157 1.71876 17.9438 0.584381 16.3969 0.584381H5.56878C4.05628 0.584381 2.78441 1.71876 2.57816 3.23126L0.859406 18.0469C0.756281 18.9063 1.03128 19.7313 1.61566 20.3844C2.20003 21.0375 3.02816 21.3813 3.92191 21.3813H18.0438C18.9375 21.3813 19.7657 21.0031 20.35 20.3844C20.9688 19.7313 21.2094 18.9063 21.1063 18.0469ZM19.2157 19.3531C18.9407 19.6625 18.5625 19.8344 18.0438 19.8344H3.92191C3.40316 19.8344 3.02503 19.6625 2.75003 19.3531C2.47503 19.0438 2.33441 18.6313 2.40003 18.1813L4.11878 3.36563C4.19066 2.71563 4.73753 2.16563 5.56878 2.16563H16.3969C17.2282 2.16563 17.775 2.71563 17.8469 3.36563L19.5657 18.1813C19.6313 18.6313 19.4907 19.0438 19.2157 19.3531Z"/>
              <path d="M14.3345 5.29375C13.922 5.39688 13.647 5.80938 13.7501 6.22188C13.7845 6.42813 13.8189 6.63438 13.8189 6.80625C13.8189 8.35313 12.547 9.625 11.0001 9.625C9.45327 9.625 8.18139 8.35313 8.18139 6.80625C8.18139 6.6 8.21577 6.42813 8.25014 6.22188C8.35327 5.80938 8.07827 5.39688 7.66577 5.29375C7.25327 5.19063 6.84077 5.46563 6.73764 5.87813C6.66577 6.1875 6.62514 6.49688 6.62514 6.80625C6.62514 9.2125 8.5939 11.1813 11.0001 11.1813C13.4064 11.1813 15.3751 9.2125 15.3751 6.80625C15.3751 6.49688 15.3345 6.1875 15.2626 5.87813C15.1595 5.46563 14.747 5.225 14.3345 5.29375Z"/>
            </svg>
          </div>
        </div>
      </div>

      <div class="rounded-sm border border-stroke bg-white px-7.5 py-6 shadow-default dark:border-strokedark dark:bg-boxdark">
        <div class="flex items-center justify-between">
          <div>
            <h4 class="text-title-md font-bold text-black dark:text-white">
              {{ stats.iku_aktif }}
            </h4>
            <span class="text-sm font-medium">IKU Aktif</span>
          </div>
          <div class="flex h-11.5 w-11.5 items-center justify-center rounded-full bg-meta-2 dark:bg-meta-4">
            <svg class="fill-success dark:fill-white" width="20" height="20" viewBox="0 0 20 20" fill="none">
              <path d="M10 0C4.48 0 0 4.48 0 10C0 15.52 4.48 20 10 20C15.52 20 20 15.52 20 10C20 4.48 15.52 0 10 0ZM8 15L3 10L4.41 8.59L8 12.17L15.59 4.58L17 6L8 15Z"/>
            </svg>
          </div>
        </div>
      </div>

      <div class="rounded-sm border border-stroke bg-white px-7.5 py-6 shadow-default dark:border-strokedark dark:bg-boxdark">
        <div class="flex items-center justify-between">
          <div>
            <h4 class="text-title-md font-bold text-black dark:text-white">
              {{ stats.total_targets }}
            </h4>
            <span class="text-sm font-medium">Total Target</span>
          </div>
          <div class="flex h-11.5 w-11.5 items-center justify-center rounded-full bg-meta-2 dark:bg-meta-4">
            <svg class="fill-warning dark:fill-white" width="20" height="20" viewBox="0 0 20 20" fill="none">
              <path d="M10 0C4.48 0 0 4.48 0 10C0 15.52 4.48 20 10 20C15.52 20 20 15.52 20 10C20 4.48 15.52 0 10 0ZM10 18C5.59 18 2 14.41 2 10C2 5.59 5.59 2 10 2C14.41 2 18 5.59 18 10C18 14.41 14.41 18 10 18ZM10 4C7.24 4 5 6.24 5 9H7C7 7.35 8.35 6 10 6C11.65 6 13 7.35 13 9C13 10.65 11.65 12 10 12H9V14H10C11.65 14 13 15.35 13 17C13 18.65 11.65 20 10 20C8.35 20 7 18.65 7 17H5C5 19.76 7.24 22 10 22C12.76 22 15 19.76 15 17C15 15.65 14.25 14.5 13.17 13.83C14.25 13.17 15 12.02 15 10.67C15 7.91 12.76 5.67 10 5.67V4Z"/>
            </svg>
          </div>
        </div>
      </div>

      <div class="rounded-sm border border-stroke bg-white px-7.5 py-6 shadow-default dark:border-strokedark dark:bg-boxdark">
        <div class="flex items-center justify-between">
          <div>
            <h4 class="text-title-md font-bold text-black dark:text-white">
              {{ stats.total_categories }}
            </h4>
            <span class="text-sm font-medium">Kategori</span>
          </div>
          <div class="flex h-11.5 w-11.5 items-center justify-center rounded-full bg-meta-2 dark:bg-meta-4">
            <svg class="fill-meta-3 dark:fill-white" width="20" height="20" viewBox="0 0 20 20" fill="none">
              <path d="M8 0H2C0.9 0 0 0.9 0 2V8C0 9.1 0.9 10 2 10H8C9.1 10 10 9.1 10 8V2C10 0.9 9.1 0 8 0ZM8 8H2V2H8V8ZM18 0H12C10.9 0 10 0.9 10 2V8C10 9.1 10.9 10 12 10H18C19.1 10 20 9.1 20 8V2C20 0.9 19.1 0 18 0ZM18 8H12V2H18V8ZM8 10H2C0.9 10 0 10.9 0 12V18C0 19.1 0.9 20 2 20H8C9.1 20 10 19.1 10 18V12C10 10.9 9.1 10 8 10ZM8 18H2V12H8V18ZM18 10H12C10.9 10 10 10.9 10 12V18C10 19.1 10.9 20 12 20H18C19.1 20 20 19.1 20 18V12C20 10.9 19.1 10 18 10ZM18 18H12V12H18V18Z"/>
            </svg>
          </div>
        </div>
      </div>
    </div>

    <!-- Recent IKUs -->
    <div class="rounded-sm border border-stroke bg-white shadow-default dark:border-strokedark dark:bg-boxdark">
      <div class="border-b border-stroke px-7 py-4 dark:border-strokedark">
        <h3 class="font-medium text-black dark:text-white">
          IKU Terbaru
        </h3>
      </div>
      <div class="p-7">
        <div v-if="loading" class="flex justify-center py-8">
          <svg class="h-8 w-8 animate-spin text-primary" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
        </div>
        <div v-else-if="recentIKUs.length === 0" class="py-8 text-center text-body">
          Belum ada IKU
        </div>
        <div v-else class="space-y-4">
          <div
            v-for="iku in recentIKUs"
            :key="iku.id"
            class="flex items-center justify-between rounded-sm border border-stroke p-4 dark:border-strokedark"
          >
            <div class="flex-1">
              <h4 class="font-medium text-black dark:text-white">
                {{ iku.kode_iku }} - {{ iku.nama_iku }}
              </h4>
              <p class="mt-1 text-sm text-body">
                {{ iku.kategori || 'Tidak ada kategori' }} • {{ getSatuanLabel(iku.satuan) }}
              </p>
            </div>
            <div class="flex items-center gap-2">
              <span :class="[
                'inline-flex rounded-full bg-opacity-10 px-3 py-1 text-sm font-medium',
                iku.is_active ? 'bg-success text-success' : 'bg-danger text-danger'
              ]">
                {{ iku.is_active ? 'Aktif' : 'Tidak Aktif' }}
              </span>
              <router-link
                :to="{ name: 'iku-detail', params: { id: iku.id } }"
                class="inline-flex items-center justify-center rounded-md bg-primary px-4 py-2 text-center text-sm font-medium text-white hover:bg-opacity-90"
              >
                Lihat
              </router-link>
            </div>
          </div>
        </div>
        <div class="mt-4 text-center">
          <router-link
            :to="{ name: 'iku-list' }"
            class="inline-flex items-center text-sm font-medium text-primary hover:underline"
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
</template>

<script setup>
import { ref, onMounted } from 'vue';
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
