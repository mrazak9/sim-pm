<template>
  <MainLayout>
    <div class="space-y-6">
      <!-- Survey Info -->
      <div class="rounded-lg border border-gray-200 bg-white p-6 shadow dark:border-gray-700 dark:bg-gray-800">
        <div class="flex items-start justify-between">
          <div>
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ survey.title }}</h2>
            <p v-if="survey.description" class="mt-2 text-sm text-gray-600 dark:text-gray-400">
              {{ survey.description }}
            </p>
            <div class="mt-3 flex gap-4">
              <span :class="getStatusBadgeClass(survey.status)" class="inline-flex rounded-full px-3 py-1 text-xs font-medium">
                {{ getStatusLabel(survey.status) }}
              </span>
              <span :class="getTypeBadgeClass(survey.type)" class="inline-flex rounded-full px-3 py-1 text-xs font-medium">
                {{ getTypeLabel(survey.type) }}
              </span>
            </div>
          </div>
          <button
            @click="$router.push('/surveys')"
            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
          >
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="flex items-center justify-center py-12">
        <svg class="h-8 w-8 animate-spin text-blue-600" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
      </div>

      <template v-else-if="analytics">
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
          <div class="rounded-lg border border-gray-200 bg-white p-6 shadow dark:border-gray-700 dark:bg-gray-800">
            <div class="flex items-center">
              <div class="flex-shrink-0 rounded-full bg-blue-100 p-3 dark:bg-blue-900/20">
                <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Respons</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ analytics.total_responses || 0 }}</p>
              </div>
            </div>
          </div>

          <div class="rounded-lg border border-gray-200 bg-white p-6 shadow dark:border-gray-700 dark:bg-gray-800">
            <div class="flex items-center">
              <div class="flex-shrink-0 rounded-full bg-green-100 p-3 dark:bg-green-900/20">
                <svg class="h-6 w-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Tingkat Penyelesaian</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ analytics.completion_rate || 0 }}%</p>
              </div>
            </div>
          </div>

          <div class="rounded-lg border border-gray-200 bg-white p-6 shadow dark:border-gray-700 dark:bg-gray-800">
            <div class="flex items-center">
              <div class="flex-shrink-0 rounded-full bg-purple-100 p-3 dark:bg-purple-900/20">
                <svg class="h-6 w-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Rata-rata Waktu</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ formatTime(analytics.avg_completion_time) }}</p>
              </div>
            </div>
          </div>

          <div class="rounded-lg border border-gray-200 bg-white p-6 shadow dark:border-gray-700 dark:bg-gray-800">
            <div class="flex items-center">
              <div class="flex-shrink-0 rounded-full bg-orange-100 p-3 dark:bg-orange-900/20">
                <svg class="h-6 w-6 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                </svg>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Tingkat Respons</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ analytics.response_rate || 0 }}%</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Question Analytics -->
        <div class="space-y-4">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Analitik Per Pertanyaan</h3>

          <div
            v-for="(question, index) in analytics.questions"
            :key="question.id"
            class="rounded-lg border border-gray-200 bg-white p-6 shadow dark:border-gray-700 dark:bg-gray-800"
          >
            <div class="mb-4">
              <h4 class="font-medium text-gray-900 dark:text-white">
                {{ index + 1 }}. {{ question.question_text }}
              </h4>
              <span :class="getQuestionTypeBadgeClass(question.type)" class="mt-2 inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium">
                {{ getQuestionTypeLabel(question.type) }}
              </span>
            </div>

            <!-- Multiple Choice Questions (radio, checkbox, dropdown) -->
            <div v-if="['radio', 'checkbox', 'dropdown'].includes(question.type) && question.options">
              <div class="space-y-3">
                <div v-for="option in question.options" :key="option.value" class="space-y-2">
                  <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ option.label }}</span>
                    <div class="flex items-center gap-2">
                      <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ option.count }}</span>
                      <span class="text-sm text-gray-500 dark:text-gray-400">({{ option.percentage }}%)</span>
                    </div>
                  </div>
                  <div class="h-4 w-full overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                    <div
                      class="h-full rounded-full bg-blue-600 transition-all dark:bg-blue-500"
                      :style="{ width: option.percentage + '%' }"
                    ></div>
                  </div>
                </div>
              </div>

              <!-- Total Responses for this question -->
              <div class="mt-4 border-t border-gray-200 pt-3 dark:border-gray-700">
                <p class="text-sm text-gray-600 dark:text-gray-400">
                  Total Respons: <span class="font-semibold text-gray-900 dark:text-white">{{ question.total_responses }}</span>
                </p>
              </div>
            </div>

            <!-- Text Questions (text, textarea) -->
            <div v-else-if="['text', 'textarea'].includes(question.type) && question.answers">
              <div class="space-y-2">
                <p class="mb-3 text-sm font-medium text-gray-700 dark:text-gray-300">
                  Jawaban ({{ question.answers.length }}):
                </p>
                <div v-if="question.answers.length === 0" class="text-center text-gray-500 dark:text-gray-400">
                  Belum ada jawaban
                </div>
                <div v-else class="max-h-96 space-y-2 overflow-y-auto">
                  <div
                    v-for="answer in question.answers"
                    :key="answer.id"
                    class="rounded-lg border border-gray-200 bg-gray-50 p-3 dark:border-gray-700 dark:bg-gray-700"
                  >
                    <p class="text-sm text-gray-900 dark:text-white">{{ answer.text }}</p>
                    <p v-if="answer.submitted_at" class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                      {{ formatDate(answer.submitted_at) }}
                    </p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Rating Questions -->
            <div v-else-if="question.type === 'rating'" class="space-y-4">
              <div class="rounded-lg bg-blue-50 p-6 text-center dark:bg-blue-900/20">
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Rata-rata Rating</p>
                <p class="mt-2 text-4xl font-bold text-blue-600 dark:text-blue-400">
                  {{ question.average_rating ? question.average_rating.toFixed(1) : '0.0' }}
                </p>
                <div class="mt-2 flex items-center justify-center gap-1">
                  <svg
                    v-for="star in 5"
                    :key="star"
                    class="h-5 w-5"
                    :class="star <= Math.round(question.average_rating) ? 'text-yellow-400' : 'text-gray-300 dark:text-gray-600'"
                    fill="currentColor"
                    viewBox="0 0 20 20"
                  >
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                  </svg>
                </div>
              </div>

              <!-- Rating Distribution -->
              <div v-if="question.rating_distribution" class="space-y-2">
                <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Distribusi Rating:</p>
                <div
                  v-for="rating in [5, 4, 3, 2, 1]"
                  :key="rating"
                  class="flex items-center gap-3"
                >
                  <span class="w-8 text-sm font-medium text-gray-700 dark:text-gray-300">{{ rating }}</span>
                  <svg class="h-4 w-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                  </svg>
                  <div class="flex flex-1 items-center gap-2">
                    <div class="h-4 flex-1 overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                      <div
                        class="h-full rounded-full bg-yellow-400 transition-all"
                        :style="{ width: getRatingPercentage(question.rating_distribution, rating) + '%' }"
                      ></div>
                    </div>
                    <span class="w-16 text-sm text-gray-600 dark:text-gray-400">
                      {{ question.rating_distribution[rating] || 0 }}
                      ({{ getRatingPercentage(question.rating_distribution, rating) }}%)
                    </span>
                  </div>
                </div>
              </div>

              <!-- Total Responses -->
              <div class="border-t border-gray-200 pt-3 dark:border-gray-700">
                <p class="text-sm text-gray-600 dark:text-gray-400">
                  Total Respons: <span class="font-semibold text-gray-900 dark:text-white">{{ question.total_responses }}</span>
                </p>
              </div>
            </div>

            <!-- No Data -->
            <div v-else class="text-center text-gray-500 dark:text-gray-400">
              <p>Belum ada data untuk pertanyaan ini</p>
            </div>
          </div>
        </div>
      </template>

      <!-- No Data State -->
      <div v-else-if="!loading" class="rounded-lg border border-gray-200 bg-white p-12 text-center shadow dark:border-gray-700 dark:bg-gray-800">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
        </svg>
        <p class="mt-4 text-gray-600 dark:text-gray-400">Belum ada data analitik</p>
        <p class="mt-2 text-sm text-gray-500 dark:text-gray-500">Data akan muncul setelah ada respons dari kuesioner</p>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import MainLayout from '@/layouts/MainLayout.vue';
