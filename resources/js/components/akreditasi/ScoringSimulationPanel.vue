<template>
  <div class="space-y-6">
    <!-- Loading State -->
    <div v-if="loading" class="flex items-center justify-center py-12">
      <div class="text-center">
        <svg class="animate-spin h-12 w-12 text-primary-600 mx-auto mb-4" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <p class="text-gray-600 dark:text-gray-400">Menghitung skor prediksi...</p>
      </div>
    </div>

    <!-- Main Content -->
    <div v-else-if="scoringData" class="space-y-6">
      <!-- Header with Grade -->
      <div class="bg-gradient-to-r from-primary-500 to-primary-600 dark:from-primary-700 dark:to-primary-800 rounded-lg p-6 text-white">
        <div class="flex items-center justify-between">
          <div>
            <h2 class="text-3xl font-bold mb-2">{{ scoringData.summary.predicted_score }}</h2>
            <p class="text-primary-100">Skor Prediksi Akreditasi</p>
          </div>
          <div class="text-right">
            <div :class="getGradeColorClass(scoringData.summary.grade)" class="text-4xl font-bold px-4 py-2 bg-white bg-opacity-20 rounded-lg">
              {{ scoringData.summary.grade }}
            </div>
            <p class="mt-2 text-sm text-primary-100">{{ scoringData.summary.grade_label }}</p>
          </div>
        </div>
        <p class="mt-4 text-sm text-primary-100">{{ scoringData.summary.grade_description }}</p>
      </div>

      <!-- Summary Cards -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
          <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">Total Butir</div>
          <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ scoringData.summary.total_butir }}</div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
          <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">Butir Selesai</div>
          <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ scoringData.summary.completed_butir }}</div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
          <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">Total Bobot</div>
          <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ scoringData.summary.total_weight }}</div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
          <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">Gap ke Maksimal</div>
          <div class="text-2xl font-bold text-orange-600 dark:text-orange-400">{{ scoringData.summary.gap_to_max }}</div>
        </div>
      </div>

      <!-- Recommendations -->
      <div v-if="scoringData.recommendations && scoringData.recommendations.length > 0" class="bg-white dark:bg-gray-800 rounded-lg p-6 border border-gray-200 dark:border-gray-700">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
          <svg class="h-5 w-5 mr-2 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
          </svg>
          Rekomendasi
        </h3>

        <div class="space-y-3">
          <div
            v-for="(rec, index) in scoringData.recommendations"
            :key="index"
            :class="getRecommendationClass(rec.priority)"
            class="border-l-4 p-4 rounded-r-lg"
          >
            <div class="flex items-start">
              <div :class="getPriorityBadgeClass(rec.priority)" class="px-2 py-1 rounded text-xs font-semibold mr-3">
                {{ rec.priority.toUpperCase() }}
              </div>
              <div class="flex-1">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-1">{{ rec.title }}</h4>
                <p class="text-sm text-gray-700 dark:text-gray-300">{{ rec.description }}</p>

                <div v-if="rec.target_score" class="mt-2 text-sm">
                  <span class="text-gray-600 dark:text-gray-400">Target Skor:</span>
                  <span class="font-semibold text-gray-900 dark:text-white ml-1">{{ rec.target_score }}</span>
                  <span class="text-gray-600 dark:text-gray-400 ml-2">(Butuh +{{ rec.score_needed }} poin)</span>
                </div>

                <div v-if="rec.butir_kode && rec.butir_kode.length > 0" class="mt-2">
                  <span class="text-xs text-gray-600 dark:text-gray-400">Fokus pada butir:</span>
                  <div class="flex flex-wrap gap-1 mt-1">
                    <span
                      v-for="(kode, idx) in rec.butir_kode.slice(0, 3)"
                      :key="idx"
                      class="inline-block px-2 py-1 bg-gray-100 dark:bg-gray-700 text-xs rounded"
                    >
                      {{ kode }}
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Category Scores -->
      <div class="bg-white dark:bg-gray-800 rounded-lg p-6 border border-gray-200 dark:border-gray-700">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Skor per Kategori</h3>

        <div class="space-y-4">
          <div
            v-for="kategori in scoringData.kategori_scores"
            :key="kategori.kategori"
            class="border-b border-gray-200 dark:border-gray-700 pb-4 last:border-0"
          >
            <div class="flex items-center justify-between mb-2">
              <span class="font-medium text-gray-900 dark:text-white">{{ kategori.kategori }}</span>
              <span class="text-sm font-semibold text-primary-600 dark:text-primary-400">
                {{ kategori.score }}%
              </span>
            </div>

            <!-- Progress Bar -->
            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3">
              <div
                :class="getScoreColor(kategori.score)"
                class="h-3 rounded-full transition-all duration-500"
                :style="{ width: kategori.score + '%' }"
              ></div>
            </div>

            <div class="flex justify-between text-xs text-gray-600 dark:text-gray-400 mt-1">
              <span>{{ kategori.completed_butir }}/{{ kategori.butir_count }} butir selesai</span>
              <span>Bobot: {{ kategori.total_weight }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Critical Butir -->
      <div v-if="scoringData.critical_butir && scoringData.critical_butir.length > 0" class="bg-white dark:bg-gray-800 rounded-lg p-6 border border-gray-200 dark:border-gray-700">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
          <svg class="h-5 w-5 mr-2 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
          </svg>
          Butir Prioritas (Bobot Tinggi)
        </h3>

        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-900">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Kode</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Nama Butir</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Kategori</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Bobot</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Completion</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
              </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
              <tr v-for="butir in scoringData.critical_butir.slice(0, 10)" :key="butir.id">
                <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white">{{ butir.kode }}</td>
                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ butir.nama }}</td>
                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">{{ butir.kategori }}</td>
                <td class="px-4 py-3 text-sm">
                  <span class="font-semibold text-primary-600 dark:text-primary-400">{{ butir.bobot }}</span>
                </td>
                <td class="px-4 py-3 text-sm">
                  <span :class="getCompletionClass(butir.completion_percentage)">
                    {{ butir.completion_percentage }}%
                  </span>
                </td>
                <td class="px-4 py-3 text-sm">
                  <span :class="getStatusBadgeClass(butir.status)" class="px-2 py-1 rounded text-xs font-medium">
                    {{ getStatusLabel(butir.status) }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Refresh Button -->
      <div class="flex justify-center">
        <button
          type="button"
          @click="loadScoringData"
          class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors"
        >
          <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
          </svg>
          Refresh Skor
        </button>
      </div>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-6 text-center">
      <svg class="h-12 w-12 text-red-600 dark:text-red-400 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
      </svg>
      <p class="text-red-800 dark:text-red-300 font-medium">{{ error }}</p>
      <button
        @click="loadScoringData"
        class="mt-4 text-sm text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-200"
      >
        Coba lagi
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const props = defineProps({
  periodeId: {
    type: Number,
    required: true
  }
})

const loading = ref(false)
const error = ref(null)
const scoringData = ref(null)

const loadScoringData = async () => {
  loading.value = true
  error.value = null

  try {
    const response = await axios.get(`/api/periode-akreditasi/${props.periodeId}/scoring-simulation`)

    if (response.data.success) {
      scoringData.value = response.data.data
    } else {
      error.value = response.data.message || 'Gagal memuat data scoring'
    }
  } catch (err) {
    console.error('Failed to load scoring data:', err)
    error.value = err.response?.data?.message || 'Terjadi kesalahan saat memuat data'
  } finally {
    loading.value = false
  }
}

const getGradeColorClass = (grade) => {
  const colors = {
    'A': 'text-green-600',
    'B': 'text-blue-600',
    'C': 'text-yellow-600',
    'TT': 'text-red-600'
  }
  return colors[grade] || 'text-gray-600'
}

const getScoreColor = (score) => {
  if (score >= 90) return 'bg-green-500'
  if (score >= 75) return 'bg-blue-500'
  if (score >= 60) return 'bg-yellow-500'
  return 'bg-red-500'
}

const getRecommendationClass = (priority) => {
  const classes = {
    'critical': 'bg-red-50 dark:bg-red-900/20 border-red-500',
    'high': 'bg-orange-50 dark:bg-orange-900/20 border-orange-500',
    'medium': 'bg-yellow-50 dark:bg-yellow-900/20 border-yellow-500',
    'low': 'bg-blue-50 dark:bg-blue-900/20 border-blue-500'
  }
  return classes[priority] || 'bg-gray-50 dark:bg-gray-800 border-gray-500'
}

const getPriorityBadgeClass = (priority) => {
  const classes = {
    'critical': 'bg-red-600 text-white',
    'high': 'bg-orange-600 text-white',
    'medium': 'bg-yellow-600 text-white',
    'low': 'bg-blue-600 text-white'
  }
  return classes[priority] || 'bg-gray-600 text-white'
}

const getCompletionClass = (percentage) => {
  if (percentage >= 100) return 'text-green-600 dark:text-green-400 font-semibold'
  if (percentage >= 75) return 'text-blue-600 dark:text-blue-400 font-semibold'
  if (percentage >= 50) return 'text-yellow-600 dark:text-yellow-400 font-semibold'
  return 'text-red-600 dark:text-red-400 font-semibold'
}

const getStatusBadgeClass = (status) => {
  const classes = {
    'approved': 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
    'review': 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
    'submitted': 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300',
    'revision': 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300',
    'draft': 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
    'not_started': 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

const getStatusLabel = (status) => {
  const labels = {
    'approved': 'Disetujui',
    'review': 'Review',
    'submitted': 'Diajukan',
    'revision': 'Revisi',
    'draft': 'Draft',
    'not_started': 'Belum Dikerjakan'
  }
  return labels[status] || status
}

onMounted(() => {
  loadScoringData()
})

defineExpose({
  refresh: loadScoringData
})
</script>
