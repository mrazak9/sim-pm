<template>
  <MainLayout>
    <div class="rounded-lg border border-gray-200 bg-white shadow dark:border-gray-700 dark:bg-gray-800">
      <!-- Header -->
      <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
        <div class="flex items-center justify-between">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
            Manajemen Kuesioner
          </h3>
          <button
            @click="$router.push('/surveys/create')"
            class="inline-flex items-center justify-center rounded-md bg-blue-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-300"
          >
            <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Buat Kuesioner
          </button>
        </div>
      </div>

      <!-- Statistics Cards -->
      <div v-if="statistics" class="grid grid-cols-1 gap-4 border-b border-gray-200 px-6 py-4 dark:border-gray-700 md:grid-cols-5">
        <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-700">
          <p class="text-sm text-gray-600 dark:text-gray-400">Total Kuesioner</p>
          <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ statistics.total || 0 }}</p>
        </div>
        <div class="rounded-lg bg-blue-50 p-4 dark:bg-blue-900/20">
          <p class="text-sm text-blue-600 dark:text-blue-400">Terbit</p>
          <p class="text-2xl font-bold text-blue-700 dark:text-blue-300">{{ statistics.published || 0 }}</p>
        </div>
        <div class="rounded-lg bg-green-50 p-4 dark:bg-green-900/20">
          <p class="text-sm text-green-600 dark:text-green-400">Aktif</p>
          <p class="text-2xl font-bold text-green-700 dark:text-green-300">{{ statistics.active || 0 }}</p>
        </div>
        <div class="rounded-lg bg-orange-50 p-4 dark:bg-orange-900/20">
          <p class="text-sm text-orange-600 dark:text-orange-400">Ditutup</p>
          <p class="text-2xl font-bold text-orange-700 dark:text-orange-300">{{ statistics.closed || 0 }}</p>
        </div>
        <div class="rounded-lg bg-purple-50 p-4 dark:bg-purple-900/20">
          <p class="text-sm text-purple-600 dark:text-purple-400">Total Respons</p>
          <p class="text-2xl font-bold text-purple-700 dark:text-purple-300">{{ statistics.total_responses || 0 }}</p>
        </div>
      </div>

      <!-- Filters -->
      <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
          <div>
            <input
              v-model="filters.search"
              type="text"
              placeholder="Cari judul kuesioner..."
              class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400"
              @input="debouncedSearch"
            />
          </div>
          <div>
            <select
              v-model="filters.type"
              class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              @change="fetchSurveys()"
            >
              <option value="">Semua Tipe</option>
              <option value="internal">Internal</option>
              <option value="external">Eksternal</option>
              <option value="public">Publik</option>
            </select>
          </div>
          <div>
            <select
              v-model="filters.status"
              class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              @change="fetchSurveys()"
            >
              <option value="">Semua Status</option>
              <option value="draft">Draft</option>
              <option value="published">Terbit</option>
              <option value="closed">Ditutup</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Table -->
      <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-gray-500 dark:text-gray-400">
          <thead class="bg-gray-50 text-xs uppercase text-gray-700 dark:bg-gray-700 dark:text-gray-400">
            <tr>
              <th scope="col" class="px-6 py-3">Judul</th>
              <th scope="col" class="px-6 py-3">Tipe</th>
              <th scope="col" class="px-6 py-3">Status</th>
              <th scope="col" class="px-6 py-3">Tanggal Mulai</th>
              <th scope="col" class="px-6 py-3">Tanggal Selesai</th>
              <th scope="col" class="px-6 py-3">Respons</th>
              <th scope="col" class="px-6 py-3">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="loading">
              <td colspan="7" class="px-6 py-8 text-center">
                <div class="flex items-center justify-center">
                  <svg class="h-8 w-8 animate-spin text-blue-600" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                </div>
              </td>
            </tr>
            <tr v-else-if="surveys.length === 0">
              <td colspan="7" class="px-6 py-8 text-center text-gray-900 dark:text-white">
                Tidak ada kuesioner
              </td>
            </tr>
            <tr v-else v-for="survey in surveys" :key="survey.id" class="border-b border-gray-200 bg-white hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700">
              <td class="px-6 py-4">
                <p class="font-medium text-gray-900 dark:text-white">{{ survey.title }}</p>
                <p v-if="survey.description" class="mt-1 text-xs text-gray-500 dark:text-gray-400 line-clamp-2">
                  {{ survey.description }}
                </p>
              </td>
              <td class="px-6 py-4">
                <span :class="getTypeBadgeClass(survey.type)" class="inline-flex rounded-full px-3 py-1 text-xs font-medium">
                  {{ getTypeLabel(survey.type) }}
                </span>
              </td>
              <td class="px-6 py-4">
                <span :class="getStatusBadgeClass(survey.status)" class="inline-flex rounded-full px-3 py-1 text-xs font-medium">
                  {{ getStatusLabel(survey.status) }}
                </span>
              </td>
              <td class="px-6 py-4 text-gray-900 dark:text-white">
                {{ formatDate(survey.start_date) }}
              </td>
              <td class="px-6 py-4 text-gray-900 dark:text-white">
                {{ formatDate(survey.end_date) }}
              </td>
              <td class="px-6 py-4 text-gray-900 dark:text-white">
                <span class="font-semibold">{{ survey.responses_count || 0 }}</span>
                <span v-if="survey.max_responses" class="text-gray-500 dark:text-gray-400">
                  / {{ survey.max_responses }}
                </span>
              </td>
              <td class="px-6 py-4">
                <div class="flex items-center gap-2">
                  <button
                    @click="$router.push(`/surveys/${survey.id}/edit`)"
                    class="text-blue-600 hover:text-blue-700 dark:text-blue-400"
                    title="Edit"
                  >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                  </button>
                  <button
                    @click="$router.push(`/surveys/${survey.id}/builder`)"
                    class="text-purple-600 hover:text-purple-700 dark:text-purple-400"
                    title="Kelola Pertanyaan"
                  >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                    </svg>
                  </button>
                  <button
                    v-if="survey.status === 'draft'"
                    @click="handlePublish(survey)"
                    class="text-green-600 hover:text-green-700 dark:text-green-400"
                    title="Terbitkan"
                  >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                  </button>
                  <button
                    v-if="survey.status === 'published'"
                    @click="handleClose(survey)"
                    class="text-orange-600 hover:text-orange-700 dark:text-orange-400"
                    title="Tutup"
                  >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                    </svg>
                  </button>
                  <button
                    @click="$router.push(`/surveys/${survey.id}/analytics`)"
                    class="text-indigo-600 hover:text-indigo-700 dark:text-indigo-400"
                    title="Lihat Analitik"
                  >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                  </button>
                  <button
                    @click="handleDuplicate(survey)"
                    class="text-cyan-600 hover:text-cyan-700 dark:text-cyan-400"
                    title="Duplikat"
                  >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                    </svg>
                  </button>
                  <button
                    @click="confirmDelete(survey)"
                    class="text-red-600 hover:text-red-700 dark:text-red-400"
                    title="Hapus"
                  >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="pagination.total > 0" class="flex items-center justify-between border-t border-gray-200 px-6 py-4 dark:border-gray-700">
        <div class="text-sm text-gray-700 dark:text-gray-400">
          Menampilkan <span class="font-medium text-gray-900 dark:text-white">{{ pagination.from }}</span> sampai
          <span class="font-medium text-gray-900 dark:text-white">{{ pagination.to }}</span> dari
          <span class="font-medium text-gray-900 dark:text-white">{{ pagination.total }}</span> kuesioner
        </div>
        <div class="flex items-center space-x-2">
          <button
            @click="changePage(pagination.current_page - 1)"
            :disabled="pagination.current_page === 1"
            class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700"
          >
            Previous
          </button>
          <button
            v-for="page in visiblePages"
            :key="page"
            @click="changePage(page)"
            :class="[
              'rounded-lg border px-3 py-2 text-sm font-medium',
              page === pagination.current_page
                ? 'border-blue-600 bg-blue-600 text-white hover:bg-blue-700'
                : 'border-gray-300 bg-white text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700'
            ]"
          >
            {{ page }}
          </button>
          <button
            @click="changePage(pagination.current_page + 1)"
            :disabled="pagination.current_page === pagination.last_page"
            class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700"
          >
            Next
          </button>
        </div>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import MainLayout from '@/layouts/MainLayout.vue';
