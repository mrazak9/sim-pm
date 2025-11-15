<template>
  <MainLayout>
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Document Dashboard</h1>
          <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            Overview manajemen dokumen sistem
          </p>
        </div>
        <div class="flex items-center space-x-3">
          <button
            @click="refreshData"
            :disabled="loading"
            class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
          >
            <svg :class="['mr-2 h-4 w-4', loading && 'animate-spin']" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            Refresh
          </button>
          <router-link
            to="/dokumen"
            class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700"
          >
            <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Kelola Dokumen
          </router-link>
        </div>
      </div>

      <!-- Metric Cards -->
      <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Total Documents -->
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow dark:border-gray-700 dark:bg-gray-800">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Dokumen</p>
              <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">
                {{ statistics.total_documents || 0 }}
              </p>
            </div>
            <div class="rounded-full bg-blue-100 p-3 dark:bg-blue-900">
              <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
            </div>
          </div>
          <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
            <span class="text-green-600 dark:text-green-400">+{{ statistics.uploads_this_month || 0 }}</span> bulan ini
          </p>
        </div>

        <!-- Categories -->
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow dark:border-gray-700 dark:bg-gray-800">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Kategori</p>
              <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">
                {{ statistics.total_categories || 0 }}
              </p>
            </div>
            <div class="rounded-full bg-purple-100 p-3 dark:bg-purple-900">
              <svg class="h-6 w-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
              </svg>
            </div>
          </div>
          <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
            {{ statistics.active_categories || 0 }} aktif
          </p>
        </div>

        <!-- Shared Documents -->
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow dark:border-gray-700 dark:bg-gray-800">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Dibagikan</p>
              <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">
                {{ statistics.shared_documents || 0 }}
              </p>
            </div>
            <div class="rounded-full bg-green-100 p-3 dark:bg-green-900">
              <svg class="h-6 w-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-5.368m0 5.368a3 3 0 105.368 0m-4.681 0C9.52 14.81 10.5 16.51 10.5 18.5h3c0-1.99 1.02-3.69 2.867-5.343" />
              </svg>
            </div>
          </div>
          <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
            {{ statistics.total_shares || 0 }} total berbagi
          </p>
        </div>

        <!-- Storage Used -->
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow dark:border-gray-700 dark:bg-gray-800">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Storage</p>
              <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">
                {{ formatBytes(statistics.total_storage || 0) }}
              </p>
            </div>
            <div class="rounded-full bg-orange-100 p-3 dark:bg-orange-900">
              <svg class="h-6 w-6 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
              </svg>
            </div>
          </div>
          <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
            {{ statistics.total_documents || 0 }} files
          </p>
        </div>
      </div>

      <!-- Charts Row -->
      <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <!-- Status Distribution -->
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow dark:border-gray-700 dark:bg-gray-800">
          <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Distribusi Status</h3>
          <canvas ref="statusChart"></canvas>
        </div>

        <!-- Category Distribution -->
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow dark:border-gray-700 dark:bg-gray-800">
          <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Distribusi Kategori</h3>
          <canvas ref="categoryChart"></canvas>
        </div>
      </div>

      <!-- Upload Trend Chart -->
      <div class="rounded-lg border border-gray-200 bg-white p-6 shadow dark:border-gray-700 dark:bg-gray-800">
        <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Trend Upload Dokumen (6 Bulan Terakhir)</h3>
        <canvas ref="uploadTrendChart"></canvas>
      </div>

      <!-- Recent Activities & Top Categories -->
      <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <!-- Recent Uploads -->
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow dark:border-gray-700 dark:bg-gray-800">
          <div class="mb-4 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Upload Terbaru</h3>
            <router-link to="/dokumen" class="text-sm text-blue-600 hover:text-blue-700 dark:text-blue-400">
              Lihat Semua →
            </router-link>
          </div>
          <div class="space-y-3">
            <div
              v-for="doc in recentUploads"
              :key="doc.id"
              class="flex items-center justify-between rounded-lg border border-gray-200 p-3 dark:border-gray-600"
            >
              <div class="flex items-center space-x-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg" :style="{ backgroundColor: doc.category?.color + '20' }">
                  <svg class="h-6 w-6" :style="{ color: doc.category?.color }" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6z" />
                    <path d="M14 2v6h6" />
                  </svg>
                </div>
                <div>
                  <p class="font-medium text-gray-900 dark:text-white">{{ doc.title }}</p>
                  <p class="text-xs text-gray-500 dark:text-gray-400">
                    {{ doc.uploader?.name }} • {{ formatDate(doc.created_at) }}
                  </p>
                </div>
              </div>
              <router-link
                :to="`/dokumen/${doc.id}`"
                class="rounded-lg bg-blue-50 p-2 text-blue-600 hover:bg-blue-100 dark:bg-blue-900/20 dark:text-blue-400"
              >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
              </router-link>
            </div>
          </div>
        </div>

        <!-- Top Categories -->
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow dark:border-gray-700 dark:bg-gray-800">
          <div class="mb-4 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Top Kategori</h3>
            <router-link to="/dokumen/categories" class="text-sm text-blue-600 hover:text-blue-700 dark:text-blue-400">
              Kelola →
            </router-link>
          </div>
          <div class="space-y-3">
            <div
              v-for="cat in topCategories"
              :key="cat.id"
              class="flex items-center justify-between"
            >
              <div class="flex items-center space-x-3">
                <div class="h-3 w-3 rounded-full" :style="{ backgroundColor: cat.color }"></div>
                <span class="font-medium text-gray-900 dark:text-white">{{ cat.name }}</span>
              </div>
              <div class="flex items-center space-x-2">
                <span class="text-sm text-gray-600 dark:text-gray-400">{{ cat.documents_count }} docs</span>
                <div class="h-2 w-24 overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                  <div
                    class="h-full rounded-full"
                    :style="{
                      width: (cat.documents_count / statistics.total_documents * 100) + '%',
                      backgroundColor: cat.color
                    }"
                  ></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