import { useSurveyApi } from '@/composables/useSurveyApi';

const router = useRouter();
const route = useRoute();

const { getSurvey, getSurveyAnalytics, loading } = useSurveyApi();

const survey = ref({});
const analytics = ref(null);

const fetchSurvey = async () => {
  try {
    const response = await getSurvey(route.params.id);
    if (response.success) {
      survey.value = response.data;
    }
  } catch (error) {
    console.error('Failed to fetch survey:', error);
    alert('Gagal memuat data kuesioner');
    router.push('/surveys');
  }
};

const fetchAnalytics = async () => {
  try {
    const response = await getSurveyAnalytics(route.params.id);
    if (response.success) {
      analytics.value = response.data;
    }
  } catch (error) {
    console.error('Failed to fetch analytics:', error);
    alert('Gagal memuat analitik: ' + (error.response?.data?.message || error.message));
  }
};

const formatTime = (seconds) => {
  if (!seconds) return '0 menit';

  const minutes = Math.floor(seconds / 60);
  const remainingSeconds = seconds % 60;

  if (minutes === 0) {
    return `${remainingSeconds} detik`;
  } else if (remainingSeconds === 0) {
    return `${minutes} menit`;
  } else {
    return `${minutes}m ${remainingSeconds}s`;
  }
};

const formatDate = (date) => {
  if (!date) return '-';
  return new Date(date).toLocaleDateString('id-ID', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};

const getRatingPercentage = (distribution, rating) => {
  if (!distribution) return 0;

  const total = Object.values(distribution).reduce((sum, count) => sum + count, 0);
  if (total === 0) return 0;

  const count = distribution[rating] || 0;
  return Math.round((count / total) * 100);
};

const getQuestionTypeLabel = (type) => {
  const labels = {
    text: 'Teks Pendek',
    textarea: 'Teks Panjang',
    radio: 'Pilihan Tunggal',
    checkbox: 'Pilihan Ganda',
    dropdown: 'Dropdown',
    rating: 'Rating'
  };
  return labels[type] || type;
};

const getQuestionTypeBadgeClass = (type) => {
  const classes = {
    text: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
    textarea: 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-300',
    radio: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
    checkbox: 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300',
    dropdown: 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300',
    rating: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300'
  };
  return classes[type] || 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300';
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
  fetchSurvey();
  fetchAnalytics();
});
</script>