import { useSurveyApi } from '@/composables/useSurveyApi';

const router = useRouter();

const { getSurveys, getSurveyStatistics, deleteSurvey, publishSurvey, closeSurvey, duplicateSurvey, loading } = useSurveyApi();

const surveys = ref([]);
const statistics = ref(null);

const filters = ref({
  search: '',
  type: '',
  status: '',
});

const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 15,
  total: 0,
  from: 0,
  to: 0,
});

const visiblePages = computed(() => {
  const pages = [];
  const current = pagination.value.current_page;
  const last = pagination.value.last_page;

  let start = Math.max(1, current - 2);
  let end = Math.min(last, current + 2);

  for (let i = start; i <= end; i++) {
    pages.push(i);
  }

  return pages;
});

let searchTimeout = null;
const debouncedSearch = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    fetchSurveys();
  }, 500);
};

const fetchSurveys = async (page = 1) => {
  try {
    const params = {
      page,
      per_page: pagination.value.per_page,
      ...filters.value,
    };

    Object.keys(params).forEach(key => {
      if (params[key] === '') delete params[key];
    });

    const response = await getSurveys(params);

    if (response.success) {
      surveys.value = response.data;
      pagination.value = response.meta;
    }
  } catch (error) {
    console.error('Failed to fetch surveys:', error);
    alert('Gagal memuat data kuesioner: ' + (error.response?.data?.message || error.message));
  }
};