import MainLayout from '@/layouts/MainLayout.vue';
import axios from 'axios';
import Chart from 'chart.js/auto';

const router = useRouter();

const loading = ref(false);
const statistics = ref({});
const recentUploads = ref([]);
const topCategories = ref([]);

const statusChart = ref(null);
const categoryChart = ref(null);
const uploadTrendChart = ref(null);

let statusChartInstance = null;
let categoryChartInstance = null;
let uploadTrendChartInstance = null;

const fetchStatistics = async () => {
  loading.value = true;
  try {
    // Fetch statistics from API
    const response = await axios.get('/api/documents/statistics');
    if (response.data.success) {
      statistics.value = response.data.data;
    }
  } catch (error) {
    console.error('Failed to fetch statistics:', error);
    // Use mock data for now
    statistics.value = {
      total_documents: 156,
      total_categories: 12,
      shared_documents: 45,
      total_shares: 78,
      total_storage: 2548965120, // ~2.4 GB
      uploads_this_month: 23,
      active_categories: 10,
      status_distribution: {
        draft: 35,
        review: 28,
        approved: 82,
        archived: 11
      },
      category_distribution: [],
      upload_trend: []
    };
  } finally {
    loading.value = false;
  }
};

const fetchRecentUploads = async () => {
  try {
    const response = await axios.get('/api/documents', {
      params: { per_page: 5, sort_by: 'created_at', sort_order: 'desc' }
    });
    if (response.data.success) {
      recentUploads.value = response.data.data;
    }
  } catch (error) {
    console.error('Failed to fetch recent uploads:', error);
  }
};

const fetchTopCategories = async () => {
  try {
    const response = await axios.get('/api/document-categories/top');
    if (response.data.success) {
      topCategories.value = response.data.data;
    }
  } catch (error) {
    console.error('Failed to fetch top categories:', error);
    // Mock data
    topCategories.value = [
      { id: 1, name: 'Dokumen Mutu', color: '#3B82F6', documents_count: 45 },
      { id: 2, name: 'SOP', color: '#10B981', documents_count: 38 },
      { id: 3, name: 'Akreditasi', color: '#F59E0B', documents_count: 32 },
      { id: 4, name: 'IKU', color: '#EF4444', documents_count: 25 },
      { id: 5, name: 'Laporan', color: '#8B5CF6', documents_count: 16 }
    ];
  }
};

const initStatusChart = () => {
  if (!statusChart.value) return;

  const ctx = statusChart.value.getContext('2d');

  statusChartInstance = new Chart(ctx, {
    type: 'doughnut',
    data: {
      labels: ['Draft', 'Review', 'Disetujui', 'Arsip'],
      datasets: [{
        data: [
          statistics.value.status_distribution?.draft || 0,
          statistics.value.status_distribution?.review || 0,
          statistics.value.status_distribution?.approved || 0,
          statistics.value.status_distribution?.archived || 0
        ],
        backgroundColor: ['#94A3B8', '#F59E0B', '#10B981', '#3B82F6'],
        borderWidth: 0
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: true,
      plugins: {
        legend: {
          position: 'bottom'
        }
      }
    }
  });
};

const initCategoryChart = () => {
  if (!categoryChart.value) return;

  const ctx = categoryChart.value.getContext('2d');

  const labels = topCategories.value.map(cat => cat.name);
  const data = topCategories.value.map(cat => cat.documents_count);
  const colors = topCategories.value.map(cat => cat.color);

  categoryChartInstance = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: labels,
      datasets: [{
        label: 'Jumlah Dokumen',
        data: data,
        backgroundColor: colors,
        borderWidth: 0
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: true,
      plugins: {
        legend: {
          display: false
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            stepSize: 10
          }
        }
      }
    }
  });
};

const initUploadTrendChart = () => {
  if (!uploadTrendChart.value) return;

  const ctx = uploadTrendChart.value.getContext('2d');

  // Generate last 6 months
  const months = [];
  const now = new Date();
  for (let i = 5; i >= 0; i--) {
    const date = new Date(now.getFullYear(), now.getMonth() - i, 1);
    months.push(date.toLocaleDateString('id-ID', { month: 'short', year: 'numeric' }));
  }

  // Mock data for upload trend
  const uploadData = [12, 19, 15, 25, 22, 23];

  uploadTrendChartInstance = new Chart(ctx, {
    type: 'line',
    data: {
      labels: months,
      datasets: [{
        label: 'Upload Dokumen',
        data: uploadData,
        borderColor: '#3B82F6',
        backgroundColor: 'rgba(59, 130, 246, 0.1)',
        fill: true,
        tension: 0.4
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: true,
      plugins: {
        legend: {
          display: false
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            stepSize: 5
          }
        }
      }
    }
  });
};

const refreshData = async () => {
  await fetchStatistics();
  await fetchRecentUploads();
  await fetchTopCategories();

  // Destroy and recreate charts
  if (statusChartInstance) statusChartInstance.destroy();
  if (categoryChartInstance) categoryChartInstance.destroy();
  if (uploadTrendChartInstance) uploadTrendChartInstance.destroy();

  setTimeout(() => {
    initStatusChart();
    initCategoryChart();
    initUploadTrendChart();
  }, 100);
};

const formatBytes = (bytes) => {
  if (bytes === 0) return '0 Bytes';
  const k = 1024;
  const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
};

const formatDate = (dateString) => {
  const date = new Date(dateString);
  const now = new Date();
  const diffTime = Math.abs(now - date);
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

  if (diffDays === 0) return 'Hari ini';
  if (diffDays === 1) return 'Kemarin';
  if (diffDays < 7) return `${diffDays} hari lalu`;

  return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
};

onMounted(async () => {
  await fetchStatistics();
  await fetchRecentUploads();
  await fetchTopCategories();

  setTimeout(() => {
    initStatusChart();
    initCategoryChart();
    initUploadTrendChart();
  }, 100);
});

onUnmounted(() => {
  if (statusChartInstance) statusChartInstance.destroy();
  if (categoryChartInstance) categoryChartInstance.destroy();
  if (uploadTrendChartInstance) uploadTrendChartInstance.destroy();
});
</script>