const fetchStatistics = async () => {
  try {
    const response = await getSurveyStatistics();
    if (response.success) {
      statistics.value = response.data;
    }
  } catch (error) {
    console.error('Failed to fetch statistics:', error);
  }
};

const changePage = (page) => {
  if (page >= 1 && page <= pagination.value.last_page) {
    fetchSurveys(page);
  }
};

const handlePublish = async (survey) => {
  if (confirm(`Apakah Anda yakin ingin menerbitkan kuesioner "${survey.title}"?`)) {
    try {
      const response = await publishSurvey(survey.id);
      if (response.success) {
        alert('Kuesioner berhasil diterbitkan');
        fetchSurveys(pagination.value.current_page);
        fetchStatistics();
      }
    } catch (error) {
      alert('Gagal menerbitkan kuesioner: ' + (error.response?.data?.message || error.message));
    }
  }
};

const handleClose = async (survey) => {
  if (confirm(`Apakah Anda yakin ingin menutup kuesioner "${survey.title}"?`)) {
    try {
      const response = await closeSurvey(survey.id);
      if (response.success) {
        alert('Kuesioner berhasil ditutup');
        fetchSurveys(pagination.value.current_page);
        fetchStatistics();
      }
    } catch (error) {
      alert('Gagal menutup kuesioner: ' + (error.response?.data?.message || error.message));
    }
  }
};

const handleDuplicate = async (survey) => {
  if (confirm(`Apakah Anda yakin ingin menduplikat kuesioner "${survey.title}"?`)) {
    try {
      const response = await duplicateSurvey(survey.id);
      if (response.success) {
        alert('Kuesioner berhasil diduplikat');
        fetchSurveys(pagination.value.current_page);
        fetchStatistics();
      }
    } catch (error) {
      alert('Gagal menduplikat kuesioner: ' + (error.response?.data?.message || error.message));
    }
  }
};

const confirmDelete = async (survey) => {
  if (confirm(`Apakah Anda yakin ingin menghapus kuesioner "${survey.title}"? Tindakan ini tidak dapat dibatalkan.`)) {
    try {
      const response = await deleteSurvey(survey.id);
      if (response.success) {
        alert('Kuesioner berhasil dihapus');
        fetchSurveys(pagination.value.current_page);
        fetchStatistics();
      }
    } catch (error) {
      alert('Gagal menghapus kuesioner: ' + (error.response?.data?.message || error.message));
    }
  }
};

const formatDate = (date) => {
  if (!date) return '-';
  return new Date(date).toLocaleDateString('id-ID', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  });
};

const getTypeLabel = (type) => {
  const labels = {
    internal: 'Internal',
    external: 'Eksternal',
    public: 'Publik'
  };
  return labels[type] || type;
};

const getTypeBadgeClass = (type) => {
  const classes = {
    internal: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
    external: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
    public: 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300'
  };
  return classes[type] || 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300';
};

const getStatusLabel = (status) => {
  const labels = {
    draft: 'Draft',
    published: 'Terbit',
    closed: 'Ditutup'
  };
  return labels[status] || status;
};

const getStatusBadgeClass = (status) => {
  const classes = {
    draft: 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300',
    published: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
    closed: 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300'
  };
  return classes[status] || 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300';
};

onMounted(() => {
  fetchStatistics();
  fetchSurveys();
});
</script>
